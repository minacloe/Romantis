<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-value" content="<?= csrf_hash() ?>">
    <title>Login Sederhana</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('asset/css/login.css') ?>">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo-container">
                <div class="logo">
                    <img src="<?= base_url('asset/img/logo-remv.png')?>" alt="Logo">
                </div>
                <h1 class="login-title">Login ke Akun Anda</h1>
                <p class="login-subtitle">Masukkan email dan password Anda untuk mengakses akun</p>
            </div>
            <form id="loginForm" action="<?= base_url('login/process') ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda" required autocomplete="email">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required autocomplete="current-password">
                        <span class="input-group-text toggle-password" id="togglePassword">
                            <i class="far fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="d-flex  mb-3">
                    <a href="#" class="forgot-password ms-auto" id="forgotPasswordLink">Lupa password?</a>
                </div>
                
                <button type="submit" class="btn btn-login w-100" style="background-color: var(--primary-color); border-color: var(--primary-color);">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Login</span>
                </button>
            </form>
        </div>
    </div>


    <!-- Bootstrap 5.3 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 untuk notifikasi -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function getCookie(name) {
        let cookieValue = null;
        if (document.cookie && document.cookie !== '') {
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i].trim();
                if (cookie.substring(0, name.length + 1) === (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }

    document.getElementById('forgotPasswordLink').addEventListener('click', function (e) {
        e.preventDefault();
        const emailInput = document.getElementById('email');
        const email = emailInput.value.trim();

        if (email === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Email Kosong',
                text: 'Silakan isi email terlebih dahulu sebelum reset password.'
            }).then(() => {
                location.reload();
            });
            return;
        }

        const csrfTokenName = document.querySelector('meta[name="csrf-name"]').getAttribute('content');
        const csrfTokenValue = document.querySelector('meta[name="csrf-value"]').getAttribute('content');

        const formData = new URLSearchParams();
        formData.append('email', email);
        formData.append(csrfTokenName, csrfTokenValue);

        fetch('<?= base_url('forgot-password') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'Berhasil' : 'Gagal',
                text: data.message
            });
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message
            }).then(() => {
                location.reload();
            });
        });
    });

    // Toggle password visibility
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

    // Handle form submission
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = this.querySelector('button[type="submit"]');
        const spinner = btn.querySelector('.spinner-border');
        const btnText = btn.querySelector('.btn-text');

        // Show loading state
        spinner.classList.remove('d-none');
        btnText.textContent = 'Memproses...';
        btn.disabled = true;

        fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCookie('csrf_cookie_name')
            },
            body: new URLSearchParams(new FormData(this))
        })
        .then(response => {
            if (response.status === 404) {
                throw new Error('Endpoint tidak ditemukan (404)');
            }
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || `Error: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil',
                    text: data.message || 'Anda akan dialihkan',
                    timer: 1500,
                    showConfirmButton: false,
                    willClose: () => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    }
                });
            } else {
                throw new Error(data.message || 'Login gagal');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: error.message || 'Terjadi kesalahan saat memproses login'
            }).then(() => {
                location.reload();
            }); 
        })
        .finally(() => {
            // Reset tombol
            spinner.classList.add('d-none');
            btnText.textContent = 'Login';
            btn.disabled = false;
        });
    });

    // Cek jika ada error dari session
    <?php if (session()->getFlashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?= session()->getFlashdata('error') ?>'
    }).then(() => {
        location.reload();
    });
    <?php endif; ?>
</script>

</body>
</html>