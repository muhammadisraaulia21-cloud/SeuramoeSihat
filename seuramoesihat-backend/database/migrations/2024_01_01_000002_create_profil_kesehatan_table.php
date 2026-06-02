<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_kesehatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('golongan_darah')->nullable(); // A, B, AB, O
            $table->decimal('berat_badan', 5, 2)->nullable(); // kg
            $table->decimal('tinggi_badan', 5, 2)->nullable(); // cm
            $table->text('alergi')->nullable();
            $table->text('riwayat_penyakit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_kesehatan');
    }
};
