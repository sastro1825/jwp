<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategoris'; // Nama tabel untuk kategori

    /**
     * The attributes that are mass assignable - untuk kategori produk kesehatan
     */
    protected $fillable = [
        'nama',
        'gambar',
        'deskripsi', 
        'harga', // Harga patokan kategori
        'category_type' // Jenis kategori kesehatan
    ];
    
    // Relasi ke produk-produk dalam kategori ini
    public function produks()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
    
    // Method untuk mendapatkan label kategori yang user-friendly
    public function getCategoryTypeLabel()
    {
        $labels = [
            'obat-obatan' => 'Obat-obatan',
            'alat-kesehatan' => 'Alat Kesehatan', 
            'suplemen-kesehatan' => 'Suplemen Kesehatan',
            'kesehatan-pribadi' => 'Kesehatan Pribadi',
            'perawatan-kecantikan' => 'Perawatan & Kecantikan',
            'gizi-nutrisi' => 'Gizi & Nutrisi Medis',
            'kesehatan-lingkungan' => 'Kesehatan Lingkungan'
        ];
        
        return $labels[$this->category_type] ?? $this->category_type;
    }
}