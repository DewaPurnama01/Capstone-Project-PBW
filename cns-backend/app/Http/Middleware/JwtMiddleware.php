<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    /**
     * Memvalidasi JWT pada setiap request ke endpoint terproteksi.
     * Jika token hilang/invalid/kadaluarsa -> 401 (frontend akan redirect ke /login).
     */
    public function handle(Request $request, Closure $next)
    {
        try {
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

        return $next($request);
    }
}
