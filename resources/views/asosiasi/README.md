# Aplikasi Analisis Pola Pembelian Produk DRW Skincare Beauty

Aplikasi ini merupakan aplikasi website yang dibuat untuk kebutuhan skripsi dengan judul **Implementasi Association Rules Menggunakan Algoritma FP-Growth pada Transaksi Penjualan Produk DRW Skincare Beauty**.

Aplikasi digunakan untuk membantu DRW Skincare Beauty dalam menganalisis pola pembelian produk berdasarkan data transaksi penjualan. Hasil analisis ditampilkan dalam bentuk aturan asosiasi (*association rules*) sehingga dapat digunakan sebagai bahan pertimbangan dalam penyusunan strategi penjualan, bundling produk, maupun promosi penjualan.

## Fitur Utama

- Login pengguna.
- Dashboard ringkasan hasil analisis.
- Upload dataset transaksi penjualan.
- Preprocessing data transaksi.
- Analisis pola pembelian menggunakan algoritma FP-Growth.
- Pembentukan association rules berdasarkan nilai support, confidence, dan lift.
- Filter analisis berdasarkan produk, operator, dan waktu transaksi.
- Pengelompokan waktu transaksi berdasarkan shift:
  - Pagi: 08:00–12:59
  - Siang: 13:00–22:00
- Menampilkan hasil frequent itemset dan association rules.
- Export atau download hasil analisis.

## Teknologi yang Digunakan

Aplikasi ini dibangun menggunakan beberapa teknologi berikut:

- Laravel sebagai web application.
- FastAPI sebagai API untuk proses analisis FP-Growth.
- Python untuk pengolahan data dan proses data mining.
- MySQL sebagai database.
- Pandas untuk pengolahan dataset.
- MLXtend untuk implementasi FP-Growth dan association rules.
- Bootstrap atau template dashboard untuk tampilan antarmuka.

## Struktur Umum Aplikasi

```bash
project-skripsi/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── python-api/
│   ├── main.py
│   ├── services/
│   ├── uploads/
│   └── output/
├── .env
├── composer.json
├── package.json
└── README.md
```

> Catatan: Struktur folder dapat disesuaikan dengan struktur project yang digunakan.

## Persyaratan Sistem

Sebelum menjalankan aplikasi, pastikan perangkat sudah memiliki:

- PHP versi 8.1 atau lebih baru.
- Composer.
- Node.js dan NPM.
- MySQL.
- Python versi 3.10 atau lebih baru.
- Git.
- Browser seperti Google Chrome atau Microsoft Edge.

## Cara Instalasi Aplikasi Laravel

### 1. Clone atau Salin Project

Jika project belum ada di perangkat, clone repository Laravel dari GitHub terlebih dahulu:

```bash
git clone https://github.com/alyayulh/drw-analytics.git
cd drw-analytics
```

Jika project tidak menggunakan Git, cukup salin folder project ke dalam komputer, lalu buka folder tersebut menggunakan Visual Studio Code.

### 2. Install Dependency Laravel

Jalankan perintah berikut pada terminal di folder utama project:

```bash
composer install
```

### 3. Install Dependency Frontend

```bash
npm install
```

### 4. Salin File Environment

```bash
cp .env.example .env
```

Jika menggunakan Windows dan perintah di atas tidak bisa dijalankan, salin file `.env.example` secara manual lalu ubah namanya menjadi `.env`.

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Konfigurasi Database

Buka file `.env`, lalu sesuaikan bagian berikut:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` dengan konfigurasi MySQL yang digunakan.

### 7. Jalankan Migrasi Database

```bash
php artisan migrate
```

Jika terdapat seeder untuk data awal, jalankan:

```bash
php artisan db:seed
```

Atau jika ingin menjalankan migrasi dan seeder sekaligus:

```bash
php artisan migrate --seed
```

### 8. Jalankan Storage Link

```bash
php artisan storage:link
```

### 9. Jalankan Laravel

```bash
php artisan serve
```

Aplikasi Laravel dapat diakses melalui browser pada alamat:

```bash
http://127.0.0.1:8000
```

## Cara Instalasi API Python FastAPI

API Python digunakan untuk menjalankan proses analisis FP-Growth. Pada project ini, folder API asosiasi **berbeda folder dengan project Laravel**, sehingga API tidak berada di dalam folder Laravel. Oleh karena itu, project API perlu di-clone secara terpisah dari GitHub.

Contoh struktur folder yang digunakan:

```bash
skripsi/
├── aplikasi-laravel/
└── association-api-drwanalytics/
```

### 1. Clone Repository API Asosiasi

Masuk ke folder tempat penyimpanan project, lalu clone repository API asosiasi dari GitHub.

```bash
git clone https://github.com/laiqahnm/association-api-drwanalytics.git
```

Setelah proses clone selesai, masuk ke folder API asosiasi.

```bash
cd association-api-drwanalytics
```

> Repository API asosiasi yang digunakan adalah `https://github.com/laiqahnm/association-api-drwanalytics`. Folder ini berbeda dengan project Laravel, sehingga harus dijalankan secara terpisah.

### 2. Buat Virtual Environment

```bash
python -m venv venv
```

