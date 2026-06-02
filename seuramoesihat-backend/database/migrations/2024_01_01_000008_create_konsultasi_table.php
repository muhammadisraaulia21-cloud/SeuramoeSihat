<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konsultasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('dokter_id')->constrained('dokter')->onDelete('cascade');
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->timestamp('terakhir_pesan_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pesan_konsultasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsultasi_id')->constrained('konsultasi')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('dari', ['pasien', 'dokter']);
            $table->text('teks');
            $table->boolean('dibaca')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesan_konsultasi');
        Schema::dropIfExists('konsultasi');
    }
};
