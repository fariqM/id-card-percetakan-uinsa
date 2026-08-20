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
        body { 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
            color: #f8fafc; overflow: hidden; font-family: 'Plus Jakarta Sans', sans-serif; 
            height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center;
        }
        
        .monitor-container { 
            display: flex; aspect-ratio: 4 / 3; height: 95vh; max-width: 100%;
            padding: 2rem; gap: 1.5rem; background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 32px;
            box-shadow: 0 0 60px rgba(0,0,0,0.4);
        }
        
        /* KELOMPOK AREA KAMERA & OVERLAY */
        .camera-wrapper {
            flex: 1; 
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        
        .camera-section { 
            position: relative; border-radius: 20px; overflow: hidden; 
            background: #000; border: 4px solid #1e293b; 
            aspect-ratio: 3 / 4; 
            height: 100%; 
            max-height: 85vh; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        #video_monitor { 
            width: 100%; height: 100%; object-fit: cover; 
            filter: contrast(1.05) brightness(1.02); 
        }
        #result_monitor {
            width: 100%; height: 100%; object-fit: cover; position: absolute; 
            top: 0; left: 0; z-index: 15;
        }
        
        .look-here-indicator {
            position: absolute; top: 20px; left: 50%; transform: translateX(-50%);
            background: rgba(239, 68, 68, 0.9); color: white; 
            padding: 8px 24px; /* Lebih ramping/pill-shaped */
            border-radius: 50px; font-weight: 800; 
            font-size: clamp(0.85rem, 1.5vw, 1.1rem); /* Ukuran font dinamis mengikuti lebar layar */
            letter-spacing: 1.5px; z-index: 10; 
            white-space: nowrap; /* Memaksa teks selalu 1 baris */
            max-width: 90%; /* Mencegah teks meluber keluar dari frame */
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.5);
            animation: pulse-red 2s infinite;
        }
        @keyframes pulse-red {
			0% { transform: translateX(-50%) scale(1); opacity: 0.9; }
            50% { transform: translateX(-50%) scale(1.03); opacity: 1; }
            100% { transform: translateX(-50%) scale(1); opacity: 0.9; }
        }

        /* Animasi Countdown Raksasa */
        .countdown-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            background: rgba(0,0,0,0.3); font-size: 12rem; font-weight: 800;
            color: #fff; text-shadow: 0 10px 30px rgba(0,0,0,0.8);
            z-index: 20;
        }

        /* GARIS PANDUAN DISAMAKAN PERSIS DENGAN PANEL OPERATOR */
		.guide-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 5;}
        .face-oval { position: absolute; top: 15%; left: 50%; transform: translateX(-50%); width: 45%; height: 50%; border: 4px solid rgba(52, 211, 153, 0.6); border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%; }
        .shoulder-line { position: absolute; top: 65%; left: 10%; width: 80%; height: 30%; border-top: 4px dashed rgba(52, 211, 153, 0.6); border-radius: 50% 50% 0 0; }
        .center-dot { position: absolute; top: 35%; left: 50%; transform: translate(-50%, -50%); width: 6px; height: 6px; background: rgba(255, 255, 255, 0.8); border-radius: 50%; }

        /* Area Kanan */
        .info-section { 
            flex: 1.2; min-width: 330px; max-width: 380px; 
            display: flex; flex-direction: column; gap: 1rem;
        }
        .header-box { text-align: center; margin-bottom: 0.5rem; }
        .uim-title { font-weight: 800; font-size: 1.4rem; background: linear-gradient(to right, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 0;}
        
        .status-card { 
            background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px; padding: 1.5rem 1rem; text-align: center; transition: all 0.3s ease;
        }
        .status-card.active { background: rgba(14, 165, 233, 0.15); border-color: rgba(56, 189, 248, 0.5); box-shadow: 0 0 20px rgba(14, 165, 233, 0.2); }
        .status-card.success { background: rgba(16, 185, 129, 0.15); border-color: rgba(52, 211, 153, 0.5); box-shadow: 0 0 20px rgba(16, 185, 129, 0.2); }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: 800; font-size: 0.9rem; letter-spacing: 1px; margin-bottom: 10px; }
        .badge-standby { background: rgba(100, 116, 139, 0.3); color: #cbd5e1; }
        .badge-ready { background: #0ea5e9; color: #fff; animation: pulse-blue 1.5s infinite; }
        .badge-success { background: #10b981; color: #fff; }
		@keyframes pulse-blue { 0% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(14, 165, 233, 0); } 100% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0); } }
		
        @keyframes pulse-green { 
            0% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7); transform: scale(1); } 
            50% { transform: scale(1.02); } 
            70% { box-shadow: 0 0 0 15px rgba(52, 211, 153, 0); } 
            100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); transform: scale(1); } 
        }
        .animate-guide { animation: pulse-green 2s infinite ease-in-out; }
		
        .comparison-area { display: flex; gap: 12px; width: 100%; flex-grow: 1; }
        .photo-box { 
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;
            background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; 
            padding: 1rem 0.5rem; text-align: center;
        }
        .box-title { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
        .img-wrapper { 
            width: 110px; height: 146px; border-radius: 8px; overflow: hidden; 
            border: 2px solid rgba(255, 255, 255, 0.1); background: #1e293b; 
            display: flex; align-items: center; justify-content: center; position: relative;
        }
        .img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .guide-text { font-size: 0.75rem; font-weight: bold; margin-top: 12px; margin-bottom: 0; line-height: 1.3; }
        
        .standby-box { 
            background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); 
            border-radius: 16px; padding: 2rem; text-align: center; flex-grow: 1; 
            display: flex; flex-direction: column; justify-content: center; align-items: center;
        }
    </style>
