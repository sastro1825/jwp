<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// Kelas untuk mengelola kategori toko
class TokoKategori extends Model
{
    use HasFactory;

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'toko_id', 
        'nama',
        'deskripsi',
        'harga', 
        'gambar', 
        'category_type', 
    ];

    // Konversi tipe data untuk atribut harga
    protected $casts = [
        'harga' => 'float', // Mengubah harga menjadi tipe float
    ];

    // Relasi ke model Toko
    public function toko()
    {
        return $this->belongsTo(Toko::class); // Mengembalikan relasi belongsTo ke model Toko
    }

    // Mendapatkan label kategori yang ramah pengguna
    public function getCategoryTypeLabel()
    {
        // Daftar label untuk tipe kategori
        $labels = [
            'alat-kesehatan' => 'Alat Kesehatan',
            'obat-obatan' => 'Obat-obatan',
            'suplemen-kesehatan' => 'Suplemen Kesehatan',
            'perawatan-kecantikan' => 'Perawatan & Kecantikan',
            'kesehatan-pribadi' => 'Kesehatan Pribadi',
        ];

        // Mengembalikan label atau format ucwords jika label tidak ditemukan
        return $labels[$this->category_type] ?? ucwords(str_replace('-', ' ', $this->category_type));
    }

    // Mendapatkan harga dalam format Rupiah
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.'); // Mengembalikan harga dengan format Rp dan pemisah ribuan
    }

    // Mendapatkan deskripsi pendek
    public function getShortDescriptionAttribute()
    {
        // Mengembalikan deskripsi terpotong atau pesan default jika kosong
        return $this->deskripsi ? Str::limit($this->deskripsi, 100) : 'Tidak ada deskripsi';
    }
}