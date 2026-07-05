<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class RoleMiddleware
{
    /**
     * Membatasi akses endpoint berdasarkan role user (RBAC).
     * Contoh penggunaan di route: ->middleware('role:owner,kasir')
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = JWTAuth::user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Akses Terkunci. Kamu tidak memiliki izin untuk mengakses modul ini.',
                'code' => 'ACCESS_LOCKED',
            ], 403);
        }

        return $next($request);
    }
}
