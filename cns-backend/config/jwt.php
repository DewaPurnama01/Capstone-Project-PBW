<?php

return [
    'secret' => env('JWT_SECRET'),

    'keys' => [
        'public'  => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    'ttl' => env('JWT_TTL', 480), // menit, default 8 jam kerja shift

    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    'algo' => env('JWT_ALGO', 'HS256'),

    'required_claims' => [
        'iss', 'iat', 'exp', 'nbf', 'sub', 'jti',
    ],

    'persistent_claims' => [],

    'lock_subject' => true,

    'leeway' => env('JWT_LEEWAY', 0),

    'exp' => true,

    'providers' => [
        'jwt' => PHPOpenSourceSaver\JWTAuth\Providers\JWT\Lcobucci::class,
        'auth' => PHPOpenSourceSaver\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => PHPOpenSourceSaver\JWTAuth\Providers\Storage\Illuminate::class,
    ],
];
