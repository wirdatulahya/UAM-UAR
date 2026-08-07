<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Otomatis sinkronisasi data master pegawai dari DWH Greenplum setiap awal bulan (tanggal 1 pukul 01:00)
Schedule::command('dwh:sync-pegawai')
    ->monthlyOn(1, '01:00')
    ->withoutOverlapping()
    ->runInBackground();
