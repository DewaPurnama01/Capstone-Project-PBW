<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Login menggunakan username & password, mengembalikan JWT.
     * Frontend menyimpan token ini di localStorage (lihat laporan bagian 4.1).
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Username dan password wajib diisi.', 'errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('username', 'password');

        $user = User::where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Username atau password salah.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Akun kamu dinonaktifkan. Hubungi Owner.'], 403);
        }

        $token = JWTAuth::fromUser($user);
        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => $this->formatUser($user),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->formatUser($request->user())]);
    }

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function refresh(Request $request)
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken());

        return response()->json([
            'token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

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
