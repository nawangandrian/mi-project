"""
train_rf.py — Script training Random Forest untuk Mi Store
═══════════════════════════════════════════════════════════
FIX v3 — Hasil IDENTIK dengan Colab:

  AKAR MASALAH (v1/v2):
  ─────────────────────
  • train_rf.py v1/v2 membaca dari tabel `data_training` yang diisi oleh PHP.
    PHP menghitung lag/rolling dengan cara berbeda (array_slice, loop),
    sehingga nilai lag/rolling TIDAK IDENTIK dengan pandas shift().rolling().
  • Akibatnya model yang ditraining di server ≠ model di Colab meski
    hyperparameter sama persis.

  SOLUSI v3:
  ──────────
  • Baca langsung dari tabel `penjualan` (raw) — sama seperti Colab membaca Excel.
  • Seluruh preprocessing & feature engineering dilakukan 100% di Python/pandas,
    tidak ada ketergantungan pada nilai yang dihitung PHP.
  • FEATURES menggunakan `time_idx` absolut (bukan trend 0-1) — konsisten
    dengan predict_rf.py.

  Mapping Colab → train_rf.py v3:
  ─────────────────────────────────────────────────────────────────
  Colab                            train_rf.py v3
  ────────────────────────────────────────────────────────────────
  df['barang']                     df['nama_produk']
  df['barang_encoded']             df['produk_encoded']
  LabelEncoder pada 'barang'       LabelEncoder pada 'nama_produk'
  FEATURES['barang_encoded']       FEATURES['produk_encoded']
  FEATURES['trend']  (0-1)         ← DIHAPUS, diganti time_idx absolut
  FEATURES['time_idx'] absolut     FEATURES['time_idx'] absolut  ✓
  bfill().ffill()   ← data leakage ← DIHAPUS, pakai fillna(0) saja  ✓
  shift(1).rolling(N)              shift(1).rolling(N, min_periods=1)  ✓
  80:20 quantile split             80:20 quantile split  ✓
  RandomizedSearchCV 50 iter       RandomizedSearchCV 50 iter  ✓
  log1p target, expm1 output       log1p target, expm1 output  ✓
  Sumber data: Excel               Sumber data: tabel penjualan (raw)  ✓
"""

import sys, os, json, argparse, logging, traceback
from datetime import datetime
from pathlib import Path
from turtle import pd

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
parser.add_argument('--date-from', default=None,
    help='Filter awal: YYYY-MM-DD (inklusif)')
parser.add_argument('--date-to',   default=None,
    help='Filter akhir: YYYY-MM-DD (inklusif)')
args = parser.parse_args()

MODEL_DIR   = Path(args.model_dir)
LOG_FILE    = Path(args.log_file)
TRAINING_ID = args.training_id

DIAGRAM_DIR = MODEL_DIR / 'diagrams' / f'v{TRAINING_ID}'
MODEL_DIR.mkdir(parents=True, exist_ok=True)
LOG_FILE.parent.mkdir(parents=True, exist_ok=True)
DIAGRAM_DIR.mkdir(parents=True, exist_ok=True)

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
    ax.set_title('Feature Importance — Random Forest',
                 fontsize=11, fontweight='bold')
    ax.set_xlabel('Importance')
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


def hitung_metrics(y_true_raw, y_pred_log, np):
    import sklearn.metrics as skm
    y_pred_raw = np.maximum(np.expm1(y_pred_log), 0)
    mae  = float(skm.mean_absolute_error(y_true_raw, y_pred_raw))
    rmse = float(np.sqrt(skm.mean_squared_error(y_true_raw, y_pred_raw)))
    r2   = float(skm.r2_score(y_true_raw, y_pred_raw))
    mape = float(skm.mean_absolute_percentage_error(y_true_raw, y_pred_raw)) * 100
    return mae, rmse, r2, mape, y_pred_raw


