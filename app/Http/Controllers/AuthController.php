<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // LOGIN

    public function index()
    {
        if (session()->has('user')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $username = strtolower(trim($request->username));

        $user = DB::table('users')->where('username', $username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['login' => 'Username atau password salah.'])
                ->withInput(['username' => $request->username]);
        }

        // Ambil inisial untuk avatar
        $words  = explode(' ', $user->name);
        $avatar = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

        session(['user' => [
            'id'     => $user->id,
            'name'   => $user->name,
            'role'   => $user->role,
            'avatar' => $avatar,
        ]]);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        session()->forget('user');
        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }

    // REGISTER

    public function registerForm()
    {
        if (session()->has('user')) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|min:3|max:100',
            'username'              => 'required|string|min:3|max:50|alpha_num|unique:users,username',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
            'role'                  => 'required|in:Owner,Admin,Kasir',
        ], [
            'name.required'                  => 'Nama lengkap wajib diisi.',
            'name.min'                       => 'Nama minimal 3 karakter.',
            'username.required'              => 'Username wajib diisi.',
            'username.min'                   => 'Username minimal 3 karakter.',
            'username.alpha_num'             => 'Username hanya boleh berisi huruf dan angka.',
            'username.unique'               => 'Username sudah digunakan.',
            'password.required'              => 'Password wajib diisi.',
            'password.min'                   => 'Password minimal 6 karakter.',
            'password.confirmed'             => 'Konfirmasi password tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'role.required'                  => 'Pilih role pengguna.',
            'role.in'                        => 'Role tidak valid.',
        ]);

        // Batasi: hanya 1 Owner per sistem
        if ($request->role === 'Owner') {
            $ownerExists = DB::table('users')->where('role', 'Owner')->exists();
            if ($ownerExists) {
                return back()
                    ->withErrors(['role' => 'Role Owner sudah ada. Hanya boleh ada satu Owner.'])
                    ->withInput();
            }
        }

        $userId = DB::table('users')->insertGetId([
            'name'       => trim($request->name),
            'username'   => strtolower(trim($request->username)),
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('login')
            ->with('success', 'Akun berhasil dibuat! Silakan login dengan username dan password Anda.');
    }
}
