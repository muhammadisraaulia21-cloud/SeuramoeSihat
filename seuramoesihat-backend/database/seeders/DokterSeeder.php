<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\Faskes;
use App\Models\JadwalDokter;
use Illuminate\Database\Seeder;

class DokterSeeder extends Seeder
{
    public function run(): void
    {
        $dokterData = [
            [
                'nama'          => 'dr. Rahmat Hidayat',
                'inisial'       => 'RH',
                'spesialis'     => 'Dokter Umum',
                'kategori'      => 'Dokter Umum',
                'faskes'        => 'Puskesmas Sigli',
                'pengalaman'    => '8 tahun',
                'jumlah_pasien' => '1.200+',
                'rating'        => 4.9,
                'total_ulasan'  => 3,
                'avatar_bg'     => '#E1F5EE',
                'avatar_color'  => '#0F6E56',
                'tentang'       => 'dr. Rahmat Hidayat adalah dokter umum berpengalaman yang telah melayani masyarakat Sigli selama lebih dari 8 tahun. Beliau dikenal ramah dan teliti dalam setiap pemeriksaan.',
                'keahlian'      => ['Pemeriksaan Umum', 'Demam & Flu', 'Hipertensi', 'Diabetes', 'Kesehatan Anak', 'Luka & Cedera'],
                'jadwal'        => [
                    ['hari' => 1, 'mulai' => '08:00', 'selesai' => '12:00'], // Senin
                    ['hari' => 2, 'mulai' => '08:00', 'selesai' => '12:00'], // Selasa
                    ['hari' => 3, 'mulai' => '08:00', 'selesai' => '12:00'], // Rabu
                    ['hari' => 4, 'mulai' => '08:00', 'selesai' => '12:00'], // Kamis
                    ['hari' => 5, 'mulai' => '08:00', 'selesai' => '12:00'], // Jumat
                    ['hari' => 6, 'mulai' => '08:00', 'selesai' => '10:00'], // Sabtu
                ],
            ],
            [
                'nama'          => 'dr. Siti Aisyah, Sp.A',
                'inisial'       => 'SA',
                'spesialis'     => 'Spesialis Anak',
                'kategori'      => 'Spesialis Anak',
                'faskes'        => 'Klinik Sehat Bersama',
                'pengalaman'    => '6 tahun',
                'jumlah_pasien' => '980+',
                'rating'        => 4.8,
                'total_ulasan'  => 2,
                'avatar_bg'     => '#E6F1FB',
                'avatar_color'  => '#185FA5',
                'tentang'       => 'dr. Siti Aisyah adalah spesialis anak yang berdedikasi tinggi. Beliau memiliki pendekatan yang lembut dan sabar dalam menangani pasien anak-anak.',
                'keahlian'      => ['Tumbuh Kembang', 'Imunisasi', 'Gizi Anak', 'Demam Anak', 'ISPA', 'Alergi'],
                'jadwal'        => [
                    ['hari' => 1, 'mulai' => '09:00', 'selesai' => '14:00'],
                    ['hari' => 2, 'mulai' => '09:00', 'selesai' => '14:00'],
                    ['hari' => 3, 'mulai' => '09:00', 'selesai' => '14:00'],
                    ['hari' => 4, 'mulai' => '09:00', 'selesai' => '14:00'],
                    ['hari' => 5, 'mulai' => '09:00', 'selesai' => '12:00'],
                ],
            ],
            [
                'nama'          => 'dr. Harun Nasution',
                'inisial'       => 'HN',
                'spesialis'     => 'Dokter Gigi',
                'kategori'      => 'Gigi',
                'faskes'        => 'Puskesmas Mila',
                'pengalaman'    => '5 tahun',
                'jumlah_pasien' => '740+',
                'rating'        => 4.7,
                'total_ulasan'  => 0,
                'avatar_bg'     => '#FAEEDA',
                'avatar_color'  => '#854F0B',
                'tentang'       => 'dr. Harun Nasution adalah dokter gigi yang berpengalaman dalam penanganan berbagai masalah gigi dan mulut.',
                'keahlian'      => ['Tambal Gigi', 'Cabut Gigi', 'Scaling', 'Perawatan Saluran Akar', 'Gigi Anak'],
                'jadwal'        => [
                    ['hari' => 1, 'mulai' => '10:00', 'selesai' => '13:00'],
                    ['hari' => 3, 'mulai' => '10:00', 'selesai' => '13:00'],
                    ['hari' => 5, 'mulai' => '10:00', 'selesai' => '13:00'],
                ],
            ],
            [
                'nama'          => 'dr. Nadia Fitri, Sp.OG',
                'inisial'       => 'NF',
                'spesialis'     => 'Kandungan',
                'kategori'      => 'Kandungan',
                'faskes'        => 'RS Umum Sigli',
                'pengalaman'    => '12 tahun',
                'jumlah_pasien' => '2.100+',
                'rating'        => 4.9,
                'total_ulasan'  => 0,
                'avatar_bg'     => '#FBEAF0',
                'avatar_color'  => '#993556',
                'tentang'       => 'dr. Nadia Fitri adalah spesialis kandungan berpengalaman yang telah membantu ribuan ibu hamil di wilayah Pidie.',
                'keahlian'      => ['USG Kehamilan', 'ANC', 'KB', 'Persalinan Normal', 'Operasi Caesar', 'Infertilitas'],
                'jadwal'        => [
                    ['hari' => 1, 'mulai' => '13:00', 'selesai' => '17:00'],
                    ['hari' => 2, 'mulai' => '13:00', 'selesai' => '17:00'],
                    ['hari' => 3, 'mulai' => '13:00', 'selesai' => '17:00'],
                    ['hari' => 4, 'mulai' => '13:00', 'selesai' => '17:00'],
                ],
            ],
            [
                'nama'          => 'dr. Ahmad Marzuki, Sp.PD',
                'inisial'       => 'AM',
                'spesialis'     => 'Penyakit Dalam',
                'kategori'      => 'Penyakit Dalam',
                'faskes'        => 'RS Umum Sigli',
                'pengalaman'    => '14 tahun',
                'jumlah_pasien' => '1.800+',
                'rating'        => 4.8,
                'total_ulasan'  => 0,
                'avatar_bg'     => '#EEEDFE',
                'avatar_color'  => '#534AB7',
                'tentang'       => 'dr. Ahmad Marzuki adalah spesialis penyakit dalam dengan pengalaman luas dalam penanganan penyakit kronis.',
                'keahlian'      => ['Diabetes', 'Hipertensi', 'Penyakit Jantung', 'Ginjal', 'Tiroid', 'Reumatik'],
                'jadwal'        => [
                    ['hari' => 1, 'mulai' => '08:00', 'selesai' => '11:00'],
                    ['hari' => 2, 'mulai' => '08:00', 'selesai' => '11:00'],
                    ['hari' => 4, 'mulai' => '08:00', 'selesai' => '11:00'],
                    ['hari' => 5, 'mulai' => '08:00', 'selesai' => '11:00'],
                ],
            ],
            [
                'nama'          => 'dr. Yusra Safrina',
                'inisial'       => 'YS',
                'spesialis'     => 'Dokter Umum',
                'kategori'      => 'Dokter Umum',
                'faskes'        => 'Puskesmas Kembang Tanjong',
                'pengalaman'    => '3 tahun',
                'jumlah_pasien' => '560+',
                'rating'        => 4.6,
                'total_ulasan'  => 0,
                'avatar_bg'     => '#E1F5EE',
                'avatar_color'  => '#0F6E56',
                'tentang'       => 'dr. Yusra Safrina adalah dokter umum muda yang bersemangat melayani masyarakat Kembang Tanjong.',
                'keahlian'      => ['Pemeriksaan Umum', 'Demam & Flu', 'Imunisasi', 'KB', 'Luka & Cedera'],
                'jadwal'        => [
                    ['hari' => 1, 'mulai' => '08:00', 'selesai' => '12:00'],
                    ['hari' => 2, 'mulai' => '08:00', 'selesai' => '12:00'],
                    ['hari' => 3, 'mulai' => '08:00', 'selesai' => '12:00'],
                    ['hari' => 4, 'mulai' => '08:00', 'selesai' => '12:00'],
                    ['hari' => 5, 'mulai' => '08:00', 'selesai' => '12:00'],
                ],
            ],
        ];

        foreach ($dokterData as $data) {
            $faskes = Faskes::where('nama', $data['faskes'])->first();
            if (! $faskes) continue;

            $dokter = Dokter::firstOrCreate(
                ['nama' => $data['nama']],
                [
                    'faskes_id'     => $faskes->id,
                    'inisial'       => $data['inisial'],
                    'spesialis'     => $data['spesialis'],
                    'kategori'      => $data['kategori'],
                    'pengalaman'    => $data['pengalaman'],
                    'jumlah_pasien' => $data['jumlah_pasien'],
                    'rating'        => $data['rating'],
                    'total_ulasan'  => $data['total_ulasan'],
                    'avatar_bg'     => $data['avatar_bg'],
                    'avatar_color'  => $data['avatar_color'],
                    'tentang'       => $data['tentang'],
                    'keahlian'      => $data['keahlian'],
                    'aktif'         => true,
                ]
            );

            // Buat jadwal
            foreach ($data['jadwal'] as $j) {
                JadwalDokter::firstOrCreate(
                    ['dokter_id' => $dokter->id, 'hari' => $j['hari']],
                    [
                        'jam_mulai'        => $j['mulai'],
                        'jam_selesai'      => $j['selesai'],
                        'kuota_per_hari'   => 20,
                        'durasi_per_pasien'=> 15,
                        'aktif'            => true,
                    ]
                );
            }
        }
    }
}
