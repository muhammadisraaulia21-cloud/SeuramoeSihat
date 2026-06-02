<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokter_id')->constrained('dokter')->onDelete('cascade');
            $table->tinyInteger('hari'); // 0=Minggu, 1=Senin, ..., 6=Sabtu
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('kuota_per_hari')->default(20);
            $table->integer('durasi_per_pasien')->default(15); // menit
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('slot_antrian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokter_id')->constrained('dokter')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam');
            $table->integer('kuota')->default(1);
            $table->integer('terisi')->default(0);
            $table->boolean('tersedia')->default(true);
            $table->timestamps();

            $table->unique(['dokter_id', 'tanggal', 'jam']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_antrian');
        Schema::dropIfExists('jadwal_dokter');
    }
};
