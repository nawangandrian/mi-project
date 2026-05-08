# -*- coding: utf-8 -*-
"""
import_excel.py
───────────────
Script Python untuk memproses file Excel TRANSAKSI HARIAN MI STORE.
Dipanggil oleh ImportController.php via exec().

Usage:
    python3 import_excel.py \
        --file /path/to/TRANSAKSI.xlsx \
        --result /path/to/result.json \
        [--sheets JAN26,FEB26,MAR26] \
        [--mode auto|manual]

Output JSON:
    {
        "status": "success"|"error",
        "message": "...",
        "summary": {
            "total_transaksi": 120,
            "total_produk_baru": 15,
            "sheets_processed": ["JAN26","FEB26"],
            "sheets_failed": []
        },
        "produk": [
            {"nama_produk": "Redmi Note 13", "is_active": 1}
        ],
        "penjualan": [
            {
                "no_nota": "001",
                "nama_produk": "Redmi Note 13",
                "qty": 2,
                "harga": 2499000,
                "tanggal": "2026-01-05",
                "promo": 0
            }
        ]
    }
"""

import sys
import os
import json
import argparse
import traceback
import re
from datetime import datetime

# ── Dependency check ──────────────────────────────────────────────────────────
try:
    import pandas as pd
    import openpyxl
except ImportError as e:
    print(json.dumps({
        "status": "error",
        "message": f"Library tidak tersedia: {e}. Jalankan: pip install pandas openpyxl"
    }))
    sys.exit(1)


# ── Argparse ──────────────────────────────────────────────────────────────────
def parse_args():
    p = argparse.ArgumentParser(description="Proses Excel MI Store → JSON")
    p.add_argument("--file",        required=True,  help="Path file Excel (.xlsx)")
    p.add_argument("--result",      default="",     help="Path output JSON")
    p.add_argument("--sheets",      default="",     help="Comma-separated sheet names")
    p.add_argument("--mode",        default="auto", choices=["auto", "manual"])
    # ← TAMBAHKAN INI
    p.add_argument("--list-sheets", action="store_true",
                   help="Hanya tampilkan daftar sheet (output JSON ke stdout)")
    return p.parse_args()


# ── Konstanta ─────────────────────────────────────────────────────────────────
MONTH_MAP = {
    "JAN": 1, "FEB": 2, "MAR": 3, "APR": 4,
    "MEI": 5, "JUN": 6, "JUL": 7, "AGS": 8,
    "SEP": 9, "OKT": 10, "NOV": 11, "DES": 12,
    "MAY": 5, "AUG": 8, "OCT": 10, "DEC": 12,
}

DAY_NAMES = {"SENIN", "SELASA", "RABU", "KAMIS", "JUMAT", "JUM'AT", "SABTU", "MINGGU", "SUNDAY"}

# Nama-nama kolom yang mungkin ada di header Excel
COL_ALIASES = {
    "no_nota":    ["no.nota", "no nota", "nota", "no_nota", "invoice"],
    "qty":        ["qty", "jumlah", "jml", "kuantitas"],
    "barang":     ["barang", "nama barang", "produk", "nama_produk", "item", "nama produk"],
    "harga":      ["srp", "harga", "price", "harga jual", "harga_srp", "harga srp"],
    "promo":      ["promo", "keterangan", "ket", "ket.", "diskon"],
    "harga_jadi": ["harga_jadi", "harga jadi", "hargajadi", "harga_netto", "harga_final", "harga_total"],
    "edc":        ["edc"],
    "kredit":     ["kredit", "credit", "kredit card", "kredit bank"],
}


def parse_number(raw):
    if raw is None:
        return 0
    if isinstance(raw, str):
        raw = raw.strip().replace(',', '').replace(' ', '')
        if raw == '':
            return 0
    try:
        return int(float(raw))
    except (ValueError, TypeError):
        return 0


# ── Helper: parse tanggal dari string ─────────────────────────────────────────
def parse_tanggal(raw: str, fallback_year: int = None) -> str | None:
    """
    Terima berbagai format:
    - '01/01/2026 RABU'  → '2026-01-01'
    - '01/01/2026'       → '2026-01-01'
    - '2026-01-01'       → '2026-01-01'
    - datetime object    → format string
    Kembalikan None jika tidak bisa di-parse.
    """
    if raw is None:
        return None

    if isinstance(raw, (datetime,)):
        return raw.strftime("%Y-%m-%d")

    if hasattr(raw, 'strftime'):  # pandas Timestamp
        return raw.strftime("%Y-%m-%d")

    raw_str = str(raw).strip()

    # Ekstrak pola tanggal dd/mm/yyyy atau yyyy-mm-dd
    m = re.search(r'(\d{1,2})[/\-](\d{1,2})[/\-](\d{2,4})', raw_str)
    if m:
        a, b, c = m.group(1), m.group(2), m.group(3)
        if len(c) == 2:
            c = "20" + c
        # Deteksi urutan: jika a > 12 → pasti hari
        if int(a) > 12:
            # dd/mm/yyyy
            try:
                return datetime(int(c), int(b), int(a)).strftime("%Y-%m-%d")
            except ValueError:
                pass
        else:
            # Coba dd/mm/yyyy dulu
            try:
                return datetime(int(c), int(b), int(a)).strftime("%Y-%m-%d")
            except ValueError:
                pass
    return None


