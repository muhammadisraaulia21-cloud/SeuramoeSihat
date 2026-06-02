<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('kategori', ['antrian', 'chat', 'kesehatan', 'sistem'])->default('sistem');
            $table->string('judul');
            $table->text('pesan');
            $table->string('icon')->default('🔔');
            $table->string('bg_class')->default('bg-gray-50');
            $table->string('aksi')->nullable(); // label tombol aksi
            $table->string('aksi_url')->nullable(); // route tujuan
            $table->boolean('dibaca')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
