# 💘 Romantis

**Romantis** merupakan proyek rekomendasi antara *statistisi* dan *pranata komputer (prakom)* dalam bentuk sistem berbasis web yang menggunakan **CodeIgniter 4**. Sistem ini mendukung tiga jenis pengguna utama: **Operator**, **Admin**, dan **Super Admin**.

## 👥 Peran dan Alur Sistem

### 🔹 Operator
- Membuat usulan untuk **statistisi**, **prakom**, dan **existing** berdasarkan instansi.
- Setelah usulan dikirim, **notifikasi email** otomatis dikirim ke seluruh **Admin**.

### 🔸 Admin
- Menerima notifikasi email atas usulan baru dari Operator.
- Melakukan **disposisi** dan **rekomendasi** atas usulan tersebut.
- Setelah dikirim, email notifikasi dikirim ke:
  - **Admin Disposisi**
  - **Operator dari instansi terkait**

### 🔻 Super Admin
- Melihat seluruh usulan dan proses disposisi/rekomendasi dari semua instansi secara menyeluruh.

## ✉️ Notifikasi Email

Pengiriman email dilakukan melalui controller berikut:

- `UsulanController.php`
- `DisposisiController.php`
- `RekomendasiController.php`

Konfigurasi email berada di file `.env`:

```env
EMAIL_FROM_ADDRESS=notifikasi@domainanda.com
EMAIL_FROM_NAME=Sistem Romantis
EMAIL_SMTP_HOST=smtp.gmail.com
EMAIL_SMTP_USER=akun@gmail.com
EMAIL_SMTP_PASS=aplikasi_khusus_password_anda
EMAIL_SMTP_PORT=587
EMAIL_PROTOCOL=smtp
EMAIL_SMTP_CRYPTO=tls
```

## 🔐 Integrasi Firebase

Untuk menghubungkan akun pengguna ke Firebase (menggunakan Firebase Authentication):

1. Buat akun Firebase di [https://firebase.google.com](https://firebase.google.com)
2. Unduh file **Service Account JSON** dari Firebase Project Settings
3. Simpan ke direktori:
   ```
   romantis/app/Config/credential-service-account.json
   ```
4. Tambahkan ke file `.env`:
   ```env
   GOOGLE_APPLICATION_CREDENTIALS=app/Config/credential-service-account.json
   ```

## ⚙️ Prasyarat

- PHP `8.2.12`
- Composer
- XAMPP atau server lokal lainnya (Apache + MySQL)

## 📦 Instalasi

### 1. Clone Repositori
```bash
git clone https://github.com/minacloe/Romantis.git
cd Romantis
```

### 2. Instal Dependency
```bash
composer install
```

### 3. Konfigurasi Environment
Ubah nama file:
```bash
cp .env.example .env
```

Edit `.env` sesuai konfigurasi lokal (email, database, Firebase).

## 🛢️ Database

- Buat database baru bernama:
  ```
  romantis_db
  ```
- Import struktur dan data ke phpMyAdmin secara manual.
- Email dan password pengguna harus **sesuai** dengan yang terdaftar di Firebase Authentication.
