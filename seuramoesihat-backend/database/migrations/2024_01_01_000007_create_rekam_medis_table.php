<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('dokter_id')->constrained('dokter')->onDelete('cascade');
            $table->foreignId('antrian_id')->nullable()->constrained('antrian')->onDelete('set null');
            $table->date('tanggal_kunjungan');
            $table->text('keluhan');
            $table->text('diagnosa')->nullable();
            $table->text('catatan_dokter')->nullable();
            $table->enum('status', ['draft', 'selesai'])->default('selesai');
            $table->timestamps();
        });

        Schema::create('resep_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekam_medis_id')->constrained('rekam_medis')->onDelete('cascade');
            $table->string('nama_obat');
            $table->string('dosis')->nullable(); // "500mg"
            $table->string('aturan_pakai')->nullable(); // "3x1"
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resep_obat');
        Schema::dropIfExists('rekam_medis');
    }
};
