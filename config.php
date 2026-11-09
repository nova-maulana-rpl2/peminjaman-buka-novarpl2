<?php
/**
 * KONFIGURASI APLIKASI
 * Sesuaikan 'db' dengan MySQL/MariaDB lokal Anda (default XAMPP sudah cocok).
 */
return [
    'db' => [
        'host'    => 'localhost',
        'name'    => 'peminjaman_buku',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'                   => 'Perpustakaan Sekolah Digital',
        'timezone'               => 'Asia/Jakarta',
        'debug'                  => true,   // ubah ke false jika sudah dipakai sungguhan
        'max_buku_per_transaksi' => 3,
        'lama_pinjam_pilihan'    => [3, 7, 14], // hari
    ],
];
