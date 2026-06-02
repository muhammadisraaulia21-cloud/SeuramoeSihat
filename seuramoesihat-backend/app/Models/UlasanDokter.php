<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UlasanDokter extends Model
{
    protected $table = 'ulasan_dokter';

    protected $fillable = [
        'dokter_id',
        'user_id',
        'antrian_id',
        'bintang',
        'komentar',
    ];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function antrian()
    {
        return $this->belongsTo(Antrian::class);
    }

    /**
     * Nama pasien disamarkan: "Ahmad S."
     */
    public function getNamaSamarAttribute(): string
    {
        $parts = explode(' ', $this->user->nama ?? 'Anonim');
        $first = $parts[0];
        $last = isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) . '.' : '';
        return trim("$first $last");
    }
}
