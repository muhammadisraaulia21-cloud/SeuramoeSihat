<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'nik',
        'email',
        'no_hp',
        'alamat',
        'tanggal_lahir',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'password' => 'hashed',
        ];
    }

    // Relasi
    public function profilKesehatan()
    {
        return $this->hasOne(ProfilKesehatan::class);
    }

    public function antrian()
    {
        return $this->hasMany(Antrian::class);
    }

    public function rekamMedis()
    {
        return $this->hasMany(RekamMedis::class);
    }

    public function konsultasi()
    {
        return $this->hasMany(Konsultasi::class);
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function ulasan()
    {
        return $this->hasMany(UlasanDokter::class);
    }
}
