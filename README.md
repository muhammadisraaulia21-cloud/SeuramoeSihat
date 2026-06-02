# SeuramoeSihat

## Deskripsi Proyek

**SeuramoeSihat** merupakan platform booking dokter lokal yang dirancang untuk memudahkan masyarakat Aceh dalam mengakses layanan kesehatan secara digital. Melalui aplikasi ini, pengguna dapat mencari dokter, melihat jadwal praktik, melakukan reservasi konsultasi, serta mengelola riwayat pemesanan dengan lebih mudah dan efisien.

Proyek ini dikembangkan sebagai bagian dari tugas mata kuliah **Pemrograman Berbasis Web (PBW)** dengan menerapkan arsitektur modern menggunakan **Laravel** sebagai backend dan **Vue.js** sebagai frontend.

---

## Fitur Utama

* Registrasi dan autentikasi pengguna
* Pencarian dokter berdasarkan spesialisasi
* Informasi profil dokter
* Sistem booking atau reservasi konsultasi
* Manajemen jadwal konsultasi
* Riwayat pemesanan pengguna
* Dashboard administrasi
* REST API untuk integrasi frontend dan backend

---

## Teknologi yang Digunakan

### Backend

* Laravel 11
* PHP 8.2+
* MySQL
* Laravel Sanctum

### Frontend

* Vue.js 3
* Vite
* Axios
* Vue Router

### Tools

* Composer
* Node.js
* NPM
* Git

---

## Struktur Proyek

```text
SeuramoeSihat/
│
├── seuramoesihat-backend/
│   ├── app/
│   ├── database/
│   ├── routes/
│   └── ...
│
├── seuramoesihat-frontend/
│   ├── src/
│   ├── public/
│   └── ...
│
└── README.md
```

---

## Instalasi dan Menjalankan Proyek

### Backend

Masuk ke direktori backend:

```bash
cd seuramoesihat-backend
```

Install dependency:

```bash
composer install
```

Salin file environment:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Konfigurasikan database pada file `.env`, kemudian jalankan:

```bash
php artisan migrate --seed
```

Jalankan server Laravel:

```bash
php artisan serve
```

Backend akan berjalan pada:

```text
http://localhost:8000
```

---

### Frontend

Masuk ke direktori frontend:

```bash
cd seuramoesihat-frontend
```

Install dependency:

```bash
npm install
```

Jalankan aplikasi:

```bash
npm run dev
```

Frontend akan berjalan pada:

```text
http://localhost:5173
```

---

## Tim Pengembang

| Nama                | NPM           |
| ------------------- | ------------- |
| Muhammad Isra Aulia | 2408107010006 |
| Andre Alfaridzi     | 2408107010011 |
| Muhammad Riyadh     | 2408107010015 |
| Urfan               | 2408107010038 |

---

## Tujuan Proyek

SeuramoeSihat dikembangkan untuk mendukung transformasi layanan kesehatan digital di Aceh dengan menyediakan sistem reservasi dokter yang mudah, cepat, dan dapat diakses oleh masyarakat kapan saja dan di mana saja.

---

## Lisensi

Proyek ini dibuat untuk keperluan akademik dan pembelajaran pada mata kuliah Pemrograman Berbasis Web.
