<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategoris'; // Nama tabel untuk kategori

    /**
     * The attributes that are mass assignable - untuk kategori produk
     */
    protected $fillable = [
        'nama',
        'gambar',
        'deskripsi', 
        'harga' // Tambahan field harga kategori
    ];
    
    // Relasi ke produk-produk dalam kategori ini
    public function produks()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}