<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    /**
     * PUT /api/profil  — update data diri
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama'          => 'sometimes|string|max:255',
            'nik'           => 'sometimes|nullable|string|max:20|unique:users,nik,' . $user->id,
            'no_hp'         => 'sometimes|nullable|string|max:20',
            'alamat'        => 'sometimes|nullable|string|max:500',
            'tanggal_lahir' => 'sometimes|nullable|date',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user'    => [
                'id'            => $user->id,
                'nama'          => $user->nama,
                'nik'           => $user->nik,
                'email'         => $user->email,
                'no_hp'         => $user->no_hp,
                'alamat'        => $user->alamat,
                'tanggal_lahir' => $user->tanggal_lahir?->format('d F Y'),
            ],
        ]);
    }

    /**
     * PUT /api/profil/kesehatan  — update data kesehatan
     */
    public function updateKesehatan(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'golongan_darah'   => 'sometimes|nullable|in:A,B,AB,O',
            'berat_badan'      => 'sometimes|nullable|numeric|min:1|max:300',
            'tinggi_badan'     => 'sometimes|nullable|numeric|min:1|max:300',
            'alergi'           => 'sometimes|nullable|string|max:500',
            'riwayat_penyakit' => 'sometimes|nullable|string|max:1000',
        ]);

        $profil = $user->profilKesehatan()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'message' => 'Data kesehatan berhasil diperbarui',
            'data'    => [
                'golongan_darah'   => $profil->golongan_darah,
                'berat_badan'      => $profil->berat_badan,
                'tinggi_badan'     => $profil->tinggi_badan,
                'alergi'           => $profil->alergi,
                'riwayat_penyakit' => $profil->riwayat_penyakit,
            ],
        ]);
    }

    /**
     * PUT /api/profil/password  — ubah password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'password_lama' => 'required|string',
            'password'      => ['required', Password::min(8), 'confirmed'],
        ]);

        if (! Hash::check($request->password_lama, $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai',
                'errors'  => ['password_lama' => ['Password lama tidak sesuai']],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Hapus semua token kecuali yang sedang dipakai
        $currentToken = $user->currentAccessToken();
        $user->tokens()->where('id', '!=', $currentToken->id)->delete();

        return response()->json(['message' => 'Password berhasil diubah']);
    }
}
