<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Mesin ZKTeco
    |--------------------------------------------------------------------------
    |
    | Pengaturan koneksi ke mesin absensi biometrik & RFID ZKTeco.
    | Nilai default sesuai dengan konfigurasi standar mesin di kantor desa.
    |
    */

    'ip' => env('ZKT_IP', '192.168.1.23'),
    'port' => (int) env('ZKT_PORT', 4370),
    'password' => (int) env('ZKT_PASSWORD', 0),

    /*
    |--------------------------------------------------------------------------
    | ADMS Secret Key
    |--------------------------------------------------------------------------
    |
    | Kunci otentikasi untuk menerima data push dari daemon Python
    | dan perangkat ADMS. Harus sama antara .env dan script Python.
    |
    */

    'adms_secret_key' => env('ADMS_SECRET_KEY', 'balunglor_secret_2026'),

    /*
    |--------------------------------------------------------------------------
    | Python Binary Path
    |--------------------------------------------------------------------------
    |
    | Path ke executable Python untuk menjalankan script sinkronisasi mesin.
    |
    */

    'python_bin' => env('PYTHON_BIN', 'python'),

];
