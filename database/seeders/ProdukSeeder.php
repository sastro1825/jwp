<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;
use App\Models\Toko; // Asumsi toko ada

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $tokoId = Toko::first()->id ?? 1; // Ambil toko pertama atau buat dummy
        $kategori1 = 1; // ID kategori Tensimeter

        Produk::create([
            'nama' => 'Tensimeter Digital DrPro-012',
            'id_produk' => 'DrPro-012',
            'harga' => 180000,
            'kategori_id' => $kategori1,
            'toko_id' => $tokoId,
        ]);

        Produk::create([
            'nama' => 'Thermometer Digital DrPro-007',
            'id_produk' => 'DrPro-007',
            'harga' => 40000,
            'kategori_id' => 2, // Thermometer
            'toko_id' => $tokoId,
        ]);

        Produk::create([
            'nama' => 'Kursi Roda Travel-016',
            'id_produk' => 'Travel-016',
            'harga' => 1300000,
            'kategori_id' => 3, // Kursi Roda
            'toko_id' => $tokoId,
        ]);
    }
}