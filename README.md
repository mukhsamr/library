# Library App

Aplikasi perpustakaan berbasis Laravel 9, Inertia, Vue 3, Vite, dan Tailwind CSS. Aplikasi ini mengelola katalog buku, data siswa/karyawan, peminjaman dan pengembalian buku, barcode, print, rekap, serta export PDF/Excel.

## Stack Stable

Proyek ini sengaja dipertahankan pada generasi stack lama yang stabil agar minim breaking change.

| Komponen | Versi yang disarankan |
| --- | --- |
| PHP | 8.2.x |
| Composer | 2.x |
| Node.js | 20 LTS (`.nvmrc`) |
| Laravel | 9.52.x |
| Vue | 3.2.x |
| Vite | 3.x |
| Tailwind CSS | 3.x |
| PHPUnit | 9.6.x |

> Catatan: `composer install` harus dijalankan sebelum `npm run build` karena konfigurasi Vite memakai Ziggy dari folder `vendor/` Composer.

## Instalasi Lokal

1. Install dependency PHP.

    ```bash
    composer install
    ```

2. Install dependency JavaScript.

    ```bash
    npm install
    ```

3. Buat file environment.

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. Sesuaikan koneksi database di `.env`, lalu jalankan migrasi dan seeder.

    ```bash
    php artisan migrate --seed
    ```

5. Buat symbolic link storage agar sampul buku dan foto profil dapat diakses dari `/storage`.

    ```bash
    php artisan storage:link
    ```

6. Jalankan server development Laravel dan Vite.

    ```bash
    php artisan serve
    npm run dev
    ```

## Akun Default Seeder

Seeder membuat tiga akun awal:

| Nama | Password | Level |
| --- | --- | --- |
| Admin | `Admin123` | 3 |
| Operator | `Operator123` | 2 |
| User | `User123` | 1 |

Level 3 memiliki akses ke menu master data seperti siswa, karyawan, kelas, dan unit.

## Build Production

Jalankan Composer terlebih dahulu agar Ziggy tersedia di folder `vendor/`, lalu build asset.

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Testing

Test menggunakan SQLite in-memory sesuai konfigurasi `phpunit.xml`. CI menjalankan Composer install, NPM install, test backend, dan build frontend pada PHP 8.2 + Node 20.

```bash
php artisan test
```

Untuk validasi frontend:

```bash
npm run build
```

## Fitur Utama

- Login/logout user.
- Manajemen buku dan sampul buku.
- Import buku dari Excel.
- Peminjaman dan pengembalian buku untuk siswa/karyawan.
- Riwayat peminjaman dan export Excel.
- Print data buku, siswa, dan karyawan.
- Rekap PDF dan ZIP per kelas/unit.
- Middleware level akses untuk menu admin.

## Catatan Maintenance

- Hindari upgrade major tanpa test karena proyek ini memakai stack Laravel 9 + Inertia adapter lama.
- Pertahankan `@inertiajs/inertia-vue3` sampai ada rencana migrasi khusus ke adapter Inertia baru.
- Pertahankan Tailwind 3 untuk menghindari perubahan konfigurasi Tailwind 4.
- Proyek tidak memakai `tw-elements`; gunakan komponen Vue internal sebelum menambahkan UI dependency baru.
- Riwayat pengembalian peminjaman memakai kolom `returned_at`; soft delete hanya dipakai untuk penghapusan riwayat administratif.
- Jika dependency perlu diperbarui, utamakan patch/minor dalam major version yang sama.
