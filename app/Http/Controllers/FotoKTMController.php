<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FotoKTMController extends Controller
{
    // --- 1. MENAMPILKAN HALAMAN OPERATOR ---
    public function index() 
    { 
        return view('foto_maba'); 
    }

    // --- 2. MENCARI DATA MAHASISWA SAAT SCAN NIM ---
    public function cariMahasiswa(Request $request)
    {
        $nim = $request->query('nim');

        if (!$nim) {
            return response()->json(['success' => false, 'message' => 'NIM wajib diisi']);
        }

        try {
            // PERBAIKAN: Gunakan "as jk" untuk menstandarkan nama variabel
            $mahasiswa = DB::table('mahasiswa')
                ->select('nama', 'fakultas', 'prodi', 'JK as jk') 
                ->where('nim', $nim)
                ->first();

            if ($mahasiswa) {
                return response()->json([
                    'success' => true,
                    'nama' => $mahasiswa->nama,
                    'prodi' => $mahasiswa->prodi ?? '-',
                    // PERBAIKAN: Panggil variabel jk yang sudah distandarkan
                    'gender' => $mahasiswa->jk ?? 'L' 
                ]);
            }

            return response()->json([
                'success' => false, 
                'message' => 'Data NIM ' . $nim . ' tidak ditemukan dalam database.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error Database: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- 3. MENYIMPAN FOTO LOKAL & UPDATE ANTRIAN ---
    public function store(Request $request)
    {
        $img = $request->input('image');
        $nim = $request->input('nim');

        try {
            $image_parts = explode(";base64,", $img);
            $image_base64 = base64_decode($image_parts[1]);
            $fileName = 'foto_maba/' . $nim . '.jpg';
            
            // 1. Simpan di Local Storage
            Storage::disk('public')->put($fileName, $image_base64);

            // 2. Update status antrean di Database
            // HAPUS updated_at agar tidak error column not found
            DB::table('mahasiswa')->where('nim', $nim)->update([
                'foto_path' => $fileName,
                'status_sync' => 0 // 0 = Masuk antrean upload
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
	
    // ==========================================================
	// AREA FUNGSI SINKRONISASI SFTP SERVER SINAU
    // ==========================================================

    // 4. Menampilkan Halaman Sinkronisasi
    public function indexSync()
    {
        return view('sinkronisasi_sftp');
    }

    // 5. Mengambil daftar NIM yang belum di-sync
    public function getUnsyncedData()
    {
        try {
            $unsyncedNims = DB::table('mahasiswa')
                ->whereNotNull('foto_path')
                ->where('status_sync', 0)
                ->pluck('nim');

            return response()->json([
                'success' => true, 
                'data' => $unsyncedNims
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'DETAIL ERROR: ' . $e->getMessage()
            ]);
        }
    }

    // 6. Proses Upload 1 File ke SFTP (Estafet)
	public function processSync(Request $request)
    {
        $nim = $request->input('nim');
        
        // 1. Ini adalah nama file + folder di LAPTOP (XAMPP)
        $localFileName = 'foto_maba/' . $nim . '.jpg';
        
        // 2. Ini adalah nama file saat mendarat di SERVER SINAU (Tanpa folder foto_maba)
        $remoteFileName = $nim . '.jpg';

        try {
            if (!Storage::disk('public')->exists($localFileName)) {
                return response()->json(['success' => false, 'message' => 'File lokal tidak ditemukan']);
            }

            // Ambil dari lokal (pakai nama lokal)
            $fileContents = Storage::disk('public')->get($localFileName);
            
            // Lempar ke SFTP (pakai nama remote murni)
            Storage::disk('sftp')->put($remoteFileName, $fileContents);

            // Update database sukses
            DB::table('mahasiswa')->where('nim', $nim)->update([
                'status_sync' => 1 
            ]);

            return response()->json(['success' => true, 'nim' => $nim]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
	
	// 7. Cek Koneksi Awal SFTP
    public function checkSftpConnection()
    {
        try {
            Storage::disk('sftp')->exists('/'); 
            
            return response()->json([
                'success' => true, 
                'message' => 'Koneksi ke Server Sinau Stabil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal Terhubung: Cek IP, Port, atau Password SFTP.'
            ]);
        }
    }
}