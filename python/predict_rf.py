"""
predict_rf.py — Script inferensi Random Forest untuk Mi Store
═══════════════════════════════════════════════════════════════
FIX v3 — Konsisten dengan train_rf.py v3:

  Perubahan utama dari v2:
  • FEATURES tidak mengandung 'trend' (0-1) — diganti time_idx absolut.
    Trend 0-1 tidak bisa direproduksi saat inferensi karena tidak tahu
    max time_idx produk saat training.
  • hitung_lag_rolling() sudah identik — tidak ada perubahan logika.
  • Sumber data histori tetap dari data_training (agregasi bulanan),
    karena untuk inferensi kita butuh histori qty bulanan per produk.
    Namun, karena train_rf.py v3 membaca dari penjualan raw,
    data_training bisa di-generate ulang atau kita query langsung
    dari penjualan. Solusi: query penjualan langsung (bukan data_training)
    agar konsisten 100% dengan pipeline training.
"""

import sys, os, json, argparse, logging, traceback
from datetime import datetime
from pathlib import Path

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


# ── FEATURES — identik 1:1 dengan train_rf.py v3 ─────────────────────────────
# 'trend' DIHAPUS — diganti time_idx absolut yang bisa dihitung saat inferensi
FEATURES = [
    'bulan', 'kuartal', 'produk_encoded', 'time_idx',
    'harga_avg', 'harga_std', 'promo_avg', 'n_transaksi',
    'qty_lag1', 'qty_lag2', 'qty_lag3',
    'qty_roll3_mean', 'qty_roll3_std', 'qty_roll6_mean',
    'bulan_sin', 'bulan_cos',
]


def hitung_lag_rolling(hist_qty: list) -> dict:
    """
    Hitung lag & rolling untuk periode t+1 dari histori qty bulanan.

    hist_qty: list qty_total per bulan, terurut ascending (lama → baru).
              Nilai ini adalah SUM(qty) per bulan dari tabel penjualan,
              IDENTIK dengan yang dipakai saat training (agregasi bulanan).

    Formula identik dengan train_rf.py v3 (feature_engineering):
      lag1(t+1) = qty(t)       = hist[-1]
      lag2(t+1) = qty(t-1)     = hist[-2]
      lag3(t+1) = qty(t-2)     = hist[-3]

      roll3(t+1): shift(1).rolling(3, min_periods=1).mean()
                = mean dari max 3 nilai terakhir sebelum t+1
                = mean([lag3, lag2, lag1]) dengan min_periods=1

      roll3_std : shift(1).rolling(3, min_periods=2).std()
                = std dari max 3 nilai terakhir, butuh minimal 2 nilai

      roll6(t+1): shift(1).rolling(6, min_periods=1).mean()
                = mean dari max 6 nilai terakhir sebelum t+1
    """
    import numpy as np

    n = len(hist_qty)

    lag1 = float(hist_qty[-1]) if n >= 1 else 0.0
    lag2 = float(hist_qty[-2]) if n >= 2 else 0.0
    lag3 = float(hist_qty[-3]) if n >= 3 else 0.0

    # roll3: min(3, n) nilai terakhir — identik rolling(3, min_periods=1)
    roll3_vals = [float(v) for v in hist_qty[max(0, n - 3):n]]
    roll3_mean = float(np.mean(roll3_vals)) if roll3_vals else 0.0
    # std dengan ddof=1 (pandas default) — identik rolling std
    roll3_std  = float(np.std(roll3_vals, ddof=1)) if len(roll3_vals) >= 2 else 0.0

    # roll6: min(6, n) nilai terakhir — identik rolling(6, min_periods=1)
    roll6_vals = [float(v) for v in hist_qty[max(0, n - 6):n]]
    roll6_mean = float(np.mean(roll6_vals)) if roll6_vals else 0.0

    return {
        'qty_lag1':       lag1,
        'qty_lag2':       lag2,
        'qty_lag3':       lag3,
        'qty_roll3_mean': roll3_mean,
        'qty_roll3_std':  roll3_std,
        'qty_roll6_mean': roll6_mean,
    }