# ── FEATURES — identik 1:1 dengan predict_rf.py ──────────────────────────────
# CATATAN: 'trend' DIHAPUS karena nilai 0-1 tidak bisa direproduksi saat
#          inferensi (tidak tahu max time_idx produk tersebut di masa training).
#          Digantikan oleh 'time_idx' absolut yang konsisten train↔predict.
FEATURES = [
    'bulan', 'kuartal', 'produk_encoded', 'time_idx',
    'harga_avg', 'harga_std', 'promo_avg', 'n_transaksi',
    'qty_lag1', 'qty_lag2', 'qty_lag3',
    'qty_roll3_mean', 'qty_roll3_std', 'qty_roll6_mean',
    'bulan_sin', 'bulan_cos',
]

def load_penjualan(conn, date_from=None, date_to=None) -> 'pd.DataFrame':
    """
    Baca data raw dari tabel penjualan dengan filter tanggal opsional.
    """
    import pandas as pd

    where_clauses = []
    params = []
    if date_from:
        where_clauses.append("tanggal >= %s")
        params.append(date_from)
    if date_to:
        where_clauses.append("tanggal <= %s")
        params.append(date_to)

    where_sql = ("WHERE " + " AND ".join(where_clauses)) if where_clauses else ""

    query = f"""
        SELECT
            UPPER(TRIM(nama_produk)) AS nama_produk,
            CAST(qty   AS SIGNED)    AS qty,
            CAST(harga AS DECIMAL(15,2)) AS harga,
            tanggal,
            promo
        FROM penjualan
        {where_sql}
        ORDER BY tanggal ASC
    """

    df = pd.read_sql(query, conn, params=params if params else None)
    return df

def preprocess_raw(df, np, pd) -> 'pd.DataFrame':
    """
    Preprocessing identik dengan cell 3 Colab:
      - Drop NA
      - Parse tanggal
      - Ekstrak bulan, tahun, kuartal
      - Encode promo → 0/1
      - Konversi harga ke numeric
    """
    df = df.dropna().copy()

    df['tanggal'] = pd.to_datetime(df['tanggal'], dayfirst=True, errors='coerce')
    df = df.dropna(subset=['tanggal'])

    df['bulan']   = df['tanggal'].dt.month
    df['tahun']   = df['tanggal'].dt.year
    df['kuartal'] = df['tanggal'].dt.quarter

    # Encode promo: 'YA'→1, 'TIDAK'→0, integer 1/0 tetap, lainnya→0
    if df['promo'].dtype == object:
        df['promo_encoded'] = (
            df['promo'].astype(str).str.upper().str.strip()
            .map({'YA': 1, 'TIDAK': 0})
            .fillna(0)
            .astype(float)
        )
    else:
        df['promo_encoded'] = pd.to_numeric(df['promo'], errors='coerce').fillna(0).astype(float)

    df['harga'] = pd.to_numeric(df['harga'], errors='coerce')

    return df


def agregasi_bulanan(df, pd) -> 'pd.DataFrame':
    """
    Agregasi identik dengan cell 5 Colab:
      GROUP BY barang, tahun, bulan, kuartal
      → sum(qty), mean(harga), std(harga), mean(promo_encoded), count(transaksi)
    """
    df_monthly = (
        df.groupby(['nama_produk', 'tahun', 'bulan', 'kuartal'], as_index=False)
        .agg(
            qty_total   = ('qty',           'sum'),
            harga_avg   = ('harga',         'mean'),
            harga_std   = ('harga',         'std'),
            promo_avg   = ('promo_encoded', 'mean'),
            n_transaksi = ('nama_produk',   'count'),  # count baris = n transaksi
        )
    )
    df_monthly['harga_std'] = df_monthly['harga_std'].fillna(0)

    # time_idx absolut — TIDAK menggunakan trend 0-1
    df_monthly['time_idx'] = df_monthly['tahun'] * 12 + df_monthly['bulan']
    df_monthly = (df_monthly
                  .sort_values(['nama_produk', 'time_idx'])
                  .reset_index(drop=True))

    return df_monthly


