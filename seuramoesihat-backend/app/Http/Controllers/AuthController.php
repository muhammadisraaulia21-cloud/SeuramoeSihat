<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\ProfilKesehatan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * POST /api/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama'     => 'required|string|max:255',
            'no_hp'    => 'nullable|string|max:20',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'nama'     => $validated['nama'],
                'no_hp'    => $validated['no_hp'] ?? null,
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'pasien',
            ]);

            // Buat profil kesehatan kosong
            ProfilKesehatan::create(['user_id' => $user->id]);

            // Notifikasi selamat datang
            Notifikasi::create([
                'user_id'  => $user->id,
                'kategori' => 'sistem',
                'judul'    => 'Selamat datang di SeuramoeSihat!',
                'pesan'    => 'Akun Anda berhasil dibuat. Mulai booking antrian dokter terdekat sekarang.',
                'icon'     => '🏥',
                'bg_class' => 'bg-purple-50',
                'aksi'     => 'Cari Dokter',
                'aksi_url' => '/cari-dokter',
            ]);

            return $user;
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ], 201);
    }

    /**
     * POST /api/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        // Hapus token lama (opsional — bisa diaktifkan untuk single session)
        // $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }

    /**
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('profilKesehatan');

        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Selalu return 200 agar tidak bocorkan info email terdaftar
        if ($user) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                ['token' => Hash::make($otp), 'created_at' => now()]
            );

            // TODO: kirim OTP via email/SMS
            // Mail::to($user->email)->send(new OtpMail($otp));

            // Untuk development, kembalikan OTP di response
            if (config('app.debug')) {
                return response()->json([
                    'message' => 'Kode OTP telah dikirim ke email Anda',
                    'otp_debug' => $otp, // hapus di production
                ]);
            }
        }

        return response()->json([
            'message' => 'Jika email terdaftar, kode OTP akan dikirim',
        ]);
    }

    /**
     * POST /api/verify-otp
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record) {
            return response()->json(['message' => 'OTP tidak valid'], 422);
        }

        // OTP expired setelah 10 menit
        if (now()->diffInMinutes($record->created_at) > 10) {
            return response()->json(['message' => 'OTP sudah kadaluarsa'], 422);
        }

        if (! Hash::check($request->otp, $record->token)) {
            return response()->json(['message' => 'Kode OTP salah'], 422);
        }

        return response()->json(['message' => 'OTP valid', 'email' => $request->email]);
    }

    /**
     * POST /api/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|string|size:6',
            'password' => ['required', Password::min(8), 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->otp, $record->token)) {
            return response()->json(['message' => 'OTP tidak valid'], 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $user->tokens()->delete(); // paksa logout semua sesi

        return response()->json(['message' => 'Password berhasil diubah']);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function formatUser(User $user): array
    {
        $profil = $user->profilKesehatan;

        return [
            'id'             => $user->id,
            'nama'           => $user->nama,
            'nik'            => $user->nik,
            'email'          => $user->email,
            'no_hp'          => $user->no_hp,
            'alamat'         => $user->alamat,
            'tanggal_lahir'  => $user->tanggal_lahir?->format('d F Y'),
            'role'           => $user->role,
            'profil_kesehatan' => $profil ? [
                'golongan_darah'   => $profil->golongan_darah,
                'berat_badan'      => $profil->berat_badan,
                'tinggi_badan'     => $profil->tinggi_badan,
                'alergi'           => $profil->alergi,
                'riwayat_penyakit' => $profil->riwayat_penyakit,
            ] : null,
        ];
    }
}
