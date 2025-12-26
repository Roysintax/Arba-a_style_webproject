# 🕌 Toko Islami (Arba-a Style Web Project)

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-DB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/HTML5)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)

**Toko Islami** adalah platform web komprehensif yang menggabungkan fitur *E-Commerce* (Toko Online) dengan portal edukasi Islam. Proyek ini tidak hanya memungkinkan pengguna untuk berbelanja produk Islami, tetapi juga membaca artikel, menonton video dakwah, mengikuti kuis interaktif, dan melihat kegiatan sosial.

---

## 🌟 Fitur Utama

### 🛍️ Modul E-Commerce (Pelanggan)
* **Katalog Produk:** Pencarian produk, filter kategori, dan detail produk lengkap dengan gambar.
* **Keranjang & Checkout:** Manajemen keranjang belanja dan proses checkout yang mudah.
* **Simulasi Pembayaran:** Mendukung simulasi pembayaran via Transfer Bank, E-Wallet (Gopay/OVO/Dana), dan Scan QR Code.
* **Lacak Pesanan:** Fitur tracking status pesanan realtime menggunakan nomor pesanan.
* **User Dashboard:** Riwayat pesanan dan manajemen profil pengguna.

### 📚 Modul Edukasi & Dakwah
* **Artikel Islami:** Blog artikel dengan kategori, pencarian, dan *rich text content*.
* **Video Dakwah:** Integrasi video YouTube untuk kajian dan ceramah.
* **Kuis Interaktif:** Kuis pengetahuan Islam dengan sistem skor, timer, dan *leaderboard* (papan peringkat).
* **Kegiatan Sosial:** Dokumentasi dan informasi kegiatan sosial/keagamaan dengan galeri foto.

### 💬 Fitur Interaktif
* **Live Chat Widget:** Chat real-time antara pengunjung dan admin dengan notifikasi badge.
* **Rating & Ulasan:** Pengguna dapat memberikan feedback layanan.

### ⚙️ Panel Admin (Backend)
* **Dashboard Statistik:** Grafik visual (Chart) penjualan, total order, dan ringkasan aktivitas.
* **Manajemen Produk:** CRUD Produk, Kategori, Stok, dan Gambar.
* **Manajemen Pesanan:** Update status pesanan (Pending, Processing, Shipped, Completed, Cancelled).
* **Konten Manajemen:** Kelola Artikel (dengan editor WYSIWYG), Video Dakwah, dan Kegiatan Sosial.
* **Kelola Kuis:** Buat soal, atur jawaban benar, dan lihat skor peserta.
* **Kelola Chat:** Balas pesan masuk dari pengunjung secara langsung.
* **Pengaturan Situs:** Ubah logo, banner, teks hero, dan footer website langsung dari admin panel.

---

## 🛠️ Teknologi yang Digunakan

* **Backend:** PHP Native (PDO) - Aman, cepat, dan mudah dikonfigurasi.
* **Database:** MySQL / MariaDB.
* **Frontend:** HTML5, CSS3 (Custom CSS Variables & Flexbox/Grid), JavaScript (Vanilla).
* **Libraries & Assets:**
    * *Quill.js* (Rich Text Editor untuk penulisan artikel).
    * *Google Fonts* (Roboto & Amiri).
    * *Google Charts API* (Untuk generate QR Code).
    * *Chart.js* (Opsional/Custom CSS Charts untuk dashboard).

---

## 📂 Struktur Folder

```text
Arba-a_style_webproject/
├── admin/              # Halaman panel admin (Dashboard, CRUD, Login)
├── api/                # Endpoint API untuk Chat, Payment, dan Quiz (AJAX)
├── assets/             # File statis
│   ├── css/            # Stylesheet utama
│   ├── js/             # JavaScript logika frontend
│   └── images/         # Aset gambar sistem
├── config/             # Konfigurasi koneksi database
├── database/           # File backup SQL (database.sql)
├── includes/           # Komponen reusable (Header, Footer, Functions, Chat Widget)
├── pages/              # Halaman publik
│   ├── auth/           # Login & Register user
│   ├── shop/           # Produk, Cart, Checkout, Payment
│   ├── articles/       # Daftar & Detail Artikel
│   └── ...             # Kuis, Aktivitas, Tentang Kami
├── uploads/            # Folder penyimpanan gambar yang diupload user/admin
└── index.php           # Halaman utama (Landing Page)
