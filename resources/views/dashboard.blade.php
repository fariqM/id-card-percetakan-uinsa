<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operator - ID Card UINSA</title>
	<link rel="icon" href="{{ asset('/public/assets/uinsa.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Perubahan 1: Hapus sistem tengah dari body agar header bisa di atas */
        body { 
            background-color: #f1f5f9; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0;
        }

        /* Perubahan 2: Buat class wrapper baru untuk menengahkan menu dashboard */
        .dashboard-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 100px); /* 100vh dikurangi perkiraan tinggi header */
        }

        .dashboard-container { 
            max-width: 1100px; 
            width: 100%;
            padding: 20px;
        }
        .menu-card {
            background: #ffffff;
            border: none;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
            border: 2px solid transparent;
        }
        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 35px rgba(0,0,0,0.1);
            border-color: #3b82f6;
            color: inherit;
        }
        .menu-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            display: inline-block;
            background: #eff6ff;
            width: 90px;
            height: 90px;
            line-height: 90px;
            border-radius: 50%;
        }
        .menu-title {
            font-weight: 800;
            color: #1e293b;
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        .menu-desc {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .header-title {
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
    </style>
</head>
<body>

@include('layouts.header')

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <div class="text-center mb-5">
            <h1 class="header-title">Sistem ID Card Terpadu</h1>
            <p class="text-secondary fs-5">Pusat Layanan Mahasiswa Baru UIN Sunan Ampel</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <a href="{{ url('/foto') }}" class="menu-card">
                    <div class="menu-icon">📸</div>
                    <h3 class="menu-title">Panel Operator Foto</h3>
                    <p class="menu-desc">Buka modul kamera untuk verifikasi data, memotret, dan menyimpan foto mahasiswa secara lokal.</p>
                    <span class="badge bg-primary mt-3 px-3 py-2 rounded-pill">Buka Modul &rarr;</span>
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ url('/foto/sinkronisasi') }}" class="menu-card">
                    <div class="menu-icon">🔄</div>
                    <h3 class="menu-title">Sinkronisasi Server</h3>
                    <p class="menu-desc">Kirim seluruh antrean foto lokal ke Server Sinau secara massal dan pantau status jaringan.</p>
                    <span class="badge bg-success mt-3 px-3 py-2 rounded-pill">Buka Modul &rarr;</span>
                </a>
            </div>

            <div class="col-md-4">
                <a href="/cetak" class="menu-card" style="background-color: #f8fafc; border: 2px dashed #cbd5e1;">
                    <div class="menu-icon" style="background: #f1f5f9;">🖨️</div>
                    <h3 class="menu-title">Layanan Cetak</h3>
                    <p class="menu-desc">Kembali ke sistem utama (Native PHP) untuk memproses tata letak dan pencetakan ID Card.</p>
                    <span class="badge bg-secondary mt-3 px-3 py-2 rounded-pill">Ke Sistem Cetak &rarr;</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>