<?php

/**
 * Pengaturan teknis. Aturan bisnis yang sering diubah (batas ongkir gratis,
 * poin per potong donasi, batas waktu retur) disimpan di tabel settings
 * supaya dapat diubah lewat phpMyAdmin atau dashboard admin.
 */
return [
    // Akun contoh dibuat seeder, kata sandinya diambil dari .env
    'demo' => [
        'password_pelanggan' => env('COLORBOX_DEMO_PASSWORD'),
        'password_admin' => env('COLORBOX_ADMIN_PASSWORD'),
        'pin_staf' => env('COLORBOX_STAFF_PIN'),
    ],

    // Jam layanan bawaan ketika seeder atau admin membuat slot
    'slot' => [
        'jam_ttc' => ['10:00', '11:00', '13:00', '14:00', '15:00', '16:00'],
        'jam_donasi' => ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'],
        'kuota_ttc' => 4,
        'kuota_donasi' => 5,
        'hari_ke_depan' => 14,
    ],

    'donasi' => [
        'min_item' => 1,
        'max_item' => 50,
    ],

    'try_on' => [
        'mode' => env('TRYON_MODE', 'preview'),
        'api_url' => env('TRYON_API_URL'),
        'api_key' => env('TRYON_API_KEY'),
        'max_upload_kb' => 5120,
    ],
];
