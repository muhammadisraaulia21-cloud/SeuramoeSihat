<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotAntrian extends Model
{
    protected $table = 'slot_antrian';

    protected $fillable = [
        'dokter_id',
        'tanggal',
        'jam',
        'kuota',
        'terisi',
        'tersedia',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tersedia' => 'boolean',
    ];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function antrian()
    {
        return $this->hasMany(Antrian::class, 'slot_id');
    }

    /**
     * Sisa kuota slot ini
     */
    public function getSisaAttribute(): int
    {
        return max(0, $this->kuota - $this->terisi);
    }
}
