<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'kategori',
        'judul',
        'pesan',
        'icon',
        'bg_class',
        'aksi',
        'aksi_url',
        'dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
