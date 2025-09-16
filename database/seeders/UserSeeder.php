<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds dengan username untuk login
     */
    public function run(): void
    {
        // Admin dengan username admin1825 sesuai permintaan
        User::create([
            'name' => 'admin1825', // Username untuk login
            'email' => 'admin@oss.com', // Email untuk notifikasi
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'dob' => '1980-01-01',
            'gender' => 'male',
            'address' => 'Jl. Admin No. 1, Jakarta',
            'city' => 'Jakarta',
            'contact_no' => '081111111111',
            'paypal_id' => 'admin@paypal.com',
        ]);

        // Customer sample dengan username
        User::create([
            'name' => 'customer123', // Username untuk login
            'email' => 'customer@oss.com', // Email untuk notifikasi
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'dob' => '1995-06-15',
            'gender' => 'female',
            'address' => 'Jl. Customer No. 2, Surabaya',
            'city' => 'Surabaya',
            'contact_no' => '082222222222',
            'paypal_id' => 'customer@paypal.com',
        ]);

        // Customer untuk test toko dengan username
        User::create([
            'name' => 'testcustomer', // Username untuk login
            'email' => 'test.customer@oss.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'dob' => '1992-03-10',
            'gender' => 'male',
            'address' => 'Jl. Test No. 456, Bandung',
            'city' => 'Bandung',
            'contact_no' => '083333333333',
            'paypal_id' => 'test.customer@paypal.com',
        ]);
    }
}