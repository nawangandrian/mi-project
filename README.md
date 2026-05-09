# Mi Store · Sistem Prediksi Penjualan Smartphone

> Platform analitik berbasis **Random Forest Regressor** untuk memprediksi penjualan smartphone di Mi Store Kudus. Dibangun di atas CodeIgniter 4 dengan integrasi Python (scikit-learn) sebagai ML engine.

---

## Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Fitur](#fitur)
- [Tech Stack](#tech-stack)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Struktur Database](#struktur-database)
- [Struktur Direktori](#struktur-direktori)
- [Role & Akses](#role--akses)
- [Alur ML Pipeline](#alur-ml-pipeline)
- [Performa Model](#performa-model)
- [Penggunaan](#penggunaan)

---

## Tentang Proyek

Mi Store Kudus membutuhkan sistem yang mampu menganalisis data penjualan historis dan menghasilkan prediksi penjualan smartphone untuk periode mendatang. Sistem ini menggunakan algoritma **Random Forest Regressor** dengan evaluasi 5-fold cross-validation, sehingga menghasilkan prediksi yang akurat dan dapat diandalkan untuk pengambilan keputusan bisnis.

---

## Fitur

- **Dashboard Analitik** — Ringkasan omset, transaksi, tren bulanan/harian, top produk, dan perbandingan antar tahun dengan filter periode dinamis
- **Manajemen Data Produk** — CRUD produk dengan import Excel/CSV dan export
- **Manajemen Data Penjualan** — CRUD transaksi penjualan dengan import massal dan export
- **Import Data** — Preview sheet Excel sebelum proses, riwayat import, validasi data
- **Training Model ML** — Latih ulang model Random Forest langsung dari UI, monitoring status training real-time, riwayat sesi training
- **Prediksi Penjualan** — Jalankan prediksi per produk untuk periode mendatang, riwayat prediksi, sinkronisasi aktual vs prediksi
- **Evaluasi Model** — Tampilan metrik R², RMSE, MAE, MAPE, akurasi, diagram feature importance, perbandingan antar model
- **Manajemen User** — CRUD user dengan role-based access control (Admin & CS)
- **Autentikasi** — Session-based login, CSRF protection, password hashing bcrypt

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| **Backend** | CodeIgniter 4.7.2 · PHP 8.3 · MySQL 8 |
| **ML Engine** | Python 3 · scikit-learn · RandomForestRegressor |
| **Data Pipeline** | Pandas · NumPy · Pipeline · ColumnTransformer |
| **Evaluasi** | R² · RMSE · MAE · MAPE · 5-fold Cross-Validation |
| **Frontend** | Bootstrap 5 · Chart.js · DataTables · SweetAlert2 |
| **Keamanan** | Session Auth · CSRF · Role-based Access · bcrypt |

---

## Persyaratan Sistem

### PHP
- PHP **8.2** atau lebih tinggi (direkomendasikan 8.3)
- Extension wajib:
  - `intl`
  - `mbstring`
  - `json` (aktif by default)
  - `mysqlnd`
  - `libcurl`
  - `zip` (untuk import/export Excel)

### Python
- Python **3.9** atau lebih tinggi
- Packages (lihat `requirements.txt`):
  ```
  scikit-learn>=1.3
  pandas>=2.0
  numpy>=1.24
  joblib>=1.3
  ```

### Database
- MySQL **8.0** atau lebih tinggi

### Web Server
- Apache (dengan `mod_rewrite`) atau Nginx
- Document root diarahkan ke folder `public/`

---

## Instalasi

### 1. Clone & Install Dependency PHP

```bash
git clone https://github.com/username/mi-store.git
cd mi-store
composer install
```

### 2. Install Dependency Python

```bash
pip install -r requirements.txt
```

### 3. Konfigurasi Environment

```bash
cp env .env
```

Edit file `.env`:

```env
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost/mi-store/public/'

database.default.hostname = localhost
database.default.database = mi_store
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.port     = 3306

# Path Python executable (sesuaikan dengan sistem)
app.pythonPath = python
# atau: /usr/bin/python3  (Linux)
# atau: C:/Python312/python.exe  (Windows)
```

### 4. Buat Database & Jalankan Migration

```bash
php spark db:create mi_store
php spark migrate
php spark db:seed UserSeeder
```

### 5. Konfigurasi Web Server

**Apache** — pastikan `mod_rewrite` aktif, arahkan document root ke `public/`:

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/mi-store/public
    ServerName mi-store.local
    <Directory /var/www/html/mi-store/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**

```nginx
server {
    listen 80;
    server_name mi-store.local;
    root /var/www/html/mi-store/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 6. Permission Folder

```bash
chmod -R 775 writable/
chmod -R 775 public/uploads/
```

---

## Konfigurasi

File konfigurasi utama ada di `app/Config/`. Yang perlu diperhatikan:

| File | Keterangan |
|---|---|
| `app/Config/App.php` | Base URL, timezone, enkripsi |
| `app/Config/Database.php` | Koneksi database |
| `app/Config/Filters.php` | Registrasi filter `auth` dan `role` |
| `app/Config/Routes.php` | Definisi route dan pembatasan akses |

---

## Struktur Database

Tabel utama yang digunakan:

| Tabel | Keterangan |
|---|---|
| `users` | Data user dengan kolom `role` (admin/cs) |
| `produk` | Katalog produk smartphone |
| `penjualan` | Transaksi penjualan harian |
| `model_training` | Riwayat sesi training ML (metrik, hyperparameter, path model) |
| `model_training_diagram` | Path diagram evaluasi per training session |
| `prediksi` | Hasil prediksi penjualan per produk per periode |
| `import_log` | Riwayat import data |

---

## Struktur Direktori

```
mi-store/
├── app/
│   ├── Config/
│   │   ├── Filters.php          # Registrasi filter auth & role
│   │   └── Routes.php           # Route dengan pembatasan role
│   ├── Controllers/
│   │   ├── AuthController.php   # Login, logout
│   │   ├── DashboardController.php
│   │   ├── TrainingController.php
│   │   ├── PrediksiController.php
│   │   ├── ProdukController.php
│   │   ├── PenjualanController.php
│   │   ├── ImportController.php
│   │   └── UserController.php
│   ├── Filters/
│   │   ├── AuthFilter.php       # Cek session logged_in
│   │   └── RoleFilter.php       # Cek role user
│   ├── Models/
│   │   ├── UserModel.php
│   │   ├── ProdukModel.php
│   │   ├── PenjualanModel.php
│   │   ├── ModelTrainingModel.php
│   │   └── PrediksiModel.php
│   └── Views/
│       ├── pages/
│       │   ├── auth/            # Halaman login
│       │   ├── dashboard/       # Dashboard utama
│       │   ├── training/        # Training & evaluasi model
│       │   ├── prediksi/        # Prediksi penjualan
│       │   ├── produk/
│       │   ├── penjualan/
│       │   ├── import/
│       │   └── user/
│       └── layouts/             # Layout, sidebar, header
├── python/
│   ├── train.py                 # Script training Random Forest
│   ├── predict.py               # Script prediksi
│   └── requirements.txt
├── public/
│   ├── index.php                # Entry point (document root di sini)
│   └── uploads/                 # Model .pkl, encoder, diagram
├── writable/                    # Cache, logs, session
├── .env                         # Konfigurasi environment (jangan di-commit)
├── composer.json
└── README.md
```

---

## Role & Akses

Sistem menggunakan dua role utama yang dikontrol via `RoleFilter`:

| Fitur | Admin | CS |
|---|---|---|
| Dashboard | ✅ | ✅ |
| Import Data | ✅ | ✅ |
| Data Produk | ✅ | ✅ |
| Data Penjualan | ✅ | ✅ |
| Prediksi Penjualan | ✅ | ❌ |
| Training Model | ✅ | ❌ |
| Manajemen User | ✅ | ❌ |

Akun default setelah seeding:

| Username | Password | Role |
|---|---|---|
| `admin` | `admin123` | admin |
| `cs` | `cs1234` | cs |

> Segera ganti password default setelah instalasi pertama.

---

## Alur ML Pipeline

```
Data Penjualan (MySQL)
        ↓
  train.py (Python)
        ↓
  Preprocessing (Pandas + ColumnTransformer)
  • Label encoding produk
  • Feature engineering (bulan, tahun, lag, rolling avg)
        ↓
  RandomForestRegressor
  • GridSearchCV (5-fold CV)
  • best_params → disimpan ke DB
        ↓
  Evaluasi: R², RMSE, MAE, MAPE, Akurasi
        ↓
  Simpan model (.pkl) + encoder (.pkl)
  Update tabel model_training (is_active = 1)
        ↓
  predict.py → hasil ke tabel prediksi
```

---

## Performa Model

Hasil evaluasi pada data penjualan Mi Store Kudus:

| Metrik | Nilai |
|---|---|
| **R² Score** | 0.930 – 0.970 |
| **Akurasi** | 94% – 97% |
| **Cross-Validation** | 5-fold CV |
| **N Estimators** | 200 Trees |
| **Max Depth** | 15 |
| **Algoritma** | Random Forest Regressor |

> Nilai aktual ditampilkan di halaman login dan sidebar setelah model dilatih.

---

## Penggunaan

### Login
Akses `http://localhost/mi-store/public/login`, masuk dengan akun yang tersedia.

### Training Model
1. Pastikan data penjualan sudah cukup (minimal 3 bulan)
2. Menu **Latih Model** → **Proses Training**
3. Monitoring status training secara real-time
4. Setelah selesai, aktifkan model dari halaman **Riwayat Model**

### Menjalankan Prediksi
1. Pastikan ada model aktif
2. Menu **Prediksi Penjualan** → **Jalankan Prediksi**
3. Pilih periode (bulan & tahun target)
4. Hasil prediksi per produk tersimpan dan dapat dievaluasi di **Evaluasi Model**

### Import Data
1. Menu **Import Data** → upload file Excel/CSV
2. Preview sheet dan kolom sebelum diproses
3. Validasi otomatis, data duplikat diabaikan

---

## Server Requirements

PHP version 8.2 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> **Warning**
> - The end of life date for PHP 8.1 was December 31, 2025.
> - The end of life date for PHP 8.2 will be December 31, 2026.
> - Gunakan PHP 8.3 untuk performa dan keamanan terbaik.

---

## Lisensi

Proyek ini dibuat untuk keperluan akademik. Tidak untuk distribusi komersial tanpa izin.

---

*Mi Store · Kudus · Sistem Prediksi Penjualan Smartphone · Random Forest Regressor*
*CodeIgniter 4 + scikit-learn*