def sheet_to_year_month(sheet_name: str):
    """JAN26 → (2026, 1), FEB25 → (2025, 2)"""
    sheet_upper = sheet_name.upper().strip()
    m = re.match(r'^([A-Z]{2,3})(\d{2,4})$', sheet_upper)
    if not m:
        return None, None
    mon_str, yr_str = m.group(1), m.group(2)
    month = MONTH_MAP.get(mon_str)
    year  = int(yr_str) if len(yr_str) == 4 else 2000 + int(yr_str)
    return year, month


# ── Deteksi kolom dari header row ─────────────────────────────────────────────
def detect_columns(header_row) -> dict:
    """Kembalikan dict {canonical_name: column_index}"""
    mapping = {}
    for idx, cell in enumerate(header_row):
        cell_str = str(cell).strip().lower() if cell is not None else ""
        for canon, aliases in COL_ALIASES.items():
            if cell_str in aliases and canon not in mapping:
                mapping[canon] = idx
    return mapping


# ── Proses satu sheet ─────────────────────────────────────────────────────────
def process_sheet(df_raw: pd.DataFrame, sheet_name: str, mode: str) -> list[dict]:
    """
    Kembalikan list dict penjualan dari satu sheet.
    Setiap row: no_nota, nama_produk, qty, harga, tanggal, promo
    """
    rows_out = []
    year, month = sheet_to_year_month(sheet_name)

    # Cari baris header (mengandung NO.NOTA / BARANG)
    header_idx = None
    for i, row in df_raw.iterrows():
        row_vals = [str(v).strip().upper() for v in row.values if pd.notna(v)]
        if any("NOTA" in v or "BARANG" in v for v in row_vals):
            header_idx = i
            break

    if header_idx is None:
        # Fallback: asumsikan header di baris ke-3 (index 2)
        header_idx = 2 if len(df_raw) > 3 else 0

    header_row = df_raw.iloc[header_idx].tolist()
    col_map    = detect_columns(header_row)

    # Minimal butuh no_nota dan barang
    if "no_nota" not in col_map or "barang" not in col_map:
        # Fallback paksa posisi berdasarkan pola Excel MI Store
        # Biasanya: col 2=NO.NOTA, col 3=QTY, col 4=BARANG, col 6=SRP
        col_map = {
            "no_nota": 2,
            "qty":     3,
            "barang":  4,
            "harga":   6,
            "promo":   7,
        }

    current_date = None
    data_rows    = df_raw.iloc[header_idx + 1:].reset_index(drop=True)

    for _, row in data_rows.iterrows():
        vals = row.tolist()
        if not vals:
            continue

        # ── Deteksi baris tanggal ─────────────────────────────────────
        val_a = str(vals[0]).strip() if pd.notna(vals[0]) else ""
        val_a_upper = val_a.upper()

        is_date_row = (
            "/" in val_a or
            "-" in val_a or
            any(d in val_a_upper for d in DAY_NAMES) or
            re.search(r'\d{1,2}[/\-]\d{1,2}[/\-]\d{2,4}', val_a)
        )

        if is_date_row and val_a:
            parsed = parse_tanggal(val_a, fallback_year=year)
            if parsed:
                current_date = parsed
            continue

        # ── Baris data transaksi ──────────────────────────────────────
        def safe_get(idx):
            try:
                v = vals[idx]
                return v if pd.notna(v) else None
            except IndexError:
                return None

        no_nota_raw = safe_get(col_map.get("no_nota", 2))
        barang_raw  = safe_get(col_map.get("barang",  4))
        qty_raw     = safe_get(col_map.get("qty",     3))
        harga_raw   = safe_get(col_map.get("harga",   6))
        promo_raw   = safe_get(col_map.get("promo",   7))
        harga_jadi_raw = safe_get(col_map.get("harga_jadi", 8))
        edc_raw        = safe_get(col_map.get("edc", 9))
        kredit_raw     = safe_get(col_map.get("kredit", 10))

        # Filter: no_nota harus ada dan berupa angka atau string non-kosong
        if no_nota_raw is None:
            continue
        no_nota_str = str(no_nota_raw).strip()
        if not no_nota_str or no_nota_str.upper() in {"NAN", "NONE", "NO.NOTA"}:
            continue
        # Harus berupa angka
        if not re.match(r'^\d+$', no_nota_str):
            continue

        barang_str = str(barang_raw).strip() if barang_raw is not None else ""
        if not barang_str or barang_str.upper() in {"NAN", "NONE", "BARANG"}:
            continue

        try:
            qty = int(float(str(qty_raw))) if qty_raw is not None else 1
        except (ValueError, TypeError):
            qty = 1

        try:
            harga = int(float(str(harga_raw))) if harga_raw is not None else 0
        except (ValueError, TypeError):
            harga = 0

        harga_jadi = parse_number(harga_jadi_raw)
        edc        = parse_number(edc_raw)
        kredit     = parse_number(kredit_raw)

        # Deteksi promo: jika kolom Harga_Jadi + EDC + Kredit berbeda dari SRP
        promo = 0
        if promo_raw is not None:
            promo_str = str(promo_raw).strip().lower()
            if any(k in promo_str for k in ["promo", "diskon", "disc", "special", "bundling"]):
                promo = 1

        if any(v is not None for v in (harga_jadi_raw, edc_raw, kredit_raw)):
            if harga_jadi + edc + kredit != harga:
                promo = 1

        # Tanggal fallback: buat dari nama sheet
        tanggal = current_date
        if tanggal is None and year and month:
            tanggal = f"{year}-{month:02d}-01"

        rows_out.append({
            "no_nota":     no_nota_str,
            "nama_produk": barang_str,
            "qty":         qty,
            "harga":       harga,
            "tanggal":     tanggal,
            "promo":       promo,
            "harga_jadi":  harga_jadi,
            "edc":         edc,
            "kredit":      kredit,
        })

    return rows_out