</head>
<body>

<div class="monitor-container">
    
    <!-- WRAPPER BARU AGAR KAMERA TETAP DI TENGAH & TIDAK MELEBAR -->
    <div class="camera-wrapper">
        <div class="camera-section">
            <div class="look-here-indicator">⬆️ TATAP KAMERA ⬆️</div>
            <video id="video_monitor" autoplay playsinline></video>
            
            <img id="result_monitor" class="d-none" alt="Hasil Foto">
            <div id="countdown-display" class="countdown-overlay d-none">3</div>
            
            <div class="guide-overlay">
                <div class="face-oval"></div>
                <div class="shoulder-line"></div>
                <div class="center-dot"></div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="header-box">
            <h3 class="uim-title">Aplikasi Foto KTM</h3>
            <p class="small text-muted mb-0" style="letter-spacing: 1px;">SISTEM ID CARD PBAK 2026</p>
        </div>

        <div id="main-status-card" class="status-card">
            <div id="status-badge" class="status-badge badge-standby">STANDBY</div>
            <h5 id="mahasiswa-nama" class="fw-bold text-white mb-2" style="line-height: 1.3;">-</h5>
            <p id="status-instruction" class="small mb-0" style="color: #94a3b8;">Menunggu petugas memindai NIM Anda...</p>
        </div>
        
        <div id="standby-box" class="standby-box">
            <div class="spinner-grow text-secondary mb-3" role="status" style="width: 2.5rem; height: 2.5rem; opacity: 0.3;"></div>
            <p class="text-muted small mb-0 fw-bold">Menunggu Verifikasi Data...</p>
        </div>

        <div id="comparison-area" class="comparison-area d-none">
            <div class="photo-box">
                <h6 class="box-title" style="color: #fbbf24;">Foto Saat Ini</h6>
                <div class="img-wrapper" style="border-color: rgba(251, 191, 36, 0.5);">
                    <img id="server-photo" src="" alt="Foto Server" style="display:none;" onerror="if(this.src.includes('.jpg')){ this.src = this.src.replace('.jpg', '.png'); } else { this.style.display='none'; document.getElementById('no-photo-text').style.display='block'; }">
                    <span id="no-photo-text" class="text-muted small fw-bold text-center px-2">Belum Ada Foto</span>
                </div>
                <p class="guide-text" style="color: #fbbf24;">Terdaftar di Server</p>
            </div>
            
            <div class="photo-box">
                <h6 class="box-title" style="color: #34d399;">Panduan</h6>
                <div class="img-wrapper animate-guide" style="border-color: rgba(52, 211, 153, 0.5);">
                    <img id="guide-photo" src="" alt="Panduan">
                </div>
                <p id="guide-text" class="guide-text" style="color: #34d399;">-</p>
            </div>
        </div>
    </div>
</div>

