<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

/**
 * MIDDLEWARE = kode yang "menyaring" setiap request sebelum sampai ke
 * controller. Middleware ini bertugas memeriksa JWT (JSON Web Token).
 *
 * Cara kerja JWT secara singkat:
 * 1. Waktu login berhasil, server membuat "token" (string acak terenkripsi)
 *    yang berisi info user (lihat User::getJWTCustomClaims).
 * 2. Frontend menyimpan token itu (di localStorage) dan mengirimkannya
 *    kembali di setiap request lewat header "Authorization: Bearer <token>".
 * 3. Middleware ini membaca token tsb, memastikan tanda tangannya valid
 *    dan belum kadaluarsa, lalu mengambil data user dari dalamnya.
 * 4. Jika token tidak ada / tidak valid / sudah kadaluarsa -> request ditolak (401)
 *    sebelum sempat masuk ke controller.
 */
class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Membaca token dari header Authorization, lalu mencari user pemiliknya
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user || ! $user->is_active) {
                return response()->json(['message' => 'Akun tidak aktif atau tidak ditemukan.'], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token kadaluarsa.', 'code' => 'TOKEN_EXPIRED'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token tidak valid.', 'code' => 'TOKEN_INVALID'], 401);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token tidak ditemukan.', 'code' => 'TOKEN_ABSENT'], 401);
        }

        // $next($request) = lanjutkan ke middleware berikutnya / controller tujuan
        return $next($request);
    }
}
