<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\RekamMedis;
use App\Models\ResepObat;
use App\Models\User;
use Illuminate\Database\Seeder;

class RekamMedisSeeder extends Seeder
{
    public function run(): void
    {
        $dokterRahmat = Dokter::where('nama', 'dr. Rahmat Hidayat')->first();
        $dokterSiti   = Dokter::where('nama', 'dr. Siti Aisyah, Sp.A')->first();
        $dokterHarun  = Dokter::where('nama', 'dr. Harun Nasution')->first();
        $dokterAhmad  = Dokter::where('nama', 'dr. Ahmad Marzuki, Sp.PD')->first();

        if (! $dokterRahmat || ! $dokterSiti) return;

        // Template rekam medis yang akan dibuat untuk setiap pasien
        $templateRekamMedis = [
            [
                'dokter'            => $dokterRahmat,
                'tanggal_kunjungan' => '2026-05-10',
                'keluhan'           => 'Demam 2 hari, batuk kering, sakit kepala',
                'diagnosa'          => 'Infeksi Saluran Pernapasan Atas (ISPA) ringan',
                'catatan_dokter'    => 'Istirahat cukup, minum air putih minimal 2 liter/hari. Kontrol jika tidak membaik dalam 3 hari.',
                'resep'             => [
                    ['nama_obat' => 'Paracetamol', 'dosis' => '500mg', 'aturan_pakai' => '3x1'],
                    ['nama_obat' => 'Ambroxol',    'dosis' => '30mg',  'aturan_pakai' => '3x1'],
                    ['nama_obat' => 'Vitamin C',   'dosis' => '500mg', 'aturan_pakai' => '1x1'],
                ],
            ],
            [
                'dokter'            => $dokterSiti,
                'tanggal_kunjungan' => '2026-04-22',
                'keluhan'           => 'Diare 1 hari, mual, lemas',
                'diagnosa'          => 'Diare akut dehidrasi ringan',
                'catatan_dokter'    => 'Hindari makanan berminyak dan pedas. Perbanyak minum air dan oralit.',
                'resep'             => [
                    ['nama_obat' => 'Oralit',                  'dosis' => '',     'aturan_pakai' => '3x1 sachet'],
                    ['nama_obat' => 'Zinc',                    'dosis' => '20mg', 'aturan_pakai' => '1x1 selama 10 hari'],
                    ['nama_obat' => 'Probiotik Lactobacillus', 'dosis' => '',     'aturan_pakai' => '2x1'],
                ],
            ],
            [
                'dokter'            => $dokterRahmat,
                'tanggal_kunjungan' => '2026-03-01',
                'keluhan'           => 'Sakit kepala berulang, tekanan darah tinggi',
                'diagnosa'          => 'Hipertensi Grade 1',
                'catatan_dokter'    => 'Kurangi konsumsi garam dan makanan berlemak. Olahraga rutin 30 menit/hari. Kontrol rutin 1 bulan sekali.',
                'resep'             => [
                    ['nama_obat' => 'Amlodipine', 'dosis' => '5mg',  'aturan_pakai' => '1x1'],
                    ['nama_obat' => 'Aspirin',    'dosis' => '80mg', 'aturan_pakai' => '1x1'],
                ],
            ],
        ];

        // Buat rekam medis HANYA untuk akun demo pasien
        $pasien = User::where('email', 'pasien@demo.com')->first();
        if (! $pasien) return;

        foreach ($templateRekamMedis as $data) {
            if (! $data['dokter']) continue;

            $rm = RekamMedis::firstOrCreate(
                [
                    'user_id'           => $pasien->id,
                    'dokter_id'         => $data['dokter']->id,
                    'tanggal_kunjungan' => $data['tanggal_kunjungan'],
                ],
                [
                    'keluhan'        => $data['keluhan'],
                    'diagnosa'       => $data['diagnosa'],
                    'catatan_dokter' => $data['catatan_dokter'],
                    'status'         => 'selesai',
                ]
            );

            if ($rm->wasRecentlyCreated) {
                foreach ($data['resep'] as $r) {
                    ResepObat::create([
                        'rekam_medis_id' => $rm->id,
                        'nama_obat'      => $r['nama_obat'],
                        'dosis'          => $r['dosis'],
                        'aturan_pakai'   => $r['aturan_pakai'],
                    ]);
                }
            }
        }
    }
}
