# SLiMS Plugin: Ekspor Koleksi & Eksemplar ke Excel (.xlsx)

[![SLiMS 9 Bulian](https://img.shields.io/badge/SLiMS-9%20Bulian-0056b3.svg?style=flat-square)](https://slims.web.id)
[![Tested PHP](https://img.shields.io/badge/PHP-7.4%20%7C%208.0%20%7C%208.1%20%7C%208.2-777bb4.svg?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Database](https://img.shields.io/badge/Database-MariaDB%20%7C%20MySQL-orange.svg?style=flat-square&logo=mysql&logoColor=white)](https://mariadb.org)
[![Version](https://img.shields.io/badge/Version-1.0.0-green.svg?style=flat-square)](https://github.com/gusnaldikrist/slims-export-koleksi-excel)
[![License: GPL-3.0](https://img.shields.io/badge/License-GPL--3.0-blue.svg?style=flat-square)](LICENSE)
[![Pure Plugin](https://img.shields.io/badge/Architecture-100%25%20Pure%20Plugin-success.svg?style=flat-square)](#keamanan--arsitektur)

Plugin resmi untuk **SLiMS 9 Bulian** (Senayan Library Management System) untuk mengekspor data bibliografi (katalog) beserta ringkasan eksemplar fisik dan status ketersediaan stok buku secara *real-time* ke format Microsoft Excel (`.xlsx`) profesional.

Dikembangkan secara khusus dengan prinsip **100% Pure Plugin (Zero Core Mutation)** — tidak mengubah satu pun berkas inti SLiMS maupun struktur tabel database aslinya, sehingga sangat aman saat pembaruan (*upgrade*) sistem SLiMS.

---

## 🔍 Work Tested On (Hasil Uji Kompatibilitas)

Plugin ini telah melalui serangkaian pengujian fungsional, performa beban data ribuan baris, dan verifikasi kueri langsung pada lingkungan berikut:

| Komponen | Spesifikasi Teruji | Status |
|---|---|:---:|
| **Aplikasi SLiMS** | SLiMS 9 Bulian (v9.6.1, v9.6.2, dan rilis Bulian terbaru) |  **Working 100%** |
| **PHP Runtime** | PHP 7.4.x, PHP 8.0.x, PHP 8.1.12, PHP 8.2.x |  **Working 100%** |
| **Basis Data** | MariaDB 10.4+, MariaDB 10.5+, MySQL 5.7+, MySQL 8.0+ |  **Working 100%** |
| **Sistem Operasi** | Windows 10/11 (Apache bundled / XAMPP) & Linux (Ubuntu/Debian) |  **Working 100%** |
| **Library Spreadsheet** | `PhpOffice\PhpSpreadsheet` (bawaan SLiMS 9) |  **Working 100%** |
| **Kompatibilitas SQL Mode** | Mendukung `ONLY_FULL_GROUP_BY` dan `STRICT_TRANS_TABLES` |  **Working 100%** |
| **Aplikasi Pembaca Excel** | Microsoft Excel 2016/2019/2021/365, LibreOffice Calc, WPS Office |  **Working 100%** |

---

## 💡 Mengapa Plugin Ini Dibuat?

Pada instalasi standar SLiMS, fitur ekspor bawaan (*Export Data / Bibliography*) memiliki beberapa keterbatasan bagi kebutuhan pelaporan fisik perpustakaan:
1. **Tidak Ada Rekap Kuantitas Fisik per Judul**: Fitur bawaan hanya mengekspor metadata katalog tanpa menghitung berapa jumlah eksemplar fisik yang dimiliki.
2. **Tidak Ada Status Ketersediaan Sirkulasi**: Sulit mengetahui secara instan berapa buku yang sedang ada di rak vs buku yang sedang dipinjam oleh pemustaka.
3. **Format Mentah**: Ekspor bawaan umumnya berupa teks CSV/delimitasi mentah yang membutuhkan perapihan format kolom secara manual.

**Plugin ini menyelesaikan masalah tersebut dengan menyediakan:**
* Format **1 baris per judul koleksi** (*title-based aggregation*).
* **4 Kolom Ketersediaan Stok Fisik Real-time**: Total Eksemplar, Tersedia di Rak, Sedang Dipinjam, dan Rusak/Khusus.
* **Tampilan Excel Siap Pakai**: Header Navy Blue, baris judul membeku (*Freeze Panes*), *AutoFilter* aktif, dan lebar kolom otomatis proporsional tanpa risiko *lag* server.

---

## ✨ Fitur Unggulan

* ** Rincian Ketersediaan Stok Real-time (4 Kolom Terstruktur)**:
  * **Total Eksemplar**: Jumlah seluruh fisik buku yang dimiliki (`COUNT(DISTINCT i.item_id)`).
  * **Tersedia di Rak**: Eksemplar dalam kondisi normal yang siap dipinjam.
  * **Sedang Dipinjam**: Eksemplar yang saat ini sedang aktif dipinjam oleh anggota (terhubung langsung ke tabel transaksi sirkulasi `loan` dengan kondisi `is_return = 0`).
  * **Rusak / Hilang / Khusus**: Eksemplar berstatus fisik khusus (*Repair*, *Missing*, *No Loan*, dll.).
  > *Kaidah stok selalu konsisten: `Total Eksemplar = Tersedia + Dipinjam + Rusak/Khusus`.*
* ** Format Spreadsheet Excel Profesional**:
  * **Freeze Top Row (`A2`)**: Baris header tetap terlihat saat menggulir ribuan data ke bawah.
  * **AutoFilter Excel Aktif**: Filter dropdown otomatis terpasang pada baris judul kolom.
  * **Styling Header Elegan**: Warna latar *Navy Blue* (`#1E3A5F`), teks putih tebal (*bold*), tinggi baris 28pt, rata tengah.
  * **Format Angka Numerik**: Kolom kuantitas stok disimpan dengan tipe numerik asli (`DataType::TYPE_NUMERIC`) dengan format angka ribuan `#,##0` rata tengah (dapat langsung di-SUM atau dianalisis matematis).
  * **Bebas Lag & CPU Spike**: Menghindari fitur auto-size dinamis PHP yang membebani memori, digantikan dengan lebar kolom terukur secara eksplisit (*fixed explicit width*).
* ** Filter Pencarian Fleksibel**:
  * Rentang **Tanggal Input** katalog (`date_from` s.d. `date_to`) dengan opsi menyertakan koleksi tanpa tanggal input.
  * Filter master: **Lokasi Rak**, **Tipe Koleksi**, dan **Status Eksemplar**.
  * Filter **Prefix Nomor Klasifikasi DDC** (misal: mencari seluruh koleksi berawalan `005` atau `2X1`).
* ** Idempotent & Anti-Duplikasi Menu**:
  * Dilengkapi pengaman internal sehingga menu tidak akan pernah muncul ganda di sidebar modul Reporting.

---

## 📦 Tutorial Pemasangan

Tersedia dua cara pemasangan, silakan pilih salah satu:

### Metode 1: Mengunduh File ZIP (Paling Mudah)

1. Unduh repositori ini dalam bentuk file ZIP melalui tombol **Code &rarr; Download ZIP** di GitHub, atau klik tautan rilis terbaru.
2. Ekstrak file ZIP tersebut di komputer Anda.
3. Ubah nama folder hasil ekstrak menjadi:
   ```text
   slims_export_koleksi_excel
   ```
4. Salin (copy) folder `slims_export_koleksi_excel` tersebut ke dalam direktori `plugins/` pada instalasi SLiMS Anda:
   * **Windows (SLiMS Portable/XAMPP)**:
     `C:\slims9_bulian\plugins\slims_export_koleksi_excel\`
   * **Linux (Web Root)**:
     `/var/www/html/slims/plugins/slims_export_koleksi_excel/`

### Metode 2: Menggunakan Git Clone (Direkomendasikan untuk Server/Developer)

Buka terminal atau Git Bash, masuk ke direktori `plugins/` SLiMS Anda, lalu jalankan:

```bash
cd /path/to/slims/plugins/
git clone https://github.com/gusnaldikrist/slims-export-koleksi-excel.git slims_export_koleksi_excel
```

---

## 🚀 Cara Aktivasi & Penggunaan

### Langkah 1: Aktivasi Plugin di Admin SLiMS
1. Masuk ke halaman admin SLiMS Anda (misal: `http://localhost/slims/admin/`).
2. Masuk ke menu **System** (Sistem) pada bilah navigasi atas.
3. Di bilah menu kiri, klik menu **Plugins** (Daftar Plugin).
4. Cari plugin bernama **Ekspor Koleksi & Eksemplar (Excel)**.
5. Ubah sakelar (*toggle switch*) pada kolom paling kanan dari **Disabled** menjadi **Enabled**.
6. Notifikasi sukses akan muncul menandakan plugin telah aktif.

### Langkah 2: Menggunakan Fitur Ekspor
1. Buka modul **Reporting** (Pelaporan) pada bilah navigasi atas.
2. Di bilah menu kiri, klik submenu baru **Ekspor Koleksi & Eksemplar (Excel)**.
3. Centang kolom-kolom data yang ingin Anda sertakan dalam berkas Excel (semua tercentang secara default).
4. *(Opsional)* Tentukan kriteria filter pencarian jika ingin menyaring sebagian koleksi saja.
5. Klik tombol hijau besar **Unduh File Excel (.xlsx)**.
6. Berkas Excel akan langsung terunduh ke komputer Anda dengan nama `ekspor-koleksi-YYYYmmdd-HHmmss.xlsx`.

---

## 📋 Daftar Kolom Data (20 Kolom)

### Grup 1: Data Bibliografi / Katalog (11 Kolom)
| No | Nama Kolom | Tipe Data | Sumber / Penjelasan SQL |
|:--:|---|---|---|
| 1 | **Judul** | Teks | `b.title` |
| 2 | **Pengarang** | Teks | `GROUP_CONCAT(DISTINCT ma.author_name)` (diurutkan alfabet & level) |
| 3 | **Penerbit** | Teks | `mp.publisher_name` |
| 4 | **Tahun Terbit** | Teks | `b.publish_year` |
| 5 | **Tempat Terbit** | Teks | `mpl.place_name` |
| 6 | **ISBN/ISSN** | Teks | `b.isbn_issn` |
| 7 | **Nomor Klasifikasi** | Teks | `b.classification` |
| 8 | **Nomor Panggil** | Teks | `b.call_number` |
| 9 | **Edisi** | Teks | `b.edition` |
| 10 | **Bahasa** | Teks | `ml.language_name` |
| 11 | **GMD** | Teks | `mg.gmd_name` |

### Grup 2: Data Eksemplar & Ketersediaan Stok (9 Kolom)
| No | Nama Kolom | Tipe Data | Sumber / Penjelasan SQL |
|:--:|---|---|---|
| 12 | **Total Eksemplar** | **Numeric** | `COUNT(DISTINCT i.item_id)` |
| 13 | **Tersedia di Rak** | **Numeric** | Eksemplar kondisi baik & tidak sedang dipinjam |
| 14 | **Sedang Dipinjam** | **Numeric** | Eksemplar pada transaksi aktif `loan.is_return = 0` |
| 15 | **Rusak/Hilang/Khusus** | **Numeric** | Eksemplar berstatus `R`, `MIS`, `NL`, dll. |
| 16 | **Daftar Barcode Eksemplar** | Teks | Seluruh barcode eksemplar buku dipisah titik koma (`; `) |
| 17 | **Lokasi Rak** | Teks | Nama master lokasi rak (`mloc.location_name`) |
| 18 | **Tipe Koleksi** | Teks | Nama master tipe koleksi (`mc.coll_type_name`) |
| 19 | **Status Eksemplar** | Teks | Nama status eksemplar (`mis.item_status_name`) |
| 20 | **Kode Inventaris** | Teks | Seluruh kode inventaris buku dipisah titik koma (`; `) |

---

## 🔒 Keamanan & Arsitektur

* **Proteksi Hak Akses (RBAC)**: Form dan endpoint ekspor diproteksi ketat menggunakan `utility::havePrivilege('reporting', 'r')`. Pengguna tanpa izin modul Reporting akan langsung ditolak (`HTTP 403 Forbidden`).
* **Batas Maksimal Ekspor (*Safety Cap*)**: Dibatasi maksimal **20.000 judul koleksi** per unduhan (`SLIMS_EXPORT_CAP`) guna melindungi server perpustakaan dari kehabisan memori (*memory exhaustion*).
* **Strict SQL Mode Compatible**: Dilengkapi penanganan otomatis `SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))` sehingga bebas dari error MySQL 1055.
* **100% Pure Plugin (Zero Core Mutation)**: Seluruh kode berada eksklusif di dalam satu folder plugin. Anda tidak perlu menyunting file SLiMS sama sekali.

---

## 📂 Struktur Berkas

```text
slims_export_koleksi_excel/
├── .gitignore                             # Aturan pengabaian berkas sementara git
├── LICENSE                                # Lisensi open-source GNU GPL-3.0
├── README.md                              # Dokumentasi lengkap & panduan instalasi
├── slims_export_koleksi_excel.plugin.php  # Metadata plugin & pendaftaran menu idempotent
├── lib/
│   └── columns.php                        # Registry 20 kolom data, lebar kolom, dan query builder
└── pages/
    ├── index.php                          # Antarmuka form filter & pemilihan kolom data
    └── download.php                       # Engine streaming download Excel & penataan gaya visual
```

---

## 👤 Author & Lisensi

* **Author**: **Gusnaldi Kristiadi Syah**
* **GitHub Profile**: [@gusnaldikrist](https://github.com/gusnaldikrist)
* **Lisensi**: [GNU General Public License v3.0 (GPL-3.0)](LICENSE)
* **Komunitas**: Dibuat dengan dedikasi untuk seluruh pengelola perpustakaan dan komunitas pegiat SLiMS (Senayan Library Management System) di Indonesia dan dunia.

---

*Jika plugin ini bermanfaat untuk perpustakaan Anda, jangan ragu untuk memberikan ⭐ **Star** pada repositori ini!*
