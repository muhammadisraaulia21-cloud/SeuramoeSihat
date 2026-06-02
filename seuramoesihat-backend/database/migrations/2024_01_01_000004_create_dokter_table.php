<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('faskes_id')->constrained('faskes')->onDelete('cascade');
            $table->string('nama');
            $table->string('inisial', 5); // "RH"
            $table->string('spesialis');
            $table->string('kategori'); // Dokter Umum, Spesialis Anak, Gigi, Kandungan, Penyakit Dalam
            $table->string('pengalaman')->nullable(); // "8 tahun"
            $table->string('jumlah_pasien')->nullable(); // "1.200+"
            $table->text('tentang')->nullable();
            $table->json('keahlian')->nullable(); // array of strings
            $table->string('avatar_bg', 10)->default('#E1F5EE');
            $table->string('avatar_color', 10)->default('#0F6E56');
            $table->decimal('rating', 3, 1)->default(0.0);
            $table->integer('total_ulasan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokter');
    }
};
