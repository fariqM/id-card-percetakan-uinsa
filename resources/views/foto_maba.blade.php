<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Operator - Capturing Foto UINSA</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .app-container { max-width: 1000px; margin: 30px auto; padding: 0 15px; }
        
        .camera-box { position: relative; border: 4px solid #1e293b; border-radius: 16px; overflow: hidden; background: #000; aspect-ratio: 3/4; width: 100%; max-width: 360px; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        
        /* Tampilan video normal, TIDAK ADA transform: scaleX(-1) agar logo almamater terbaca */
        #video { width: 100%; height: 100%; object-fit: cover; }
        
        .results-preview { height: 250px; border: 2px dashed #cbd5e1; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #fff; overflow: hidden; position: relative; }
        
        /* Guide Overlay untuk Operator */
        .overlay-container { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; }
        .face-oval { position: absolute; top: 15%; left: 50%; transform: translateX(-50%); width: 45%; height: 50%; border: 4px solid rgba(52, 211, 153, 0.6); border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%; }
        .shoulder-line { position: absolute; top: 65%; left: 10%; width: 80%; height: 30%; border-top: 4px dashed rgba(52, 211, 153, 0.6); border-radius: 50% 50% 0 0; }
        .center-dot { position: absolute; top: 35%; left: 50%; transform: translate(-50%, -50%); width: 6px; height: 6px; background: rgba(255, 255, 255, 0.8); border-radius: 50%; }

        .card { border: none; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card-header { border-radius: 16px 16px 0 0 !important; border-bottom: none; }
    </style>
</head>
<body>

<?php 
    // include('/path/ke/aplikasi_id_card/header.php'); 
?>
@include('layouts.header')
<div class="app-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Panel Operator Capturing</h3>
            <p class="text-muted small mb-0">Sistem ID Card Terpadu UIN Sunan Ampel</p>
        </div>
        <button class="btn btn-primary fw-bold shadow-sm rounded-pill px-4" onclick="bukaMonitorMahasiswa()">
            🖥️ BUKA MONITOR MAHASISWA
        </button>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header bg-dark text-white fw-bold text-center py-3">FOKUS KAMERA</div>
                <div class="card-body d-flex flex-column justify-content-center p-4">
                    <div class="camera-box">
                        <video id="video" autoplay playsinline></video>
                        <div class="overlay-container">
                            <div class="face-oval"></div>
                            <div class="shoulder-line"></div>
                            <div class="center-dot"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header" style="background-color: #f1f5f9; color: #334155; font-weight: 800;">VERIFIKASI MAHASISWA</div>
                <div class="card-body p-4">
                    <div class="input-group input-group-lg mb-3 shadow-sm">
                        <input type="text" id="nim" class="form-control border-primary" placeholder="Scan Barcode / Ketik NIM..." autofocus>
                        <button class="btn btn-primary px-4 fw-bold" onclick="cariData()">CEK DATA</button>
                        
                        <!-- TOMBOL BARU UNTUK MELIHAT HASIL LOKAL -->
                        <button class="btn btn-dark px-3 fw-bold" onclick="lihatHasilLokal()" title="Lihat Foto Lokal">
                            🔍 LIHAT
                        </button>
                    </div>
                    
                    <div id="info-mahasiswa" class="p-3 bg-light rounded-3 border d-none">
                        <div id="nama-val" class="h5 mb-1 text-primary fw-bold">-</div>
                        <div id="prodi-val" class="small fw-bold text-secondary">-</div>
                        <input type="hidden" id="gender-val" value=""> 
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-5">
                            <canvas id="canvas" width="480" height="640" class="d-none"></canvas>
                            <div id="results" class="results-preview">
                                <span class="text-muted small fw-bold">Belum Ada Foto</span>
                            </div>
                        </div>
                        <div class="col-7">
                            <div id="log-status" class="mb-3 text-center small fw-bold text-uppercase" style="letter-spacing: 1px;">STANDBY</div>
                            
                            <div class="d-grid gap-3">
                                <button id="btn-capture" class="btn btn-danger btn-lg py-3 fw-bold shadow" onclick="takeSnap()" disabled>
                                    📸 AMBIL FOTO (SPASI)
                                </button>
                                <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="resetForm()">BATAL / REFRESH</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL PRATINJAU FOTO LOKAL -->
<div class="modal fade" id="modalPreviewFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2);">
            <div class="modal-header bg-dark text-white border-0 py-2">
                <h6 class="modal-title fw-bold mx-auto text-uppercase" style="letter-spacing: 1px;">Hasil Pemotretan</h6>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light text-center position-relative">
                <div class="p-4">
                    <img id="img-preview-lokal" src="" alt="Preview Lokal" class="img-fluid rounded shadow-sm" style="width: 100%; aspect-ratio: 3/4; object-fit: cover; display: none;" 
                         onerror="this.style.display='none'; document.getElementById('error-preview-teks').style.display='block';">
                    
                    <div id="error-preview-teks" class="py-5" style="display: none;">
                        <span class="fs-1">📭</span>
                        <p class="text-muted fw-bold mt-2 mb-0">Foto Tidak Ditemukan</p>
                        <p class="small text-secondary">NIM belum difoto atau belum tersimpan.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2 justify-content-center">
                <span id="label-nim-modal" class="fw-bold text-dark font-monospace fs-5">-</span>
            </div>
        </div>
    </div>
</div>
<!-- AKHIR MODAL -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const nimInput = document.getElementById('nim');
    const btnCapture = document.getElementById('btn-capture');
    const resultsBox = document.getElementById('results');
    const logStatus = document.getElementById('log-status');

    const bc = new BroadcastChannel('uinsa_capture_monitor');

    navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720 } })
        .then(stream => { video.srcObject = stream; })
        .catch(err => alert("Gagal mengakses kamera: " + err));

    function bukaMonitorMahasiswa() {
        window.open('{{ route("foto.monitor") }}', 'uinsa_monitor', 'width=800,height=600');
    }

    document.addEventListener('keydown', (e) => {
        if (e.code === "Space" && !btnCapture.disabled) {
            e.preventDefault();
            takeSnap();
        }
    });

    function cariData() {
        const nim = nimInput.value.trim();
        if(!nim) { alert("NIM tidak boleh kosong!"); return; }

        logStatus.className = "mb-3 text-center small fw-bold text-warning";
        logStatus.innerText = "Mencari Data...";

        fetch(`{{ route('foto.cari') }}?nim=${nim}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('info-mahasiswa').classList.remove('d-none');
                    document.getElementById('nama-val').innerText = data.nama;
                    document.getElementById('prodi-val').innerText = data.prodi;
                    
                    btnCapture.disabled = false;
                    logStatus.className = "mb-3 text-center small fw-bold text-primary";
                    logStatus.innerText = "MAHASISWA TERVERIFIKASI";

                    const cacheBuster = new Date().getTime(); 
                    const existingPhotoUrl = `https://sinau.uinsa.ac.id/uploads/fotomhs/${nim}.jpg?v=${cacheBuster}`;

                    resultsBox.innerHTML = `
                        <div class="position-relative w-100 h-100 d-flex justify-content-center align-items-center bg-light">
                            <span class="badge bg-warning text-dark position-absolute top-0 start-50 translate-middle-x mt-2" style="font-size:0.65rem; z-index:5;">FOTO SINAU</span>
                            <img src="${existingPhotoUrl}" class="img-fluid h-100 w-100" style="object-fit: cover;" 
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\\'text-muted small fw-bold\\'>Belum Ada Foto</span>';">
                        </div>
                    `;

                    bc.postMessage({ type: 'verifikasi', nama: data.nama, gender: data.gender, nim: nim });
                } else {
                    alert("Info: " + data.message);
                    resetForm();
                }
            })
            .catch(error => {
                logStatus.className = "mb-3 text-center small fw-bold text-danger";
                logStatus.innerText = "ERROR SERVER";
            });
    }

    function takeSnap() {
        btnCapture.disabled = true;
        
        bc.postMessage({ type: 'countdown' });
        
        let count = 3;
        logStatus.className = "mb-3 text-center small fw-bold text-danger";
        logStatus.innerText = "MENGAMBIL FOTO (" + count + ")...";

        let cdInterval = setInterval(() => {
            count--;
            if(count > 0) {
                logStatus.innerText = "MENGAMBIL FOTO (" + count + ")...";
            } else {
                clearInterval(cdInterval);
                executeCapture(); 
            }
        }, 1000);
    }

    function executeCapture() {
        const context = canvas.getContext('2d');
        const sourceWidth = video.videoHeight * (3/4);
        const sourceX = (video.videoWidth - sourceWidth) / 2;

        context.save();
        // LOGIKA KANVAS NORMAL (Tanpa pembalikan horizontal agar foto terbaca benar)
        context.drawImage(video, sourceX, 0, sourceWidth, video.videoHeight, 0, 0, canvas.width, canvas.height);
        context.restore();

        const dataURL = canvas.toDataURL('image/jpeg', 0.95);

        resultsBox.innerHTML = `
            <div class="position-relative h-100 w-100">
                <span class="badge bg-success position-absolute top-0 start-50 translate-middle-x mt-2" style="font-size:0.65rem; z-index:5;">FOTO BARU</span>
                <img src="${dataURL}" class="img-fluid h-100 w-100" style="object-fit: cover;">
            </div>
        `;

        fetch('{{ route("foto.simpan") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ image: dataURL, nim: nimInput.value })
        })
        .then(res => res.json())
        .then(data => {
            logStatus.className = "mb-3 text-center small fw-bold text-success";
            logStatus.innerText = "✅ TERSIMPAN DI LOKAL";
            
            bc.postMessage({ type: 'tersimpan', nim: nimInput.value, image: dataURL });
        });
    }

    function resetForm() {
        nimInput.value = "";
        nimInput.focus();
        btnCapture.disabled = true;
        document.getElementById('info-mahasiswa').classList.add('d-none');
        resultsBox.innerHTML = '<span class="text-muted small fw-bold">Belum Ada Foto</span>';
        logStatus.className = "mb-3 text-center small fw-bold text-uppercase";
        logStatus.innerText = "STANDBY";
        
        bc.postMessage({ type: 'reset' });
    }

    // FUNGSI UNTUK MENAMPILKAN MODAL PREVIEW FOTO LOKAL
    function lihatHasilLokal() {
        const nim = nimInput.value.trim();
        if(!nim) { 
            alert("Silakan ketik/scan NIM terlebih dahulu untuk melihat foto."); 
            nimInput.focus();
            return; 
        }

        document.getElementById('label-nim-modal').innerText = nim;
        const imgEl = document.getElementById('img-preview-lokal');
        const errEl = document.getElementById('error-preview-teks');
        
        imgEl.style.display = 'none';
        errEl.style.display = 'none';

        const cacheBuster = new Date().getTime();
        imgEl.src = `{{ url('storage/app/public/foto_maba') }}/${nim}.jpg?v=${cacheBuster}`;
        
        imgEl.style.display = 'block';

        const previewModal = new bootstrap.Modal(document.getElementById('modalPreviewFoto'));
        previewModal.show();
    }
</script>
</body>
</html>
