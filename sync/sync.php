<?php

// /**
//  * Sinkronisasi Foto Mahasiswa
//  * Cron: */3 * * * * /usr/bin/php /path/to/sinkronisasi_foto.php
//  *
//  * Deskripsi:
//  * Script ini melakukan sinkronisasi foto mahasiswa dari server id-card-percetakan
//  * ke direktori lokal siakad. Dijalankan setiap 3 menit via cron job.
//  */

// import encode algoritm
require __DIR__ . '/encode.php';
// ============================================================
// KONFIGURASI
// ============================================================

define('APP_ENV', 'development');
define('BASE_API_URL', 'https://id-card-percetakan.test');
define('FOTO_SOURCE_URL', BASE_API_URL . '/storage/foto_maba/');
define('API_SYNC_DATA', BASE_API_URL . '/foto/sinkronisasi/data');
define('API_FLAG_URL', BASE_API_URL . '/foto/sinkronisasi/data/flag-sync/');

// Direktori tujuan penyimpanan foto
if (APP_ENV === 'production') {
    define('DIR_UTAMA', '/var/www/html/siakad/uploads/fotomhs/');
    define('DIR_THUMB', '/var/www/html/siakad/uploads/fotomhs/thumb/'); // Opsional: comment baris ini jika tidak diperlukan
} else {
    define('DIR_UTAMA', __DIR__ . '/fotomhs/');
    define('DIR_THUMB', __DIR__ . '/fotomhs/thumb/'); // Opsional: comment baris ini jika tidak diperlukan
}

// Log file (disimpan di direktori yang sama dengan file .php ini)
define('LOG_FILE', __DIR__ . '/sinkronisasi_foto.log');

// Timeout cURL (detik)
define('CURL_TIMEOUT', 30);


// ============================================================
// FUNGSI LOGGING
// ============================================================

/**
 * Menulis pesan log ke file log.
 *
 * @param string $level   Level log: INFO | WARNING | ERROR
 * @param string $fungsi  Nama fungsi/konteks saat log terjadi
 * @param string $pesan   Pesan log
 */
function tulisLog(string $level, string $fungsi, string $pesan): void
{
    $waktu = date('Y-m-d H:i:s');
    $baris = "[{$waktu}] [{$level}] [{$fungsi}] {$pesan}" . PHP_EOL;

    // Tampilkan ke stdout (terlihat saat dijalankan manual / via cron dengan output redirect)
    echo $baris;

    // Simpan ke file log
    file_put_contents(LOG_FILE, $baris, FILE_APPEND | LOCK_EX);
}


// ============================================================
// FUNGSI HELPER cURL
// ============================================================

/**
 * Melakukan HTTP GET menggunakan cURL.
 *
 * @param  string      $url  URL tujuan
 * @return string|false      Response body, atau false jika gagal
 */
function curlGet(string $url): string|false
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => CURL_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => false, // Sesuaikan jika server sudah pakai SSL valid
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'SinkronisasiFoto/1.0',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $error) {
        tulisLog('ERROR', 'curlGet', "cURL error pada URL [{$url}]: {$error}");
        return false;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        tulisLog('ERROR', 'curlGet', "HTTP {$httpCode} pada URL [{$url}]");
        return false;
    }

    return $response;
}


// ============================================================
// STEP 1 — Ambil daftar mahasiswa belum tersinkron
// ============================================================

/**
 * Memanggil API sinkronisasi dan mengembalikan array NIM mahasiswa
 * yang belum tersinkron. Mengembalikan array kosong jika gagal.
 *
 * @return array<string>
 */
function ambilDataBelumSync(): array
{
    $fungsi = 'ambilDataBelumSync';

    tulisLog('INFO', $fungsi, 'Memanggil API: ' . API_SYNC_DATA);

    $response = curlGet(API_SYNC_DATA);

    if ($response === false) {
        tulisLog('ERROR', $fungsi, 'Gagal mendapatkan response dari API sinkronisasi.');
        return [];
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        tulisLog('ERROR', $fungsi, 'Gagal parsing JSON response: ' . json_last_error_msg());
        return [];
    }

    if (empty($data['success']) || $data['success'] !== true) {
        tulisLog('WARNING', $fungsi, 'API mengembalikan success=false. Response: ' . $response);
        return [];
    }

    if (!isset($data['data']) || !is_array($data['data'])) {
        tulisLog('WARNING', $fungsi, 'Properti "data" tidak ditemukan atau bukan array.');
        return [];
    }

    $jumlah = count($data['data']);
    tulisLog('INFO', $fungsi, "Ditemukan {$jumlah} mahasiswa belum tersinkron.");

    return $data['data'];
}


// ============================================================
// STEP 2 — Download dan simpan foto mahasiswa
// ============================================================

/**
 * Mendownload foto mahasiswa dari server id-card dan menyimpannya
 * ke direktori lokal yang telah dikonfigurasi.
 *
 * @param  string $nim  NIM mahasiswa (misal: H06218016)
 * @return bool         true jika berhasil, false jika gagal
 */
