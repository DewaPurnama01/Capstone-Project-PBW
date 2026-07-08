<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Controller = tempat menampung logika untuk satu bagian fitur.
 * AuthController menangani semua hal terkait login/logout (laporan 4.1).
 */
class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * Login pakai username & password biasa, lalu mengembalikan JWT
     * yang akan dipakai frontend untuk semua request berikutnya.
     */
    public function login(Request $request)
    {
        // 1. Validasi input: pastikan username & password memang dikirim
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Username dan password wajib diisi.', 'errors' => $validator->errors()], 422);
        }

        // 2. Cari user dengan username tsb di database
        $user = User::where('username', $request->username)->first();

        // 3. Hash::check membandingkan password yang diketik dengan hash
        //    yang tersimpan di database (password ASLI tidak pernah disimpan).
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Username atau password salah.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Akun kamu dinonaktifkan. Hubungi Owner.'], 403);
        }

        // 4. Login berhasil -> buat token JWT untuk user ini
        $token = JWTAuth::fromUser($user);
        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60, // detik
            'user' => $this->formatUser($user),
        ]);
    }

    /** GET /api/auth/me — mengambil data user yang sedang login (dari token) */
    public function me(Request $request)
    {
        return response()->json(['user' => $this->formatUser($request->user())]);
    }

    /** POST /api/auth/logout — token yang sedang dipakai langsung dibatalkan */
    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /** POST /api/auth/refresh — menukar token lama dengan token baru sebelum kadaluarsa */
    public function refresh(Request $request)
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken());

        return response()->json([
            'token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    // Helper kecil supaya bentuk data user yang dikirim ke frontend selalu konsisten
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'avatar_initial' => $user->avatar_initial ?? strtoupper(substr($user->name, 0, 2)),
        ];
    }
}
