<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database dengan urutan yang benar
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,          // Buat user dulu
            KategoriSeeder::class,      // Buat kategori
            TokoSeeder::class,          // Buat toko (butuh user)
            ProdukSeeder::class,        // Buat produk (butuh toko dan kategori)
            TokoRequestSeeder::class,   // Buat permohonan toko (butuh user)
        ]);
    }
}