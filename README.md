# 📚 Perpustakaan Sekolah Digital (Peminjaman Buku)

Aplikasi web berbasis **PHP native (MVC) + MySQL/MariaDB** yang berjalan lokal (Localhost) sesuai
SPK UKK RPL 2025/2026 — Paket 4: *Pengembangan Aplikasi Peminjaman Buku*.

## 1. Kebutuhan
- XAMPP / Laragon (Apache + MySQL/MariaDB), **PHP 7.3 atau lebih baru** (diuji kompatibel s.d. PHP 8.x)
- Ekstensi PHP: `pdo_mysql`, `mbstring` (aktif secara default di XAMPP)
- Browser modern

## 2. Instalasi (±3 menit)
1. Salin folder `perpustakaan` ke `C:\xampp\htdocs\` (Linux: `/var/www/html/`).
2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin` → tab **Import** → pilih `database/peminjaman_buku.sql` → **Go**.
   (File ini membuat database `peminjaman_buku` sekaligus datanya.)
4. Buka **http://localhost/perpustakaan/**
5. Jika password MySQL Anda bukan kosong, ubah `config/config.php`.

### Akun demo
| Peran | Username | Password |
|-------|----------|----------|
| Admin | `admin`  | `admin123` |
| Siswa | `citra`, `fitri`, `yaspa`, `ivel`, `ariputra` | `siswa123` |

## 3. Struktur Folder (MVC)
```
perpustakaan/
├── index.php              ← Front controller & router  (index.php?r=controller/method)
├── config/config.php      ← Pengaturan database & aplikasi
├── core/                  ← Inti framework mini
│   ├── Database.php       (koneksi PDO)        ├── Model.php  (basis model)
│   ├── Controller.php     (basis controller)   ├── Auth.php   (sesi login)
│   ├── Validator.php      (validasi input)     └── helpers.php (fungsi bantu)
├── model/                 ← M : akses data (SQL hanya di sini)
│   ├── PenggunaModel.php  ├── AnggotaModel.php
│   ├── BukuModel.php      └── PeminjamanModel.php
├── controller/            ← C : alur logika (tanpa HTML)
│   ├── AuthController.php        login • daftar • logout
│   ├── DashboardController.php   dashboard admin & siswa (pemilihan menu)
│   ├── BukuController.php        CRUD data buku        (admin)
│   ├── AnggotaController.php     CRUD kelola anggota   (admin)
│   ├── TransaksiController.php   CRUD transaksi        (admin)
│   ├── PinjamController.php      peminjaman buku       (siswa)
│   ├── KembaliController.php     pengembalian/batal    (siswa)
│   └── RiwayatController.php     riwayat + pencarian   (siswa)
├── view/                  ← V : tampilan (HTML + sedikit PHP)
│   ├── layout/ (auth, admin, siswa, _flash)   ├── auth/ (login, daftar)
│   ├── admin/  (dashboard, buku/, anggota/, transaksi/)
│   ├── siswa/  (dashboard, pinjam, kembali, riwayat)
│   └── errors/error.php
├── assets/css/style.css • assets/js/app.js
└── database/peminjaman_buku.sql
```

## 4. Kesesuaian dengan Flowmap & Tabel Fitur SPK
| Fitur SPK | Admin | Siswa | Lokasi |
|---|:-:|:-:|---|
| Login (pilih Admin / Siswa) | ✅ | ✅ | `auth/login` (tab peran) |
| Daftar anggota (flowmap: *Anggota? False → Daftar*) | ✅ (admin menambah anggota) | ✅ (daftar mandiri) | `auth/daftar`, `anggota/tambah` |
| Pemilihan menu | ✅ | ✅ | Sidebar admin / Topbar siswa |
| CRUD Transaksi | ✅ C R U D | ✅ C (pinjam) R (riwayat) U (kembalikan) D (batalkan, hari yang sama) | `transaksi/*`, `pinjam`, `kembali`, `riwayat` |
| CRUD Data Buku | ✅ | ❌ (ditolak) | `buku/*` |
| CRUD Kelola Anggota | ✅ | ❌ (ditolak) | `anggota/*` |
| Pencarian kondisi tertentu | ✅ | ✅ | filter di buku, anggota, transaksi, pinjam, riwayat |

## 5. Aturan Bisnis
- Stok berkurang saat dipinjam, bertambah saat dikembalikan / transaksi dibatalkan / dihapus admin (dalam transaksi DB + row lock → stok tidak bisa minus).
- Maks. **3 buku** per transaksi; lama pinjam siswa 3 / 7 / 14 hari (ubah di `config/config.php`).
- Anggota tidak bisa meminjam buku yang sama selama belum dikembalikan.
- Status **Terlambat** dihitung otomatis dari jatuh tempo (tidak disimpan di DB).
- Buku / anggota yang sudah punya riwayat transaksi **tidak bisa dihapus** (menjaga riwayat).

## 6. Keamanan yang diterapkan
Prepared statement PDO (anti SQL injection) • `htmlspecialchars` di semua output (anti XSS) • token CSRF di semua form POST •
hapus/ubah status hanya lewat POST • `password_hash` (bcrypt) • `session_regenerate_id` saat login • guard role di setiap controller.

## 7. Checklist Uji Manual (disarankan dijalankan sekali setelah instalasi)
1. `http://localhost/perpustakaan/` → diarahkan ke halaman login.
2. Login admin salah password → muncul pesan error. Login `admin/admin123` pada tab **Admin** → Dashboard.
3. Coba login `admin` pada tab **Siswa** → harus ditolak.
4. **Buku**: tambah → edit → cari ("dilan", kategori Novel, stok Habis) → hapus buku baru (berhasil) → hapus "Dilan 1990" (ditolak, punya transaksi).
5. **Anggota**: tambah → edit (kosongkan password) → cari → hapus anggota baru.
6. **Transaksi**: tambah (pilih anggota + 2 buku) → stok buku berkurang 1 → Edit tanggal → Kembalikan → stok kembali → filter status *Terlambat* menampilkan transaksi #1.
7. Logout → daftar anggota baru lewat **Daftar di sini** → login siswa tersebut.
8. **Siswa**: Pinjam Buku (centang 1-3 buku) → Pengembalian → Kembalikan / Batalkan → Riwayat + filter.
9. Siswa membuka `index.php?r=buku` → dialihkan ke dashboard (akses ditolak).

## 8. Pemecahan Masalah
| Gejala | Solusi |
|---|---|
| "Database tidak dapat diakses" | Pastikan MySQL aktif, SQL sudah di-import, dan `config/config.php` benar |
| Halaman putih | Cek log error Apache; pastikan PHP ≥ 7.3 |
| Tampilan tanpa CSS | Folder harus bernama `perpustakaan` & diakses lewat `http://localhost/perpustakaan/` |
| Ingin menyembunyikan detail error | `config/config.php` → `'debug' => false` |
