<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="X-CSRF-TOKEN" content="<?= csrf_hash() ?>">
    <title>Manajemen Akun | Rekomendasi Prakom & Statistisi</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Utama -->
    <link rel="stylesheet" href="<?= base_url('asset/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/css/manajemen.css') ?>">

</head>
<body>
    <!-- Navbar -->
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


    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid px-3 mt-4">   
            <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <h5 class="mb-0 fw-semibold text-dark">Pengguna</h5>
                    <div class="ms-auto d-flex flex-sm-row">
                        <div class="d-flex flex-column flex-sm-row gap-3 mt-3 mt-md-0">
                            <!-- Dropdown Cari (Mobile) -->
                            <div class="dropdown d-md-none">
                                <button class="btn btn-primary btn-sm py-2 px-3 rounded-lg shadow-lg hover:bg-indigo-500 transition duration-300" type="button" id="dropdownCari" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-search me-2"></i> Cari
                                </button>
                                <div class="dropdown-menu p-3 dropdown-menu-end" style="min-width: 300px;">
                                    <form method="get" action="<?= base_url('manajemen') ?>">
                                        <div class="input-group input-group-sm rounded-lg shadow-sm">
                                            <input type="text" class="form-control py-2 px-3 rounded-lg border-2 border-gray-300 focus:ring-2 focus:ring-indigo-500" name="q" placeholder="Cari nama atau email..." value="<?= esc($keyword ?? '') ?>">
                                            <button class="btn btn-primary py-2 px-3 rounded-lg hover:bg-indigo-500 hover:text-white transition duration-300" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Form Cari (Desktop) -->
                            <div class="d-none d-md-flex flex-sm-row gap-3">
                                <form method="get" action="<?= base_url('manajemen') ?>" class="flex w-full max-w-xs">
                                    <div class="input-group input-group-sm rounded-lg shadow-sm">
                                        <input type="text" class="form-control py-2 px-3 rounded-lg border-2 border-gray-300 focus:ring-2 focus:ring-indigo-500" name="q" placeholder="Apa yang Anda cari?" value="<?= esc($keyword ?? '') ?>">
                                        <button class="btn btn-primary btn-sm py-2 px-3 rounded-lg hover:bg-indigo-500 hover:text-white transition duration-300" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tombol Tambah Akun -->
                        <button class="w-auto btn btn-primary btn-sm py-2 px-3 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300 ms-auto" data-bs-toggle="modal" data-bs-target="#tambahAkunModal">
                            <i class="fas fa-plus me-1"></i> Tambah
                        </button>

                        <!-- Tombol Cetak -->
                        <div class="dropdown">
                            <button type="button" class="btn btn-success btn-sm py-2 px-3 rounded-lg shadow-lg hover:bg-green-600 transition duration-300" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i> Cetak
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="<?= base_url('manajemen/cetak/pdf') ?>"><i class="fas fa-file-pdf text-danger me-2"></i> PDF</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('manajemen/cetak/excel') ?>"><i class="fas fa-file-excel text-success me-2"></i> Excel</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('manajemen/cetak/csv') ?>"><i class="fas fa-file-csv text-info me-2"></i> CSV</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

                <div class="card-body px-0 pt-0">
                    <div class="table-responsive">
                        <table id="accountsTable" class="table table-hover align-middle mb-0" style="width:100%">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Instansi</th>
                                    <th>Tipe Akun</th>
                                    <th class="text-center pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($akun)) : ?>
                                    <?php foreach ($akun as $row) : ?>
                                    <tr class="border-top text-center">
                                        <td>
                                            <div class="align-items-center">
                                                <div>
                                                    <h6 class="mb-0 fw-semibold"><?= esc($row['nama']) ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted"><?= esc($row['email']) ?></td>
                                        <td class="text-muted"><?= esc($row['nama_instansi'] ?? '-') ?></td>
                                        <td>
                                            <?php 
                                            $badgeClass = [
                                                'Super Admin' => 'bg-primary bg-opacity-10 text-primary',
                                                'Admin' => 'bg-success bg-opacity-10 text-success',
                                                'Operator' => 'bg-info bg-opacity-10 text-info'
                                            ];
                                            ?>
                                            <span class="badge <?= $badgeClass[$row['tipe_akun']] ?? 'bg-secondary' ?> py-2 px-3 rounded-pill">
                                                <?= esc($row['tipe_akun']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="#" class="btn btn-sm action-btn btn-detail rounded" title="Detail" data-bs-toggle="tooltip" data-id="<?= $row['id'] ?>">
                                                    <i class="fas fa-search"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm action-btn btn-edit rounded" title="Edit" data-bs-toggle="tooltip" data-id="<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm action-btn btn-delete rounded" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $row['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Tidak ada data akun</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center px-4">
                        <small class="text-muted mb-2 mb-md-0">
                            Menampilkan <?= count($akun) ?> dari <?= $pager->getTotal('akun') ?> entri
                        </small>
                        <?php
                            $group = 'akun';
                            $currentPage = $pager->getCurrentPage($group);
                            $pageCount = $pager->getPageCount($group);
                            
                        ?>

                        <?php if ($pageCount > 1): ?>
                        <div class="d-flex justify-content-center mt-3">
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
    </main>
    
    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">
                © 2023 Rekomendasi Prakom dan Statistisi. Hak Cipta Dilindungi.
            </span>
        </div>
    </footer>

    <!-- Modal Tambah Akun -->
    <div class="modal fade" id="tambahAkunModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Akun Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="tambahAkunForm" action="<?= base_url('manajemen/tambah') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Nama Lengkap -->
                            <div class="col-md-6">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                placeholder="Masukkan nama lengkap" required>
                            </div>
                            <!-- NIP -->
                            <div class="col-md-6">
                                <label for="nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="nip" name="nip" required
                                placeholder="Masukkan NIP (18 digit)">
                            </div>
                            <!-- Nomor HP -->
                            <div class="col-md-6">
                                <label for="nomor_hp" class="form-label">Telepon</label>
                                <input type="text" class="form-control" id="nomor_hp" name="nomor_hp" required
                                placeholder="Contoh: 081234567890">
                            </div>
                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                placeholder="Contoh: user@example.com">
                            </div>
                            <!-- Instansi -->
                            <div class="col-md-6">
                                <label for="instansi" class="form-label">Instansi</label>
                                <input list="instansiList" class="form-control" id="instansi" name="instansi" required
                                placeholder="Masukkan nama instansi">
                            </div>
                            <!-- Tipe Akun -->
                            <div class="col-md-6">
                                <label for="tipe_akun" class="form-label">Tipe Akun</label>
                                <select class="form-select" id="tipe_akun" name="tipe_akun" required>
                                    <option value="">Pilih Tipe Akun</option>
                                    <option value="Super Admin">Super Admin</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Operator">Operator</option>
                                </select>
                            </div>
                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitTambahBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="tambahLoadingSpinner"></span>
                            <span id="submitText">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>               
        
    <!-- Modal Edit Akun -->
    <div class="modal fade" id="editAkunModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAkunForm" action="<?= base_url('manajemen/update/') ?>" method="post">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    <input type="hidden" name="_method" value="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Nama Lengkap -->
                            <div class="col-md-6">
                                <label for="edit_nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="edit_nama" name="nama" required>
                            </div>
                            <!-- NIP -->
                            <div class="col-md-6">
                                <label for="edit_nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="edit_nip" name="nip" required>
                            </div>
                            <!-- Nomor HP -->
                            <div class="col-md-6">
                                <label for="edit_nomor_hp" class="form-label">Nomor HP</label>
                                <input type="text" class="form-control" id="edit_nomor_hp" name="nomor_hp" required>
                            </div>
                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <!-- Instansi -->
                            <div class="col-md-6">
                                <label for="edit_instansi" class="form-label">Instansi</label>
                                <input type="text" class="form-control" id="edit_instansi" name="instansi" required>
                            </div>
                            <!-- Tipe Akun -->
                            <div class="col-md-6">
                                <label for="edit_tipe_akun" class="form-label">Tipe Akun</label>
                                <select class="form-select" id="edit_tipe_akun" name="tipe_akun" required>
                                    <option value="Super Admin">Super Admin</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Operator">Operator</option>
                                </select>
                            </div>
                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="edit_password" class="form-label">Password</label>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" id="edit_password" name="password" value="">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <!-- Tombol Simpan Perubahan -->
                        <button type="submit" class="btn btn-primary" id="submitEditBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="editLoadingSpinner"></span>
                            <span id="editSubmitText">Simpan Perubahan</span>
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Lihat Detail Akun -->
    <div class="modal fade" id="lihatDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Nama Lengkap -->
                        <div class="col-md-6">
                            <label for="detail_nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="detail_nama" disabled>
                        </div>
                        <!-- NIP -->
                        <div class="col-md-6">
                            <label for="detail_nip" class="form-label">NIP</label>
                            <input type="text" class="form-control" id="detail_nip" disabled>
                        </div>
                        <!-- Nomor HP -->
                        <div class="col-md-6">
                            <label for="detail_nomor_hp" class="form-label">Nomor HP</label>
                            <input type="text" class="form-control" id="detail_nomor_hp" disabled>
                        </div>
                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="detail_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="detail_email" disabled>
                        </div>
                        <!-- Instansi -->
                        <div class="col-md-6">
                            <label for="detail_instansi" class="form-label">Instansi</label>
                            <input type="text" class="form-control" id="detail_instansi" disabled>
                        </div>
                        <!-- Tipe Akun -->
                        <div class="col-md-6">
                            <label for="detail_tipe_akun" class="form-label">Tipe Akun</label>
                            <input type="text" class="form-control" id="detail_tipe_akun" disabled>
                        </div>
                        <!-- Password Temp -->
                        <div class="col-md-6">
                            <label for="detail_password_temp" class="form-label">Password</label>
                            <input type="text" class="form-control" id="detail_password_temp" disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to handle auto-refresh after error
            function handleError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonText: 'OK',
                    willClose: () => {
                        window.location.reload();
                    }
                });
            }

            // Function to handle success with optional redirect
            function handleSuccess(message, redirectUrl = null) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    confirmButtonText: 'OK',
                    willClose: () => {
                        if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else {
                            window.location.reload();
                        }
                    }
                });
            }

            // Handle detail button clicks
            document.querySelectorAll('.btn-detail').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    
                    fetch(`<?= base_url('manajemen/detail/') ?>${id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.data) {
                            // Populate modal with data
                            document.getElementById('detail_nama').value = data.data.nama || '';
                            document.getElementById('detail_nip').value = data.data.nip || '';
                            document.getElementById('detail_email').value = data.data.email || '';
                            document.getElementById('detail_instansi').value = data.data.nama_instansi || '';
                            document.getElementById('detail_tipe_akun').value = data.data.tipe_akun || '';
                            document.getElementById('detail_nomor_hp').value = data.data.nomor_hp || '';
                            document.getElementById('detail_password_temp').value = data.data.password_temp || '';

                            const detailModal = new bootstrap.Modal(document.getElementById('lihatDetailModal'));
                            detailModal.show();
                        } else {
                            handleError(data.message || 'Gagal memuat detail akun');
                        }
                    })
                    .catch(error => {
                        handleError('Terjadi kesalahan saat memuat detail akun');
                    });
                });
            });

            // Toggle password visibility for add form
            document.getElementById('togglePassword').addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });

            // Toggle password visibility for edit forms
            document.querySelectorAll('.toggle-password').forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                });
            });

            // Handle edit button click
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    
                    fetch(`<?= base_url('manajemen/edit/') ?>${id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.data) {
                            document.getElementById('edit_id').value = data.data.id;
                            document.getElementById('edit_nama').value = data.data.nama || '';
                            document.getElementById('edit_nip').value = data.data.nip || '';
                            document.getElementById('edit_email').value = data.data.email || '';
                            document.getElementById('edit_instansi').value = data.data.instansi || '';
                            document.getElementById('edit_tipe_akun').value = data.data.tipe_akun || 'Operator';
                            document.getElementById('edit_password').value = data.data.password_temp || '';
                            
                            // Make sure this matches the field name from your database
                            document.getElementById('edit_nomor_hp').value = data.data.nomor_hp || data.data.no_hp || data.data.phone || '';
                            // Added fallbacks in case the field is named differently in the response

                            const editModal = new bootstrap.Modal(document.getElementById('editAkunModal'));
                            editModal.show();
                        } else {
                            handleError(data.message || 'Gagal memuat data akun');
                        }
                    })
                    .catch(error => {
                        handleError('Terjadi kesalahan saat memuat data akun');
                    });
                });
            });

            // Form submission for edit
            document.getElementById('editAkunForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const submitBtn = document.getElementById('submitEditBtn');
                const spinner = submitBtn.querySelector('#editLoadingSpinner');
                const submitText = submitBtn.querySelector('#editSubmitText');
                const id = document.getElementById('edit_id').value;

                spinner.classList.remove('d-none');
                submitText.textContent = 'Menyimpan...';
                submitBtn.disabled = true;

                const formData = new URLSearchParams(new FormData(form));

                fetch(`<?= base_url('manajemen/update/') ?>${id}`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        handleSuccess(data.message, '<?= site_url('manajemen') ?>');
                    } else {
                        handleError(data.message);
                    }
                })
                .catch(error => {
                    handleError('Terjadi kesalahan jaringan');
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                    submitText.textContent = 'Simpan Perubahan';
                    submitBtn.disabled = false;
                });
            });

            // Form submission for add
            document.getElementById('tambahAkunForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const submitBtn = document.getElementById('submitTambahBtn');
                const spinner = submitBtn.querySelector('#tambahLoadingSpinner');
                const submitText = submitBtn.querySelector('#submitText');

                spinner.classList.remove('d-none');
                submitText.textContent = 'Menyimpan...';
                submitBtn.disabled = true;

                const formData = new URLSearchParams(new FormData(form));

                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        handleSuccess(data.message, '<?= site_url('manajemen') ?>');
                        bootstrap.Modal.getInstance(document.getElementById('tambahAkunModal')).hide();
                    } else {
                        handleError(data.message);
                    }
                })
                .catch(error => {
                    handleError('Terjadi kesalahan saat menyimpan data');
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                    submitText.textContent = 'Simpan';
                    submitBtn.disabled = false;
                });
            });

            // Delete functionality
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const csrfToken = document.querySelector('meta[name="X-CSRF-TOKEN"]').content;

                    Swal.fire({
                        title: 'Yakin ingin menghapus akun ini?',
                        text: 'Tindakan ini tidak dapat dibatalkan!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`<?= base_url('manajemen/delete/') ?>${id}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: `<?= csrf_token() ?>=<?= csrf_hash() ?>`
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    handleSuccess(data.message);
                                } else {
                                    handleError(data.message);
                                }
                            })
                            .catch(error => {
                                handleError('Terjadi kesalahan saat menghapus data');
                            });
                        }
                    });
                });
            });

            // Handle session flash messages
            <?php if (session()->getFlashdata('success')): ?>
                handleSuccess('<?= session('success') ?>');
            <?php elseif (session()->getFlashdata('error')): ?>
                handleError('<?= esc(session('error')) ?>');
            <?php endif; ?>
        });
    </script>


</body>
</html>