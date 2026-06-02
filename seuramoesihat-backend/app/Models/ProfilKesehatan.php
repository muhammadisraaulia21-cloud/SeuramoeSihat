<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilKesehatan extends Model
{
    protected $table = 'profil_kesehatan';

    protected $fillable = [
        'user_id',
        'golongan_darah',
        'berat_badan',
        'tinggi_badan',
        'alergi',
        'riwayat_penyakit',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
