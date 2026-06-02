<?php

namespace Database\Seeders;

use App\Models\Faskes;
use Illuminate\Database\Seeder;

class FaksesSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Puskesmas Sigli',           'tipe' => 'puskesmas', 'wilayah' => 'Sigli',           'alamat' => 'Jl. Banda Aceh No. 1, Sigli'],
            ['nama' => 'Puskesmas Mila',             'tipe' => 'puskesmas', 'wilayah' => 'Mila',            'alamat' => 'Jl. Mila Raya, Kec. Mila'],
            ['nama' => 'Puskesmas Kembang Tanjong',  'tipe' => 'puskesmas', 'wilayah' => 'Kembang Tanjong', 'alamat' => 'Jl. Kembang Tanjong, Pidie'],
            ['nama' => 'Puskesmas Grong-Grong',      'tipe' => 'puskesmas', 'wilayah' => 'Grong-Grong',     'alamat' => 'Jl. Grong-Grong, Pidie'],
            ['nama' => 'Klinik Sehat Bersama',       'tipe' => 'klinik',    'wilayah' => 'Sigli',           'alamat' => 'Jl. Diponegoro No. 12, Sigli'],
            ['nama' => 'RS Umum Sigli',              'tipe' => 'rs',        'wilayah' => 'Sigli',           'alamat' => 'Jl. T. Hamzah Bendahara, Sigli'],
        ];

        foreach ($data as $item) {
            Faskes::firstOrCreate(['nama' => $item['nama']], $item);
        }
    }
}
