<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FotoKTMController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
require __DIR__ . '/encode.php';
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. ROUTE DASHBOARD UTAMA (Pintu Gerbang Aplikasi)
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

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

    // 5. Call api internal mhs detail
    Route::post('/get-mhs-detail', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'nim' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response(['success' => false, 'message' => 'bad input'], 422);
        }

        // ==========================================================
        // START PROTOKOL INTERNAL API SERVICE
        // ==========================================================
        $timeStamp = time();
        $nim = $request->nim;
        $plainText = base64_encode("$nim|$timeStamp"); // plain with salt
        $keyPassword = "percetakane_pakandik"; // static key password
        $token = my_encode($plainText, $keyPassword);
        $response = Http::get("https://servicesurat.uinsa.ac.id/api/internal/mahasiswa-by-nim/$nim/$token");
        // ==========================================================
        // END PROTOKOL INTERNAL API SERVICE
        // ==========================================================
        $data = $response->json();
        $apiMsg = $data['message'];
        if ($data['status'] !== 'success') {
            return response(['success' => false, 'data' => null, 'message' => 'Telah terjadi kesalahan internal! ' . "\nAPI-SERVICE-MESSAGE : $apiMsg"], 404);
        }
        return response(['success' => true, 'detail_mhs' => $data['data']]);
    })->name('get.mhs.detail');
});


