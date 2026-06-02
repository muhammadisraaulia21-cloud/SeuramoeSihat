<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * GET /api/notifikasi
     * Query param: kategori (antrian|chat|kesehatan|sistem)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Notifikasi::where('user_id', $user->id)
            ->orderByDesc('created_at');

        if ($kategori = $request->query('kategori')) {
            $query->where('kategori', $kategori);
        }

        $notifikasi = $query->get();

        // Hitung badge per kategori
        $counts = Notifikasi::where('user_id', $user->id)
            ->where('dibaca', false)
            ->selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $totalUnread = $counts->sum();

        // Group by tanggal
        $grouped = $notifikasi->groupBy(function ($n) {
            if ($n->created_at->isToday()) return 'Hari ini';
            if ($n->created_at->isYesterday()) return 'Kemarin';
            return $n->created_at->isoFormat('D MMMM YYYY');
        })->map(function ($items, $tanggal) {
            return [
                'tanggal' => $tanggal,
                'items'   => $items->map(fn($n) => $this->formatNotif($n))->values(),
            ];
        })->values();

        return response()->json([
            'data'         => $grouped,
            'total_unread' => $totalUnread,
            'counts'       => $counts,
        ]);
    }

    /**
     * PATCH /api/notifikasi/{id}/baca
     */
    public function baca(int $id, Request $request): JsonResponse
    {
        $notif = Notifikasi::where('user_id', $request->user()->id)->findOrFail($id);
        $notif->update(['dibaca' => true]);

        return response()->json(['message' => 'Notifikasi ditandai dibaca']);
    }

    /**
     * PATCH /api/notifikasi/baca-semua
     */
    public function bacaSemua(Request $request): JsonResponse
    {
        Notifikasi::where('user_id', $request->user()->id)
            ->where('dibaca', false)
            ->update(['dibaca' => true]);

        return response()->json(['message' => 'Semua notifikasi ditandai dibaca']);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function formatNotif(Notifikasi $n): array
    {
        return [
            'id'       => $n->id,
            'kategori' => $n->kategori,
            'dibaca'   => $n->dibaca,
            'icon'     => $n->icon,
            'bg_class' => $n->bg_class,
            'judul'    => $n->judul,
            'pesan'    => $n->pesan,
            'waktu'    => $n->created_at->format('H.i'),
            'aksi'     => $n->aksi,
            'aksi_url' => $n->aksi_url,
        ];
    }
}
