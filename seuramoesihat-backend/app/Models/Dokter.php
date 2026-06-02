<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $table = 'dokter';

    protected $fillable = [
        'user_id',
        'faskes_id',
        'nama',
        'inisial',
        'spesialis',
        'kategori',
        'pengalaman',
        'jumlah_pasien',
        'tentang',
        'keahlian',
        'avatar_bg',
        'avatar_color',
        'rating',
        'total_ulasan',
        'aktif',
    ];

    protected $casts = [
        'keahlian' => 'array',
        'aktif' => 'boolean',
        'rating' => 'float',
    ];

    // Relasi
    public function faskes()
    {
        return $this->belongsTo(Faskes::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jadwal()
    {
        return $this->hasMany(JadwalDokter::class);
    }

    public function slot()
    {
        return $this->hasMany(SlotAntrian::class);
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

    public function ulasan()
    {
        return $this->hasMany(UlasanDokter::class);
    }

    /**
     * Hitung kuota tersisa untuk tanggal tertentu
     */
    public function kuotaTersisa(string $tanggal): int
    {
        $slot = $this->slot()
            ->where('tanggal', $tanggal)
            ->selectRaw('SUM(kuota - terisi) as sisa')
            ->first();

        return (int) ($slot->sisa ?? 0);
    }

    /**
     * Cek apakah dokter tersedia hari ini
     */
    public function getTersediaAttribute(): bool
    {
        return $this->kuotaTersisa(now()->toDateString()) > 0;
    }
}
