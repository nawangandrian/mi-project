"""
train_xgb.py — Script training XGBoost untuk Mi Store
Menggantikan Random Forest (train_rf.py) dengan XGBoost yang lebih akurat
untuk data terbatas (< 12 bulan per produk).

Keunggulan XGBoost vs Random Forest untuk kasus ini:
  - Gradient boosting mengoreksi error secara iteratif → akurasi lebih tinggi
  - Regularisasi L1/L2 bawaan → lebih tahan overfitting pada data sedikit
  - early_stopping_rounds → otomatis henti sebelum overfit
  - Lebih cepat (parallel boosting) dibanding RF 500 estimator

Konsistensi kolom dengan train_rf.py (tidak ada breaking change di DB):
  train_rf.py                    train_xgb.py
  ─────────────────────────────────────────────────────
  df['nama_produk']              df['nama_produk']        ← sama
  df['produk_encoded']           df['produk_encoded']     ← sama
  FEATURES (16 kolom)            FEATURES (16 kolom)      ← sama
  log1p target, expm1 output     log1p target, expm1 output ← sama
  80:20 quantile split           80:20 quantile split     ← sama
  RandomizedSearchCV 50 iter     RandomizedSearchCV 40 iter (XGB faster)
  model_rf_optimized.pkl         model_xgb_optimized.pkl  ← nama beda

Argumen:
  --training-id  : ID baris model_training (WAJIB, dibuat PHP)
  --db-host / --db-port / --db-name / --db-user / --db-pass
  --model-dir    : folder simpan .pkl & diagram
  --log-file     : path file log
"""

import sys, os, json, argparse, logging, traceback
from datetime import datetime
from pathlib import Path

# ── Argparse ──────────────────────────────────────────────────────────────────
parser = argparse.ArgumentParser()
parser.add_argument('--training-id', required=True, type=int)
parser.add_argument('--db-host',   default=os.getenv('DB_HOST',   '127.0.0.1'))
parser.add_argument('--db-port',   default=int(os.getenv('DB_PORT', 3306)), type=int)
parser.add_argument('--db-name',   default=os.getenv('DB_NAME',   'mi_store'))
parser.add_argument('--db-user',   default=os.getenv('DB_USER',   'root'))
parser.add_argument('--db-pass',   default=os.getenv('DB_PASS',   ''))
parser.add_argument('--model-dir', default=os.getenv('MODEL_DIR',
    str(Path(__file__).parent / 'models')))
parser.add_argument('--log-file',  default=os.getenv('LOG_FILE',
    str(Path(__file__).parent / 'logs' / 'train.log')))
args = parser.parse_args()

MODEL_DIR   = Path(args.model_dir)
LOG_FILE    = Path(args.log_file)
TRAINING_ID = args.training_id

DIAGRAM_DIR = MODEL_DIR / 'diagrams' / f'v{TRAINING_ID}'
MODEL_DIR.mkdir(parents=True, exist_ok=True)
LOG_FILE.parent.mkdir(parents=True, exist_ok=True)
DIAGRAM_DIR.mkdir(parents=True, exist_ok=True)

# ── Logging ───────────────────────────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    handlers=[
        logging.FileHandler(LOG_FILE, encoding='utf-8'),
        logging.StreamHandler(sys.stdout),
    ]
)
log = logging.getLogger(__name__)
STATUS_FILE = MODEL_DIR / 'train_status.json'


# ── Helpers DB & Status ───────────────────────────────────────────────────────
def write_status(status: str, message: str, extra: dict = None):
    data = {
        'status':      status,
        'training_id': TRAINING_ID,
        'message':     message,
        'updated_at':  datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
    }
    if extra:
        data.update(extra)
    STATUS_FILE.write_text(
        json.dumps(data, ensure_ascii=False, indent=2), encoding='utf-8')


def get_conn():
    import pymysql
    return pymysql.connect(
        host=args.db_host, port=args.db_port,
        db=args.db_name,   user=args.db_user,
        password=args.db_pass, charset='utf8mb4',
        autocommit=True,
        init_command="SET time_zone='+07:00'",
    )


def db_log(conn, level: str, pesan: str):
    try:
        with conn.cursor() as cur:
            cur.execute(
                "INSERT INTO model_training_log "
                "(training_id, level, pesan, logged_at) VALUES (%s,%s,%s,%s)",
                (TRAINING_ID, level, pesan,
                 datetime.now().strftime('%Y-%m-%d %H:%M:%S'))
            )
    except Exception:
        pass


