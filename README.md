# Plugin SLiMS: Ekspor Koleksi & Eksemplar ke Excel (.xlsx)

Plugin untuk aplikasi **SLiMS 9 Bulian** untuk mengunduh data buku dan jumlah eksemplar ke dalam satu file spreadsheet Microsoft Excel (`.xlsx`).

Hasil unduhan menampilkan data lengkap: data judul buku, jumlah buku di rak, buku yang sedang dipinjam, dan buku yang rusak atau hilang.

---

## ✅ Telah Diuji Pada (Work Tested On)

Plugin ini telah diuji dan berfungsi dengan baik pada:
* **Aplikasi**: SLiMS 9.8.0 Bulian D'Ace
* **Program Pembaca Excel**: Microsoft Excel, LibreOffice Calc, dan WPS Office

---

## 💡 Mengapa Menggunakan Plugin Ini?

Fitur ekspor bawaan SLiMS hanya mengunduh data judul buku tanpa menghitung jumlah fisik buku di perpustakaan.

Plugin ini membantu pustakawan dengan cara:
1. **Satu Judul Satu Baris**: Tiap judul buku hanya memakan satu baris di Excel sehingga data tidak berantakan.
2. **Menghitung Stok Otomatis**: Menampilkan total buku, buku yang ada di rak, dan buku yang sedang dipinjam pemustaka secara tepat.
3. **Format Rapi Siap Pakai**: Judul kolom berwarna biru, baris judul terkunci saat digulir ke bawah (*freeze pane*), dan tombol filter otomatis sudah aktif di Excel.

---

## 📦 Cara Memasang Plugin

Ikuti 4 langkah mudah berikut:

1. Klik tombol hijau **Code** di bagian atas halaman GitHub ini, lalu pilih **Download ZIP**.
2. Buka (ekstrak) file ZIP yang sudah diunduh di komputer Anda.
3. Ubah nama folder hasil ekstrak menjadi:
   ```text
   slims_export_koleksi_excel
   ```
4. Salin (copy) folder `slims_export_koleksi_excel` tersebut ke dalam folder **plugins** pada komputer atau server SLiMS Anda.
   * Contoh lokasi: `C:\slims9_bulian\plugins\slims_export_koleksi_excel\`

---

## 🚀 Cara Mengaktifkan dan Menggunakan

### Langkah 1: Mengaktifkan Plugin
1. Masuk ke halaman admin SLiMS Anda.
2. Klik menu **System** (Sistem) di bilah navigasi atas.
3. Klik menu **Plugins** di menu sebelah kiri.
4. Cari nama plugin **Ekspor Koleksi & Eksemplar (Excel)**.
5. Ubah tombol sakelar di sebelah kanan menjadi **Enabled** (Aktif).

### Langkah 2: Mengunduh Laporan Excel
1. Klik menu **Reporting** (Pelaporan) di bilah navigasi atas.
2. Klik submenu **Ekspor Koleksi & Eksemplar (Excel)** di sebelah kiri.
3. Centang data yang ingin Anda masukkan ke dalam laporan (semua kotak sudah tercentang secara otomatis).
4. *(Pilihan)* Isi pilihan tanggal input atau lokasi rak jika Anda hanya ingin menyaring sebagian buku.
5. Klik tombol hijau **Unduh File Excel (.xlsx)**. File laporan Excel akan langsung terunduh ke komputer Anda.

---

## 📋 Daftar Kolom Laporan (20 Kolom)

### 1. Data Katalog Buku (11 Kolom)
| No | Nama Kolom | Keterangan Isi Kolom |
|:--:|---|---|
| 1 | **Judul** | Judul utama dan anak judul buku |
| 2 | **Pengarang** | Nama-nama penulis atau pengarang buku |
| 3 | **Penerbit** | Nama perusahaan atau lembaga yang menerbitkan buku |
| 4 | **Tahun Terbit** | Tahun buku diterbitkan |
| 5 | **Tempat Terbit** | Kota tempat buku diterbitkan |
| 6 | **ISBN/ISSN** | Nomor standar buku atau terbitan berseri |
| 7 | **Nomor Klasifikasi** | Nomor klasifikasi subjek buku (misal: 005 atau 2X1) |
| 8 | **Nomor Panggil** | Nomor panggil buku pada label punggung buku |
| 9 | **Edisi** | Keterangan cetakan atau edisi buku |
| 10 | **Bahasa** | Bahasa yang digunakan di dalam buku |
| 11 | **GMD** | Bentuk media buku (misal: Text, Audio, Visual) |

### 2. Data Stok dan Ketersediaan Fisik Buku (9 Kolom)
| No | Nama Kolom | Keterangan Isi Kolom |
|:--:|---|---|
| 12 | **Total Eksemplar** | Jumlah seluruh fisik buku yang dimiliki perpustakaan |
| 13 | **Tersedia di Rak** | Jumlah buku yang ada di rak dan siap dipinjam pemustaka |
| 14 | **Sedang Dipinjam** | Jumlah buku yang saat ini sedang dipinjam oleh pemustaka |
| 15 | **Rusak/Hilang/Khusus** | Jumlah buku yang rusak, hilang, atau hanya dibaca di tempat |
| 16 | **Daftar Barcode Eksemplar** | Seluruh nomor barcode eksemplar buku dipisah titik koma (`; `) |
| 17 | **Lokasi Rak** | Lokasi atau ruangan tempat buku disimpan |
| 18 | **Tipe Koleksi** | Jenis koleksi buku (misal: Buku Teks, Referensi, Tandon) |
| 19 | **Status Eksemplar** | Status kondisi fisik buku |
| 20 | **Kode Inventaris** | Nomor inventaris fisik buku |

---

## 👤 Penulis & Lisensi

* **Penulis**: **Gusnaldi Kristiadi Syah**
* **Profil GitHub**: [@gusnaldikrist](https://github.com/gusnaldikrist)
* **Lisensi**: GNU General Public License v3.0 (GPL-3.0)
