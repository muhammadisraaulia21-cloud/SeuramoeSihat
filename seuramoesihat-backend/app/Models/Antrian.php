<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Antrian extends Model
{
    protected $table = 'antrian';

    protected $fillable = [
        'user_id',
        'dokter_id',
        'slot_id',
        'tanggal',
        'jam',
        'nomor_antrian',
        'nama_pasien',
        'no_hp',
        'keluhan',
        'alergi',
        'tipe_pasien',
        'notif_wa',
        'status',
        'dipanggil_at',
        'selesai_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'notif_wa' => 'boolean',
        'dipanggil_at' => 'datetime',
        'selesai_at' => 'datetime',
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

    public function slot()
    {
        return $this->belongsTo(SlotAntrian::class, 'slot_id');
    }

    public function rekamMedis()
    {
        return $this->hasOne(RekamMedis::class);
    }

    public function ulasan()
    {
        return $this->hasOne(UlasanDokter::class);
    }

    /**
     * Nomor antrian yang sedang dipanggil (antrian sebelum ini yang sudah dipanggil)
     */
    public function getNomorDipanggilAttribute(): int
    {
        return Antrian::where('dokter_id', $this->dokter_id)
            ->where('tanggal', $this->tanggal)
            ->where('status', 'dipanggil')
            ->max('nomor_antrian') ?? 0;
    }

    /**
     * Sisa antrian sebelum giliran user
     */
    public function getSisaAntrianAttribute(): int
    {
        return Antrian::where('dokter_id', $this->dokter_id)
            ->where('tanggal', $this->tanggal)
            ->where('status', 'menunggu')
            ->where('nomor_antrian', '<', $this->nomor_antrian)
            ->count();
    }
}
