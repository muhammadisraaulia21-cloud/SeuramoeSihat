<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faskes extends Model
{
    protected $table = 'faskes';

    protected $fillable = [
        'nama',
        'tipe',
        'wilayah',
        'alamat',
        'telepon',
        'deskripsi',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function dokter()
    {
        return $this->hasMany(Dokter::class);
    }
}