# ── Main ──────────────────────────────────────────────────────────────────────
def main():
    args = parse_args()

    # Validasi file ada
    if not os.path.isfile(args.file):
        out = {"status": "error", "message": f"File tidak ditemukan: {args.file}"}
        if args.list_sheets:
            print(json.dumps(out))
        elif args.result:
            write_result(args.result, out)
        sys.exit(1)

    # ── MODE BARU: --list-sheets → hanya kembalikan daftar sheet ke stdout ──
    if args.list_sheets:
        try:
            wb = openpyxl.load_workbook(args.file, read_only=True, data_only=True)
            sheets = wb.sheetnames
            wb.close()
            print(json.dumps({"status": "success", "sheets": sheets}))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": f"Gagal membaca file: {e}"}))
            sys.exit(1)

    # Buka workbook
    try:
        xl = pd.ExcelFile(args.file, engine="openpyxl")
        available_sheets = xl.sheet_names
    except Exception as e:
        out = {"status": "error", "message": f"Gagal membuka file Excel: {e}"}
        write_result(args.result, out)
        sys.exit(1)

    # Tentukan sheet yang akan diproses
    if args.sheets.strip():
        target_sheets = [s.strip() for s in args.sheets.split(",") if s.strip()]
    else:
        # Auto-detect: filter sheet yang cocok pola bulan
        target_sheets = [s for s in available_sheets if sheet_to_year_month(s)[0] is not None]
        if not target_sheets:
            target_sheets = available_sheets  # proses semua jika tidak ada yang cocok

    all_penjualan    = []
    sheets_processed = []
    sheets_failed    = []

    for sheet in target_sheets:
        if sheet not in available_sheets:
            sheets_failed.append({"sheet": sheet, "error": "Sheet tidak ada"})
            continue
        try:
            df_raw = pd.read_excel(args.file, sheet_name=sheet, header=None, engine="openpyxl")
            rows   = process_sheet(df_raw, sheet, args.mode)
            all_penjualan.extend(rows)
            sheets_processed.append(sheet)
            print(f"✅ Sheet {sheet}: {len(rows)} transaksi ditemukan", file=sys.stderr)
        except Exception as e:
            sheets_failed.append({"sheet": sheet, "error": str(e)})
            print(f"⚠️  Sheet {sheet} gagal: {e}", file=sys.stderr)
            traceback.print_exc(file=sys.stderr)

    if not all_penjualan:
        out = {
            "status":  "error",
            "message": "Tidak ada data transaksi yang berhasil diekstrak dari file.",
            "summary": {
                "total_transaksi":  0,
                "sheets_processed": sheets_processed,
                "sheets_failed":    sheets_failed,
            },
        }
        write_result(args.result, out)
        sys.exit(1)

    # Deduplikasi + kumpulkan produk unik
    seen_pairs = set()
    clean_penjualan = []
    produk_set = set()

    for row in all_penjualan:
        key = (row["no_nota"], row["nama_produk"])
        if key in seen_pairs:
            continue
        seen_pairs.add(key)
        clean_penjualan.append(row)
        produk_set.add(row["nama_produk"])

    produk_list = [{"nama_produk": p, "is_active": 1} for p in sorted(produk_set)]

    out = {
        "status":  "success",
        "message": f"Berhasil memproses {len(sheets_processed)} sheet, {len(clean_penjualan)} transaksi, {len(produk_list)} produk unik.",
        "summary": {
            "total_transaksi":  len(clean_penjualan),
            "total_produk_baru": len(produk_list),
            "sheets_processed": sheets_processed,
            "sheets_failed":    sheets_failed,
        },
        "produk":    produk_list,
        "penjualan": clean_penjualan,
    }

    write_result(args.result, out)
    print(f"\n✨ Selesai. Total: {len(clean_penjualan)} transaksi, {len(produk_list)} produk.", file=sys.stderr)


def write_result(path: str, data: dict):
    os.makedirs(os.path.dirname(path) or ".", exist_ok=True)
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)


if __name__ == "__main__":
    main()