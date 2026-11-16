<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Leasing
|--------------------------------------------------------------------------
|
| Persiapan integrasi dengan pihak leasing (lihat docs/LEASING_RESEARCH.md).
|
| - subdomain  : subdomain khusus untuk kebutuhan pihak leasing
|                (mis. leasing.jomotocenter.com). Dipakai sebagai acuan
|                konfigurasi DNS/vhost saat portal leasing diaktifkan.
| - access_mode: 'admin'   → seluruh proses (SPK, survey, PO, pembayaran)
|                              tetap dikelola oleh admin dealer (default).
|                'portal'  → pihak leasing memiliki akses login sendiri
|                              untuk memantau/memperbarui status pengajuan.
|                Keputusan akses login leasing belum final — saat ini
|                sistem berjalan dengan mode 'admin'.
|
*/
return [
    'subdomain' => env('LEASING_SUBDOMAIN', 'leasing'),

    'access_mode' => env('LEASING_ACCESS_MODE', 'admin'),

    'portal' => [
        'enabled' => env('LEASING_PORTAL_ENABLED', false),
        'path' => env('LEASING_PORTAL_PATH', 'leasing-portal'),
    ],
];
