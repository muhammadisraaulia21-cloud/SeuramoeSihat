<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulasan_dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokter_id')->constrained('dokter')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('antrian_id')->nullable()->constrained('antrian')->onDelete('set null');
            $table->tinyInteger('bintang'); // 1-5
            $table->text('komentar')->nullable();
            $table->timestamps();

            // Index untuk performa query
            $table->index(['dokter_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan_dokter');
    }
};
