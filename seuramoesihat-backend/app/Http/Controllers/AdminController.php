<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Dokter;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────

    /**
     * GET /api/admin/dashboard
     */
    public function dashboard(): JsonResponse
    {
        $today = now()->toDateString();

        return response()->json([
            'data' => [
                'total_pasien'         => User::where('role', 'pasien')->count(),
                'total_dokter'         => Dokter::count(),
                'antrian_hari_ini'     => Antrian::where('tanggal', $today)->count(),
                'antrian_menunggu'     => Antrian::where('tanggal', $today)->where('status', 'menunggu')->count(),
                'antrian_selesai'      => Antrian::where('tanggal', $today)->where('status', 'selesai')->count(),
                'antrian_dibatalkan'   => Antrian::where('tanggal', $today)->where('status', 'dibatalkan')->count(),
            ],
        ]);
    }

    // ─── Antrian ──────────────────────────────────────────────────────────────

    /**
     * GET /api/admin/antrian
     */
    public function indexAntrian(Request $request): JsonResponse
    {
        $query = Antrian::with(['user', 'dokter.faskes'])
            ->orderByDesc('tanggal')
            ->orderBy('nomor_antrian');

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter dokter
        if ($request->filled('dokter_id')) {
            $query->where('dokter_id', $request->dokter_id);
        }

        // Search nama pasien
        if ($request->filled('search')) {
            $query->where('nama_pasien', 'like', '%' . $request->search . '%');
        }

        $antrian = $query->paginate(15);

        return response()->json([
            'data' => $antrian->getCollection()->map(fn($a) => $this->formatAntrian($a)),
            'meta' => [
                'current_page' => $antrian->currentPage(),
                'last_page'    => $antrian->lastPage(),
                'total'        => $antrian->total(),
            ],
        ]);
    }

    /**
     * PATCH /api/admin/antrian/{id}/status
     * Body: { status: 'menunggu'|'dipanggil'|'selesai'|'dibatalkan' }
     */
    public function updateStatusAntrian(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:menunggu,dipanggil,selesai,dibatalkan',
        ]);

        $antrian = Antrian::with(['user', 'dokter'])->findOrFail($id);
        $statusLama = $antrian->status;
        $statusBaru = $request->status;

        DB::transaction(function () use ($antrian, $statusBaru, $statusLama) {
            $update = ['status' => $statusBaru];

            if ($statusBaru === 'dipanggil') {
                $update['dipanggil_at'] = now();
            } elseif ($statusBaru === 'selesai') {
                $update['selesai_at'] = now();
            }

            $antrian->update($update);

            // Kirim notifikasi ke pasien
            $pesanMap = [
                'dipanggil'  => "Nomor antrian #{$antrian->nomor_antrian} Anda sedang dipanggil. Segera menuju ruang periksa.",
                'selesai'    => "Pemeriksaan antrian #{$antrian->nomor_antrian} telah selesai. Terima kasih telah menggunakan SeuramoeSihat.",
                'dibatalkan' => "Antrian #{$antrian->nomor_antrian} Anda telah dibatalkan oleh admin.",
            ];

            if (isset($pesanMap[$statusBaru]) && $antrian->user_id) {
                Notifikasi::create([
                    'user_id'  => $antrian->user_id,
                    'kategori' => 'antrian',
                    'judul'    => 'Update status antrian',
                    'pesan'    => $pesanMap[$statusBaru],
                    'icon'     => $statusBaru === 'dipanggil' ? '🔔' : ($statusBaru === 'selesai' ? '✅' : '❌'),
                    'bg_class' => $statusBaru === 'dipanggil' ? 'bg-yellow-50' : ($statusBaru === 'selesai' ? 'bg-emerald-50' : 'bg-red-50'),
                    'aksi'     => 'Lihat Antrian',
                    'aksi_url' => '/antrian',
                ]);
            }
        });

        return response()->json([
            'message' => 'Status antrian berhasil diperbarui',
            'data'    => $this->formatAntrian($antrian->fresh(['user', 'dokter.faskes'])),
        ]);
    }

    /**
     * DELETE /api/admin/antrian/{id}
     */
    public function deleteAntrian(int $id): JsonResponse
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->delete();

        return response()->json(['message' => 'Antrian berhasil dihapus']);
    }

    /**
     * POST /api/admin/antrian/{id}/rekam-medis
     * Buat rekam medis dari antrian yang selesai
     */
    public function buatRekamMedis(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'diagnosa'       => 'required|string',
            'catatan_dokter' => 'nullable|string',
            'resep'          => 'nullable|array',
            'resep.*.nama_obat'    => 'required|string',
            'resep.*.dosis'        => 'nullable|string',
            'resep.*.aturan_pakai' => 'nullable|string',
        ]);

        $antrian = Antrian::with('dokter')->findOrFail($id);

        if ($antrian->status !== 'selesai') {
            return response()->json(['message' => 'Rekam medis hanya bisa dibuat untuk antrian yang sudah selesai'], 422);
        }

        $rm = \App\Models\RekamMedis::firstOrCreate(
            ['antrian_id' => $antrian->id],
            [
                'user_id'           => $antrian->user_id,
                'dokter_id'         => $antrian->dokter_id,
                'tanggal_kunjungan' => $antrian->tanggal,
                'keluhan'           => $antrian->keluhan,
                'diagnosa'          => $request->diagnosa,
                'catatan_dokter'    => $request->catatan_dokter,
                'status'            => 'selesai',
            ]
        );

        if ($rm->wasRecentlyCreated && $request->filled('resep')) {
            foreach ($request->resep as $r) {
                \App\Models\ResepObat::create([
                    'rekam_medis_id' => $rm->id,
                    'nama_obat'      => $r['nama_obat'],
                    'dosis'          => $r['dosis'] ?? '',
                    'aturan_pakai'   => $r['aturan_pakai'] ?? '',
                ]);
            }
        }

        // Kirim notifikasi ke pasien jika rekam medis baru dibuat
        if ($rm->wasRecentlyCreated && $antrian->user_id) {
            $dokterNama = $antrian->dokter?->nama ?? 'dokter';
            $tanggal    = \Carbon\Carbon::parse($antrian->tanggal)->isoFormat('D MMMM YYYY');

            Notifikasi::create([
                'user_id'  => $antrian->user_id,
                'kategori' => 'kesehatan',
                'judul'    => 'Rekam medis tersedia',
                'pesan'    => "Rekam medis kunjungan Anda pada {$tanggal} dengan {$dokterNama} telah diisi. Diagnosa: {$request->diagnosa}.",
                'icon'     => '📄',
                'bg_class' => 'bg-purple-50',
                'aksi'     => 'Lihat Rekam Medis',
                'aksi_url' => '/rekam-medis',
            ]);
        }

        return response()->json([
            'message' => $rm->wasRecentlyCreated ? 'Rekam medis berhasil dibuat' : 'Rekam medis sudah ada',
            'data'    => ['id' => $rm->id],
        ], 201);
    }

    // ─── Users ────────────────────────────────────────────────────────────────

    /**
     * GET /api/admin/users
     */
    public function indexUsers(Request $request): JsonResponse
    {
        $query = User::where('role', 'pasien')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->paginate(15);

        return response()->json([
            'data' => $users->getCollection()->map(fn($u) => [
                'id'             => $u->id,
                'nama'           => $u->nama,
                'email'          => $u->email,
                'no_hp'          => $u->no_hp,
                'role'           => $u->role,
                'total_antrian'  => Antrian::where('user_id', $u->id)->count(),
                'created_at'     => $u->created_at->isoFormat('D MMM YYYY'),
            ]),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    /**
     * DELETE /api/admin/users/{id}
     */
    public function deleteUser(int $id): JsonResponse
    {
        $user = User::where('role', 'pasien')->findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }

    // ─── Dokter ───────────────────────────────────────────────────────────────

    /**
     * GET /api/admin/dokter
     */
    public function indexDokter(Request $request): JsonResponse
    {
        $query = Dokter::with(['faskes', 'jadwal'])->orderBy('nama');

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $dokters = $query->paginate(15);

        return response()->json([
            'data' => $dokters->getCollection()->map(fn($d) => $this->formatDokter($d)),
            'meta' => [
                'current_page' => $dokters->currentPage(),
                'last_page'    => $dokters->lastPage(),
                'total'        => $dokters->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/faskes  — daftar faskes untuk dropdown
     */
    public function indexFaskes(): JsonResponse
    {
        $faskes = \App\Models\Faskes::orderBy('nama')->get(['id', 'nama', 'tipe', 'wilayah']);
        return response()->json(['data' => $faskes]);
    }

    /**
     * POST /api/admin/dokter
     */
    public function storeDokter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'faskes_id'       => 'required|exists:faskes,id',
            'nama'            => 'required|string|max:255',
            'spesialis'       => 'required|string|max:255',
            'kategori'        => 'required|in:Dokter Umum,Spesialis Anak,Gigi,Kandungan,Penyakit Dalam',
            'pengalaman'      => 'nullable|string|max:50',
            'jumlah_pasien'   => 'nullable|string|max:50',
            'tentang'         => 'nullable|string',
            'keahlian'        => 'nullable|array',
            'keahlian.*'      => 'string|max:100',
            'avatar_bg'       => 'nullable|string|max:10',
            'avatar_color'    => 'nullable|string|max:10',
            'jadwal'          => 'nullable|array',
            'jadwal.*.hari'   => 'required|integer|between:0,6',
            'jadwal.*.jam_mulai'  => 'required|date_format:H:i',
            'jadwal.*.jam_selesai'=> 'required|date_format:H:i',
            'jadwal.*.kuota_per_hari' => 'nullable|integer|min:1',
        ]);

        // Auto-generate inisial dari nama
        $kata = preg_split('/\s+/', preg_replace('/[^A-Za-z\s]/', '', $validated['nama']));
        $inisial = strtoupper(implode('', array_map(fn($k) => substr($k, 0, 1), array_slice(array_filter($kata), 0, 2))));

        $dokter = DB::transaction(function () use ($validated, $inisial) {
            $dokter = Dokter::create([
                'faskes_id'      => $validated['faskes_id'],
                'nama'           => $validated['nama'],
                'inisial'        => $inisial ?: 'DR',
                'spesialis'      => $validated['spesialis'],
                'kategori'       => $validated['kategori'],
                'pengalaman'     => $validated['pengalaman'] ?? null,
                'jumlah_pasien'  => $validated['jumlah_pasien'] ?? '0',
                'tentang'        => $validated['tentang'] ?? null,
                'keahlian'       => $validated['keahlian'] ?? [],
                'avatar_bg'      => $validated['avatar_bg'] ?? '#E1F5EE',
                'avatar_color'   => $validated['avatar_color'] ?? '#0F6E56',
                'rating'         => 0.0,
                'total_ulasan'   => 0,
                'aktif'          => true,
            ]);

            // Simpan jadwal
            foreach ($validated['jadwal'] ?? [] as $j) {
                \App\Models\JadwalDokter::create([
                    'dokter_id'         => $dokter->id,
                    'hari'              => $j['hari'],
                    'jam_mulai'         => $j['jam_mulai'] . ':00',
                    'jam_selesai'       => $j['jam_selesai'] . ':00',
                    'kuota_per_hari'    => $j['kuota_per_hari'] ?? 20,
                    'durasi_per_pasien' => 15,
                    'aktif'             => true,
                ]);
            }

            return $dokter;
        });

        return response()->json([
            'message' => 'Dokter berhasil ditambahkan',
            'data'    => $this->formatDokter($dokter->load('faskes')),
        ], 201);
    }

    /**
     * PUT /api/admin/dokter/{id}
     */
    public function updateDokter(int $id, Request $request): JsonResponse
    {
        $dokter = Dokter::findOrFail($id);

        $validated = $request->validate([
            'faskes_id'       => 'required|exists:faskes,id',
            'nama'            => 'required|string|max:255',
            'spesialis'       => 'required|string|max:255',
            'kategori'        => 'required|in:Dokter Umum,Spesialis Anak,Gigi,Kandungan,Penyakit Dalam',
            'pengalaman'      => 'nullable|string|max:50',
            'jumlah_pasien'   => 'nullable|string|max:50',
            'tentang'         => 'nullable|string',
            'keahlian'        => 'nullable|array',
            'keahlian.*'      => 'string|max:100',
            'avatar_bg'       => 'nullable|string|max:10',
            'avatar_color'    => 'nullable|string|max:10',
            'jadwal'          => 'nullable|array',
            'jadwal.*.hari'   => 'required|integer|between:0,6',
            'jadwal.*.jam_mulai'  => 'required|date_format:H:i',
            'jadwal.*.jam_selesai'=> 'required|date_format:H:i',
            'jadwal.*.kuota_per_hari' => 'nullable|integer|min:1',
        ]);

        $kata = preg_split('/\s+/', preg_replace('/[^A-Za-z\s]/', '', $validated['nama']));
        $inisial = strtoupper(implode('', array_map(fn($k) => substr($k, 0, 1), array_slice(array_filter($kata), 0, 2))));

        DB::transaction(function () use ($dokter, $validated, $inisial) {
            $dokter->update([
                'faskes_id'     => $validated['faskes_id'],
                'nama'          => $validated['nama'],
                'inisial'       => $inisial ?: $dokter->inisial,
                'spesialis'     => $validated['spesialis'],
                'kategori'      => $validated['kategori'],
                'pengalaman'    => $validated['pengalaman'] ?? $dokter->pengalaman,
                'jumlah_pasien' => $validated['jumlah_pasien'] ?? $dokter->jumlah_pasien,
                'tentang'       => $validated['tentang'] ?? $dokter->tentang,
                'keahlian'      => $validated['keahlian'] ?? $dokter->keahlian,
                'avatar_bg'     => $validated['avatar_bg'] ?? $dokter->avatar_bg,
                'avatar_color'  => $validated['avatar_color'] ?? $dokter->avatar_color,
            ]);

            // Reset dan buat ulang jadwal
            if (isset($validated['jadwal'])) {
                \App\Models\JadwalDokter::where('dokter_id', $dokter->id)->delete();
                foreach ($validated['jadwal'] as $j) {
                    \App\Models\JadwalDokter::create([
                        'dokter_id'         => $dokter->id,
                        'hari'              => $j['hari'],
                        'jam_mulai'         => $j['jam_mulai'] . ':00',
                        'jam_selesai'       => $j['jam_selesai'] . ':00',
                        'kuota_per_hari'    => $j['kuota_per_hari'] ?? 20,
                        'durasi_per_pasien' => 15,
                        'aktif'             => true,
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Data dokter berhasil diperbarui',
            'data'    => $this->formatDokter($dokter->fresh('faskes')),
        ]);
    }

    /**
     * DELETE /api/admin/dokter/{id}
     */
    public function deleteDokter(int $id): JsonResponse
    {
        $dokter = Dokter::findOrFail($id);
        $dokter->delete();

        return response()->json(['message' => 'Dokter berhasil dihapus']);
    }

    /**
     * PATCH /api/admin/dokter/{id}/tersedia
     * Body: { tersedia: true|false }
     */
    public function toggleDokter(int $id, Request $request): JsonResponse
    {
        $request->validate(['tersedia' => 'required|boolean']);
        $dokter = Dokter::findOrFail($id);
        $dokter->update(['aktif' => $request->tersedia]);

        return response()->json(['message' => 'Status dokter diperbarui']);
    }

    // ─── Helper format dokter ─────────────────────────────────────────────────

    private function formatDokter(Dokter $d): array
    {
        return [
            'id'            => $d->id,
            'nama'          => $d->nama,
            'inisial'       => $d->inisial,
            'spesialis'     => $d->spesialis,
            'kategori'      => $d->kategori,
            'pengalaman'    => $d->pengalaman,
            'jumlah_pasien' => $d->jumlah_pasien,
            'tentang'       => $d->tentang,
            'keahlian'      => $d->keahlian ?? [],
            'avatar_bg'     => $d->avatar_bg,
            'avatar_color'  => $d->avatar_color,
            'rating'        => $d->rating,
            'total_ulasan'  => $d->total_ulasan,
            'aktif'         => $d->aktif,
            'faskes_id'     => $d->faskes_id,
            'faskes'        => $d->faskes?->nama,
            'faskes_wilayah'=> $d->faskes?->wilayah,
            'jadwal'        => $d->jadwal->map(fn($j) => [
                'hari'         => $j->hari,
                'jam_mulai'    => substr($j->jam_mulai, 0, 5),
                'jam_selesai'  => substr($j->jam_selesai, 0, 5),
                'kuota_per_hari' => $j->kuota_per_hari,
            ])->toArray(),
        ];
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function formatAntrian(Antrian $a): array
    {
        return [
            'id'            => $a->id,
            'nomor_antrian' => $a->nomor_antrian,
            'tanggal'       => $a->tanggal?->isoFormat('D MMMM YYYY'),
            'tanggal_raw'   => $a->tanggal?->toDateString(),
            'jam'           => substr($a->jam ?? '', 0, 5),
            'status'        => $a->status,
            'nama_pasien'   => $a->nama_pasien,
            'no_hp'         => $a->no_hp,
            'keluhan'       => $a->keluhan,
            'tipe_pasien'   => $a->tipe_pasien,
            'dokter'        => [
                'id'        => $a->dokter?->id,
                'nama'      => $a->dokter?->nama,
                'spesialis' => $a->dokter?->spesialis,
            ],
            'faskes'        => $a->dokter?->faskes?->nama,
            'user'          => [
                'id'    => $a->user?->id,
                'nama'  => $a->user?->nama,
                'email' => $a->user?->email,
            ],
            'dipanggil_at'  => $a->dipanggil_at?->format('H:i'),
            'selesai_at'    => $a->selesai_at?->format('H:i'),
        ];
    }
}