def feature_engineering(df_monthly, np, pd) -> 'pd.DataFrame':
    """
    Feature engineering identik dengan cell 7 Colab (TANPA bfill):

    Colab:
      grp = df_monthly.groupby('barang_encoded')['qty_total']
      lag1 = grp.shift(1)
      roll3_mean = grp.transform(lambda x: x.shift(1).rolling(3).mean())
      # lalu: bfill().ffill()  ← DATA LEAKAGE — DIHAPUS di sini

    Sini (anti-leakage):
      - Groupby pada 'nama_produk' (bukan encoded, encoder dibuat setelah ini)
      - Lag & rolling identik
      - fillna(0) saja — tanpa bfill/ffill pada target qty
      - ffill pada harga/promo aman (tidak melibatkan target)
    """
    grp = df_monthly.groupby('nama_produk')['qty_total']

    # Lag features — identik Colab
    df_monthly['qty_lag1'] = grp.shift(1)
    df_monthly['qty_lag2'] = grp.shift(2)
    df_monthly['qty_lag3'] = grp.shift(3)

    # Rolling features — identik Colab, min_periods=1 agar tidak NaN untuk produk baru
    df_monthly['qty_roll3_mean'] = grp.transform(
        lambda x: x.shift(1).rolling(3, min_periods=1).mean())
    df_monthly['qty_roll3_std']  = grp.transform(
        lambda x: x.shift(1).rolling(3, min_periods=2).std())
    df_monthly['qty_roll6_mean'] = grp.transform(
        lambda x: x.shift(1).rolling(6, min_periods=1).mean())

    # Seasonal encoding — identik Colab
    df_monthly['bulan_sin'] = np.sin(2 * np.pi * df_monthly['bulan'] / 12)
    df_monthly['bulan_cos'] = np.cos(2 * np.pi * df_monthly['bulan'] / 12)

    # ── ANTI-LEAKAGE: fillna(0) saja pada lag/rolling ────────────────────────
    # Colab pakai bfill().ffill() yang menyebabkan lag pertama suatu produk
    # diisi dari nilai MASA DEPAN → data leakage.
    # Nilai 0 = "tidak ada histori" yang semantiknya benar untuk produk baru.
    lag_roll_cols = [
        'qty_lag1', 'qty_lag2', 'qty_lag3',
        'qty_roll3_mean', 'qty_roll3_std', 'qty_roll6_mean',
    ]
    for col in lag_roll_cols:
        df_monthly[col] = df_monthly[col].fillna(0)

    # ffill pada harga/promo aman (bukan target, data valid sebelumnya)
    for col in ['harga_avg', 'harga_std', 'promo_avg', 'n_transaksi']:
        df_monthly[col] = df_monthly.groupby('nama_produk')[col].transform(
            lambda s: s.ffill())
        df_monthly[col] = df_monthly[col].fillna(0)

    df_monthly.fillna(0, inplace=True)

    # Log-transform target — identik Colab
    df_monthly['qty_log'] = np.log1p(df_monthly['qty_total'])

    return df_monthly


