<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;
use App\Models\Toko;
use App\Models\Kategori;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds dengan pengecekan toko dan kategori yang ada
     */
    public function run(): void
    {
        // Ambil toko pertama yang ada
        $toko = Toko::first();
        
        // Jika tidak ada toko, skip seeder ini
        if (!$toko) {
            return;
        }

        // Ambil kategori yang ada
        $kategoriTensimeter = Kategori::where('nama', 'Tensimeter Digital')->first();
        $kategoriThermometer = Kategori::where('nama', 'Thermometer Digital')->first();
        $kategoriKursiRoda = Kategori::where('nama', 'Kursi Roda')->first();

        // Buat produk jika kategori ada
        if ($kategoriTensimeter) {
            Produk::create([
                'nama' => 'Tensimeter Digital DrPro-012',
                'id_produk' => 'DrPro-012',
                'harga' => 180000,
                'kategori_id' => $kategoriTensimeter->id,
                'toko_id' => $toko->id,
                'deskripsi' => 'Tensimeter digital berkualitas tinggi untuk monitoring tekanan darah'
            ]);
        }

        if ($kategoriThermometer) {
            Produk::create([
                'nama' => 'Thermometer Digital DrPro-007',
                'id_produk' => 'DrPro-007',
                'harga' => 40000,
                'kategori_id' => $kategoriThermometer->id,
                'toko_id' => $toko->id,
                'deskripsi' => 'Thermometer digital akurat untuk pengukuran suhu tubuh'
            ]);
        }

        if ($kategoriKursiRoda) {
            Produk::create([
                'nama' => 'Kursi Roda Travel-016',
                'id_produk' => 'Travel-016',
                'harga' => 1300000,
                'kategori_id' => $kategoriKursiRoda->id,
                'toko_id' => $toko->id,
                'deskripsi' => 'Kursi roda travel ringan dan mudah dilipat'
            ]);
        }
    }
}