<script>
    const videoMonitor = document.getElementById('video_monitor');
    const resultMonitor = document.getElementById('result_monitor');
    const guideOverlay = document.querySelector('.guide-overlay');
    const lookHere = document.querySelector('.look-here-indicator');
    const countdownEl = document.getElementById('countdown-display');
    const bc = new BroadcastChannel('uinsa_capture_monitor');

    navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720 } })
        .then(stream => { videoMonitor.srcObject = stream; })
        .catch(err => { console.error("Kamera gagal:", err); });

    function updateUI(type, data = {}) {
        const standbyBox = document.getElementById('standby-box');
        const compArea = document.getElementById('comparison-area');
        const statusCard = document.getElementById('main-status-card');
        const statusBadge = document.getElementById('status-badge');
        const namaBox = document.getElementById('mahasiswa-nama');
        const instructionBox = document.getElementById('status-instruction');

        if (type === 'verifikasi') {
            videoMonitor.classList.remove('d-none');
            resultMonitor.classList.add('d-none');
            guideOverlay.classList.remove('d-none');
            lookHere.classList.remove('d-none');
            countdownEl.classList.add('d-none');

            standbyBox.classList.add('d-none');
            compArea.classList.remove('d-none');
            
            statusCard.className = "status-card active";
            statusBadge.className = "status-badge badge-ready";
            statusBadge.innerText = "SIAP DIFOTO";
            namaBox.innerText = "Halo, " + data.nama.split(' ')[0] + "!"; 
            instructionBox.innerHTML = "<strong class='text-white'>Rapikan pakaian sebagaimana PANDUAN</strong>";
            
            let gender = (data.gender || 'L').toString().toUpperCase();
            const guidePhoto = document.getElementById('guide-photo');
            const guideText = document.getElementById('guide-text');
            
            if(gender === 'L' || gender === 'LAKI-LAKI') {
                guidePhoto.src = "{{ asset('public/img/contoh_ktm_1.jpg') }}";
                guideText.innerText = "Kemeja Putih & Dasi";
            } else {
                guidePhoto.src = "{{ asset('public/img/contoh_ktm_2.jpg') }}";
                guideText.innerText = "Kemeja / Jilbab Putih";
            }

            const serverPhoto = document.getElementById('server-photo');
            const noPhotoText = document.getElementById('no-photo-text');
            
            if (data.nim) {
                const cacheBuster = new Date().getTime();
                serverPhoto.src = `https://sinau.uinsa.ac.id/uploads/fotomhs/${data.nim}.jpg?v=${cacheBuster}`;
                serverPhoto.style.display = 'block';
                noPhotoText.style.display = 'none';
            } else {
                serverPhoto.style.display = 'none';
                noPhotoText.style.display = 'block';
            }

        } else if (type === 'countdown') {
            countdownEl.classList.remove('d-none');
            let count = 3;
            countdownEl.innerText = count;
            
            statusBadge.innerText = "MENGAMBIL FOTO...";
            instructionBox.innerHTML = "<strong class='text-warning' style='font-size: 1.1rem;'>Tahan posisi dan senyum Anda...</strong>";
            
            let cdInterval = setInterval(() => {
                count--;
                if(count > 0) {
                    countdownEl.innerText = count;
                } else {
                    clearInterval(cdInterval);
                    countdownEl.classList.add('d-none');
                }
            }, 1000);

        } else if (type === 'tersimpan') {
            statusCard.className = "status-card success";
            statusBadge.className = "status-badge badge-success";
            statusBadge.innerText = "✔ BERHASIL";
            namaBox.innerText = "Foto Selesai!";
            instructionBox.innerHTML = "<strong>Ini hasil foto Anda,<br>Pengambilan fisik KTM diumumkan kemudian</strong>";
            
            videoMonitor.classList.add('d-none');
            guideOverlay.classList.add('d-none');
            lookHere.classList.add('d-none');
            
            resultMonitor.src = data.image; 
            resultMonitor.classList.remove('d-none');

        } else if (type === 'reset') {
            videoMonitor.classList.remove('d-none');
            resultMonitor.classList.add('d-none');
            guideOverlay.classList.remove('d-none');
            lookHere.classList.remove('d-none');
            countdownEl.classList.add('d-none');

            statusCard.className = "status-card";
            statusBadge.className = "status-badge badge-standby";
            statusBadge.innerText = "STANDBY";
            namaBox.innerText = "-";
            instructionBox.innerHTML = "Menunggu petugas memindai NIM Anda...";
            
            standbyBox.classList.remove('d-none');
            compArea.classList.add('d-none');
        }
    }

    bc.onmessage = (event) => updateUI(event.data.type, event.data);
</script>
</body>
</html>
