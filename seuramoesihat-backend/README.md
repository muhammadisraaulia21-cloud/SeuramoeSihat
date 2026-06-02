# SeuramoeSihat Backend — Laravel 11 API

Backend REST API untuk aplikasi SeuramoeSihat, platform booking dokter lokal wilayah Aceh.

## Cara Install

### 1. Pindahkan folder backend ke lokasi yang tepat

Folder `seuramoesihat-backend` ini perlu dipindahkan ke luar folder frontend:

```
SeuramoeSihat/
├── seuramoesihat-frontend/   ← Vue.js
└── seuramoesihat-backend/    ← Laravel (pindahkan ke sini)
```

### 2. Install dependencies

```bash
cd seuramoesihat-backend
composer install
```

### 3. Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Buat database MySQL

Buat database bernama `seuramoesihat` di MySQL/Laragon, lalu sesuaikan `.env`:

```env
DB_DATABASE=seuramoesihat
DB_USERNAME=root
DB_PASSWORD=        # kosong jika Laragon default
```

### 5. Migrasi & Seeder

```bash
php artisan migrate --seed
```

Ini akan membuat semua tabel dan mengisi data awal:
- 6 faskes (Puskesmas Sigli, Mila, Kembang Tanjong, Grong-Grong, Klinik Sehat Bersama, RS Umum Sigli)
- 6 dokter dengan jadwal lengkap
- Akun demo: `pasien@demo.com` / `password123`
- Rekam medis & ulasan contoh

### 6. Jalankan server

```bash
php artisan serve
# Server berjalan di http://localhost:8000
```

---

## Struktur Project

```
seuramoesihat-backend/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php        ← Register, Login, Logout, OTP
│   │   ├── DokterController.php      ← List, Detail, Jadwal
│   │   ├── BookingController.php     ← Booking, Antrian, Status
│   │   ├── RekamMedisController.php  ← Rekam medis pasien
│   │   ├── KonsultasiController.php  ← Chat konsultasi
│   │   ├── NotifikasiController.php  ← Notifikasi
│   │   └── ProfilController.php      ← Update profil & password
│   └── Models/
│       ├── User.php
│       ├── ProfilKesehatan.php
│       ├── Faskes.php
│       ├── Dokter.php
│       ├── JadwalDokter.php
│       ├── SlotAntrian.php
│       ├── Antrian.php
│       ├── RekamMedis.php
│       ├── ResepObat.php
│       ├── Konsultasi.php
│       ├── PesanKonsultasi.php
│       ├── Notifikasi.php
│       └── UlasanDokter.php
├── database/
│   ├── migrations/                   ← 10 migration files
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── FaksesSeeder.php
│       ├── DokterSeeder.php
│       ├── UserSeeder.php
│       ├── RekamMedisSeeder.php
│       └── UlasanSeeder.php
├── routes/
│   └── api.php                       ← Semua API routes
└── config/
    ├── cors.php                      ← CORS untuk Vue.js
    └── sanctum.php                   ← Token auth config
```

---

## API Endpoints

Base URL: `http://localhost:8000/api`

### Auth (Publik)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/register` | Daftar akun baru |
| POST | `/login` | Login → dapat Bearer token |
| POST | `/forgot-password` | Kirim OTP ke email |
| POST | `/verify-otp` | Verifikasi OTP |
| POST | `/reset-password` | Reset password baru |

### Auth (Protected — butuh `Authorization: Bearer <token>`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/logout` | Logout |
| GET | `/me` | Data user + profil kesehatan |

### Dokter (Publik)
| Method | Endpoint | Query Params | Deskripsi |
|--------|----------|-------------|-----------|
| GET | `/dokter` | `search`, `kategori`, `wilayah`, `sort`, `tersedia` | List dokter |
| GET | `/dokter/{id}` | — | Detail + ulasan |
| GET | `/dokter/{id}/jadwal` | — | Slot 7 hari ke depan |

### Booking & Antrian (Protected)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/booking` | Buat booking baru |
| GET | `/antrian` | Antrian aktif hari ini |
| GET | `/antrian/riwayat` | Riwayat antrian |
| GET | `/antrian/{id}` | Detail antrian |
| DELETE | `/antrian/{id}` | Batalkan antrian |
| GET | `/antrian/{id}/status` | Status live (nomor dipanggil, sisa) |

### Rekam Medis (Protected)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/rekam-medis` | List + statistik |
| GET | `/rekam-medis/{id}` | Detail |

### Konsultasi Chat (Protected)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/konsultasi` | List sesi + daftar dokter |
| POST | `/konsultasi` | Mulai sesi baru |
| GET | `/konsultasi/{id}/pesan` | List pesan |
| POST | `/konsultasi/{id}/pesan` | Kirim pesan (auto-reply aktif) |

### Notifikasi (Protected)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/notifikasi` | List (grouped by tanggal) |
| PATCH | `/notifikasi/{id}/baca` | Tandai dibaca |
| PATCH | `/notifikasi/baca-semua` | Tandai semua dibaca |

### Profil (Protected)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| PUT | `/profil` | Update data diri |
| PUT | `/profil/kesehatan` | Update data kesehatan |
| PUT | `/profil/password` | Ubah password |

---

## Akun Demo

| Email | Password | Role |
|-------|----------|------|
| `pasien@demo.com` | `password123` | Pasien |
| `admin@seuramoesihat.id` | `admin123456` | Admin |
