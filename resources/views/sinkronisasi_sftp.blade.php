<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinkronisasi Server Sinau - UINSA</title>
	<link rel="icon" href="{{ asset('/public/assets/uinsa.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Plus Jakarta Sans', sans-serif; }
        .sync-container { max-width: 800px; margin: 50px auto; }
        .log-box { height: 250px; overflow-y: auto; background: #1e293b; color: #38bdf8; font-family: monospace; padding: 15px; border-radius: 8px; font-size: 0.85rem; }
        .log-success { color: #34d399; }
        .log-error { color: #ef4444; }
    </style>
</head>
<body>

<?php 
    // include('/path/ke/aplikasi_id_card/header.php'); 
?>
@include('layouts.header')
<div class="container sync-container">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-dark text-white p-4 text-center rounded-top-4">
            <h4 class="fw-bold mb-0">☁️ Sinkronisasi SFTP Server SINAU UINSA</h4>
            <p class="text-muted small mb-0 mt-1">Mengunggah file foto lokal ke server utama</p>
        </div>
		
		<div class="d-flex align-items-center justify-content-between p-3 mb-4 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
			<div class="d-flex align-items-center">
				<span class="fs-4 me-2">🌐</span>
				<div>
					<h6 class="fw-bold mb-0 text-dark">Status Server SFTP</h6>
					<small class="text-muted">Target: Server Sinau UINSA</small>
				</div>
            </div>
            <div>
				<span id="sftp-status-badge" class="badge bg-secondary py-2 px-3 fw-bold" style="font-size: 0.85rem;">
					<span class="spinner-border spinner-border-sm me-1" role="status"></span> Mengecek...
					</span>
                </div>
		</div>
		
        <div class="card-body p-4">
            
            <div class="row text-center mb-4">
                <div class="col-6 border-end">
                    <h1 id="total-pending" class="fw-bold text-warning">0</h1>
                    <span class="text-muted small text-uppercase">Antrean (Pending)</span>
                </div>
                <div class="col-6">
                    <h1 id="total-success" class="fw-bold text-success">0</h1>
                    <span class="text-muted small text-uppercase">Berhasil Diunggah</span>
                </div>
            </div>

            <div class="progress mb-3" style="height: 25px; border-radius: 12px;">
                <div id="sync-progress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; font-weight: bold;">0%</div>
            </div>

            <div class="d-grid mb-4">
                <button id="btn-start" class="btn btn-primary btn-lg fw-bold" onclick="startSync()" disabled>
                    Menunggu Proses Singkronisasi
                </button>
            </div>

            <label class="fw-bold small mb-2">System Log:</label>
            <div id="sync-log" class="log-box">
                <div>[Sistem] Menunggu instruksi...</div>
            </div>

        </div>
    </div>
</div>

<script>
    let unsyncedData = [];
    let totalItems = 0;
    let successCount = 0;
    const btnStart = document.getElementById('btn-start');
    const logBox = document.getElementById('sync-log');

    // 1. Saat halaman dimuat, cek ada berapa data yang belum di-sync
    document.addEventListener('DOMContentLoaded', () => {
        checkSftp(); // Cek server Sinau dulu
		fetchData(); // Baru cek database antrean
    });

    function addLog(message, type = '') {
        const div = document.createElement('div');
        div.className = type;
        div.innerText = `[${new Date().toLocaleTimeString()}] ${message}`;
        logBox.appendChild(div);
        logBox.scrollTop = logBox.scrollHeight; // Auto scroll ke bawah
    }

    function fetchData() {
        addLog("Memeriksa database...");
        fetch('{{ route("foto.sync.data") }}')
            .then(res => res.json())
            .then(data => {
                console.log(data);
                
                if(data.success) {
                    unsyncedData = data.data;
                    totalItems = unsyncedData.length;
                    document.getElementById('total-pending').innerText = totalItems;
                    
                    if(totalItems > 0) {
                        addLog(`Ditemukan ${totalItems} foto yang belum diunggah ke Server Sinau.`);
                        btnStart.disabled = true;
                    } else {
                        addLog(`Semua foto sudah sinkron dengan Server Sinau.`, 'log-success');
                        btnStart.disabled = true;
                        btnStart.className = "btn btn-success btn-lg fw-bold";
                        btnStart.innerText = "SEMUA DATA SUDAH SINKRON";
                    }
                }
            });
    }

    // 2. Proses upload satu per satu menggunakan Async/Await
    async function startSync() {
        if(unsyncedData.length === 0) return;

        btnStart.disabled = true;
        btnStart.innerText = "⏳ PROSES SINKRONISASI BERJALAN...";
        addLog("=== MEMULAI SINKRONISASI SFTP ===");
        console.log(totalItems);
        return
        for (let i = 0; i < totalItems; i++) {
            let nim = unsyncedData[i];
            
            try {
                // Proses upload 1 per 1 agar server tidak timeout
                let response = await fetch('{{ route("foto.sync.proses") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ nim: nim })
                });
                
                let result = await response.json();

                if (result.success) {
                    successCount++;
                    document.getElementById('total-success').innerText = successCount;
                    document.getElementById('total-pending').innerText = totalItems - successCount;
                    addLog(`[OK] Foto NIM ${nim} berhasil diunggah.`, 'log-success');
                } else {
                    addLog(`[GAGAL] NIM ${nim} -> ${result.message}`, 'log-error');
                }

            } catch (error) {
                addLog(`[ERROR SISTEM] NIM ${nim} gagal diproses.`, 'log-error');
            }

            // Update Progress Bar
            let percentage = Math.round((successCount / totalItems) * 100);
            const progressBar = document.getElementById('sync-progress');
            progressBar.style.width = percentage + '%';
            progressBar.innerText = percentage + '%';
        }

        addLog("=== SINKRONISASI SELESAI ===");
        btnStart.innerText = "✅ PROSES SELESAI";
    }
	
	// 3. Cek koneksi SFTP
	function checkSftp() {
        const badge = document.getElementById('sftp-status-badge');
    }
</script>
</body>
</html>