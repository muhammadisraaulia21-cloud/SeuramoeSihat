<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Dokter;
use App\Models\Notifikasi;
use App\Models\SlotAntrian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * POST /api/booking
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dokter_id'   => 'required|exists:dokter,id',
            'tanggal'     => 'required|date|after_or_equal:today',
            'jam'         => 'required|date_format:H:i',
            'nama_pasien' => 'required|string|max:255',
            'no_hp'       => 'required|string|max:20',
            'keluhan'     => 'required|string',
            'alergi'      => 'nullable|string|max:255',
            'tipe_pasien' => 'required|in:Pasien Baru,Pasien Lama',
            'notif_wa'    => 'boolean',
        ]);

        $user = $request->user();

        // Cek apakah user sudah punya antrian aktif di dokter & tanggal yang sama
        $sudahAda = Antrian::where('user_id', $user->id)
            ->where('dokter_id', $validated['dokter_id'])
            ->where('tanggal', $validated['tanggal'])
            ->whereIn('status', ['menunggu', 'dipanggil'])
            ->exists();

        if ($sudahAda) {
            return response()->json([
                'message' => 'Anda sudah memiliki antrian aktif untuk dokter ini pada tanggal tersebut',
            ], 422);
        }

        $antrian = DB::transaction(function () use ($validated, $user) {
            $jamFull = $validated['jam'] . ':00';

            // Cari atau buat slot
            $slot = SlotAntrian::where('dokter_id', $validated['dokter_id'])
                ->where('tanggal', $validated['tanggal'])
                ->where('jam', $jamFull)
                ->lockForUpdate()
                ->first();

            if (! $slot || $slot->sisa <= 0) {
                abort(422, 'Slot waktu ini sudah penuh atau tidak tersedia');
            }

            // Nomor antrian: hitung antrian hari itu + 1
            $nomorAntrian = Antrian::where('dokter_id', $validated['dokter_id'])
                ->where('tanggal', $validated['tanggal'])
                ->whereIn('status', ['menunggu', 'dipanggil', 'selesai'])
                ->count() + 1;

            // Buat antrian
            $antrian = Antrian::create([
                'user_id'      => $user->id,
                'dokter_id'    => $validated['dokter_id'],
                'slot_id'      => $slot->id,
                'tanggal'      => $validated['tanggal'],
                'jam'          => $jamFull,
                'nomor_antrian'=> $nomorAntrian,
                'nama_pasien'  => $validated['nama_pasien'],
                'no_hp'        => $validated['no_hp'],
                'keluhan'      => $validated['keluhan'],
                'alergi'       => $validated['alergi'] ?? null,
                'tipe_pasien'  => $validated['tipe_pasien'],
                'notif_wa'     => $validated['notif_wa'] ?? true,
                'status'       => 'menunggu',
            ]);

            // Update slot
            $slot->increment('terisi');
            if ($slot->terisi >= $slot->kuota) {
                $slot->update(['tersedia' => false]);
            }

            // Kirim notifikasi
            $dokter = Dokter::with('faskes')->find($validated['dokter_id']);
            Notifikasi::create([
                'user_id'  => $user->id,
                'kategori' => 'antrian',
                'judul'    => 'Booking antrian berhasil',
                'pesan'    => "Antrian #{$nomorAntrian} di {$dokter->faskes->nama} dengan {$dokter->nama} pada " .
                              \Carbon\Carbon::parse($validated['tanggal'])->isoFormat('D MMMM YYYY') .
                              " pukul {$validated['jam']} telah dikonfirmasi.",
                'icon'     => '✅',
                'bg_class' => 'bg-emerald-50',
                'aksi'     => 'Pantau Antrian',
                'aksi_url' => '/antrian',
            ]);

            return $antrian;
        });

        $antrian->load(['dokter.faskes']);

        return response()->json([
            'message' => 'Booking berhasil',
            'data'    => $this->formatAntrian($antrian),
        ], 201);
    }

    /**
     * GET /api/antrian  — semua antrian aktif user (hari ini & mendatang)
     */
    public function aktif(Request $request): JsonResponse
    {
        $antrian = Antrian::with(['dokter.faskes'])
            ->where('user_id', $request->user()->id)
            ->where('tanggal', '>=', now()->toDateString())
            ->whereIn('status', ['menunggu', 'dipanggil'])
            ->orderBy('tanggal')
            ->orderBy('nomor_antrian')
            ->get();

        if ($antrian->isEmpty()) {
            return response()->json(['data' => []]);
        }

        return response()->json([
            'data' => $antrian->map(fn($a) => $this->formatAntrian($a, true)),
        ]);
    }

    /**
     * GET /api/antrian/riwayat
     */
    public function riwayat(Request $request): JsonResponse
    {
        $riwayat = Antrian::with(['dokter.faskes', 'rekamMedis'])
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['selesai', 'dibatalkan'])
            ->orderByDesc('tanggal')
            ->orderByDesc('nomor_antrian')
            ->paginate(10);

        $data = $riwayat->getCollection()->map(fn($a) => $this->formatAntrian($a));

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $riwayat->currentPage(),
                'last_page'    => $riwayat->lastPage(),
                'total'        => $riwayat->total(),
            ],
        ]);
    }

    /**
     * GET /api/antrian/{id}
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $antrian = Antrian::with(['dokter.faskes', 'rekamMedis.resepObat'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json(['data' => $this->formatAntrian($antrian, true)]);
    }

    /**
     * DELETE /api/antrian/{id}  — batalkan antrian
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        $antrian = Antrian::where('user_id', $request->user()->id)
            ->whereIn('status', ['menunggu'])
            ->findOrFail($id);

        DB::transaction(function () use ($antrian) {
            $antrian->update(['status' => 'dibatalkan']);

            // Kembalikan slot
            if ($antrian->slot_id) {
                $slot = SlotAntrian::find($antrian->slot_id);
                if ($slot) {
                    $slot->decrement('terisi');
                    $slot->update(['tersedia' => true]);
                }
            }
        });

        return response()->json(['message' => 'Antrian berhasil dibatalkan']);
    }

    /**
     * GET /api/antrian/{id}/status  — status live antrian
     */
    public function status(int $id, Request $request): JsonResponse
    {
        $antrian = Antrian::where('user_id', $request->user()->id)->findOrFail($id);

        $nomorDipanggil = Antrian::where('dokter_id', $antrian->dokter_id)
            ->where('tanggal', $antrian->tanggal)
            ->where('status', 'dipanggil')
            ->max('nomor_antrian') ?? 0;

        $sisaAntrian = Antrian::where('dokter_id', $antrian->dokter_id)
            ->where('tanggal', $antrian->tanggal)
            ->where('status', 'menunggu')
            ->where('nomor_antrian', '<', $antrian->nomor_antrian)
            ->count();

        return response()->json([
            'data' => [
                'nomor_antrian'   => $antrian->nomor_antrian,
                'nomor_dipanggil' => $nomorDipanggil,
                'sisa_antrian'    => $sisaAntrian,
                'estimasi_menit'  => $sisaAntrian * 7,
                'status'          => $antrian->status,
            ],
        ]);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function formatAntrian(Antrian $a, bool $withDetail = false): array
    {
        $dokter = $a->dokter;
        $faskes = $dokter?->faskes;

        $data = [
            'id'             => $a->id,
            'nomor_antrian'  => $a->nomor_antrian,
            'tanggal'        => $a->tanggal?->isoFormat('D MMMM YYYY'),
            'tanggal_raw'    => $a->tanggal?->toDateString(),
            'jam'            => substr($a->jam, 0, 5),
            'status'         => $a->status,
            'nama_pasien'    => $a->nama_pasien,
            'no_hp'          => $a->no_hp,
            'tipe_pasien'    => $a->tipe_pasien,
            'dokter'         => [
                'id'           => $dokter?->id,
                'nama'         => $dokter?->nama,
                'spesialis'    => $dokter?->spesialis,
                'inisial'      => $dokter?->inisial,
                'avatar_bg'    => $dokter?->avatar_bg,
                'avatar_color' => $dokter?->avatar_color,
            ],
            'faskes'         => $faskes?->nama,
        ];

        if ($withDetail) {
            $data['keluhan']  = $a->keluhan;
            $data['alergi']   = $a->alergi;
            $data['notif_wa'] = $a->notif_wa;

            if ($a->rekamMedis) {
                $rm = $a->rekamMedis;
                $data['rekam_medis'] = [
                    'diagnosa'      => $rm->diagnosa,
                    'catatan_dokter'=> $rm->catatan_dokter,
                    'resep'         => $rm->resepObat->map(fn($r) => $r->format_lengkap),
                ];
            }
        }

        return $data;
    }
}
