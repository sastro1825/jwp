<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Toko;

class TokoSeeder extends Seeder
{
    public function run(): void
    {
        Toko::create([
            'nama' => 'Toko Alat Kesehatan Prima',
            'user_id' => 2, // Customer sample ID 2
            'status' => 'approved',
            'alamat' => 'Jakarta',
        ]);
    }
}