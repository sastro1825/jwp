<?php

namespace App\Models;

// Menggunakan trait dan kelas yang diperlukan
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas untuk mengelola data produk
class Produk extends Model
{
    // Menggunakan trait HasFactory untuk membuat data dummy
    use HasFactory;

    // Daftar kolom yang dapat diisi secara massal
    protected $fillable = [
        'nama', 
        'id_produk',
        'harga',
        'deskripsi',
        'kategori_id',
        'toko_id', 
    ];

    // Mendefinisikan relasi dengan model Kategori
    /** @method Relasi ke model Kategori */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class); // Mengembalikan relasi belongsTo ke Kategori
    }

    // Mendefinisikan relasi dengan model Toko
    /** @method Relasi ke model Toko */
    public function toko()
    {
        return $this->belongsTo(Toko::class); // Mengembalikan relasi belongsTo ke Toko
    }
}