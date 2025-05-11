<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Usulan</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Style -->
    <link rel="stylesheet" href="<?= base_url('asset/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/css/detail.css') ?>">
</head>
<body class="d-flex flex-column vw-100" style="overflow-x: hidden;">

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid px-3">
            <a class="navbar-brand d-flex align-items-center" href="<?= site_url('dashboard/operator') ?>">
                <img style="width: 50px;" src="<?= base_url('asset/img/logo-remv.png') ?>" alt="" class="me-2">
                <span class="fw-bold">ROMANTIS</span>
            </a>
            <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('dashboard/operator') ?>"><i class="fas fa-home me-1"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('usulan') ?>"><i class="fas fa-file-alt me-1"></i> Usulan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('logout') ?>"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">
    <div class="container-fluid full-width">
        <div class="card-header d-flex justify-content-between align-items-center px-0 py-4">
            <a href="javascript:window.history.back();" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
        <!-- Section Usulan -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header text-white" style="background-color: var(--primary);">Informasi Usulan</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="info-label">Nama Instansi</label>
                        <div class="info-value"><?= esc($usulan['nama_instansi']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="info-label">Nama Operator</label>
                        <div class="info-value"><?= esc($usulan['nama']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="info-label">Email</label>
                        <div class="info-value"><?= esc($usulan['email']) ?></div>
                    </div>

                    <div class="col-md-4">
                        <label class="info-label">No HP</label>
                        <div class="info-value"><?= esc($usulan['nomor_hp']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="info-label">Tanggal Usulan</label>
                        <div class="info-value"><?= date('d M Y', strtotime($usulan['tanggal_usulan'])) ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="info-label">Nomor Surat Usulan</label>
                        <div class="info-value"><?= esc($usulan['no_surat']) ?></div>
                    </div>

                    <div class="col-md-4">
                        <label class="info-label">Berkas Usulan</label>
                        <div class="info-value">
                            <a href="<?= base_url('writable/uploads/' . $usulan['berkas_usulan']) ?>" target="_blank" class="file-link">Lihat Berkas</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="info-label">Surat Usulan</label>
                        <div class="info-value">
                            <a href="<?= base_url('writable/uploads/' . $usulan['surat_usulan']) ?>" target="_blank" class="file-link">Lihat Surat</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Rekomendasi -->
        <?php if ($rekomendasi): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header text-white" style="background-color: var(--primary);">Informasi Rekomendasi</div>
            <div class="card-body">
                <p><span class="info-label">Tanggal Rekomendasi:</span> <div class="info-value"><?= date('d M Y', strtotime($rekomendasi['tanggal_rekomendasi'])) ?></div></p>
                <p><span class="info-label">Nomor Rekomendasi:</span> <div class="info-value"><?= esc($rekomendasi['no_rekomendasi']) ?></div></p>
            </div>
        </div>
        <?php else: ?>
        <p class="text-muted">Tidak ada rekomendasi yang tersedia.</p>
        <?php endif; ?>


        <!-- Tabel Usulan -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header text-white" style="background-color: var(--primary);">Tabel Usulan Prakom & Statistisi</div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Jabatan</th>
                            <th>Prakom</th>
                            <th>Statistisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tingkatan as $t): ?>
                        <tr class="text-center">
                            <td><?= $t['nama'] ?></td>
                            <td><?= $usulanPrakom[$t['id']] ?? 0 ?></td>
                            <td><?= $usulanStatistisi[$t['id']] ?? 0 ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Existing -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header text-white" style="background-color: var(--primary);">Tabel Existing Prakom & Statistisi</div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Jabatan</th>
                            <th>Prakom</th>
                            <th>Statistisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tingkatan as $t): ?>
                        <tr class="text-center">
                            <td><?= $t['nama'] ?></td>
                            <td><?= $existingPrakom[$t['id']] ?? 0 ?></td>
                            <td><?= $existingStatistisi[$t['id']] ?? 0 ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Rekomendasi -->
        <?php if (!empty($disetujuiKompetensi)): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header text-white" style="background-color: var(--primary);">Tabel Rekomendasi Prakom & Statistisi</div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Jabatan</th>
                            <th>Prakom</th>
                            <th>Statistisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tingkatan as $t): ?>
                        <tr class="text-center">
                            <td><?= $t['nama'] ?></td>
                            <td><?= $rekomendasiPrakom[$t['id']] ?? 0 ?></td>
                            <td><?= $rekomendasiStatistisi[$t['id']] ?? 0 ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© 2023 Rekomendasi Prakom dan Statistisi. Hak Cipta Dilindungi.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