def db_update_error(conn, msg: str):
    try:
        with conn.cursor() as cur:
            cur.execute(
                "UPDATE model_training "
                "SET status='error', selesai_at=NOW(), "
                "pesan_error=%s, updated_at=NOW() WHERE id=%s",
                (msg[:2000], TRAINING_ID)
            )
    except Exception:
        pass


def simpan_feature_importance(conn, fi_dict: dict):
    items = sorted(fi_dict.items(), key=lambda x: x[1], reverse=True)
    rows  = [(TRAINING_ID, name, round(imp, 8), rank + 1)
             for rank, (name, imp) in enumerate(items)]
    with conn.cursor() as cur:
        cur.execute(
            "DELETE FROM model_feature_importance WHERE training_id=%s",
            (TRAINING_ID,))
        cur.executemany(
            "INSERT INTO model_feature_importance "
            "(training_id, nama_fitur, importance, ranking) VALUES (%s,%s,%s,%s)",
            rows)


# ── Diagram helpers ───────────────────────────────────────────────────────────
def save_diagram(fig, name: str) -> str:
    path = DIAGRAM_DIR / f'{name}.png'
    fig.savefig(path, dpi=150, bbox_inches='tight', facecolor='white')
    return str(path)


def plot_actual_vs_pred(y_true, y_pred, title, mae, rmse, r2, mape):
    import matplotlib.pyplot as plt
    fig, ax = plt.subplots(figsize=(6, 6))
    ax.scatter(y_true, y_pred, alpha=0.55, edgecolors='none',
               color='steelblue', s=25)
    lo = min(float(y_true.min()), float(y_pred.min())) * 0.95
    hi = max(float(y_true.max()), float(y_pred.max())) * 1.05
    ax.plot([lo, hi], [lo, hi], '--', color='red', lw=1.5, label='Ideal')
    txt = f'MAE  : {mae:.2f}\nRMSE : {rmse:.2f}\nR²   : {r2:.3f}\nMAPE : {mape:.1f}%'
    ax.text(0.05, 0.95, txt, transform=ax.transAxes, fontsize=9,
            verticalalignment='top',
            bbox=dict(boxstyle='round', facecolor='lightyellow', alpha=0.7))
    ax.set_title(title, fontsize=11, fontweight='bold')
    ax.set_xlabel('Aktual'); ax.set_ylabel('Prediksi')
    ax.legend(fontsize=9); ax.grid(True, alpha=0.25)
    plt.tight_layout()
    return fig


def plot_residual(y_true, y_pred, title):
    import matplotlib.pyplot as plt
    residual = y_true.values - y_pred
    fig, axes = plt.subplots(1, 2, figsize=(12, 4))
    axes[0].scatter(y_pred, residual, alpha=0.5, s=20,
                    color='darkorange', edgecolors='none')
    axes[0].axhline(0, color='red', linestyle='--', lw=1.5)
    axes[0].set_title(f'Residual Plot — {title}', fontweight='bold')
    axes[0].set_xlabel('Prediksi'); axes[0].set_ylabel('Error (Aktual − Prediksi)')
    axes[0].grid(True, alpha=0.25)
    axes[1].hist(residual, bins=30, color='steelblue',
                 edgecolor='white', alpha=0.85)
    axes[1].axvline(0, color='red', linestyle='--', lw=1.5)
    axes[1].set_title(f'Distribusi Residual — {title}', fontweight='bold')
    axes[1].set_xlabel('Error'); axes[1].set_ylabel('Frekuensi')
    axes[1].grid(True, alpha=0.25)
    plt.tight_layout()
    return fig


def plot_feature_importance(feat_imp_df):
    import matplotlib.pyplot as plt
    import seaborn as sns
    top = feat_imp_df.head(16)
    fig, ax = plt.subplots(figsize=(9, 5))
    palette = sns.color_palette('viridis', len(top))
    bars = ax.barh(top['feature'][::-1], top['importance'][::-1],
                   color=palette[::-1], edgecolor='white', height=0.65)
    for bar, val in zip(bars, top['importance'][::-1]):
        ax.text(bar.get_width() + 0.001, bar.get_y() + bar.get_height() / 2,
                f'{val:.4f}', va='center', ha='left', fontsize=8)
    ax.set_title('Feature Importance — XGBoost',
                 fontsize=11, fontweight='bold')
    ax.set_xlabel('Importance (gain)')
    ax.grid(True, axis='x', alpha=0.25)
    plt.tight_layout()
    return fig


