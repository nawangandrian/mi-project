"""
predict_xgb.py — Script inferensi XGBoost untuk Mi Store
Menggantikan predict_rf.py. Konsisten 1:1 dengan train_xgb.py.

Perubahan dari predict_rf.py:
  - Load model_xgb_optimized.pkl (bukan model_rf_optimized.pkl)
  - FEATURES identik 16 kolom (termasuk 'trend', sama seperti train_xgb.py)
  - Kalkulasi 'trend' untuk periode prediksi menggunakan rentang historis produk
    sehingga tidak pernah out-of-distribution (> 1.0 maksimal extrapolasi kecil)
  - Semua logika lag & rolling identik dengan predict_rf.py
"""

import sys, os, json, argparse, logging, traceback
from datetime import datetime
from pathlib import Path

# ── Argparse ──────────────────────────────────────────────────────────────────
parser = argparse.ArgumentParser()
parser.add_argument('--model-path',   required=True)
parser.add_argument('--encoder-path', required=True)
parser.add_argument('--produk-file',  required=True)
parser.add_argument('--result-file',  required=True)
parser.add_argument('--db-host',  default=os.getenv('DB_HOST',  '127.0.0.1'))
parser.add_argument('--db-port',  default=int(os.getenv('DB_PORT', 3306)), type=int)
parser.add_argument('--db-name',  default=os.getenv('DB_NAME',  'mi_store'))
parser.add_argument('--db-user',  default=os.getenv('DB_USER',  'root'))
parser.add_argument('--db-pass',  default=os.getenv('DB_PASS',  ''))
parser.add_argument('--log-file', default=os.getenv('LOG_FILE',
    str(Path(__file__).parent / 'logs' / 'predict.log')))
args = parser.parse_args()

LOG_FILE = Path(args.log_file)
LOG_FILE.parent.mkdir(parents=True, exist_ok=True)

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    handlers=[
        logging.FileHandler(LOG_FILE, encoding='utf-8'),
        logging.StreamHandler(sys.stdout),
    ]
)
log = logging.getLogger(__name__)
RESULT_FILE = Path(args.result_file)


def write_result(status: str, message: str, extra: dict = None):
    data = {'status': status, 'message': message,
            'updated_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
    if extra:
        data.update(extra)
    RESULT_FILE.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding='utf-8')


def get_conn():
    import pymysql
    return pymysql.connect(
        host=args.db_host, port=args.db_port,
        db=args.db_name,   user=args.db_user,
        password=args.db_pass, charset='utf8mb4',
        autocommit=True,
        init_command="SET time_zone='+07:00'",
    )


# ── FEATURES — identik dengan train_xgb.py ───────────────────────────────────
FEATURES = [
    'bulan', 'kuartal', 'produk_encoded', 'trend',
    'harga_avg', 'harga_std', 'promo_avg', 'n_transaksi',
    'qty_lag1', 'qty_lag2', 'qty_lag3',
    'qty_roll3_mean', 'qty_roll3_std', 'qty_roll6_mean',
    'bulan_sin', 'bulan_cos',
]