### 3. Aktifkan Virtual Environment

Untuk Windows:

```bash
venv\Scripts\activate
```

Untuk macOS atau Linux:

```bash
source venv/bin/activate
```

### 4. Install Dependency Python

Jika tersedia file `requirements.txt`, jalankan:

```bash
pip install -r requirements.txt
```

Jika belum tersedia, install package utama berikut:

```bash
pip install fastapi uvicorn pandas openpyxl mlxtend python-multipart
```

### 5. Jalankan API FastAPI

Karena aplikasi Laravel juga berjalan di localhost, API FastAPI disarankan dijalankan pada port yang berbeda, misalnya port `8001`.

```bash
uvicorn main:app --reload --port 8001
```

API dapat diakses melalui alamat:

```bash
http://127.0.0.1:8000/docs
```

Kemudian pastikan URL API pada aplikasi Laravel sudah disesuaikan menjadi:

```bash
http://127.0.0.1:8000
```

## Cara Menjalankan Aplikasi

Untuk menjalankan aplikasi secara lengkap, buka dua terminal.

### Terminal 1: Menjalankan Laravel

```bash
php artisan serve
```

### Terminal 2: Menjalankan API Python

```bash
cd association-api-drwanalytics
venv\Scripts\activate
uvicorn main:app --reload --port 8000
```

Setelah itu, buka browser dan akses:

```bash
http://127.0.0.1:8000
```

## Cara Pemakaian Aplikasi

1. Buka aplikasi melalui browser.
2. Login menggunakan akun yang tersedia.
3. Masuk ke halaman analisis atau menu upload dataset.
4. Upload file dataset transaksi penjualan DRW Skincare Beauty.
5. Pastikan format dataset sesuai dengan kebutuhan aplikasi.
6. Jalankan proses analisis FP-Growth.
7. Sistem akan melakukan preprocessing data.
8. Sistem akan membentuk frequent itemset.
9. Sistem akan menghasilkan association rules.
10. Lihat hasil analisis pada halaman aplikasi.
11. Gunakan hasil aturan asosiasi sebagai rekomendasi strategi penjualan atau promosi.
12. Jika tersedia, download hasil analisis dalam format Excel atau laporan.

## Format Dataset

Dataset yang digunakan adalah data transaksi penjualan produk DRW Skincare Beauty. Dataset minimal memuat informasi seperti:

- Nomor transaksi.
- Tanggal atau waktu transaksi.
- Nama produk.
- Operator.
- Jumlah atau detail pembelian.

Contoh format dataset:

| No Transaksi | Tanggal Transaksi | Produk | Operator | Kategori Waktu | Kanal |
| TRX001 | 2026-01-01 09:15:00 | Facial Wash | Admin 1 | Pagi | Offline |
| TRX001 | 2026-01-01 09:15:00 | Toner | Admin 1 | Pagi | Online |
| TRX002 | 2026-01-01 14:20:00 | Serum | Admin 2 | Siang | Online |

> Nama kolom dapat disesuaikan dengan format dataset asli yang digunakan pada aplikasi.

## Output Aplikasi

Output dari aplikasi berupa:

- Daftar frequent itemset.
- Nilai support.
- Daftar association rules.
- Nilai confidence.
- Nilai lift.
- Rekomendasi pola pembelian produk.
- File hasil analisis jika fitur export tersedia.

Contoh hasil association rules:

| Antecedent | Consequent | Support | Confidence | Lift |
|---|---|---:|---:|---:|
| Facial Wash | Toner | 0.25 | 0.80 | 1.20 |
| Serum | Sunscreen | 0.18 | 0.75 | 1.10 |

## Konfigurasi API pada Laravel

Pastikan file `.env` pada Laravel memiliki konfigurasi URL API, misalnya:

```env
FP_GROWTH_API_URL=http://127.0.0.1:8000
```

Jika nama variabel pada project berbeda, sesuaikan dengan konfigurasi yang digunakan di kode Laravel.

## Troubleshooting

### 1. Laravel tidak bisa dijalankan

Jalankan kembali:

```bash
composer install
php artisan key:generate
php artisan serve
```

### 2. Database error

Pastikan database sudah dibuat di MySQL dan konfigurasi `.env` sudah benar.

```bash
php artisan migrate
```

### 3. API Python tidak terbaca oleh Laravel

Pastikan API FastAPI sudah berjalan.

```bash
uvicorn main:app --reload --port 8001
```

Lalu cek melalui browser:

```bash
http://127.0.0.1:8000/docs
```

### 4. File upload gagal

Pastikan folder upload dan output sudah tersedia serta memiliki izin akses.

```bash
mkdir uploads
mkdir output
```

### 5. Library Python belum terinstall

Aktifkan virtual environment, lalu jalankan:

```bash
pip install fastapi uvicorn pandas openpyxl mlxtend python-multipart
```

## Pengembang

Nama: Laiqah Noor Muin  
NIM: 2207412023  
Program Studi: Teknik Informatika  
Perguruan Tinggi: Politeknik Negeri Jakarta  
Tahun: 2026

## Lisensi

Aplikasi ini dibuat untuk keperluan akademik sebagai bagian dari penyusunan skripsi.
