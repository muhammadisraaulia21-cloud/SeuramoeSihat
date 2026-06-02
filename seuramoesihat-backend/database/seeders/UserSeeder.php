<?php

namespace Database\Seeders;

use App\Models\Notifikasi;
use App\Models\ProfilKesehatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun demo pasien (sesuai Login.vue)
        $pasien = User::firstOrCreate(
            ['email' => 'pasien@demo.com'],
            [
                'nama'          => 'Muhammad Isra Aulia',
                'nik'           => '1111000000000001',
                'no_hp'         => '081200000001',
                'alamat'        => 'Jl. Banda Aceh, Sigli, Aceh',
                'tanggal_lahir' => '2000-01-01',
                'password'      => Hash::make('password123'),
                'role'          => 'pasien',
            ]
        );

        ProfilKesehatan::firstOrCreate(
            ['user_id' => $pasien->id],
            [
                'golongan_darah'   => 'B',
                'berat_badan'      => 65,
                'tinggi_badan'     => 170,
                'alergi'           => 'Penisilin',
                'riwayat_penyakit' => 'Hipertensi Grade 1',
            ]
        );

        // Notifikasi selamat datang
        if (Notifikasi::where('user_id', $pasien->id)->doesntExist()) {
            Notifikasi::create([
                'user_id'  => $pasien->id,
                'kategori' => 'sistem',
                'judul'    => 'Selamat datang di SeuramoeSihat!',
                'pesan'    => 'Akun Anda berhasil dibuat. Mulai booking antrian dokter terdekat sekarang.',
                'icon'     => '🏥',
                'bg_class' => 'bg-purple-50',
                'aksi'     => 'Cari Dokter',
                'aksi_url' => '/cari-dokter',
                'dibaca'   => true,
            ]);
        }

        // Akun admin
        User::firstOrCreate(
            ['email' => 'admin@seuramoesihat.id'],
            [
                'nama'     => 'Admin SeuramoeSihat',
                'password' => Hash::make('admin123456'),
                'role'     => 'admin',
            ]
        );
    }
}
