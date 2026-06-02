<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\UlasanDokter;
use App\Models\User;
use Illuminate\Database\Seeder;

class UlasanSeeder extends Seeder
{
    public function run(): void
    {
        $pasien = User::where('email', 'pasien@demo.com')->first();
        $dokterRahmat = Dokter::where('nama', 'dr. Rahmat Hidayat')->first();
        $dokterSiti   = Dokter::where('nama', 'dr. Siti Aisyah, Sp.A')->first();

        if (! $pasien || ! $dokterRahmat) return;

        $ulasanData = [
            [
                'dokter_id' => $dokterRahmat->id,
                'nama'      => 'Ahmad S.',
                'bintang'   => 5,
                'komentar'  => 'Dokternya sangat ramah dan penjelasannya mudah dipahami. Antrian juga cepat karena pakai SeuramoeSihat.',
            ],
            [
                'dokter_id' => $dokterRahmat->id,
                'nama'      => 'Sari W.',
                'bintang'   => 5,
                'komentar'  => 'Sudah 3x berobat ke sini, selalu puas. Rekomendasikan banget!',
            ],
            [
                'dokter_id' => $dokterRahmat->id,
                'nama'      => 'Budi R.',
                'bintang'   => 4,
                'komentar'  => 'Pelayanan bagus, hanya waktu tunggu sedikit lama tapi wajar.',
            ],
        ];

        if ($dokterSiti) {
            $ulasanData[] = [
                'dokter_id' => $dokterSiti->id,
                'nama'      => 'Rina M.',
                'bintang'   => 5,
                'komentar'  => 'Anak saya selalu tenang diperiksa dr. Siti. Sangat sabar dan profesional.',
            ];
            $ulasanData[] = [
                'dokter_id' => $dokterSiti->id,
                'nama'      => 'Doni A.',
                'bintang'   => 5,
                'komentar'  => 'Penjelasan tentang tumbuh kembang anak sangat detail. Terima kasih dok!',
            ];
        }

        // Hapus unique constraint — insert langsung tanpa firstOrCreate
        // karena beberapa ulasan dari user yang sama untuk dokter yang sama
        foreach ($ulasanData as $u) {
            $exists = UlasanDokter::where('dokter_id', $u['dokter_id'])
                ->where('user_id', $pasien->id)
                ->where('komentar', $u['komentar'])
                ->exists();

            if (! $exists) {
                UlasanDokter::create([
                    'dokter_id'  => $u['dokter_id'],
                    'user_id'    => $pasien->id,
                    'antrian_id' => null,
                    'bintang'    => $u['bintang'],
                    'komentar'   => $u['komentar'],
                ]);
            }
        }
    }
}
