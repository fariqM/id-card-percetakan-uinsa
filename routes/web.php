<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FotoKTMController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. ROUTE DASHBOARD UTAMA (Pintu Gerbang Aplikasi)
Route::get('/', function () {
    return view('dashboard');
});

// 2. ROUTE PANEL OPERATOR (Kamera & Pencarian)
Route::get('/foto', [FotoKTMController::class, 'index'])->name('foto.index');
Route::get('/foto/cari', [FotoKTMController::class, 'cariMahasiswa'])->name('foto.cari');
Route::post('/foto/simpan', [FotoKTMController::class, 'store'])->name('foto.simpan');

// 3. ROUTE MONITOR MAHASISWA (Layar Ganda)
Route::get('/foto/monitor', function () {
    return view('monitor_mahasiswa');
})->name('foto.monitor');

// 4. ROUTE SINKRONISASI SERVER SFTP (Sinau)
Route::get('/foto/sinkronisasi', [FotoKTMController::class, 'indexSync'])->name('foto.sync.index');
Route::get('/foto/sinkronisasi/data', [FotoKTMController::class, 'getUnsyncedData'])->name('foto.sync.data');
Route::get('/foto/sinkronisasi/cek-sftp', [FotoKTMController::class, 'checkSftpConnection'])->name('foto.sync.check-sftp');
Route::post('/foto/sinkronisasi/proses', [FotoKTMController::class, 'processSync'])->name('foto.sync.proses');