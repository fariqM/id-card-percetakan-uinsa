<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Mahasiswa - UINSA 2026</title>
	<link rel="icon" href="{{ asset('/public/assets/uinsa.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 1. BODY DIGUNAKAN SEBAGAI KANVAS TENGAH */
        body { 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
            color: #f8fafc; 
            overflow: hidden; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* 2. CONTAINER DIKUNCI PADA SKALA 4:3 DAN TIDAK BISA MELEBAR BEBAS */
        .monitor-container { 
            display: flex; 
            aspect-ratio: 4 / 3; /* Mengunci skala layout secara keseluruhan */
            height: 95vh; /* Mengikuti tinggi layar, lebar menyesuaikan otomatis */
            max-width: 100%;
            padding: 2rem; 
            gap: 1.5rem; 
            /* Tambahan efek box agar batas aplikasi terlihat saat di layar widescreen */
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 32px;
            box-shadow: 0 0 60px rgba(0,0,0,0.4);
        }
        
        /* Area Kamera */
        .camera-section { 
            flex: 3.5; 
            position: relative; 
            border-radius: 20px; 
            overflow: hidden; 
            background: #000;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        #video_monitor { 
            width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); 
            filter: contrast(1.05) brightness(1.02); 
        }

        .look-here-indicator {
            position: absolute; top: 25px; left: 50%; transform: translateX(-50%);
            background: rgba(239, 68, 68, 0.9); color: white;
            padding: 10px 30px; border-radius: 50px; font-weight: 800;
            font-size: 1.2rem; letter-spacing: 1px; z-index: 10;
            animation: pulse-red 2s infinite; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.5);
        }

        @keyframes pulse-red {
            0% { transform: translateX(-50%) scale(1); opacity: 0.9; }
            50% { transform: translateX(-50%) scale(1.05); opacity: 1; }
            100% { transform: translateX(-50%) scale(1); opacity: 0.9; }
        }
        
        /* Garis Panduan */
        .guide-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; }
        .face-oval { position: absolute; top: 17%; left: 50%; transform: translateX(-50%); width: 30%; height: 35%; border: 8px solid rgba(0, 255, 0, 0.5); border-radius: 10% 10% 40% 40% / 60% 60% 40% 40%; }
        .shoulder-line { position: absolute; top: 54%; left: 5%; width: 90%; height: 80%; border-top: 8px dashed rgba(0, 255, 0, 0.5); border-radius: 50% 50% 0 0; }
        .center-dot { position: absolute; top: 35%; left: 50%; transform: translate(-50%, -50%); width: 6px; height: 6px; background: rgba(255, 255, 255, 0.6); border-radius: 50%; }

        /* Area Kanan */
        .info-section { 
            flex: 1; min-width: 280px; max-width: 330px; 
            display: flex; flex-direction: column; gap: 1rem;
        }
        
        /* Header Universitas */
        .header-box { text-align: center; margin-bottom: 0.5rem; }
        .uim-title { font-weight: 800; font-size: 1.4rem; background: linear-gradient(to right, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 0;}
        
        /* Status Card */
        .status-card { 
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px; padding: 1.5rem 1rem; text-align: center; 
            transition: all 0.3s ease;
        }
        .status-card.active { background: rgba(14, 165, 233, 0.15); border-color: rgba(56, 189, 248, 0.5); box-shadow: 0 0 20px rgba(14, 165, 233, 0.2); }
        .status-card.success { background: rgba(16, 185, 129, 0.15); border-color: rgba(52, 211, 153, 0.5); box-shadow: 0 0 20px rgba(16, 185, 129, 0.2); }
        
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: 800; font-size: 0.9rem; letter-spacing: 1px; margin-bottom: 10px; }
        .badge-standby { background: rgba(100, 116, 139, 0.3); color: #cbd5e1; }
        .badge-ready { background: #0ea5e9; color: #fff; animation: pulse-blue 1.5s infinite; }
        .badge-success { background: #10b981; color: #fff; }

        @keyframes pulse-blue { 0% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(14, 165, 233, 0); } 100% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0); } }

        /* Reference Box */
        .reference-box { 
            background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; 
            padding: 1.5rem 1rem; text-align: center; flex-grow: 1; 
            display: flex; flex-direction: column; justify-content: center; align-items: center;
        }
        .reference-photo { 
            width: 130px; height: 173px; border: 2px solid rgba(255, 255, 255, 0.2); 
            border-radius: 12px; object-fit: cover; margin-top: 15px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

<div class="monitor-container">
    <div class="camera-section">
        <div class="look-here-indicator">⬆️ TATAP LENSA KAMERA ⬆️</div>
        <video id="video_monitor" autoplay playsinline></video>
        <div class="guide-overlay">
            <div class="face-oval"></div>
            <div class="shoulder-line"></div>
			<div class="center-dot"></div>
        </div>
    </div>

    <div class="info-section">
        <div class="header-box">
            <h3 class="uim-title">UIN Sunan Ampel</h3>
            <p class="small text-muted mb-0" style="letter-spacing: 1px;">SISTEM ID CARD PBAK 2026</p>
        </div>

        <div id="main-status-card" class="status-card">
            <div id="status-badge" class="status-badge badge-standby">STANDBY</div>
            <h5 id="mahasiswa-nama" class="fw-bold text-white mb-2" style="line-height: 1.3;">-</h5>
            <p id="status-instruction" class="small mb-0" style="color: #94a3b8;">Menunggu petugas memindai NIM Anda...</p>
        </div>
        
        <div class="reference-box">
            <h6 class="fw-bold text-uppercase mb-1" style="color: #cbd5e1; letter-spacing: 1px;">Panduan Posisi</h6>
            
            <div id="ref-default" class="py-4 w-100">
                <div class="spinner-grow text-secondary mb-3" role="status" style="width: 2rem; height: 2rem; opacity: 0.3;"></div>
            </div>

            <div id="ref-laki" class="d-none w-100">
                <img src="{{ asset('public/img/contoh_ktm_1.jpg') }}" class="reference-photo" alt="KTM Laki-laki"> 
                <p class="mt-3 mb-0 fw-bold" style="font-size: 0.85rem; color: #f8fafc;">Kemeja Putih & Dasi</p>
                <p class="small mt-1 mb-0" style="color: #cbd5e1;">Posisikan wajah di dalam garis oval</p>
            </div>
            
            <div id="ref-perempuan" class="d-none w-100">
                <img src="{{ asset('public/img/contoh_ktm_2.jpg') }}" class="reference-photo" alt="KTM Perempuan">
                <p class="mt-3 mb-0 fw-bold" style="font-size: 0.85rem; color: #f8fafc;">Kemeja/Jilbab Putih</p>
                <p class="small mt-1 mb-0" style="color: #cbd5e1;">Posisikan wajah di dalam garis oval</p>
            </div>
        </div>
    </div>
</div>

<script>
    const video = document.getElementById('video_monitor');
    const bc = new BroadcastChannel('uinsa_capture_monitor');

    navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720 } })
        .then(stream => { video.srcObject = stream; })
        .catch(err => { console.error("Kamera gagal:", err); });

    function updateUI(type, data = {}) {
        const refLaki = document.getElementById('ref-laki');
        const refPerempuan = document.getElementById('ref-perempuan');
        const refDefault = document.getElementById('ref-default');
        
        const statusCard = document.getElementById('main-status-card');
        const statusBadge = document.getElementById('status-badge');
        const namaBox = document.getElementById('mahasiswa-nama');
        const instructionBox = document.getElementById('status-instruction');

        if (type === 'verifikasi') {
            refDefault.classList.add('d-none');
            statusCard.className = "status-card active";
            statusBadge.className = "status-badge badge-ready";
            statusBadge.innerText = "SIAP DIFOTO";
            
            namaBox.innerText = "Halo, " + data.nama.split(' ')[0] + "!"; 
            instructionBox.innerHTML = "Silakan ikuti panduan posisi di bawah, <strong class='text-white'>rapikan pakaian anda</strong>.";
            
            // PERBAIKAN LOGIKA: Ubah data menjadi huruf besar semua agar kebal error
            let gender = (data.gender || 'L').toString().toUpperCase();
            
            if(gender === 'L' || gender === 'LAKI-LAKI') {
                refLaki.classList.remove('d-none');
                refPerempuan.classList.add('d-none');
            } else {
                // Semua yang bukan 'L' akan otomatis masuk ke panduan Perempuan
                refLaki.classList.add('d-none');
                refPerempuan.classList.remove('d-none');
            }
        } else if (type === 'tersimpan') {
            statusCard.className = "status-card success";
            statusBadge.className = "status-badge badge-success";
            statusBadge.innerText = "✔ BERHASIL";
            namaBox.innerText = "Foto Tersimpan!";
            instructionBox.innerHTML = "Terima kasih. Anda dapat meninggalkan area foto.";
            
            setTimeout(() => updateUI('reset'), 3500);
        } else if (type === 'reset') {
            statusCard.className = "status-card";
            statusBadge.className = "status-badge badge-standby";
            statusBadge.innerText = "STANDBY";
            namaBox.innerText = "-";
            instructionBox.innerHTML = "Menunggu petugas memindai NIM Anda...";
            
            refDefault.classList.remove('d-none');
            refLaki.classList.add('d-none');
            refPerempuan.classList.add('d-none');
        }
    }

    bc.onmessage = (event) => updateUI(event.data.type, event.data);

    document.addEventListener('keydown', (e) => {
        if(e.key.toLowerCase() === 't') {
            updateUI('verifikasi', { nama: 'MAHASISWA UJI COBA', gender: 'L' }); 
        }
    });
</script>
</body>
</html>