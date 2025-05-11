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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid px-3">
            <a class="navbar-brand d-flex align-items-center" href=#>
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
                        href="<?= site_url('dashboard/admin') ?>">
                            <i class="fas fa-home me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= service('uri')->getSegment(1) === 'manajemen' ? 'active' : '' ?>" 
                        href="<?= site_url('manajemen') ?>">
                            <i class="fas fa-users-cog me-1"></i> Pengguna
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

    <!-- Main -->
    <main class="main-content">
        <div class="container-fluid px-0 py-0">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="javascript:history.back()" class="btn" style="background-color: #FF6F00; color: white;">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <form method="POST" action="<?= site_url('rekomendasi/tambah') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="disposisi_id" value="<?= esc($disposisi['id']) ?>">
                <div class="card form-section">
                    <div class="card-header">Informasi</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="nama_instansi" class="form-label">Nama Kementerian</label>
                                <input type="text" class="form-control bg-light" value="<?= esc($disposisi['nama_instansi']) ?>" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="nama_operator" class="form-label">Nama Operator</label>
                                <input type="text" class="form-control bg-light" value="<?= esc($disposisi['nama_operator']) ?>" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="no_surat" class="form-label">Nomor Surat Usulan</label>
                                <input type="text" class="form-control bg-light" value="<?= esc($disposisi['no_surat']) ?>" disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tanggal_usulan" class="form-label">Tanggal Surat Usulan</label>
                                <input type="date" class="form-control bg-light" value="<?= esc($disposisi['tanggal_usulan']) ?>" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Surat Usulan</label>
                                <div class="form-control bg-light">
                                    <a href="<?= base_url('writable/uploads/' . $disposisi['surat_usulan']) ?>" target="_blank" class="text-decoration-none">
                                        <?= esc($disposisi['surat_usulan']) ?>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Berkas Usulan</label>
                                <div class="form-control bg-light">
                                    <a href="<?= base_url('writable/uploads/' . $disposisi['berkas_usulan']) ?>" target="_blank" class="text-decoration-none">
                                        <?= esc($disposisi['berkas_usulan']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="admin_disposisi" class="form-label">Admin Disposisi</label>
                                <input type="text" class="form-control bg-light" id="admin_disposisi" name="admin_disposisi" value="<?= esc($disposisi['nama_admin']) ?>" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tanggal_disposisi" class="form-label">Tanggal Disposisi</label>
                                <input type="date" class="form-control bg-light" value="<?= esc($disposisi['tanggal_disposisi']) ?>" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="jadwal_rapat" class="form-label">Jadwal Rapat</label>
                                <input type="date" class="form-control bg-light" value="<?= esc($disposisi['jadwal_rapat']) ?>" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tanggal_rekomendasi" class="form-label">Tanggal Rekomendasi</label>
                                <input type="date" class="form-control" name="tanggal_rekomendasi" required placeholder="Pilih Tanggal Rekomendasi">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="no_rekomendasi" class="form-label">Nomor Rekomendasi</label>
                                <input type="text" class="form-control" name="no_rekomendasi" required placeholder="Masukkan Nomor Rekomendasi">
                            </div>
                            <div class="col-md-4 mb-3">
                            <label for="surat_rekomendasi" class="form-label">Surat Rekomendasi (PDF Maks. 5 MB)</label>
                            <input type="file" class="form-control" name="surat_rekomendasi" accept="application/pdf" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card form-section mt-4">
                    <div class="card-header">Rekomendasi Jabatan</div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Jabatan</th>
                                    <th>Statistisi</th>
                                    <th>Prakom</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $jabatanList = ['Utama', 'Madya', 'Muda', 'Pertama', 'Penyelia', 'Mahir', 'Terampil'];
                                foreach ($jabatanList as $i => $jabatan):
                                    $statMax = $usulanStatistisi[$i] ?? 0;
                                    $prakomMax = $usulanPrakom[$i] ?? 0;
                                ?>
                                <tr>
                                    <td><?= esc($jabatan) ?></td>
                                    <td>
                                        <input type="number" class="form-control text-center"
                                               name="statistisi_<?= $i+1 ?>"
                                               min="0" max="<?= $statMax ?>"
                                               placeholder="Maks: <?= $statMax ?>"
                                               value="0">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control text-center"
                                               name="prakom_<?= $i+1 ?>"
                                               min="0" max="<?= $prakomMax ?>"
                                               placeholder="Maks: <?= $prakomMax ?>"
                                               value="0">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                            </tbody>
                        </table>
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

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">
                © 2023 Rekomendasi Prakom dan Statistisi. Hak Cipta Dilindungi.
            </span>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('asset/js/script.js') ?>"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                Swal.fire({
                    title: 'Memproses Rekomendasi...',
                    text: 'Harap tunggu sebentar.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            });
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?= session('success') ?>',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "<?= site_url('dashboard/admin') ?>"; // Redirect ke dashboard admin
                }
            });
        <?php elseif (session()->getFlashdata('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: '<?= esc(session('error')) ?>',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload(); // Auto refresh saat klik OK
                }
            });
        <?php endif; ?>
    });
</script>


</body>
</html>