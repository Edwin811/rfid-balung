<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks (Safety Net Sinkronisasi Mesin ZKTeco)
|--------------------------------------------------------------------------
|
| Menarik data presensi dari mesin setiap 1 menit sebagai safety net.
| Jalur utama real-time tetap melalui Python daemon (live_capture).
| Scheduler ini memastikan data tetap tersinkron jika daemon mati.
|
*/

Schedule::command('rfid:sync')->everyMinute()->withoutOverlapping();
