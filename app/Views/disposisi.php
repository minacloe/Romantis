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
            <div class="card-body">
            <form method="POST" action="<?= site_url('disposisi/tambah') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="usulan_id" value="<?= esc($usulan['id']) ?>">
                <div class="card form-section">
                    <div class="card-header">Disposisi</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="nama_instansi" class="form-label">Nama Kementerian</label>
                                <input type="text" class="form-control bg-light" id="nama_instansi" name="nama_instansi" value="<?= esc($usulan['nama_instansi']) ?>" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="nama_operator" class="form-label">Nama Operator</label>
                                <input type="text" class="form-control bg-light" id="nama_operator" name="nama_operator" value="<?= esc($usulan['nama_operator']) ?>" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="no_surat" class="form-label">Nomor Surat Usulan</label>
                                <input type="text" class="form-control bg-light" id="no_surat" name="no_surat" value="<?= esc($usulan['no_surat']) ?>" disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tanggal_usulan" class="form-label">Tanggal Surat Usulan</label>
                                <input type="date" class="form-control bg-light" id="tanggal_usulan" name="tanggal_usulan" value="<?= esc($usulan['tanggal_usulan']) ?>" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="surat_usulan" class="form-label">Surat Usulan</label><br>
                                <div class="form-control bg-light d-flex align-items-center" style="height: 38px;">
                                    <a href="<?= base_url('writable/uploads/' . $usulan['surat_usulan']) ?>" target="_blank" class="text-decoration-none" style="width: 100%;">
                                        <?= esc($usulan['surat_usulan']) ?>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="berkas_usulan" class="form-label">Berkas Usulan</label><br>
                                <div class="form-control bg-light d-flex align-items-center" style="height: 38px;">
                                    <a href="<?= base_url('writable/uploads/' . $usulan['berkas_usulan']) ?>" target="_blank" class="text-decoration-none" style="width: 100%;">
                                        <?= esc($usulan['berkas_usulan']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="disposisi_admin" class="form-label">Pilih Admin Disposisi</label>
                                <select class="form-select" id="disposisi_admin" name="disposisi_admin" required>
                                    <option value="">-- Pilih Admin --</option>
                                    <?php foreach ($akunList as $akun): ?>
                                        <option value="<?= esc($akun['id']) ?>">
                                            <?= esc($akun['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="tanggal_disposisi" class="form-label">Tanggal Disposisi</label>
                                <input type="date" class="form-control" id="tanggal_disposisi" name="tanggal_disposisi" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="jadwal_rapat" class="form-label">Jadwal Pelaksanaan Rapat</label>
                                <input type="date" class="form-control" id="jadwal_rapat" name="jadwal_rapat" required>
                            </div>
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
    const form = document.querySelector('form');
    const submitBtn = document.querySelector('button[type="submit"]');

    form.addEventListener('submit', function(e) {
        Swal.fire({
            title: 'Memproses Disposisi...',
            text: 'Harap tunggu sebentar.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
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
                    window.location.href = "<?= site_url('dashboard/admin') ?>";
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
                    location.reload(); 
                }
            });
        <?php endif; ?>
    });
</script>



</body>
</html>