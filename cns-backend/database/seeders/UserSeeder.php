<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Dewi Wijaya',
            'username' => 'owner',
            'email' => 'owner@cafecns.id',
            'password' => Hash::make('owner2026'),
            'role' => 'owner',
            'avatar_initial' => 'DW',
        ]);

        User::create([
            'name' => 'Dani Admin',
            'username' => 'admin',
            'email' => 'admin@cafecns.id',
            'password' => Hash::make('admin2026'),
            'role' => 'admin',
            'avatar_initial' => 'DA',
        ]);

        User::create([
            'name' => 'Rini Kasir',
            'username' => 'kasir',
            'email' => 'kasir@cafecns.id',
            'password' => Hash::make('kasir2026'),
            'role' => 'kasir',
            'avatar_initial' => 'RK',
        ]);
    }
}
