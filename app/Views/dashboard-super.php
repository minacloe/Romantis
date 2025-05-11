<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Utama -->
    <link rel="stylesheet" href="<?= base_url('asset/css/style.css') ?>">
    <!-- CSS Khusus Dashboard -->
    <link rel="stylesheet" href="<?= base_url('asset/css/dashboard.css') ?>">
    <!-- Chart.js -->
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
                        href="<?= site_url('dashboard/super-admin') ?>">
                            <i class="fas fa-home me-1"></i> Beranda
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid main-content px-0 py-3">
            <div class="container-fluid full-width">

                <!-- Info Cards Row -->
                <div class="row row-equal-height mb-4 justify-content-center">
                    <!-- Card 1 -->
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card info-card h-100 border-0" style="background-color: #E3F2FD;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="text-dark">Total Usulan</h5>
                                        <div class="value text-dark"><?= $totalPengajuan ?></div>
                                    </div>
                                    <div class="p-3 rounded">
                                        <i class="fas fa-file-alt fa-2x" style="color: #0D47A1;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card info-card h-100 border-0" style="background-color: #E8F5E9;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="text-dark">Existing Prakom</h5>
                                        <div class="value text-dark"><?= $totalPrakom ?></div>
                                        <div class="label text-dark">Dari Usulan Terakhir</div>
                                    </div>
                                    <div class="p-3 rounded">
                                        <i class="fas fa-user-tie fa-2x" style="color: #2E7D32;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card info-card h-100 border-0" style="background-color: #E0F7FA;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="text-dark">Existing Statistisi</h5>
                                        <div class="value text-dark"><?= $totalStatistisi ?></div>
                                        <div class="label text-dark">Dari Usulan Terakhir</div>
                                    </div>
                                    <div class="p-3 rounded">
                                        <i class="fas fa-chart-pie fa-2x" style="color: #00838F;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="row row-equal-height mb-4 justify-content-center">
                    <!-- Chart 1 -->
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-orange-pastel border-0 d-flex justify-content-between align-items-center py-3">
                                <span class="fw-semibold text-dark">Rekomendasi Statistisi</span>
                            </div>
                            <div class="card-body pt-0">
                                <div class="chart-container" style="height: 280px;">
                                    <canvas id="statistisiChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2 -->
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-orange-pastel border-0 d-flex justify-content-between align-items-center py-3">
                                <span class="fw-semibold text-dark">Rekomendasi Prakom</span>
                            </div>
                            <div class="card-body pt-0">
                                <div class="chart-container" style="height: 280px;">
                                    <canvas id="prakomChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="row row-equal-height mb-4 justify-content-center">
                    <!-- Chart 3 -->
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-orange-pastel border-0 d-flex justify-content-between align-items-center py-3">
                                <span class="fw-semibold text-dark">Existing Statistisi</span>
                            </div>
                            <div class="card-body pt-0">
                                <div class="chart-container" style="height: 280px;">
                                    <canvas id="existingStatistisiChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 4 -->
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-orange-pastel border-0 d-flex justify-content-between align-items-center py-3">
                                <span class="fw-semibold text-dark">Existing Prakom</span>
                                
                            </div>
                            <div class="card-body pt-0">
                                <div class="chart-container" style="height: 280px;">
                                    <canvas id="existingPrakomChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 d-flex flex-column flex-md-row justify-content-between align-items-center py-3">
                        <h5 class="mb-2 mb-md-0 fw-semibold text-dark text-center text-md-start">Daftar Pengajuan Rekomendasi</h5>
                        <form method="GET" action="<?= site_url('dashboard/super-admin') ?>" class="search-container position-relative">
                            <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
                            <input type="text" name="q" value="<?= esc($keyword ?? '') ?>" 
                                class="form-control ps-5 rounded border-0 shadow-sm text-center text-md-start" 
                                placeholder="Apa yang ingin Anda cari?" style="min-width: 250px;">
                            <button class="btn btn-primary rounded position-absolute end-0 top-0 h-100 d-none d-md-block" type="submit">
                                Cari
                            </button>
                        </form>
                    </div>
                    <div class="card-body px-0 pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="text-center align-middle">
                                        <th>No</th>
                                        <th>Nama Kementerian/Lembaga/Pemda</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>
                                            <div class="d-flex flex-column align-items-center">
                                                <div>Jumlah Usulan</div>
                                                <div class="d-flex justify-content-center border-top mt-1 pt-1 w-100">
                                                    <div class="flex-fill text-center px-2">
                                                        <small class="text-muted"><i class="fas fa-user-tie me-1"></i>Prakom</small>
                                                    </div>
                                                    <div class="flex-fill text-center px-2">
                                                        <small class="text-muted"><i class="fas fa-chart-pie me-1"></i>Statistisi</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="d-flex flex-column align-items-center">
                                                <div>Jumlah Rekomendasi</div>
                                                <div class="d-flex justify-content-center border-top mt-1 pt-1 w-100">
                                                    <div class="flex-fill text-center px-2">
                                                        <small class="text-muted"><i class="fas fa-user-tie me-1"></i>Prakom</small>
                                                    </div>
                                                    <div class="flex-fill text-center px-2">
                                                        <small class="text-muted"><i class="fas fa-chart-pie me-1"></i>Statistisi</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th>Status</th>
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    <?php $no = 1; foreach ($tabelData as $row): ?>
                                        <tr>
                                            <td class="fw-medium align-middle"><?= $no++ ?></td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div>
                                                        <h6 class="mb-0"><?= esc($row['instansi']) ?></h6>
                                                        <small class="text-muted"><?= esc($row['nama_akun']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle"><?= date('d M Y', strtotime($row['tanggal_usulan'])) ?></td>
                                            <td class="align-middle">
                                                <div class="d-flex justify-content-center">
                                                    <div class="flex-fill text-center px-3"><?= $row['jumlah_prakom_usulan'] ?></div>
                                                    <div class="flex-fill text-center px-3"><?= $row['jumlah_statistisi_usulan'] ?></div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex justify-content-center">
                                                    <!-- Menampilkan jumlah rekomendasi -->
                                                    <div class="flex-fill text-center px-3"><?= $row['jumlah_prakom_rekomendasi'] ?></div>
                                                    <div class="flex-fill text-center px-3"><?= $row['jumlah_statistisi_rekomendasi'] ?></div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <?php if ($row['status'] == 'Diterima'): ?>
                                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary py-2 px-3">
                                                        <i class="fas fa-check-circle me-1"></i> Diterima
                                                    </span>
                                                <?php elseif ($row['status'] == 'Diproses'): ?>
                                                    <span class="badge rounded-pill" style="background-color: #fff3cd; color: #856404; padding: 0.5rem 0.75rem;">
                                                        <i class="fas fa-spinner me-1"></i> Diproses
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success py-2 px-3">
                                                        <i class="fas fa-check-double me-1"></i> Selesai
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex justify-content-center">
                                                    <button class="btn btn-sm btn-outline-primary rounded" data-bs-toggle="tooltip" title="Detail"
                                                        onclick="window.location.href='<?= site_url('dashboard/detail/' . $row['id']) ?>'">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                    <?php if (!empty($row['surat_usulan'])): ?>
                                                    <a href="<?= base_url('writable/uploads/' . $row['surat_usulan']) ?>" 
                                                        class="btn btn-sm btn-outline-warning rounded mx-1" 
                                                        data-bs-toggle="tooltip" title="Unduh Surat Usulan" 
                                                        download>
                                                        <i class="fas fa-file-download"></i>
                                                    </a>
                                                    <?php endif; ?>

                                                    <?php if (!empty($row['berkas_usulan'])): ?>
                                                    <a href="<?= base_url('writable/uploads/' . $row['berkas_usulan']) ?>" 
                                                        class="btn btn-sm btn-outline-success rounded" 
                                                        data-bs-toggle="tooltip" title="Unduh Berkas Usulan" 
                                                        download>
                                                        <i class="fas fa-file-archive"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($row['surat_rekomendasi'])): ?>
                                                        <a href="<?= base_url('writable/uploads/' . $row['surat_rekomendasi']) ?>" 
                                                            class="btn btn-sm btn-outline-danger rounded mx-1" 
                                                            data-bs-toggle="tooltip" title="Unduh Surat Rekomendasi" 
                                                            download>
                                                            <i class="fas fa-file-signature"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-secondary rounded mx-1" disabled data-bs-toggle="tooltip"
                                                            title="Surat rekomendasi belum tersedia">
                                                            <i class="fas fa-file-signature"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center px-4 pt-3">
                            <small class="text-muted mb-2 mb-md-0">
                                Menampilkan 
                                <?= ($pager->getCurrentPage('usulan') - 1) * $pager->getPerPage('usulan') + 1 ?>
                                hingga 
                                <?= min($pager->getCurrentPage('usulan') * $pager->getPerPage('usulan'), $pager->getTotal('usulan')) ?>
                                dari <?= $pager->getTotal('usulan') ?> entri
                            </small>

                            <?php
                                $group = 'usulan';
                                $currentPage = $pager->getCurrentPage($group);
                                $pageCount = $pager->getPageCount($group);
                            ?>

                            <?php if ($pageCount > 1): ?>
                            <div class="d-flex justify-content-center mt-3 mt-md-0">
                                <div class="pagination-custom d-flex gap-2 align-items-center flex-wrap">
                                    <!-- Tombol ke Halaman Pertama -->
                                    <a href="<?= $pager->getPageURI(1, $group) ?>"
                                    class="page-btn <?= $currentPage === 1 ? 'active' : '' ?>">
                                        <i class="bi bi-chevron-double-left"></i>
                                    </a>

                                    <!-- Tombol Halaman -->
                                    <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                                        <a href="<?= $pager->getPageURI($i, $group) ?>"
                                        class="page-btn <?= $i === $currentPage ? 'active' : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>

                                    <!-- Tombol ke Halaman Terakhir -->
                                    <a href="<?= $pager->getPageURI($pageCount, $group) ?>"
                                    class="page-btn <?= $currentPage === $pageCount ? 'active' : '' ?>">
                                        <i class="bi bi-chevron-double-right"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

            </div>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bootstrap Tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Data Chart dari PHP
        const rekomendasiStatistisi = <?= json_encode($rekomendasiStatistisi ?? []) ?>;
        const rekomendasiPrakom = <?= json_encode($rekomendasiPrakom ?? []) ?>;
        const existingStatistisi = <?= json_encode($existingStatistisi ?? []) ?>;
        const existingPrakom = <?= json_encode($existingPrakom ?? []) ?>;

        // Mapping untuk kategori ke label yang lebih user-friendly
        const kategoriLabels = {
            'Terampil': 'Terampil',
            'Mahir': 'Mahir',
            'Penyelia': 'Penyelia',
            'Pertama': 'Ahli Pertama',
            'Muda': 'Ahli Muda',
            'Madya': 'Ahli Madya',
            'Utama': 'Ahli Utama',
            '7': 'Terampil',
            '14': 'Terampil',
            '6': 'Mahir',
            '13': 'Mahir',
            '5': 'Penyelia',
            '12': 'Penyelia',
            '4': 'Ahli Pertama',
            '11': 'Ahli Pertama',
            '3': 'Ahli Muda',
            '10': 'Ahli Muda',
            '2': 'Ahli Madya',
            '9': 'Ahli Madya',
            '1': 'Ahli Utama',
            '8': 'Ahli Utama'
        };

        // Fungsi untuk mendapatkan label dari key
        function getLabel(key) {
            return kategoriLabels[key] || key; // Kembalikan key asli jika tidak ada dalam mapping
        }

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false },
                    ticks: { color: '#6c757d' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#6c757d' }
                }
            },
            animation: { duration: 1000, easing: 'easeOutQuart' }
        };

        // Rekomendasi Statistisi
        if (document.getElementById('statistisiChart')) {
            new Chart(document.getElementById('statistisiChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(rekomendasiStatistisi).map(key => getLabel(key)),
                    datasets: [{
                        label: 'Jumlah Rekomendasi',
                        data: Object.values(rekomendasiStatistisi),
                        backgroundColor: '#17a2b8',
                        borderColor: '#117a8b',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: chartOptions
            });
        }

        // Rekomendasi Prakom
        if (document.getElementById('prakomChart')) {
            new Chart(document.getElementById('prakomChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(rekomendasiPrakom).map(key => getLabel(key)),
                    datasets: [{
                        label: 'Jumlah Rekomendasi',
                        data: Object.values(rekomendasiPrakom),
                        backgroundColor: '#28a745',
                        borderColor: '#1e7e34',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: chartOptions
            });
        }

        // Existing Statistisi
        if (document.getElementById('existingStatistisiChart')) {
            new Chart(document.getElementById('existingStatistisiChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(existingStatistisi).map(key => getLabel(key)),
                    datasets: [{
                        label: 'Jumlah Existing',
                        data: Object.values(existingStatistisi),
                        backgroundColor: '#6f42c1',
                        borderColor: '#563d7c',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: chartOptions
            });
        }

        // Existing Prakom
        if (document.getElementById('existingPrakomChart')) {
            new Chart(document.getElementById('existingPrakomChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(existingPrakom).map(key => getLabel(key)),
                    datasets: [{
                        label: 'Jumlah Existing',
                        data: Object.values(existingPrakom),
                        backgroundColor: '#fd7e14',
                        borderColor: '#d9480f',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: chartOptions
            });
        }
    });
    </script>


</body>
</html>