def main():
    log.info('=== predict_xgb.py dimulai ===')

    try:
        import pandas as pd
        import numpy as np
        import joblib
        import xgboost as xgb  # noqa: F401 — pastikan library tersedia
        import warnings
        warnings.filterwarnings('ignore')
    except ImportError as e:
        write_result('error', f'Import error: {e}. Pastikan xgboost terinstall.')
        log.error(str(e)); sys.exit(1)

    # ── Baca produk-file ──────────────────────────────────────────────────────
    try:
        produk_cfg     = json.loads(Path(args.produk_file).read_text(encoding='utf-8'))
        produk_list    = produk_cfg['produk_list']
        bulan_prediksi = int(produk_cfg['bulan_prediksi'])
        tahun_prediksi = int(produk_cfg['tahun_prediksi'])
    except Exception as e:
        write_result('error', f'Gagal baca produk-file: {e}')
        log.error(str(e)); sys.exit(1)

    if not produk_list:
        write_result('error', 'Daftar produk kosong.')
        sys.exit(1)

    time_idx_target = tahun_prediksi * 12 + bulan_prediksi
    log.info(f'Target: {len(produk_list)} produk | {bulan_prediksi}/{tahun_prediksi} '
             f'(time_idx={time_idx_target})')

    # ── Load model & encoder ──────────────────────────────────────────────────
    try:
        xgb_model = joblib.load(args.model_path)
        encoder   = joblib.load(args.encoder_path)
        log.info(f'Model XGBoost: {args.model_path}')
        log.info(f'Encoder: {args.encoder_path} | kelas: {len(encoder.classes_)}')
    except Exception as e:
        write_result('error', f'Gagal load model/encoder: {e}')
        log.error(str(e)); sys.exit(1)

    # ── Koneksi DB ────────────────────────────────────────────────────────────
    try:
        conn = get_conn()
        log.info('Koneksi database berhasil.')
    except Exception as e:
        write_result('error', f'Koneksi DB gagal: {e}')
        log.error(str(e)); sys.exit(1)

    # ── Ambil data historis ───────────────────────────────────────────────────
    try:
        produk_upper_list = [p.strip().upper() for p in produk_list]
        placeholders      = ', '.join(['%s'] * len(produk_upper_list))

        query = f"""
            SELECT nama_produk, tahun, bulan, kuartal, time_idx,
                   qty_total,
                   qty_lag1, qty_lag2, qty_lag3,
                   qty_roll3_mean, qty_roll3_std, qty_roll6_mean,
                   harga_avg, harga_std, promo_avg, n_transaksi
            FROM data_training
            WHERE UPPER(nama_produk) IN ({placeholders})
              AND time_idx < %s
            ORDER BY nama_produk, time_idx ASC
        """
        params  = produk_upper_list + [time_idx_target]
        df_hist = pd.read_sql(query, conn, params=params)

        df_hist = df_hist.groupby(
            ['nama_produk', 'tahun', 'bulan', 'kuartal', 'time_idx'], as_index=False
        ).agg({
            'qty_total': 'sum', 'qty_lag1': 'mean', 'qty_lag2': 'mean',
            'qty_lag3': 'mean', 'qty_roll3_mean': 'mean', 'qty_roll3_std': 'mean',
            'qty_roll6_mean': 'mean', 'harga_avg': 'mean', 'harga_std': 'mean',
            'promo_avg': 'mean', 'n_transaksi': 'sum',
        })
        log.info(f'Data historis: {df_hist.shape[0]} baris, '
                 f'{df_hist["nama_produk"].nunique()} produk')
    except Exception as e:
        conn.close()
        write_result('error', f'Gagal baca data_training: {e}')
        log.error(str(e)); sys.exit(1)

    conn.close()

    # ── Bangun fitur per produk ───────────────────────────────────────────────
    kuartal   = (bulan_prediksi - 1) // 3 + 1
    bulan_sin = float(np.sin(2 * np.pi * bulan_prediksi / 12))
    bulan_cos = float(np.cos(2 * np.pi * bulan_prediksi / 12))

    known_classes = set(encoder.classes_)
    records = []
    skipped = []

    def safe(val, default=0.0):
        try:
            v = float(val)
            return v if not np.isnan(v) else default
        except Exception:
            return default

    for produk in produk_list:
        produk_upper = produk.strip().upper()

        if produk_upper not in known_classes:
            log.warning(f'"{produk_upper}" tidak ada di encoder — dilewati.')
            skipped.append(produk_upper)
            continue

        hist = df_hist[df_hist['nama_produk'] == produk_upper].sort_values('time_idx')

        if hist.empty:
            log.warning(f'Tidak ada histori untuk {produk_upper} — pakai nilai nol.')
            records.append({
                'nama_produk':    produk_upper,
                'bulan':          bulan_prediksi,
                'kuartal':        kuartal,
                'produk_encoded': int(encoder.transform([produk_upper])[0]),
                # trend = 1.0 untuk produk tanpa histori (di ujung range)
                'trend':          1.0,
                'harga_avg': 0.0, 'harga_std': 0.0,
                'promo_avg': 0.0, 'n_transaksi': 0.0,
                'qty_lag1': 0.0,  'qty_lag2': 0.0, 'qty_lag3': 0.0,
                'qty_roll3_mean': 0.0, 'qty_roll3_std': 0.0, 'qty_roll6_mean': 0.0,
                'bulan_sin': bulan_sin, 'bulan_cos': bulan_cos,
            })
            continue

        last = hist.iloc[-1]

        # ── Kalkulasi 'trend' untuk periode prediksi ──────────────────────────
        # trend = (time_idx_target - t_min) / (t_max - t_min)
        # Menggunakan rentang data historis produk ini sebagai basis normalisasi.
        # Jika model dilatih dengan rentang [t_min_train, t_max_train] dan
        # time_idx_target = t_max_train + 1, maka trend ≈ 1.0 + epsilon kecil
        # yang masih dalam distribusi model (tidak out-of-distribution jauh).
        t_min = float(hist['time_idx'].min())
        t_max = float(hist['time_idx'].max())
        denom = max(t_max - t_min, 1.0)
        trend_val = (time_idx_target - t_min) / denom
        # Clip ke [0, 1.5] agar tidak terlalu jauh OOD
        trend_val = float(np.clip(trend_val, 0.0, 1.5))

        # ── Fitur lag untuk periode t+1 ───────────────────────────────────────
        lag1 = safe(last['qty_total'])
        lag2 = safe(last['qty_lag1'])
        lag3 = safe(last['qty_lag2'])

        # ── Rolling untuk t+1 ─────────────────────────────────────────────────
        w3 = [v for v in [lag1, lag2, lag3] if v > 0]
        roll3_mean = float(np.mean(w3))         if w3 else 0.0
        roll3_std  = float(np.std(w3, ddof=1))  if len(w3) > 1 else 0.0

        lag3_t = safe(last['qty_lag3'])
        w6     = [v for v in [lag1, lag2, lag3, lag3_t] if v > 0]
        if len(hist) >= 2:
            w6.append(safe(hist.iloc[-2]['qty_total']))
        if len(hist) >= 3:
            w6.append(safe(hist.iloc[-3]['qty_total']))
        roll6_mean = float(np.mean(w6[:6])) if w6 else 0.0

        harga_avg = safe(last['harga_avg'])
        harga_std = safe(last['harga_std'])
        promo_avg = safe(last['promo_avg'])
        n_trans   = safe(last['n_transaksi'])

        produk_encoded = int(encoder.transform([produk_upper])[0])

        log.info(
            f'[{produk_upper}] encoded={produk_encoded} '
            f'trend={trend_val:.4f} '
            f'lag1={lag1:.1f} lag2={lag2:.1f} lag3={lag3:.1f} | '
            f'roll3_mean={roll3_mean:.2f} roll6_mean={roll6_mean:.2f} | '
            f'harga_avg={harga_avg:.0f}'
        )

        records.append({
            'nama_produk':    produk_upper,
            'bulan':          bulan_prediksi,
            'kuartal':        kuartal,
            'produk_encoded': produk_encoded,
            'trend':          trend_val,
            'harga_avg':      harga_avg,
            'harga_std':      harga_std,
            'promo_avg':      promo_avg,
            'n_transaksi':    n_trans,
            'qty_lag1':       lag1,
            'qty_lag2':       lag2,
            'qty_lag3':       lag3,
            'qty_roll3_mean': roll3_mean,
            'qty_roll3_std':  roll3_std,
            'qty_roll6_mean': roll6_mean,
            'bulan_sin':      bulan_sin,
            'bulan_cos':      bulan_cos,
        })

    if not records:
        write_result('error', 'Tidak ada produk valid untuk diprediksi.')
        sys.exit(1)

    # ── Inferensi XGBoost ─────────────────────────────────────────────────────
    df_input = pd.DataFrame(records)
    X        = df_input[FEATURES].values

    log.info(f'Inferensi {len(records)} produk dengan XGBoost...')
    log.info(f'Fitur sample[0]: {dict(zip(FEATURES, X[0]))}')

    try:
        y_log  = xgb_model.predict(X)
        y_pred = np.maximum(np.expm1(y_log), 0)
        log.info(f'y_log : {y_log}')
        log.info(f'y_pred: {y_pred}')
    except Exception as e:
        write_result('error', f'Inferensi gagal: {e}')
        log.error(str(e)); sys.exit(1)

    predictions = []
    for i, rec in enumerate(records):
        qty = round(float(y_pred[i]), 2)
        predictions.append({'nama_produk': rec['nama_produk'], 'qty_prediksi': qty})
        log.info(f'  {rec["nama_produk"]}: y_log={y_log[i]:.4f} → qty={qty}')

    log.info(f'Prediksi selesai: {len(predictions)} produk, {len(skipped)} dilewati.')

    write_result('success', f'Prediksi berhasil untuk {len(predictions)} produk.', {
        'predictions': predictions,
        'skipped':     skipped,
        'total':       len(predictions),
        'periode':     {'bulan': bulan_prediksi, 'tahun': tahun_prediksi},
        'metode':      'XGBoost',
        'finished_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
    })

    log.info('=== predict_xgb.py selesai ===')


if __name__ == '__main__':
    try:
        main()
    except Exception as e:
        tb = traceback.format_exc()
        log.error(f'Fatal error:\n{tb}')
        write_result('error', f'Fatal error: {e}', {'detail': tb})
        sys.exit(1)