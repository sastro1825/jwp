<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        Kategori::create(['nama' => 'Tensimeter', 'deskripsi' => 'Alat ukur tekanan darah']);
        Kategori::create(['nama' => 'Thermometer', 'deskripsi' => 'Alat ukur suhu']);
        Kategori::create(['nama' => 'Kursi Roda', 'deskripsi' => 'Mobilitas']);
    }
}