def load_histori_penjualan(conn, produk_upper_list: list,
                           time_idx_target: int) -> 'pd.DataFrame':
    """
    Ambil histori penjualan bulanan dari tabel penjualan (RAW),
    lalu agregasi per (nama_produk, tahun, bulan) — IDENTIK dengan
    pipeline training yang menggunakan agregasi_bulanan().

    Menggunakan tabel penjualan langsung (bukan data_training) agar
    nilai qty_total, harga_avg, harga_std, promo_avg, n_transaksi
    IDENTIK dengan yang digunakan saat training.
    """
    import pandas as pd

    placeholders = ', '.join(['%s'] * len(produk_upper_list))

    query = f"""
        SELECT
            UPPER(TRIM(nama_produk)) AS nama_produk,
            YEAR(tanggal)            AS tahun,
            MONTH(tanggal)           AS bulan,
            CEIL(MONTH(tanggal)/3)   AS kuartal,
            (YEAR(tanggal)*12 + MONTH(tanggal)) AS time_idx,
            SUM(qty)                 AS qty_total,
            AVG(harga)               AS harga_avg,
            STDDEV_SAMP(harga)       AS harga_std,
            AVG(CASE
                WHEN UPPER(TRIM(promo)) = 'YA' THEN 1
                WHEN UPPER(TRIM(promo)) = 'TIDAK' THEN 0
                WHEN promo REGEXP '^[01]$' THEN CAST(promo AS UNSIGNED)
                ELSE 0
            END)                     AS promo_avg,
            COUNT(*)                 AS n_transaksi
        FROM penjualan
        WHERE UPPER(TRIM(nama_produk)) IN ({placeholders})
          AND (YEAR(tanggal)*12 + MONTH(tanggal)) < %s
        GROUP BY
            UPPER(TRIM(nama_produk)),
            YEAR(tanggal),
            MONTH(tanggal),
            CEIL(MONTH(tanggal)/3),
            YEAR(tanggal)*12 + MONTH(tanggal)
        ORDER BY nama_produk, tahun, bulan ASC
    """
    params  = produk_upper_list + [time_idx_target]
    df_hist = pd.read_sql(query, conn, params=params)

    # Pastikan harga_std tidak NULL (produk dengan 1 transaksi/bulan → std=0)
    df_hist['harga_std'] = df_hist['harga_std'].fillna(0)

    return df_hist


