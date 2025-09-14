<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds - untuk populate data kategori kesehatan dengan tipe
     */
    public function run(): void
    {
        // Kategori Alat Kesehatan
        Kategori::create([
            'nama' => 'Tensimeter Digital', 
            'deskripsi' => 'Alat ukur tekanan darah digital untuk monitoring kesehatan jantung',
            'harga' => 180000, // Harga patokan kategori
            'category_type' => 'alat-kesehatan'
        ]);
        
        Kategori::create([
            'nama' => 'Thermometer Digital', 
            'deskripsi' => 'Alat ukur suhu badan digital dan infrared untuk pemeriksaan',
            'harga' => 45000,
            'category_type' => 'alat-kesehatan'
        ]);
        
        Kategori::create([
            'nama' => 'Kursi Roda', 
            'deskripsi' => 'Alat bantu mobilitas untuk pasien dan lansia',
            'harga' => 1300000,
            'category_type' => 'alat-kesehatan'
        ]);

        // Kategori Obat-obatan
        Kategori::create([
            'nama' => 'Obat Demam', 
            'deskripsi' => 'Obat penurun demam dan pereda nyeri',
            'harga' => 25000,
            'category_type' => 'obat-obatan'
        ]);

        // Kategori Suplemen
        Kategori::create([
            'nama' => 'Vitamin C', 
            'deskripsi' => 'Suplemen vitamin C untuk meningkatkan daya tahan tubuh',
            'harga' => 50000,
            'category_type' => 'suplemen-kesehatan'
        ]);

        // Kategori Kesehatan Pribadi
        Kategori::create([
            'nama' => 'Masker Medis', 
            'deskripsi' => 'Masker medis disposable dan N95 untuk perlindungan',
            'harga' => 15000,
            'category_type' => 'kesehatan-pribadi'
        ]);

        // Kategori Perawatan & Kecantikan
        Kategori::create([
            'nama' => 'Hand Sanitizer', 
            'deskripsi' => 'Pembersih tangan berbahan alkohol untuk kebersihan',
            'harga' => 20000,
            'category_type' => 'perawatan-kecantikan'
        ]);
    }
}