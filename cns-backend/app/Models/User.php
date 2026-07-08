<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * MODEL = kelas PHP yang mewakili satu baris data di tabel database.
 * Ini konsep ORM (Object-Relational Mapping): kita menulis kode PHP
 * biasa (mis. $user->name), Laravel yang menerjemahkannya jadi query SQL.
 *
 * User implements JWTSubject supaya bisa dipakai untuk membuat token JWT
 * saat login (lihat AuthController::login).
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // $fillable = daftar kolom yang boleh diisi lewat mass-assignment,
    // contoh: User::create(['name' => ..., 'username' => ...])
    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'avatar_initial', 'is_active', 'last_login_at',
    ];

    // $hidden = kolom yang TIDAK ikut dikirim ke frontend saat data user
    // diubah jadi JSON (demi keamanan, password tidak boleh bocor ke API response)
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // Dipanggil otomatis oleh package JWT saat membuat token: berisi ID user
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Data tambahan yang disisipkan ke dalam token JWT (selain ID),
    // supaya frontend/middleware bisa langsung tahu role user tanpa query ulang
    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'name' => $this->name,
        ];
    }

    // Helper method sederhana untuk cek role, dipakai di beberapa tempat
    public function isOwner(): bool { return $this->role === 'owner'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isKasir(): bool { return $this->role === 'kasir'; }
}
