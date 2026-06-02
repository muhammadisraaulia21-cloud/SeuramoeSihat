<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\SlotAntrian;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DokterController extends Controller
{
    /**
     * GET /api/dokter
     * Query params: search, kategori, wilayah, sort (rating|kuota|nama), tersedia
     */
    public function index(Request $request): JsonResponse
    {
        $query = Dokter::with('faskes')
            ->where('aktif', true);

        // Filter pencarian nama/spesialis
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('spesialis', 'like', "%{$search}%");
            });
        }

        // Filter kategori
        if ($kategori = $request->query('kategori')) {
            $query->where('kategori', $kategori);
        }

        // Filter wilayah (via faskes)
        if ($wilayah = $request->query('wilayah')) {
            $query->whereHas('faskes', fn($q) => $q->where('wilayah', $wilayah));
        }

        // Sorting
        $sort = $request->query('sort', 'rating');
        match ($sort) {
            'kuota' => $query->orderByDesc('rating'), // kuota dihitung setelah fetch
            'nama'  => $query->orderBy('nama'),
            default => $query->orderByDesc('rating'),
        };

        $dokters = $query->get();

        $today = now()->toDateString();

        // Generate slot hari ini untuk semua dokter jika belum ada
        foreach ($dokters as $d) {
            $this->generateSlotJikaPerlu($d->id, $today);
        }

        $result = $dokters->map(function (Dokter $d) use ($today) {
            $kuota = $this->kuotaTersisa($d->id, $today);
            return [
                'id'           => $d->id,
                'inisial'      => $d->inisial,
                'nama'         => $d->nama,
                'spesialis'    => $d->spesialis,
                'kategori'     => $d->kategori,
                'faskes'       => $d->faskes->nama,
                'wilayah'      => $d->faskes->wilayah,
                'jadwal'       => $this->jadwalHariIni($d),
                'kuota'        => $kuota,
                'rating'       => $d->rating,
                'pengalaman'   => $d->pengalaman,
                'jumlah_pasien'=> $d->jumlah_pasien,
                'tersedia'     => $kuota > 0,
                'avatar_bg'    => $d->avatar_bg,
                'avatar_color' => $d->avatar_color,
            ];
        });

        // Sort by kuota setelah dihitung
        if ($sort === 'kuota') {
            $result = $result->sortByDesc('kuota')->values();
        }

        // Filter tersedia saja
        if ($request->boolean('tersedia')) {
            $result = $result->filter(fn($d) => $d['tersedia'])->values();
        }

        return response()->json(['data' => $result]);
    }

    /**
     * GET /api/dokter/{id}
     */
    public function show(int $id): JsonResponse
    {
        $dokter = Dokter::with(['faskes', 'ulasan.user'])->findOrFail($id);

        $today = now()->toDateString();
        $kuota = $this->kuotaTersisa($dokter->id, $today);

        $ulasan = $dokter->ulasan()
            ->with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($u) => [
                'nama'     => $u->nama_samar,
                'bintang'  => $u->bintang,
                'komentar' => $u->komentar,
                'tanggal'  => $u->created_at->diffForHumans(),
            ]);

        return response()->json([
            'data' => [
                'id'            => $dokter->id,
                'inisial'       => $dokter->inisial,
                'nama'          => $dokter->nama,
                'spesialis'     => $dokter->spesialis,
                'kategori'      => $dokter->kategori,
                'faskes'        => $dokter->faskes->nama,
                'wilayah'       => $dokter->faskes->wilayah,
                'jadwal'        => $this->jadwalHariIni($dokter),
                'kuota'         => $kuota,
                'rating'        => $dokter->rating,
                'total_ulasan'  => $dokter->total_ulasan,
                'pengalaman'    => $dokter->pengalaman,
                'jumlah_pasien' => $dokter->jumlah_pasien,
                'tentang'       => $dokter->tentang,
                'keahlian'      => $dokter->keahlian ?? [],
                'tersedia'      => $kuota > 0,
                'avatar_bg'     => $dokter->avatar_bg,
                'avatar_color'  => $dokter->avatar_color,
                'ulasan'        => $ulasan,
            ],
        ]);
    }

    /**
     * GET /api/dokter/{id}/jadwal
     * Query param: tanggal (Y-m-d), default hari ini
     */
    public function jadwal(int $id, Request $request): JsonResponse
    {
        $dokter = Dokter::findOrFail($id);

        // Generate slot untuk 7 hari ke depan jika belum ada
        $dates = collect(range(0, 6))->map(fn($i) => now()->addDays($i)->toDateString());

        $slots = [];
        foreach ($dates as $date) {
            $this->generateSlotJikaPerlu($dokter->id, $date);

            $slotHari = SlotAntrian::where('dokter_id', $dokter->id)
                ->where('tanggal', $date)
                ->orderBy('jam')
                ->get()
                ->map(fn($s) => [
                    'id'       => $s->id,
                    'jam'      => substr($s->jam, 0, 5),
                    'tersedia' => $s->tersedia && $s->sisa > 0,
                    'sisa'     => $s->sisa,
                ]);

            $carbon = Carbon::parse($date);
            $slots[] = [
                'tanggal' => $date,
                'label'   => $carbon->isoFormat('D MMM YYYY'),
                'hari'    => $carbon->isToday() ? 'Hari ini' : $carbon->isoFormat('ddd'),
                'tanggal_angka' => $carbon->day,
                'bulan'   => $carbon->isoFormat('MMM'),
                'slot'    => $slotHari,
            ];
        }

        return response()->json(['data' => $slots]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function kuotaTersisa(int $dokterId, string $tanggal): int
    {
        $result = \Illuminate\Support\Facades\DB::selectOne(
            'SELECT COALESCE(SUM(kuota - terisi), 0) as sisa FROM slot_antrian WHERE dokter_id = ? AND tanggal = ? AND tersedia = 1',
            [$dokterId, $tanggal]
        );

        return (int) ($result->sisa ?? 0);
    }

    private function jadwalHariIni(Dokter $dokter): string
    {
        $hari = now()->dayOfWeek; // 0=Minggu
        $jadwal = $dokter->jadwal()->where('hari', $hari)->where('aktif', true)->first();
        if (! $jadwal) return '-';
        return substr($jadwal->jam_mulai, 0, 5) . '–' . substr($jadwal->jam_selesai, 0, 5);
    }

    /**
     * Generate slot antrian otomatis berdasarkan jadwal dokter
     */
    private function generateSlotJikaPerlu(int $dokterId, string $tanggal): void
    {
        // Cek apakah slot sudah ada
        if (SlotAntrian::where('dokter_id', $dokterId)->where('tanggal', $tanggal)->exists()) {
            return;
        }

        $hari = Carbon::parse($tanggal)->dayOfWeek;
        $jadwal = \App\Models\JadwalDokter::where('dokter_id', $dokterId)
            ->where('hari', $hari)
            ->where('aktif', true)
            ->first();

        if (! $jadwal) return;

        $jam = Carbon::parse($jadwal->jam_mulai);
        $selesai = Carbon::parse($jadwal->jam_selesai);
        $durasi = $jadwal->durasi_per_pasien;

        while ($jam->lt($selesai)) {
            SlotAntrian::firstOrCreate(
                ['dokter_id' => $dokterId, 'tanggal' => $tanggal, 'jam' => $jam->format('H:i:s')],
                ['kuota' => 1, 'terisi' => 0, 'tersedia' => true]
            );
            $jam->addMinutes($durasi);
        }
    }
}
