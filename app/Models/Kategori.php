<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategoris'; // Nama tabel untuk kategori produk kesehatan

    /**
     * The attributes that are mass assignable - untuk kategori produk kesehatan OSS
     */
    protected $fillable = [
        'nama',           // Nama kategori (contoh: Tensimeter Digital)
        'gambar',         // Path gambar kategori
        'deskripsi',      // Deskripsi kategori kesehatan
        'harga',          // Harga patokan kategori
        'category_type'   // Jenis kategori kesehatan (obat-obatan, alat-kesehatan, dll)
    ];
    
    /**
     * Relasi ke produk-produk dalam kategori ini
     * Satu kategori memiliki banyak produk
     */
    public function produks()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
    
    /**
     * Method untuk mendapatkan label kategori yang user-friendly
     * Mengubah category_type menjadi label yang mudah dibaca
     */
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
        
        return $labels[$this->category_type] ?? ucwords(str_replace('-', ' ', $this->category_type));
    }

    /**
     * Accessor untuk mendapatkan URL gambar kategori
     * Otomatis menambahkan storage URL jika gambar ada
     */
    public function getGambarUrlAttribute()
    {
        if ($this->gambar) {
            return asset('storage/' . $this->gambar);
        }
        return null;
    }
}