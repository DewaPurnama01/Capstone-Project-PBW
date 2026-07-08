<?php

use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

/**
 * File ini adalah titik masuk konfigurasi utama Laravel: di sinilah
 * routing, middleware global, dan penanganan error didaftarkan.
 * Aplikasi ini "API-only" (tidak ada tampilan HTML dari Laravel) — semua
 * tampilan ada di project React terpisah (cns-frontend).
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php', // semua route didaftarkan di sini, otomatis diberi prefix /api
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CORS = izin bagi browser untuk memanggil API ini dari domain/port
        // berbeda (frontend React jalan di port 5173, beda dari backend di 8000)
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Alias middleware: memberi nama pendek ('jwt.auth', 'role') supaya
        // gampang dipakai di routes/api.php, misalnya ->middleware('role:owner')
        $middleware->alias([
            'jwt.auth' => JwtMiddleware::class,
            'role'     => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Menyeragamkan bentuk error jadi JSON yang rapi (bukan halaman HTML
        // error bawaan Laravel), supaya gampang dibaca dan ditangani frontend.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json(['message' => 'Unauthenticated. Token tidak valid atau kadaluarsa.'], 401);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'message' => 'Data yang dikirim tidak valid.',
                'errors'  => $e->errors(),
            ], 422);
        });
    })->create();
