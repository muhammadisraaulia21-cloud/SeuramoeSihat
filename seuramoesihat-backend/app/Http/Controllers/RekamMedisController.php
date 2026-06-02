<?php

namespace App\Http\Controllers;

use App\Models\RekamMedis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RekamMedisController extends Controller
{
    /**
     * GET /api/rekam-medis
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $rekamMedis = RekamMedis::with(['dokter.faskes', 'resepObat'])
            ->where('user_id', $user->id)
            ->where('status', 'selesai')
            ->orderByDesc('tanggal_kunjungan')
            ->get();

        // Statistik
        $stats = [
            'total_kunjungan'   => $rekamMedis->count(),
            'dokter_berbeda'    => $rekamMedis->pluck('dokter_id')->unique()->count(),
            'faskes_dikunjungi' => $rekamMedis->pluck('dokter.faskes_id')->unique()->count(),
        ];

        $data = $rekamMedis->map(fn($rm) => $this->formatRekamMedis($rm));

        return response()->json([
            'data'  => $data,
            'stats' => $stats,
        ]);
    }

    /**
     * GET /api/rekam-medis/{id}
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $rm = RekamMedis::with(['dokter.faskes', 'resepObat'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json(['data' => $this->formatRekamMedis($rm, true)]);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function formatRekamMedis(RekamMedis $rm, bool $full = false): array
    {
        $data = [
            'id'               => $rm->id,
            'faskes'           => $rm->dokter?->faskes?->nama,
            'dokter'           => $rm->dokter?->nama,
            'tanggal'          => $rm->tanggal_kunjungan?->isoFormat('D MMMM YYYY'),
            'keluhan'          => $rm->keluhan,
            'diagnosa'         => $rm->diagnosa,
            'resep'            => $rm->resepObat->map(fn($r) => $r->format_lengkap)->toArray(),
            'catatan'          => $rm->catatan_dokter,
        ];

        return $data;
    }
}
