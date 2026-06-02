<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekamMedis extends Model
{
    protected $table = 'rekam_medis';

    protected $fillable = [
        'user_id',
        'dokter_id',
        'antrian_id',
        'tanggal_kunjungan',
        'keluhan',
        'diagnosa',
        'catatan_dokter',
        'status',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function antrian()
    {
        return $this->belongsTo(Antrian::class);
    }

    public function resepObat()
    {
        return $this->hasMany(ResepObat::class);
    }
}
