<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResepObat extends Model
{
    protected $table = 'resep_obat';

    protected $fillable = [
        'rekam_medis_id',
        'nama_obat',
        'dosis',
        'aturan_pakai',
        'keterangan',
    ];

    public function rekamMedis()
    {
        return $this->belongsTo(RekamMedis::class);
    }

    /**
     * Format lengkap: "Paracetamol 500mg 3x1"
     */
    public function getFormatLengkapAttribute(): string
    {
        return trim("{$this->nama_obat} {$this->dosis} {$this->aturan_pakai}");
    }
}