def main():
    write_status('running', 'Memulai proses training…')
    log.info(f'=== Training ID={TRAINING_ID} dimulai ===')

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
        from sklearn.ensemble import RandomForestRegressor
        from sklearn.model_selection import TimeSeriesSplit, RandomizedSearchCV
        import warnings
        warnings.filterwarnings('ignore')
    except ImportError as e:
        msg = f'Import error: {e}'
        log.error(msg); write_status('error', msg); sys.exit(1)

    # ── Koneksi DB ────────────────────────────────────────────────────────────
    write_status('running', 'Membaca data penjualan dari database…')
    try:
        conn = get_conn()
    except Exception as e:
        write_status('error', f'Koneksi DB gagal: {e}')
        log.error(str(e)); sys.exit(1)

    db_log(conn, 'info', 'Koneksi database berhasil.')

    # ── Step 1: Load data RAW dari tabel penjualan ───────────────────────────
    # (Identik Colab cell 2: df = pd.read_excel(...))
    try:
        df_raw = load_penjualan(conn,
            date_from=args.date_from or None,
            date_to=args.date_to   or None)

        date_from_log = args.date_from or 'semua'
        date_to_log   = args.date_to   or 'semua'
        log.info(f'Filter tanggal: {date_from_log} s/d {date_to_log}')
        db_log(conn, 'info',
            f'Filter tanggal: {date_from_log} s/d {date_to_log}')
    except Exception as e:
        msg = f'Gagal baca tabel penjualan: {e}'
        log.error(msg); write_status('error', msg)
        db_update_error(conn, msg); db_log(conn, 'error', msg); sys.exit(1)

    if df_raw.empty:
        msg = 'Tabel penjualan kosong. Isi data penjualan terlebih dahulu.'
        write_status('error', msg)
        db_update_error(conn, msg); db_log(conn, 'error', msg); sys.exit(1)

    # ── Step 2: Preprocessing (identik Colab cell 3) ─────────────────────────
    write_status('running', 'Preprocessing data penjualan…')
    try:
        df = preprocess_raw(df_raw, np, pd)
        log.info(f'Setelah preprocessing: {df.shape}')
        db_log(conn, 'info', f'Preprocessing selesai. Shape: {df.shape}')
    except Exception as e:
        msg = f'Preprocessing gagal: {e}'
        log.error(msg); write_status('error', msg)
        db_update_error(conn, msg); db_log(conn, 'error', msg); sys.exit(1)

    # ── Step 3: Agregasi bulanan (identik Colab cell 5) ──────────────────────
    write_status('running', 'Agregasi bulanan…')
    df_monthly = agregasi_bulanan(df, pd)
    log.info(f'Data bulanan: {df_monthly.shape}, '
             f'{df_monthly["nama_produk"].nunique()} produk')
    db_log(conn, 'info',
           f'Agregasi bulanan selesai. Shape: {df_monthly.shape}')

    # ── Step 4: Feature engineering (identik Colab cell 7, tanpa bfill) ──────
    write_status('running', 'Feature engineering…')
    df_monthly = feature_engineering(df_monthly, np, pd)

    # ── Step 5: Label Encoder (identik Colab cell 4) ─────────────────────────
    encoder = LabelEncoder()
    df_monthly['produk_encoded'] = encoder.fit_transform(
        df_monthly['nama_produk'].astype(str))

    log.info(f'Feature engineering selesai. Shape: {df_monthly.shape}, '
             f'Produk: {len(encoder.classes_)}')
    db_log(conn, 'info',
           f'Feature engineering selesai. {len(encoder.classes_)} produk di-encode.')

    # ── Step 6: Validasi fitur ────────────────────────────────────────────────
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

    # ── Step 7: Train/Test split 80:20 (identik Colab cell 9) ────────────────
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

    # ── Step 8: Training (identik Colab cell 10) ─────────────────────────────
    write_status('running', 'Melatih Random Forest (RandomizedSearchCV 50 iter)…')
    db_log(conn, 'info',
           'RandomizedSearchCV 50 iter, TimeSeriesSplit 5-fold dimulai.')

    tscv = TimeSeriesSplit(n_splits=5)

    param_dist = {
        'n_estimators':      [200, 300, 400, 500, 600],
        'max_depth':         [6, 8, 10, 12, 15, None],
        'min_samples_split': [2, 5, 10],
        'min_samples_leaf':  [1, 2, 4],
        'max_features':      ['sqrt', 'log2', 0.5, 0.7],
        'bootstrap':         [True, False],
    }

    rf_base = RandomForestRegressor(random_state=42, n_jobs=-1)
    search  = RandomizedSearchCV(
        rf_base, param_dist,
        n_iter=50, cv=tscv,
        scoring='neg_mean_squared_error',
        random_state=42, n_jobs=-1, verbose=1,
    )
    search.fit(X_train, y_train)

    rf_model    = search.best_estimator_
    best_params = search.best_params_
    best_params['_filter_date_from'] = args.date_from or None
    best_params['_filter_date_to']   = args.date_to   or None
    log.info(f'Best params: {best_params}')
    db_log(conn, 'info',
           f'Best params: {json.dumps(best_params, default=str)}')

    # ── Step 9: Evaluasi ──────────────────────────────────────────────────────
    write_status('running', 'Evaluasi training & testing…')

    train_pred_log = rf_model.predict(X_train)
    mae_tr, rmse_tr, r2_tr, mape_tr, train_pred_raw = hitung_metrics(
        y_train_raw, train_pred_log, np)
    akurasi_tr = max(0.0, round(100 - mape_tr, 2))

    log.info(f'[TRAIN] MAE={mae_tr:.4f} RMSE={rmse_tr:.4f} '
             f'R²={r2_tr:.6f} MAPE={mape_tr:.4f}% Akurasi≈{akurasi_tr:.2f}%')
    db_log(conn, 'info',
           f'[TRAIN] MAE={mae_tr:.4f}, RMSE={rmse_tr:.4f}, '
           f'R²={r2_tr:.6f}, MAPE={mape_tr:.4f}%, Akurasi≈{akurasi_tr:.2f}%')

    test_pred_log = rf_model.predict(X_test)
    mae_te, rmse_te, r2_te, mape_te, test_pred_raw = hitung_metrics(
        y_test_raw, test_pred_log, np)
    akurasi_te = max(0.0, round(100 - mape_te, 2))

    log.info(f'[TEST]  MAE={mae_te:.4f} RMSE={rmse_te:.4f} '
             f'R²={r2_te:.6f} MAPE={mape_te:.4f}% Akurasi≈{akurasi_te:.2f}%')
    db_log(conn, 'success',
           f'[TEST]  MAE={mae_te:.4f}, RMSE={rmse_te:.4f}, '
           f'R²={r2_te:.6f}, MAPE={mape_te:.4f}%, Akurasi≈{akurasi_te:.2f}%')

    feat_imp = pd.DataFrame({
        'feature':    FEATURES,
        'importance': rf_model.feature_importances_,
    }).sort_values('importance', ascending=False).reset_index(drop=True)

    sorted_fi_dict = {
        row['feature']: round(float(row['importance']), 8)
        for _, row in feat_imp.iterrows()
    }

    # ── Step 10: Simpan diagram ───────────────────────────────────────────────
    write_status('running', 'Menyimpan diagram…')
    db_log(conn, 'info', f'Menyimpan diagram ke {DIAGRAM_DIR}')
    diagram_paths = {}

    fig = plot_actual_vs_pred(
        y_train_raw, train_pred_raw,
        'Training: Actual vs Predicted',
        mae_tr, rmse_tr, r2_tr, mape_tr)
    diagram_paths['train_actual_vs_pred'] = save_diagram(fig, 'train_actual_vs_pred')
    plt.close(fig)

    fig = plot_actual_vs_pred(
        y_test_raw, test_pred_raw,
        'Testing: Actual vs Predicted',
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

    # ── Step 11: Simpan model & encoder ──────────────────────────────────────
    write_status('running', 'Menyimpan file model & encoder…')

    model_path   = MODEL_DIR / f'model_rf_v{TRAINING_ID}.pkl'
    encoder_path = MODEL_DIR / f'label_encoder_v{TRAINING_ID}.pkl'
    active_model   = MODEL_DIR / 'model_rf_optimized.pkl'
    active_encoder = MODEL_DIR / 'label_encoder.pkl'

    joblib.dump(rf_model, model_path)
    joblib.dump(encoder,  encoder_path)

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
    db_log(conn, 'info', f'Model disimpan: {model_path} ({ukuran_kb} KB)')

    # ── Step 12: Update database ──────────────────────────────────────────────
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
            int(best_params.get('n_estimators', 0)),
            int(_md) if _md is not None else None,
            int(best_params.get('min_samples_split', 2)),
            int(best_params.get('min_samples_leaf',  1)),
            str(best_params.get('max_features', 'sqrt')),
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

    write_status('success', 'Training selesai!', {
        'training_id':    TRAINING_ID,
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
        'best_params':        best_params,
        'feature_importance': sorted_fi_dict,
        'diagram_paths':      diagram_paths,
        'total_sampel':       int(len(df_monthly)),
        'total_produk':       int(df_monthly['nama_produk'].nunique()),
        'model_path':         str(model_path),
        'encoder_path':       str(encoder_path),
        'finished_at':        selesai_at,
    })

    log.info(f'=== Training ID={TRAINING_ID} selesai — '
             f'Train: {akurasi_tr}% | Test: {akurasi_te}% ===')


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