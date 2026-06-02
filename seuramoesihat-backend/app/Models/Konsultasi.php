<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konsultasi extends Model
{
    protected $table = 'konsultasi';

    protected $fillable = [
        'user_id',
        'dokter_id',
        'status',
        'terakhir_pesan_at',
    ];

    protected $casts = [
        'terakhir_pesan_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function pesan()
    {
        return $this->hasMany(PesanKonsultasi::class);
    }

    public function pesanTerakhir()
    {
        return $this->hasOne(PesanKonsultasi::class)->latestOfMany();
    }

    public function unreadCount(int $userId): int
    {
        return $this->pesan()
            ->where('dibaca', false)
            ->where('user_id', '!=', $userId)
            ->count();
    }
}
