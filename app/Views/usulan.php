<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="X-CSRF-TOKEN" content="<?= csrf_hash() ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('asset/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/css/usulan.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="d-flex flex-column vw-100" style="overflow-x: hidden;">
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid px-3">
            <a class="navbar-brand d-flex align-items-center" href="<?= site_url('dashboard/admin') ?>">
                <img style="width: 50px;" src="<?= base_url('asset/img/logo-remv.png') ?>" alt="" class="me-2">
                <span class="fw-bold">ROMANTIS</span>
            </a>
            <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list"></i>
            </button>
            <div class="collapse navbar-collapse mt-2 mt-lg-0" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item">
                        <a class="nav-link <?= service('uri')->getSegment(1) === 'dashboard' ? 'active' : '' ?>" 
                        href="<?= site_url('dashboard/operator') ?>">
                            <i class="fas fa-home me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= service('uri')->getSegment(1) === 'usulan' ? 'active' : '' ?>" 
                        href="<?= site_url('usulan') ?>">
                            <i class="fas fa-file-alt me-1"></i> Usulan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link logout-btn" href="<?= site_url('logout') ?>">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container-fluid px-0 py-4">
            <form method="POST" action="<?= site_url('usulan/tambah') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="card form-section">
                    <div class="card-header">Informasi</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_instansi" class="form-label">Nama Instansi</label>
                                <input type="text" class="form-control bg-light" id="nama_instansi" name="nama_instansi" value="<?= esc($akun['nama_instansi']) ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nama_operator" class="form-label">Nama Operator</label>
                                <input type="text" class="form-control bg-light" id="nama_operator" name="nama_operator" value="<?= esc($akun['nama']) ?>" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control bg-light" id="email" name="email" value="<?= esc($akun['email']) ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nomor_hp" class="form-label">No HP</label>
                                <input type="tel" class="form-control bg-light" id="nomor_hp" name="nomor_hp" value="<?= esc($akun['nomor_hp']) ?>" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_surat" class="form-label">Tanggal Surat Usulan</label>
                                <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nomor_surat" class="form-label">Nomor Surat Usulan</label>
                                <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="berkas_usulan" class="form-label">Berkas Usulan (ZIP Maks 20MB)</label>
                                <input type="file" class="form-control" id="berkas_usulan" name="berkas_usulan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="surat_usulan" class="form-label">Surat Usulan (PDF Maks 5MB)</label>
                                <input type="file" class="form-control" id="surat_usulan" name="surat_usulan" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- (bagian usulan tenaga fungsional tetap, tanpa perubahan) -->
                <div class="card form-section">
                    <div class="card-header">Usulan Tenaga Fungsional</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th rowspan="2">Jabatan</th>
                                            <th colspan="2">Statistisi</th>
                                            <th colspan="2">Prakom</th>
                                        </tr>
                                        <tr>
                                            <th>Jumlah Usulan</th>
                                            <th>Existing</th>
                                            <th>Jumlah Usulan</th>
                                            <th>Existing</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Ahli Utama</td>
                                            <td><input type="number" name="statistisi_utama_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="statistisi_utama_existing" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_utama_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_utama_existing" class="form-control" min="0" value="0" required></td>
                                        </tr>
                                        <tr>
                                            <td>Ahli Madya</td>
                                            <td><input type="number" name="statistisi_madya_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="statistisi_madya_existing" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_madya_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_madya_existing" class="form-control" min="0" value="0" required></td>
                                        </tr>
                                        <tr>
                                            <td>Ahli Muda</td>
                                            <td><input type="number" name="statistisi_muda_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="statistisi_muda_existing" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_muda_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_muda_existing" class="form-control" min="0" value="0" required></td>
                                        </tr>
                                        <tr>
                                            <td>Ahli Pertama</td>
                                            <td><input type="number" name="statistisi_pertama_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="statistisi_pertama_existing" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_pertama_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_pertama_existing" class="form-control" min="0" value="0" required></td>
                                        </tr>
                                        <tr>
                                            <td>Penyelia</td>
                                            <td><input type="number" name="statistisi_penyelia_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="statistisi_penyelia_existing" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_penyelia_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_penyelia_existing" class="form-control" min="0" value="0" required></td>
                                        </tr>
                                        <tr>
                                            <td>Mahir</td>
                                            <td><input type="number" name="statistisi_mahir_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="statistisi_mahir_existing" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_mahir_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_mahir_existing" class="form-control" min="0" value="0" required></td>
                                        </tr>
                                        <tr>
                                            <td>Terampil</td>
                                            <td><input type="number" name="statistisi_terampil_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="statistisi_terampil_existing" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_terampil_usulan" class="form-control" min="0" value="0" required></td>
                                            <td><input type="number" name="prakom_terampil_existing" class="form-control" min="0" value="0" required></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                </div>

                <div class="container-fluid px-0 mt-4">
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-submit w-100">
                                <i class="fas fa-paper-plane me-2"></i>Kirim
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </main>


    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© 2023 Rekomendasi Prakom dan Statistisi. Hak Cipta Dilindungi.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('asset/js/script.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const submitButton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop submit default

            const tanggalSurat = document.getElementById('tanggal_surat').value.trim();
            const nomorSurat = document.getElementById('nomor_surat').value.trim();
            const berkasUsulan = document.getElementById('berkas_usulan').files.length;
            const suratUsulan = document.getElementById('surat_usulan').files.length;

            // 🔥 Validasi form umum
            if (!tanggalSurat || !nomorSurat || !berkasUsulan || !suratUsulan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap!',
                    text: 'Harap lengkapi semua data sebelum mengajukan.',
                    confirmButtonText: 'Oke'
                }).then(() => {
                    location.reload();
                });
                return;
            }

            // 🔥 Validasi angka input tidak boleh negatif
            const allNumberInputs = form.querySelectorAll('input[type="number"]');
            let hasNegative = false;
            allNumberInputs.forEach(input => {
                if (parseInt(input.value) < 0) {
                    hasNegative = true;
                }
            });

            if (hasNegative) {
                Swal.fire({
                    icon: 'error',
                    title: 'Input Tidak Valid!',
                    text: 'Jumlah usulan atau existing tidak boleh negatif!',
                    confirmButtonText: 'Perbaiki'
                }).then(() => {
                    location.reload();
                });
                return;
            }

            const MAX_SIZE = 20 * 1024 * 1024; 

            const berkasUsulanFile = document.getElementById('berkas_usulan').files[0];
            const suratUsulanFile = document.getElementById('surat_usulan').files[0];

            if (berkasUsulanFile && berkasUsulanFile.size > MAX_SIZE) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar!',
                    text: 'Ukuran berkas usulan melebihi 20 MB.',
                    confirmButtonText: 'Oke'
                }).then(() => {
                    location.reload();
                });
                return;
            }

            if (suratUsulanFile && suratUsulanFile.size > MAX_SIZE) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar!',
                    text: 'Ukuran surat usulan melebihi 20 MB.',
                    confirmButtonText: 'Oke'
                }).then(() => {
                    location.reload();
                });
                return;
            }

            // Kalau semua valid, kirim data
            const formData = new FormData(form);

            Swal.fire({
                title: 'Mengirim Usulan...',
                text: 'Harap tunggu sebentar.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('<?= site_url('usulan/tambah') ?>', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.text())
            .then(text => {
                console.log('=== SERVER RESPONSE ===');
                console.log(text);

                // Karena response masih teks biasa, kita perlu parse manual ke JSON
                let data;
                try {
                    data = JSON.parse(text);
                } catch (error) {
                    console.error('Bukan JSON:', text);
                    throw new Error('Server tidak mengembalikan JSON');
                }

                Swal.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        confirmButtonText: 'Oke'
                    }).then(() => {
                        window.location.href = "<?= site_url('dashboard/operator') ?>";
                    });
                } else {
                    let fullError = data.message;
                    if (data.errors) {
                        fullError += '\n\n' + Object.values(data.errors).join('\n');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: fullError,
                        confirmButtonText: 'Oke'
                    }).then(() => {
                        location.reload();
                    });
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Tidak dapat menghubungi server.',
                    confirmButtonText: 'Oke'
                }).then(() => {
                    location.reload();
                });
            });

        });
    });
</script>




</body>
</html>