function downloadDanSimpanFoto(string $nim): bool
{
    $fungsi = 'downloadDanSimpanFoto';
    $namaFile = $nim . '.jpg';
    $urlFoto = FOTO_SOURCE_URL . $namaFile;

    tulisLog('INFO', $fungsi, "Mengunduh foto [{$nim}] dari: {$urlFoto}");

    $binaryFoto = curlGet($urlFoto);

    if ($binaryFoto === false || strlen($binaryFoto) === 0) {
        tulisLog('ERROR', $fungsi, "Gagal mengunduh foto untuk NIM [{$nim}].");
        return false;
    }

    $berhasil = true;

    // --- Simpan ke direktori utama ---
    $pathUtama = DIR_UTAMA . $namaFile;
    if (!pastikanDirAda(DIR_UTAMA, $fungsi)) {
        $berhasil = false;
    } elseif (file_put_contents($pathUtama, $binaryFoto) === false) {
        tulisLog('ERROR', $fungsi, "Gagal menyimpan foto ke direktori utama: {$pathUtama}");
        $berhasil = false;
    } else {
        tulisLog('INFO', $fungsi, "Foto berhasil disimpan di: {$pathUtama}");
    }

    // --- Simpan ke direktori thumb (opsional) ---
    // Comment blok berikut jika direktori thumb tidak diperlukan:
    // ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼
    $pathThumb = DIR_THUMB . $namaFile;
    if (!pastikanDirAda(DIR_THUMB, $fungsi)) {
        // Kegagalan thumb tidak menggagalkan keseluruhan proses
        tulisLog('WARNING', $fungsi, "Direktori thumb tidak dapat dibuat, foto thumb dilewati.");
    } elseif (file_put_contents($pathThumb, $binaryFoto) === false) {
        tulisLog('WARNING', $fungsi, "Gagal menyimpan foto ke direktori thumb: {$pathThumb}");
    } else {
        tulisLog('INFO', $fungsi, "Foto thumb berhasil disimpan di: {$pathThumb}");
    }
    // ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲

    return $berhasil;
}

/**
 * Memastikan direktori ada; membuat secara rekursif jika belum ada.
 *
 * @param  string $dir     Path direktori
 * @param  string $fungsi  Nama pemanggil (untuk log)
 * @return bool
 */
function pastikanDirAda(string $dir, string $fungsi): bool
{
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            tulisLog('ERROR', $fungsi, "Gagal membuat direktori: {$dir}");
            return false;
        }
        tulisLog('INFO', $fungsi, "Direktori dibuat: {$dir}");
    }
    return true;
}


// ============================================================
// STEP 3 — Kirim flag sinkronisasi ke server id-card
// ============================================================

/**
 * Mengirim flag status sinkronisasi ke API id-card.
 *
 * @param string $nim      NIM mahasiswa
 * @param bool   $sukses   true jika download berhasil, false jika gagal
 */
function kirimFlagSync(string $nim, bool $sukses): void
{
    $fungsi = 'kirimFlagSync';
    $nilaiFlag = $sukses ? 1 : 0;
    $token = generateRandomString();
    $time = time();
    $salt = base64_encode("$nim|$token|$time");
    $password = "percetakanuinsa_" . strval($time);
    $_finalToken = my_encode($salt, $password);
    $urlFlag = API_FLAG_URL . urlencode($nim) . '?success=' . $nilaiFlag . "&_token=" . $_finalToken . "&time=" . $time;

    tulisLog('INFO', $fungsi, "Mengirim flag sync NIM [{$nim}] success={$nilaiFlag} ke: {$urlFlag}");

    $response = curlGet($urlFlag);
    $data = json_decode($response, true);
    if ($response === false) {
        tulisLog('ERROR', $fungsi, "Gagal mengirim flag sync untuk NIM [{$nim}].");
        return;
    }

    if (json_last_error() === JSON_ERROR_NONE && isset($data['success'])) {
        $status = $data['success'] ? 'sukses' : 'gagal (response API)';
        tulisLog('INFO', $fungsi, "Flag sync NIM [{$nim}]: server merespons {$status}.");
    } else {
        tulisLog('INFO', $fungsi, "Flag sync NIM [{$nim}] terkirim. Raw response: {$response}");
    }
}

function generateRandomString($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        // Use random_int for cryptographically secure randomness
        $index = random_int(0, $charactersLength - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}


// ============================================================
// MAIN — Orkestrasi proses sinkronisasi
// ============================================================

function main(): void
{
    $fungsi = 'main';
    tulisLog('INFO', $fungsi, '===== Mulai proses sinkronisasi foto =====');

    // Step 1: Ambil daftar NIM yang belum tersinkron
    $listNim = ambilDataBelumSync();

    if (empty($listNim)) {
        tulisLog('INFO', $fungsi, 'Tidak ada mahasiswa yang perlu disinkronisasi. Selesai.');
        tulisLog('INFO', $fungsi, '===== Proses sinkronisasi foto selesai =====');
        return;
    }

    // Step 2 & 3: Proses tiap NIM
    foreach ($listNim as $nim) {
        $nim = trim((string) $nim);
        if ($nim === '') {
            tulisLog('WARNING', $fungsi, 'Ditemukan NIM kosong dalam daftar, dilewati.');
            continue;
        }

        tulisLog('INFO', $fungsi, "--- Memproses NIM: {$nim} ---");

        // Step 2: Download & simpan foto
        $berhasil = downloadDanSimpanFoto($nim);

        // Step 3: Kirim flag ke server id-card
        kirimFlagSync($nim, $berhasil);
    }

    tulisLog('INFO', $fungsi, '===== Proses sinkronisasi foto selesai =====');
}

// Jalankan
main();