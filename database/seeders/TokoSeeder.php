<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Toko;
use App\Models\User;

class TokoSeeder extends Seeder
{
    /**
     * Run the database seeds dengan user yang valid
     */
    public function run(): void
    {
        // Ambil customer yang ada (customer pertama dari UserSeeder)
        $customer = User::where('role', 'customer')->first();
        
        // Jika tidak ada customer, gunakan admin
        if (!$customer) {
            $customer = User::where('role', 'admin')->first();
        }

        // Buat toko dengan user_id yang valid
        Toko::create([
            'nama' => 'Toko Alat Kesehatan Prima',
            'user_id' => $customer->id,
            'status' => 'approved',
            'alamat' => 'Jl. Prima Health No. 123, Jakarta',
            'deskripsi' => 'Toko alat kesehatan terpercaya dengan produk berkualitas',
            'kategori_usaha' => 'alat-kesehatan',
            'no_telepon' => '021-1234567',
        ]);
    }
}