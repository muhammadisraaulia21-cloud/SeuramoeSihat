<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\PesanKonsultasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KonsultasiController extends Controller
{
    /**
     * GET /api/konsultasi  — list sesi konsultasi user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $sesi = Konsultasi::with(['dokter.faskes', 'pesanTerakhir'])
            ->where('user_id', $user->id)
            ->orderByDesc('terakhir_pesan_at')
            ->get()
            ->map(function (Konsultasi $k) use ($user) {
                $dokter = $k->dokter;
                return [
                    'id'             => $k->id,
                    'status'         => $k->status,
                    'dokter'         => [
                        'id'           => $dokter->id,
                        'nama'         => $dokter->nama,
                        'inisial'      => $dokter->inisial,
                        'spesialis'    => $dokter->spesialis,
                        'faskes'       => $dokter->faskes->nama,
                        'avatar_bg'    => $dokter->avatar_bg,
                        'avatar_color' => $dokter->avatar_color,
                        'online'       => false, // TODO: implementasi presence
                        'respon_time'  => '< 15 mnt',
                        'rating'       => $dokter->rating,
                    ],
                    'pesan_terakhir' => $k->pesanTerakhir?->teks,
                    'waktu'          => $k->terakhir_pesan_at?->format('H.i'),
                    'unread'         => $k->unreadCount($user->id),
                ];
            });

        // Daftar semua dokter yang bisa dikonsultasi
        $dokterList = Dokter::with('faskes')
            ->where('aktif', true)
            ->orderByDesc('rating')
            ->get()
            ->map(fn($d) => [
                'id'           => $d->id,
                'nama'         => $d->nama,
                'inisial'      => $d->inisial,
                'spesialis'    => $d->spesialis,
                'faskes'       => $d->faskes->nama,
                'avatar_bg'    => $d->avatar_bg,
                'avatar_color' => $d->avatar_color,
                'online'       => false,
                'respon_time'  => '< 15 mnt',
                'rating'       => $d->rating,
            ]);

        return response()->json([
            'riwayat_chat' => $sesi,
            'dokter_list'  => $dokterList,
        ]);
    }

    /**
     * POST /api/konsultasi  — mulai sesi baru atau buka yang sudah ada
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(['dokter_id' => 'required|exists:dokter,id']);

        $user = $request->user();

        // Cari sesi aktif yang sudah ada
        $sesi = Konsultasi::firstOrCreate(
            [
                'user_id'  => $user->id,
                'dokter_id'=> $request->dokter_id,
                'status'   => 'aktif',
            ],
            ['terakhir_pesan_at' => now()]
        );

        // Jika sesi baru, kirim pesan sambutan dari dokter
        if ($sesi->wasRecentlyCreated) {
            $dokter = Dokter::find($request->dokter_id);
            PesanKonsultasi::create([
                'konsultasi_id' => $sesi->id,
                'user_id'       => $user->id, // placeholder, idealnya user_id dokter
                'dari'          => 'dokter',
                'teks'          => "Assalamualaikum, selamat datang di SeuramoeSihat 🌿 Saya {$dokter->nama}. Ada yang bisa saya bantu hari ini?",
                'dibaca'        => false,
            ]);
        }

        return response()->json([
            'data' => ['id' => $sesi->id],
        ], $sesi->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * GET /api/konsultasi/{id}/pesan
     */
    public function pesan(int $id, Request $request): JsonResponse
    {
        $sesi = Konsultasi::where('user_id', $request->user()->id)->findOrFail($id);

        // Tandai pesan dokter sebagai dibaca
        PesanKonsultasi::where('konsultasi_id', $id)
            ->where('dari', 'dokter')
            ->where('dibaca', false)
            ->update(['dibaca' => true]);

        $pesan = PesanKonsultasi::where('konsultasi_id', $id)
            ->orderBy('created_at')
            ->get()
            ->map(fn($p) => [
                'id'     => $p->id,
                'dari'   => $p->dari,
                'teks'   => $p->teks,
                'waktu'  => $p->created_at->format('H.i'),
                'dibaca' => $p->dibaca,
            ]);

        return response()->json(['data' => $pesan]);
    }

    /**
     * POST /api/konsultasi/{id}/pesan  — kirim pesan
     */
    public function kirimPesan(int $id, Request $request): JsonResponse
    {
        $request->validate(['teks' => 'required|string|max:2000']);

        $user = $request->user();
        $sesi = Konsultasi::where('user_id', $user->id)
            ->where('status', 'aktif')
            ->findOrFail($id);

        // Simpan pesan pasien
        $pesan = PesanKonsultasi::create([
            'konsultasi_id' => $sesi->id,
            'user_id'       => $user->id,
            'dari'          => 'pasien',
            'teks'          => $request->teks,
            'dibaca'        => false,
        ]);

        $sesi->update(['terakhir_pesan_at' => now()]);

        // Auto-reply dokter (simulasi — di production pakai queue/websocket)
        $balasan = $this->autoReply($request->teks);
        $pesanDokter = PesanKonsultasi::create([
            'konsultasi_id' => $sesi->id,
            'user_id'       => $user->id,
            'dari'          => 'dokter',
            'teks'          => $balasan,
            'dibaca'        => false,
        ]);

        return response()->json([
            'data' => [
                'pesan_pasien' => [
                    'id'    => $pesan->id,
                    'dari'  => 'pasien',
                    'teks'  => $pesan->teks,
                    'waktu' => $pesan->created_at->format('H.i'),
                ],
                'pesan_dokter' => [
                    'id'    => $pesanDokter->id,
                    'dari'  => 'dokter',
                    'teks'  => $pesanDokter->teks,
                    'waktu' => $pesanDokter->created_at->format('H.i'),
                ],
            ],
        ], 201);
    }

    // ─── Auto-reply sederhana ─────────────────────────────────────────────────

    private function autoReply(string $pesan): string
    {
        $p = strtolower($pesan);

        if (str_contains($p, 'demam')) {
            return 'Untuk demam, pastikan minum air putih yang cukup minimal 2 liter/hari. Bisa konsumsi paracetamol 500mg jika suhu di atas 38°C. Jika demam lebih dari 3 hari, segera periksa ke faskes terdekat ya 🙏';
        }
        if (str_contains($p, 'batuk')) {
            return 'Batuk bisa disebabkan banyak hal. Hindari minuman dingin, madu hangat + jahe bisa membantu. Jika batuk berdahak atau lebih dari 2 minggu, perlu diperiksa langsung.';
        }
        if (str_contains($p, 'sakit kepala') || str_contains($p, 'pusing')) {
            return 'Sakit kepala bisa karena kurang tidur, dehidrasi, atau tekanan darah. Coba istirahat cukup dan minum air putih. Jika sangat mengganggu, konsumsi paracetamol sesuai dosis.';
        }
        if (str_contains($p, 'rujukan')) {
            return 'Untuk mendapatkan surat rujukan, Anda perlu datang langsung ke puskesmas dan diperiksa terlebih dahulu. Saya bisa bantu jadwalkan booking antrian.';
        }
        if (str_contains($p, 'obat')) {
            return 'Konsumsi obat harus sesuai resep dokter ya. Jangan menghentikan obat tanpa konsultasi, terutama untuk antibiotik. Ada obat spesifik yang ingin ditanyakan?';
        }

        return 'Terima kasih atas informasinya. Untuk penanganan yang lebih tepat, saya sarankan untuk datang langsung ke puskesmas. Saya bisa bantu booking antrian sekarang jika diperlukan 🏥';
    }
}
