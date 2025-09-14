<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds - untuk populate data kategori dengan harga
     */
    public function run(): void
    {
        // Seed data kategori Tensimeter dengan harga
        Kategori::create([
            'nama' => 'Tensimeter', 
            'deskripsi' => 'Alat ukur tekanan darah digital dan manual untuk monitoring kesehatan',
            'harga' => 180000 // Harga patokan kategori tensimeter
        ]);
        
        // Seed data kategori Thermometer dengan harga
        Kategori::create([
            'nama' => 'Thermometer', 
            'deskripsi' => 'Alat ukur suhu badan digital dan infrared untuk pemeriksaan kesehatan',
            'harga' => 45000 // Harga patokan kategori thermometer
        ]);
        
        // Seed data kategori Kursi Roda dengan harga
        Kategori::create([
            'nama' => 'Kursi Roda', 
            'deskripsi' => 'Alat bantu mobilitas untuk pasien dan lansia, manual dan elektrik',
            'harga' => 1300000 // Harga patokan kategori kursi roda
        ]);

        // Tambahan kategori lain untuk variasi
        Kategori::create([
            'nama' => 'Alat P3K', 
            'deskripsi' => 'Perlengkapan pertolongan pertama pada kecelakaan dan emergency kit',
            'harga' => 75000 // Harga patokan kategori alat P3K
        ]);

        Kategori::create([
            'nama' => 'Masker Medis', 
            'deskripsi' => 'Masker medis disposable dan N95 untuk perlindungan kesehatan',
            'harga' => 25000 // Harga patokan kategori masker
        ]);
    }
}