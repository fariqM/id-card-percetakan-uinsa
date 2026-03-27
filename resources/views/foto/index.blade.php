<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Foto KTM UINSA</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
        }
        .navbar-uinsa {
            background-color: #006633;
            color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-search {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .profile-card {
            border-left: 5px solid #006633;
            border-radius: 10px;
        }
        .btn-uinsa {
            background-color: #006633;
            color: white;
            transition: all 0.3s;
        }
        .btn-uinsa:hover {
            background-color: #004d26;
            color: white;
            transform: translateY(-2px);
        }
        .instruction-box {
            background-color: #e8f5e9;
            border-radius: 10px;
            padding: 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-uinsa mb-5">
    <div class="container py-2">
        <span class="navbar-brand mb-0 h1">UIN Sunan Ampel Surabaya</span>
        <span class="badge bg-light text-dark">Layanan Foto KTM Massal v1.0</span>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <strong>Gagal:</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card card-search mb-4">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-3">Input Data Mahasiswa Baru</h5>
                    <form action="{{ route('foto.cari') }}" method="GET">
                        <div class="input-group input-group-lg">
                            <input type="text" name="nim" class="form-control" placeholder="Masukkan 11 Digit NIM..." required autofocus value="{{ request('nim') }}">
                            <button class="btn btn-uinsa" type="submit">Cari Data</button>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($mahasiswa))
            <div class="card shadow-sm profile-card animate__animated animate__fadeIn">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-grow-1">
                            <h4 class="fw-bold mb-0">{{ $mahasiswa->nama }}</h4>
                            <p class="text-muted mb-0">NIM: {{ $mahasiswa->nim }}</p>
                        </div>
                        <div class="text-end">
                            <span class="badge {{ $mahasiswa->status_foto == 'sudah' ? 'bg-success' : 'bg-warning' }}">
                                {{ strtoupper($mahasiswa->status_foto ?? 'Belum Foto') }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-3 mb-4 text-sm">
                        <div class="col-6">
                            <label class="text-muted small d-block">Fakultas</label>
                            <strong>{{ $mahasiswa->fakultas }}</strong>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small d-block">Program Studi</label>
                            <strong>{{ $mahasiswa->prodi }}</strong>
                        </div>
                    </div>

                    <div class="instruction-box mb-4">
                        <strong>Instruksi Operator:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Pastikan Mahasiswa mengenakan <strong>Jas Almamater</strong>.</li>
                            <li>Posisi kerudung dimasukkan (bagi mahasiswi).</li>
                            <li>Pastikan pencahayaan cukup untuk background merah otomatis.</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button onclick="ambilFoto('{{ $mahasiswa->nim }}')" class="btn btn-danger btn-lg py-3">
                            📸 Mulai Pengambilan Foto
                        </button>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<footer class="text-center mt-5 py-4 text-muted small">
    &copy; 2026 Teknologi Informasi - UIN Sunan Ampel Surabaya
</footer>

<script>
    function ambilFoto(nim) {
        Swal.fire({
            title: 'Aktifkan Kamera?',
            text: "Sistem akan menjalankan modul pengenalan pola untuk NIM: " + nim,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#006633',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Buka Kamera',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                
                // Tampilkan Loading Spinner
                Swal.fire({
                    title: 'Kamera Sedang Aktif',
                    html: 'Mohon selesaikan pengambilan foto di jendela aplikasi kamera.<br><b>Status: Menunggu Validasi AI...</b>',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                // Memanggil endpoint Laravel yang menjalankan Python
                fetch(`/foto/capture/${nim}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Foto Tersimpan!',
                                text: data.message,
                                confirmButtonColor: '#006633'
                            }).then(() => {
                                // Redirect kembali ke halaman pencarian (siap untuk maba selanjutnya)
                                window.location.href = "{{ route('foto.index') }}";
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Proses Gagal',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Sistem',
                            text: 'Gagal terhubung ke modul Python. Pastikan script berjalan.'
                        });
                    });
            }
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>