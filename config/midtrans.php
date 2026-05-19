<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    /**
     * Verifikasi SSL saat memanggil API Midtrans (cURL).
     * Di Windows sering error "unable to get local issuer certificate (20)" jika php.ini
     * tidak mengarah ke cacert.pem. Untuk lokal bisa set MIDTRANS_VERIFY_SSL=false;
     * di server production sebaiknya true + CA bundle sistem atau MIDTRANS_CAINFO.
     */
    'verify_ssl' => env('MIDTRANS_VERIFY_SSL', true),
    /** Path opsional ke cacert.pem (unduh dari https://curl.se/ca/cacert.pem) */
    'curl_cainfo' => env('MIDTRANS_CAINFO', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => true,
    'is_3ds' => true,
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];
