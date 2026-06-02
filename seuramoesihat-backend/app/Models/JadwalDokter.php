<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalDokter extends Model
{
    protected $table = 'jadwal_dokter';

    protected $fillable = [
        'dokter_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'kuota_per_hari',
        'durasi_per_pasien',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    /**
     * Nama hari dalam Bahasa Indonesia
     */
    public function getNamaHariAttribute(): string
    {
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $hari[$this->hari] ?? '-';
    }

    /**
     * Format jadwal: "08.00–12.00"
     */
    public function getFormatJadwalAttribute(): string
    {
        return substr($this->jam_mulai, 0, 5) . '–' . substr($this->jam_selesai, 0, 5);
    }
}