def main():
    log.info('=== predict_rf.py v3 dimulai ===')

    try:
        import pandas as pd
        import numpy as np
        import joblib
        import warnings
        warnings.filterwarnings('ignore')
    except ImportError as e:
        write_result('error', f'Import error: {e}')
        log.error(str(e)); sys.exit(1)

    # ── Baca konfigurasi produk ───────────────────────────────────────────────
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

    # time_idx absolut periode prediksi — identik dengan training
    time_idx_target = tahun_prediksi * 12 + bulan_prediksi
    log.info(f'Target: {len(produk_list)} produk | '
             f'{bulan_prediksi}/{tahun_prediksi} (time_idx={time_idx_target})')

    # ── Load model & encoder ──────────────────────────────────────────────────
    try:
        rf_model = joblib.load(args.model_path)
        encoder  = joblib.load(args.encoder_path)
        log.info(f'Model   : {args.model_path}')
        log.info(f'Encoder : {args.encoder_path} | kelas: {len(encoder.classes_)}')
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

    # ── Ambil histori penjualan (RAW, identik dengan pipeline training) ───────
    try:
        produk_upper_list = [p.strip().upper() for p in produk_list]
        df_hist = load_histori_penjualan(conn, produk_upper_list, time_idx_target)
        log.info(f'Data historis penjualan: {df_hist.shape[0]} baris, '
                 f'{df_hist["nama_produk"].nunique()} produk')
    except Exception as e:
        conn.close()
        write_result('error', f'Gagal baca histori penjualan: {e}')
        log.error(str(e)); sys.exit(1)

    conn.close()

    # ── Fitur tetap per periode ───────────────────────────────────────────────
    kuartal   = (bulan_prediksi - 1) // 3 + 1
    bulan_sin = float(np.sin(2 * np.pi * bulan_prediksi / 12))
    bulan_cos = float(np.cos(2 * np.pi * bulan_prediksi / 12))

    known_classes = set(encoder.classes_)
    records = []
    skipped = []

    for produk in produk_list:
        produk_upper = produk.strip().upper()

        # ── Cek encoder ───────────────────────────────────────────────────────
        if produk_upper not in known_classes:
            log.warning(f'"{produk_upper}" tidak ada di encoder — dilewati.')
            skipped.append(produk_upper)
            continue

        produk_encoded = int(encoder.transform([produk_upper])[0])

        # ── Histori produk ini ────────────────────────────────────────────────
        hist = df_hist[df_hist['nama_produk'] == produk_upper].sort_values('time_idx')

        if hist.empty:
            log.warning(f'Tidak ada histori untuk {produk_upper} — pakai nilai nol.')
            lag_roll = {
                'qty_lag1': 0.0, 'qty_lag2': 0.0, 'qty_lag3': 0.0,
                'qty_roll3_mean': 0.0, 'qty_roll3_std': 0.0,
                'qty_roll6_mean': 0.0,
            }
            harga_avg = 0.0
            harga_std = 0.0
            promo_avg = 0.0
            n_trans   = 0.0
        else:
            # Hitung lag & rolling dari qty_total bulanan — identik training
            hist_qty = hist['qty_total'].fillna(0).tolist()
            lag_roll = hitung_lag_rolling(hist_qty)

            # Ambil harga/promo dari baris terakhir (ffill semantics)
            last = hist.iloc[-1]
            harga_avg = float(last['harga_avg'])   if not pd.isna(last['harga_avg'])   else 0.0
            harga_std = float(last['harga_std'])   if not pd.isna(last['harga_std'])   else 0.0
            promo_avg = float(last['promo_avg'])   if not pd.isna(last['promo_avg'])   else 0.0
            n_trans   = float(last['n_transaksi']) if not pd.isna(last['n_transaksi']) else 0.0

        log.info(
            f'[{produk_upper}] encoded={produk_encoded} '
            f'lag1={lag_roll["qty_lag1"]:.1f} '
            f'lag2={lag_roll["qty_lag2"]:.1f} '
            f'lag3={lag_roll["qty_lag3"]:.1f} | '
            f'roll3_mean={lag_roll["qty_roll3_mean"]:.2f} '
            f'roll6_mean={lag_roll["qty_roll6_mean"]:.2f} | '
            f'time_idx={time_idx_target} harga_avg={harga_avg:.0f}'
        )

        records.append({
            'nama_produk':    produk_upper,
            'bulan':          bulan_prediksi,
            'kuartal':        kuartal,
            'produk_encoded': produk_encoded,
            'time_idx':       time_idx_target,   # absolut — identik training
            'harga_avg':      harga_avg,
            'harga_std':      harga_std,
            'promo_avg':      promo_avg,
            'n_transaksi':    n_trans,
            **lag_roll,
            'bulan_sin':      bulan_sin,
            'bulan_cos':      bulan_cos,
        })

    if not records:
        write_result('error', 'Tidak ada produk valid untuk diprediksi.')
        sys.exit(1)

    # ── Inferensi ─────────────────────────────────────────────────────────────
    df_input = pd.DataFrame(records)

    # Pastikan urutan kolom IDENTIK dengan FEATURES
    X = df_input[FEATURES].values

    log.info(f'Inferensi {len(records)} produk…')
    log.info(f'Fitur sample[0]: {dict(zip(FEATURES, X[0]))}')

    try:
        y_log  = rf_model.predict(X)
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
        'finished_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
    })

    log.info('=== predict_rf.py v3 selesai ===')


if __name__ == '__main__':
    try:
        main()
    except Exception as e:
        tb = traceback.format_exc()
        log.error(f'Fatal error:\n{tb}')
        write_result('error', f'Fatal error: {e}', {'detail': tb})
        sys.exit(1)