def plot_actual_vs_time(df_plot, title):
    import matplotlib.pyplot as plt
    agg = (df_plot.groupby('time_idx')[['aktual', 'prediksi']]
           .mean().reset_index())
    fig, ax = plt.subplots(figsize=(12, 4))
    ax.plot(agg['time_idx'], agg['aktual'],   label='Aktual',
            lw=1.8, color='steelblue')
    ax.plot(agg['time_idx'], agg['prediksi'], label='Prediksi',
            lw=1.5, color='tomato', linestyle='--')
    ax.set_title(
        f'Aktual vs Prediksi (rata-rata semua produk) — {title}',
        fontsize=10, fontweight='bold')
    ax.set_xlabel('Time Index (tahun×12 + bulan)')
    ax.set_ylabel('Qty (rata-rata)')
    ax.legend(fontsize=9); ax.grid(True, alpha=0.25)
    plt.tight_layout()
    return fig


def plot_top_products(df_monthly, n=10):
    import matplotlib.pyplot as plt
    top = (df_monthly.groupby('nama_produk')['qty_total']
           .sum().nlargest(n).reset_index())
    fig, ax = plt.subplots(figsize=(10, 4))
    ax.bar(range(len(top)), top['qty_total'],
           color='steelblue', edgecolor='white')
    ax.set_xticks(range(len(top)))
    ax.set_xticklabels(top['nama_produk'], rotation=35, ha='right', fontsize=8)
    ax.set_title(f'Top {n} Produk Terlaris (Total Qty)',
                 fontsize=10, fontweight='bold')
    ax.set_ylabel('Total Qty')
    ax.grid(True, axis='y', alpha=0.25)
    plt.tight_layout()
    return fig


