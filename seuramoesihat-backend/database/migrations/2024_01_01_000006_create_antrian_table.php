<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antrian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('dokter_id')->constrained('dokter')->onDelete('cascade');
            $table->foreignId('slot_id')->nullable()->constrained('slot_antrian')->onDelete('set null');
            $table->date('tanggal');
            $table->time('jam');
            $table->integer('nomor_antrian');
            $table->string('nama_pasien');
            $table->string('no_hp');
            $table->text('keluhan');
            $table->string('alergi')->nullable();
            $table->enum('tipe_pasien', ['Pasien Baru', 'Pasien Lama'])->default('Pasien Baru');
            $table->boolean('notif_wa')->default(true);
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'dibatalkan'])->default('menunggu');
            $table->timestamp('dipanggil_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrian');
    }
};
