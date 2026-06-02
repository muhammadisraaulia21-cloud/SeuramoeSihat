<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesanKonsultasi extends Model
{
    protected $table = 'pesan_konsultasi';

    protected $fillable = [
        'konsultasi_id',
        'user_id',
        'dari',
        'teks',
        'dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
    ];

    public function konsultasi()
    {
        return $this->belongsTo(Konsultasi::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