def plot_monthly_trend(df_monthly):
    import matplotlib.pyplot as plt
    import matplotlib.ticker as mticker
    agg = (df_monthly.groupby(['tahun', 'bulan'])['qty_total']
           .sum().reset_index())
    agg['label'] = (agg['tahun'].astype(str) + '-'
                    + agg['bulan'].astype(str).str.zfill(2))
    fig, ax = plt.subplots(figsize=(13, 4))
    ax.fill_between(range(len(agg)), agg['qty_total'],
                    alpha=0.18, color='steelblue')
    ax.plot(range(len(agg)), agg['qty_total'], lw=2,
            color='steelblue', marker='o', markersize=4)
    step = max(1, len(agg) // 12)
    ax.set_xticks(range(0, len(agg), step))
    ax.set_xticklabels(agg['label'].iloc[::step], rotation=40,
                       ha='right', fontsize=8)
    ax.yaxis.set_major_formatter(
        mticker.FuncFormatter(lambda x, _: f'{int(x):,}'))
    ax.set_title('Tren Penjualan Bulanan (Semua Produk)',
                 fontsize=10, fontweight='bold')
    ax.set_ylabel('Total Qty')
    ax.grid(True, alpha=0.25)
    plt.tight_layout()
    return fig


# ── Metrik evaluasi ───────────────────────────────────────────────────────────
def hitung_metrics(y_true_raw, y_pred_log, np):
    """Konversi prediksi log → skala asli, lalu hitung MAE/RMSE/R²/MAPE."""
    import sklearn.metrics as skm
    y_pred_raw = np.maximum(np.expm1(y_pred_log), 0)
    mae  = float(skm.mean_absolute_error(y_true_raw, y_pred_raw))
    rmse = float(np.sqrt(skm.mean_squared_error(y_true_raw, y_pred_raw)))
    r2   = float(skm.r2_score(y_true_raw, y_pred_raw))
    mape = float(skm.mean_absolute_percentage_error(y_true_raw, y_pred_raw)) * 100
    return mae, rmse, r2, mape, y_pred_raw


# ══════════════════════════════════════════════════════════════════════════════
# MAIN
# ══════════════════════════════════════════════════════════════════════════════
def main():
    write_status('running', 'Memulai proses training XGBoost…')
    log.info(f'=== Training XGBoost ID={TRAINING_ID} dimulai ===')

    # ── Import dependensi ─────────────────────────────────────────────────────
    try:
        import pandas as pd
        import numpy as np
        import pymysql
        import joblib
        import matplotlib
        matplotlib.use('Agg')
        import matplotlib.pyplot as plt
        import seaborn as sns
        sns.set(style='whitegrid')
        from sklearn.preprocessing import LabelEncoder
        from sklearn.model_selection import TimeSeriesSplit, RandomizedSearchCV
        import xgboost as xgb
        import warnings
        warnings.filterwarnings('ignore')
        log.info(f'XGBoost version: {xgb.__version__}')
    except ImportError as e:
        msg = f'Import error: {e}. Pastikan xgboost terinstall: pip install xgboost'
        log.error(msg); write_status('error', msg); sys.exit(1)

    # ── Koneksi DB ────────────────────────────────────────────────────────────
    write_status('running', 'Membaca data training dari database…')
    try:
        conn = get_conn()
    except Exception as e:
        write_status('error', f'Koneksi DB gagal: {e}')
        log.error(str(e)); sys.exit(1)

    db_log(conn, 'info', 'Koneksi database berhasil. Metode: XGBoost.')

    # ── Load data dari data_training ──────────────────────────────────────────
    try:
        df_raw = pd.read_sql("""
            SELECT
                nama_produk,
                tahun, bulan, kuartal, time_idx,
                qty_total,
                harga_avg, harga_std, promo_avg, n_transaksi
            FROM data_training
            ORDER BY nama_produk, tahun, bulan
        """, conn)
        log.info(f'Data training dimuat: {df_raw.shape[0]} baris, '
                 f'{df_raw["nama_produk"].nunique()} produk')
        db_log(conn, 'info',
               f'Data training: {df_raw.shape[0]} baris, '
               f'{df_raw["nama_produk"].nunique()} produk.')
    except Exception as e:
        msg = f'Gagal baca data_training: {e}'
        log.error(msg); write_status('error', msg)
        db_update_error(conn, msg); db_log(conn, 'error', msg); sys.exit(1)

    if df_raw.empty:
        msg = 'Data training kosong. Generate data terlebih dahulu.'
        write_status('error', msg)
        db_update_error(conn, msg); db_log(conn, 'error', msg); sys.exit(1)

    # ── Preprocessing ─────────────────────────────────────────────────────────
    write_status('running', 'Preprocessing & feature engineering…')

    df = df_raw.copy()

    num_cols = ['qty_total', 'harga_avg', 'harga_std', 'promo_avg',
                'n_transaksi', 'tahun', 'bulan', 'kuartal', 'time_idx']
    for col in num_cols:
        df[col] = pd.to_numeric(df[col], errors='coerce')

    df['harga_std'] = df['harga_std'].fillna(0)

    # ── Agregasi bulanan ──────────────────────────────────────────────────────
    df_monthly = (
        df.groupby(['nama_produk', 'tahun', 'bulan', 'kuartal'], as_index=False)
        .agg(
            time_idx    = ('time_idx',    'first'),
            qty_total   = ('qty_total',   'sum'),
            harga_avg   = ('harga_avg',   'mean'),
            harga_std   = ('harga_std',   'mean'),
            promo_avg   = ('promo_avg',   'mean'),
            n_transaksi = ('n_transaksi', 'sum'),
        )
    )
    df_monthly['harga_std'] = df_monthly['harga_std'].fillna(0)

    # ── Time index & sorting ──────────────────────────────────────────────────
    df_monthly['time_idx'] = (
        df_monthly['tahun'] * 12 + df_monthly['bulan'])
    df_monthly = (df_monthly
                  .sort_values(['nama_produk', 'time_idx'])
                  .reset_index(drop=True))

    # ── Feature engineering ───────────────────────────────────────────────────
    grp = df_monthly.groupby('nama_produk')['qty_total']

    # Lag features
    df_monthly['qty_lag1'] = grp.shift(1)
    df_monthly['qty_lag2'] = grp.shift(2)
    df_monthly['qty_lag3'] = grp.shift(3)

    # Rolling (shift dulu agar tidak ada data leakage)
    df_monthly['qty_roll3_mean'] = grp.transform(
        lambda x: x.shift(1).rolling(3).mean())
    df_monthly['qty_roll3_std']  = grp.transform(
        lambda x: x.shift(1).rolling(3).std())
    df_monthly['qty_roll6_mean'] = grp.transform(
        lambda x: x.shift(1).rolling(6).mean())

    # Trend linier 0–1 per produk
    df_monthly['trend'] = df_monthly.groupby('nama_produk')['time_idx'].transform(
        lambda x: (x - x.min()) / max(x.max() - x.min(), 1)
    )

    # Seasonal encoding
    df_monthly['bulan_sin'] = np.sin(2 * np.pi * df_monthly['bulan'] / 12)
    df_monthly['bulan_cos'] = np.cos(2 * np.pi * df_monthly['bulan'] / 12)

    # bfill → ffill per grup
    fill_cols = [
        'qty_lag1', 'qty_lag2', 'qty_lag3',
        'qty_roll3_mean', 'qty_roll3_std', 'qty_roll6_mean',
        'harga_avg', 'harga_std', 'promo_avg', 'n_transaksi',
    ]
    for col in fill_cols:
        df_monthly[col] = df_monthly.groupby('nama_produk')[col].transform(
            lambda s: s.bfill())
        df_monthly[col] = df_monthly.groupby('nama_produk')[col].transform(
            lambda s: s.ffill())

    df_monthly.fillna(0, inplace=True)

    # ── Label encoder ─────────────────────────────────────────────────────────
    encoder = LabelEncoder()
    df_monthly['produk_encoded'] = encoder.fit_transform(
        df_monthly['nama_produk'].astype(str))

    # Log-transform target
    df_monthly['qty_log'] = np.log1p(df_monthly['qty_total'])

    log.info(f'Feature engineering selesai. Shape: {df_monthly.shape}')
    db_log(conn, 'info',
           f'Feature engineering selesai. Shape: {df_monthly.shape}')

    # ── Definisi fitur (identik dengan train_rf.py) ───────────────────────────
    FEATURES = [
        'bulan', 'kuartal', 'produk_encoded', 'trend',
        'harga_avg', 'harga_std', 'promo_avg', 'n_transaksi',
        'qty_lag1', 'qty_lag2', 'qty_lag3',
        'qty_roll3_mean', 'qty_roll3_std', 'qty_roll6_mean',
        'bulan_sin', 'bulan_cos',
    ]
    TARGET     = 'qty_log'
    TARGET_RAW = 'qty_total'

    missing = [f for f in FEATURES if f not in df_monthly.columns]
    if missing:
        msg = f'Kolom fitur tidak ditemukan: {missing}'
        write_status('error', msg)
        db_update_error(conn, msg); db_log(conn, 'error', msg); sys.exit(1)

    log.info(f'Fitur ({len(FEATURES)}): {FEATURES}')
    db_log(conn, 'info',
           f'Fitur yang digunakan ({len(FEATURES)}): {", ".join(FEATURES)}')

    # ── Train/Test split 80:20 ────────────────────────────────────────────────
    cutoff = df_monthly['time_idx'].quantile(0.8)
    train_df = df_monthly[df_monthly['time_idx'] <= cutoff].copy()
    test_df  = df_monthly[df_monthly['time_idx'] >  cutoff].copy()

    X_train     = train_df[FEATURES].values
    y_train     = train_df[TARGET].values
    y_train_raw = train_df[TARGET_RAW]

    X_test      = test_df[FEATURES].values
    y_test      = test_df[TARGET].values
    y_test_raw  = test_df[TARGET_RAW]

    log.info(f'Train: {X_train.shape}, Test: {X_test.shape}, '
             f'cutoff time_idx={cutoff:.0f}')
    db_log(conn, 'info',
           f'Train/Test 80:20 — Train: {len(X_train)}, Test: {len(X_test)}.')

    # ── RandomizedSearchCV XGBoost 40 iter ───────────────────────────────────
    # XGBoost lebih cepat dari RF → 40 iter sudah sangat memadai.
    # early_stopping tidak bisa langsung di RandomizedSearchCV, maka setelah
    # search kita fine-tune ulang dengan early stopping di set validasi.
    write_status('running', 'Melatih XGBoost (RandomizedSearchCV 40 iter)…')
    db_log(conn, 'info',
           'RandomizedSearchCV 40 iter, TimeSeriesSplit 5-fold dimulai.')

    tscv = TimeSeriesSplit(n_splits=5)

    # Parameter grid XGBoost — disesuaikan untuk data < 12 bulan per produk
    param_dist = {
        'n_estimators':      [200, 300, 400, 500, 600, 800],
        'max_depth':         [3, 4, 5, 6, 7, 8],
        'learning_rate':     [0.01, 0.03, 0.05, 0.07, 0.1, 0.15],
        'subsample':         [0.6, 0.7, 0.8, 0.9, 1.0],
        'colsample_bytree':  [0.5, 0.6, 0.7, 0.8, 1.0],
        'min_child_weight':  [1, 3, 5, 7],
        'gamma':             [0, 0.1, 0.2, 0.3, 0.5],
        'reg_alpha':         [0, 0.01, 0.1, 0.5, 1.0],   # L1
        'reg_lambda':        [0.5, 1.0, 1.5, 2.0, 5.0],  # L2
    }

    xgb_base = xgb.XGBRegressor(
        objective='reg:squarederror',
        tree_method='hist',     # lebih cepat untuk data tabular
        random_state=42,
        n_jobs=-1,
        verbosity=0,
    )

    search = RandomizedSearchCV(
        xgb_base, param_dist,
        n_iter=40, cv=tscv,
        scoring='neg_mean_squared_error',
        random_state=42, n_jobs=-1, verbose=1,
    )
    search.fit(X_train, y_train)

    best_params = search.best_params_
    log.info(f'Best params (search): {best_params}')
    db_log(conn, 'info',
           f'Best params: {json.dumps(best_params, default=str)}')

    # ── Fine-tune dengan early stopping ──────────────────────────────────────
    # Gunakan 15% terakhir dari data train sebagai validasi early stopping
    write_status('running', 'Fine-tune XGBoost dengan early stopping…')
    split_idx = int(len(X_train) * 0.85)
    X_tr2, X_val = X_train[:split_idx], X_train[split_idx:]
    y_tr2, y_val = y_train[:split_idx], y_train[split_idx:]

    best_params = search.best_params_.copy()
    best_params.pop('n_estimators', None)

    xgb_final = xgb.XGBRegressor(
        **best_params,
        objective='reg:squarederror',
        tree_method='hist',
        random_state=42,
        n_jobs=-1,
        verbosity=0,
        # Tingkatkan n_estimators untuk early stopping, model akan berhenti sendiri
        n_estimators=1500,
        early_stopping_rounds=30,
        eval_metric='rmse',
    )
    xgb_final.fit(
        X_tr2, y_tr2,
        eval_set=[(X_val, y_val)],
        verbose=False,
    )

    best_iteration = xgb_final.best_iteration
    log.info(f'Best iteration (early stopping): {best_iteration}')
    db_log(conn, 'info', f'Early stopping — best iteration: {best_iteration}')

    # Update best_params dengan n_estimators aktual
    best_params['n_estimators']      = best_iteration + 1
    best_params['best_iteration']    = best_iteration
    best_params['early_stopping']    = 30

    xgb_model = xgb_final  # model final

    # ── Evaluasi Training ─────────────────────────────────────────────────────
    write_status('running', 'Evaluasi training & testing…')

    train_pred_log = xgb_model.predict(X_train)
    mae_tr, rmse_tr, r2_tr, mape_tr, train_pred_raw = hitung_metrics(
        y_train_raw, train_pred_log, np)
    akurasi_tr = max(0.0, round(100 - mape_tr, 2))

    log.info(f'[TRAIN] MAE={mae_tr:.4f} RMSE={rmse_tr:.4f} '
             f'R²={r2_tr:.6f} MAPE={mape_tr:.4f}% Akurasi≈{akurasi_tr:.2f}%')
    db_log(conn, 'info',
           f'[TRAIN] MAE={mae_tr:.4f}, RMSE={rmse_tr:.4f}, '
           f'R²={r2_tr:.6f}, MAPE={mape_tr:.4f}%, Akurasi≈{akurasi_tr:.2f}%')

    # ── Evaluasi Testing ──────────────────────────────────────────────────────
    test_pred_log = xgb_model.predict(X_test)
    mae_te, rmse_te, r2_te, mape_te, test_pred_raw = hitung_metrics(
        y_test_raw, test_pred_log, np)
    akurasi_te = max(0.0, round(100 - mape_te, 2))

    log.info(f'[TEST]  MAE={mae_te:.4f} RMSE={rmse_te:.4f} '
             f'R²={r2_te:.6f} MAPE={mape_te:.4f}% Akurasi≈{akurasi_te:.2f}%')
    db_log(conn, 'success',
           f'[TEST]  MAE={mae_te:.4f}, RMSE={rmse_te:.4f}, '
           f'R²={r2_te:.6f}, MAPE={mape_te:.4f}%, Akurasi≈{akurasi_te:.2f}%')

    # ── Feature importance (gain-based, lebih informatif dari RF) ─────────────
    importance_gain = xgb_model.get_booster().get_score(importance_type='gain')
    # Normalisasi agar total = 1.0 (konsisten dengan RF output)
    total_gain = sum(importance_gain.values()) or 1.0
    fi_normalized = {k: v / total_gain for k, v in importance_gain.items()}

    # Isi fitur yang tidak muncul (importance = 0)
    for f in FEATURES:
        if f not in fi_normalized:
            fi_normalized[f] = 0.0

    feat_imp = pd.DataFrame({
        'feature':    list(fi_normalized.keys()),
        'importance': list(fi_normalized.values()),
    }).sort_values('importance', ascending=False).reset_index(drop=True)

    sorted_fi_dict = {
        row['feature']: round(float(row['importance']), 8)
        for _, row in feat_imp.iterrows()
    }

    # ── Simpan diagram ────────────────────────────────────────────────────────
    write_status('running', 'Menyimpan diagram…')
    db_log(conn, 'info', f'Menyimpan diagram ke {DIAGRAM_DIR}')
    diagram_paths = {}

    fig = plot_actual_vs_pred(
        y_train_raw, train_pred_raw,
        'Training: Actual vs Predicted (XGBoost)',
        mae_tr, rmse_tr, r2_tr, mape_tr)
    diagram_paths['train_actual_vs_pred'] = save_diagram(fig, 'train_actual_vs_pred')
    plt.close(fig)

    fig = plot_actual_vs_pred(
        y_test_raw, test_pred_raw,
        'Testing: Actual vs Predicted (XGBoost)',
        mae_te, rmse_te, r2_te, mape_te)
    diagram_paths['test_actual_vs_pred'] = save_diagram(fig, 'test_actual_vs_pred')
    plt.close(fig)

    fig = plot_residual(y_train_raw, train_pred_raw, 'Training')
    diagram_paths['train_residual'] = save_diagram(fig, 'train_residual')
    plt.close(fig)

    fig = plot_residual(y_test_raw, test_pred_raw, 'Testing')
    diagram_paths['test_residual'] = save_diagram(fig, 'test_residual')
    plt.close(fig)

    fig = plot_feature_importance(feat_imp)
    diagram_paths['feature_importance'] = save_diagram(fig, 'feature_importance')
    plt.close(fig)

    train_plot_df = pd.DataFrame({
        'time_idx': train_df['time_idx'].values,
        'aktual':   y_train_raw.values,
        'prediksi': train_pred_raw,
    })
    fig = plot_actual_vs_time(train_plot_df, 'Training')
    diagram_paths['train_timeseries'] = save_diagram(fig, 'train_timeseries')
    plt.close(fig)

    test_plot_df = pd.DataFrame({
        'time_idx': test_df['time_idx'].values,
        'aktual':   y_test_raw.values,
        'prediksi': test_pred_raw,
    })
    fig = plot_actual_vs_time(test_plot_df, 'Testing')
    diagram_paths['test_timeseries'] = save_diagram(fig, 'test_timeseries')
    plt.close(fig)

    fig = plot_top_products(df_monthly, n=10)
    diagram_paths['top_products'] = save_diagram(fig, 'top_products')
    plt.close(fig)

    fig = plot_monthly_trend(df_monthly)
    diagram_paths['monthly_trend'] = save_diagram(fig, 'monthly_trend')
    plt.close(fig)

    log.info(f'Diagram tersimpan: {list(diagram_paths.keys())}')
    db_log(conn, 'info', f'{len(diagram_paths)} diagram berhasil disimpan.')

    # ── Simpan model & encoder ────────────────────────────────────────────────
    write_status('running', 'Menyimpan file model & encoder…')

    model_path   = MODEL_DIR / f'model_xgb_v{TRAINING_ID}.pkl'
    encoder_path = MODEL_DIR / f'label_encoder_v{TRAINING_ID}.pkl'
    # Alias aktif — predict_xgb.py membaca dari nama ini
    active_model   = MODEL_DIR / 'model_xgb_optimized.pkl'
    active_encoder = MODEL_DIR / 'label_encoder.pkl'

    joblib.dump(xgb_model, model_path)
    joblib.dump(encoder,   encoder_path)

    for src, dst in [(model_path, active_model),
                     (encoder_path, active_encoder)]:
        if dst.exists() or dst.is_symlink():
            dst.unlink()
        try:
            dst.symlink_to(src.resolve())
        except Exception:
            import shutil
            shutil.copy2(src, dst)

    ukuran_kb = int(round(model_path.stat().st_size / 1024))
    log.info(f'Model: {model_path} ({ukuran_kb} KB)')
    db_log(conn, 'info', f'Model XGBoost disimpan: {model_path} ({ukuran_kb} KB)')

    # ── Update DB ─────────────────────────────────────────────────────────────
    write_status('running', 'Menyimpan hasil ke database…')
    selesai_at = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    _md = best_params.get('max_depth')

    with conn.cursor() as cur:
        cur.execute("""
            UPDATE model_training SET
                status              = 'success',
                selesai_at          = %s,
                durasi_detik        = TIMESTAMPDIFF(SECOND, mulai_at, %s),
                total_record        = %s,
                total_produk        = %s,
                total_fitur         = %s,
                mae                 = %s,
                rmse                = %s,
                r2                  = %s,
                mape                = %s,
                akurasi             = %s,
                mae_test            = %s,
                rmse_test           = %s,
                r2_test             = %s,
                mape_test           = %s,
                akurasi_test        = %s,
                n_estimators        = %s,
                max_depth           = %s,
                min_samples_split   = %s,
                min_samples_leaf    = %s,
                max_features        = %s,
                path_model          = %s,
                path_encoder        = %s,
                ukuran_model_kb     = %s,
                best_params         = %s,
                feature_importance  = %s,
                diagram_paths       = %s,
                updated_at          = NOW()
            WHERE id = %s
        """, (
            selesai_at, selesai_at,
            int(len(df_monthly)),
            int(df_monthly['nama_produk'].nunique()),
            len(FEATURES),
            round(mae_tr,  4), round(rmse_tr, 4),
            round(r2_tr,   6), round(mape_tr, 4), akurasi_tr,
            round(mae_te,  4), round(rmse_te, 4),
            round(r2_te,   6), round(mape_te, 4), akurasi_te,
            # Kolom hyperparameter — mapping ke kolom RF yang sudah ada di DB
            int(best_params.get('n_estimators', 0)),
            int(_md) if _md is not None else None,
            # min_samples_split → min_child_weight (semantik terdekat)
            int(best_params.get('min_child_weight', 1)),
            # min_samples_leaf → tidak ada padanan langsung, simpan gamma
            int(best_params.get('gamma', 0)),
            # max_features → colsample_bytree
            str(best_params.get('colsample_bytree', 1.0)),
            str(model_path), str(encoder_path), ukuran_kb,
            json.dumps(best_params,    default=str, ensure_ascii=False),
            json.dumps(sorted_fi_dict, ensure_ascii=False),
            json.dumps(diagram_paths,  ensure_ascii=False),
            TRAINING_ID,
        ))

    simpan_feature_importance(conn, sorted_fi_dict)
    db_log(conn, 'info', 'Feature importance tersimpan.')

    try:
        with conn.cursor() as cur:
            cur.execute(
                "DELETE FROM model_training_diagram WHERE training_id=%s",
                (TRAINING_ID,))
            cur.executemany(
                "INSERT INTO model_training_diagram "
                "(training_id, diagram_key, path, created_at) "
                "VALUES (%s,%s,%s,%s)",
                [(TRAINING_ID, k, v, selesai_at)
                 for k, v in diagram_paths.items()])
        db_log(conn, 'info',
               f'{len(diagram_paths)} diagram path tersimpan ke tabel.')
    except Exception as e:
        log.warning(f'model_training_diagram skip: {e}')
        db_log(conn, 'warning', f'Diagram path tidak disimpan ke tabel: {e}')

    conn.close()

    # ── Status akhir ──────────────────────────────────────────────────────────
    write_status('success', 'Training XGBoost selesai!', {
        'training_id':    TRAINING_ID,
        'metode':         'XGBoost',
        'akurasi':        akurasi_te,
        'mae':            round(mae_te,  4),
        'rmse':           round(rmse_te, 4),
        'r2':             round(r2_te,   6),
        'mape':           round(mape_te, 4),
        'akurasi_train':  akurasi_tr,
        'mae_train':      round(mae_tr,  4),
        'rmse_train':     round(rmse_tr, 4),
        'r2_train':       round(r2_tr,   6),
        'mape_train':     round(mape_tr, 4),
        'akurasi_test':   akurasi_te,
        'mae_test':       round(mae_te,  4),
        'rmse_test':      round(rmse_te, 4),
        'r2_test':        round(r2_te,   6),
        'mape_test':      round(mape_te, 4),
        'best_iteration': best_iteration,
        'best_params':        best_params,
        'feature_importance': sorted_fi_dict,
        'diagram_paths':      diagram_paths,
        'total_sampel':       int(len(df_monthly)),
        'total_produk':       int(df_monthly['nama_produk'].nunique()),
        'model_path':         str(model_path),
        'encoder_path':       str(encoder_path),
        'finished_at':        selesai_at,
    })

    log.info(f'=== Training XGBoost ID={TRAINING_ID} selesai — '
             f'Train: {akurasi_tr}% | Test: {akurasi_te}% ===')


# ── Entry point ───────────────────────────────────────────────────────────────
if __name__ == '__main__':
    try:
        main()
    except Exception as e:
        tb = traceback.format_exc()
        log.error(f'Fatal error:\n{tb}')
        write_status('error', f'Fatal error: {e}')
        try:
            conn = get_conn()
            db_update_error(conn, str(e)[:2000])
            db_log(conn, 'error', str(e)[:2000])
            conn.close()
        except Exception:
            pass
        sys.exit(1)