<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin default
        User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@oss.com',
            'password' => Hash::make('password123'), // Ganti password kuat
            'role' => 'admin',
        ]);

        // Customer sample
        User::create([
            'name' => 'Pelanggan Test',
            'email' => 'customer@oss.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
        ]);
    }
}