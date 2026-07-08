<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * MIDDLEWARE untuk RBAC (Role-Based Access Control).
 *
 * Dipakai di routes/api.php seperti ini:
 *   Route::middleware('role:owner,kasir')->group(...)
 * Artinya: hanya user dengan role "owner" ATAU "kasir" yang boleh lewat.
 *
 * $roles di bawah ini otomatis terisi dari teks setelah tanda ":" pada
 * pemanggilan middleware tadi (fitur bawaan Laravel).
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        // Middleware ini selalu dipasang SETELAH JwtMiddleware di routes/api.php,
        // jadi di titik ini token sudah pasti valid dan user sudah bisa diambil.
        $user = JWTAuth::user();

        // in_array: cek apakah role user ada di dalam daftar $roles yang diizinkan
        if (! $user || ! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Akses Terkunci. Kamu tidak memiliki izin untuk mengakses modul ini.',
                'code' => 'ACCESS_LOCKED',
            ], 403); // 403 = Forbidden (beda dengan 401 Unauthenticated)
        }

        return $next($request);
    }
}
