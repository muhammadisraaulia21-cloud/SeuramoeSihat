<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FaksesSeeder::class,
            DokterSeeder::class,
            UserSeeder::class,
            RekamMedisSeeder::class,
            UlasanSeeder::class,
        ]);
    }
}
