<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Kelas untuk mengelola model Kategori
class Kategori extends Model
{
    use HasFactory;

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'nama',
        'deskripsi',
        'harga',
        'gambar',
        'category_type',
    ];

    // Atribut yang perlu di-cast ke tipe tertentu
    protected $casts = [
        'harga' => 'float', // Mengubah tipe harga ke float untuk menghindari warning
    ];

    // Relasi dengan model Produk (satu kategori memiliki banyak produk)
    public function produks()
    {
        return $this->hasMany(Produk::class);
    }

    // Relasi dengan model Keranjang (satu kategori memiliki banyak keranjang)
    public function keranjangs()
    {
        return $this->hasMany(Keranjang::class);
    }

    // Mendapatkan label ramah pengguna untuk tipe kategori
    public function getCategoryTypeLabel()
    {
        $labels = [
            'alat-kesehatan' => 'Alat Kesehatan',
            'obat-obatan' => 'Obat-obatan',
            'suplemen-kesehatan' => 'Suplemen Kesehatan',
            'perawatan-kecantikan' => 'Perawatan & Kecantikan',
            'kesehatan-pribadi' => 'Kesehatan Pribadi',
        ];

        // Mengembalikan label atau format kapital jika tipe tidak ditemukan
        return $labels[$this->category_type] ?? ucwords(str_replace('-', ' ', $this->category_type));
    }

    // Mendapatkan ikon untuk tipe kategori
    public function getCategoryIcon()
    {
        $icons = [
            'alat-kesehatan' => 'bi-heart-pulse',
            'obat-obatan' => 'bi-capsule',
            'suplemen-kesehatan' => 'bi-capsule-pill',
            'perawatan-kecantikan' => 'bi-palette',
            'kesehatan-pribadi' => 'bi-person-check',
        ];

        // Mengembalikan ikon atau ikon default jika tipe tidak ditemukan
        return $icons[$this->category_type] ?? 'bi-tag';
    }

    // Mendapatkan warna badge untuk tipe kategori
    public function getCategoryBadgeColor()
    {
        $colors = [
            'alat-kesehatan' => 'primary',
            'obat-obatan' => 'success',
            'suplemen-kesehatan' => 'info',
            'perawatan-kecantikan' => 'warning',
            'kesehatan-pribadi' => 'secondary',
        ];

        // Mengembalikan warna atau warna default jika tipe tidak ditemukan
        return $colors[$this->category_type] ?? 'dark';
    }

    // Scope untuk memfilter berdasarkan tipe kategori
    public function scopeByType($query, $type)
    {
        return $query->where('category_type', $type);
    }

    // Scope untuk memfilter kategori berdasarkan rentang harga
    public function scopeInPriceRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('harga', '>=', floatval($min)); // Filter harga minimum
        }
        
        if ($max !== null) {
            $query->where('harga', '<=', floatval($max)); // Filter harga maksimum
        }
        
        return $query;
    }

    // Scope untuk memfilter kategori yang memiliki gambar
    public function scopeWithImage($query)
    {
        return $query->whereNotNull('gambar');
    }

    // Accessor untuk memformat harga dengan format Rupiah
    public function getFormattedHargaAttribute()
    {
        $harga = floatval($this->harga ?? 0); // Mengubah harga ke float dengan default 0
        return 'Rp ' . number_format($harga, 0, ',', '.');
    }

    // Accessor untuk mendapatkan URL gambar
    public function getImageUrlAttribute()
    {
        if ($this->gambar) {
            return asset('storage/' . $this->gambar); // Mengembalikan URL gambar dari storage
        }
        
        return asset('images/no-image.png'); // Mengembalikan gambar default jika tidak ada
    }

    // Memeriksa apakah kategori memiliki produk
    public function hasProducts()
    {
        return $this->produks()->exists();
    }

    // Mendapatkan jumlah total produk dalam kategori
    public function getTotalProducts()
    {
        return $this->produks()->count();
    }

    // Mendapatkan rentang harga produk dalam kategori
    public function getProductPriceRange()
    {
        $min = $this->produks()->min('harga'); // Harga minimum produk
        $max = $this->produks()->max('harga'); // Harga maksimum produk
        
        // Mengubah ke float untuk keamanan tipe
        $minFloat = $min ? floatval($min) : 0;
        $maxFloat = $max ? floatval($max) : 0;
        
        // Mengembalikan array dengan harga minimum, maksimum, dan format Rupiah
        return [
            'min' => $minFloat,
            'max' => $maxFloat,
            'formatted' => ($minFloat && $maxFloat) 
                ? 'Rp ' . number_format($minFloat, 0, ',', '.') . ' - Rp ' . number_format($maxFloat, 0, ',', '.')
                : null
        ];
    }

    // Mendapatkan kategori populer berdasarkan jumlah di keranjang
    public static function getPopularCategories($limit = 5)
    {
        return self::withCount('keranjangs') // Menghitung jumlah keranjang
            ->orderBy('keranjangs_count', 'desc') // Mengurutkan berdasarkan jumlah keranjang
            ->limit($limit) // Membatasi jumlah hasil
            ->get();
    }

    // Scope untuk mencari kategori berdasarkan nama atau deskripsi
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%') // Pencarian pada nama
              ->orWhere('deskripsi', 'like', '%' . $search . '%'); // Pencarian pada deskripsi
        });
    }

    // Scope untuk mendapatkan kategori terbaru
    public function scopeLatest($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit); // Mengurutkan berdasarkan tanggal pembuatan
    }

    // Scope untuk mengurutkan kategori berdasarkan harga
    public function scopeOrderByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('harga', $direction); // Mengurutkan berdasarkan harga
    }

    // Accessor untuk mendapatkan deskripsi singkat
    public function getShortDescriptionAttribute()
    {
        if ($this->deskripsi) {
            return Str::limit($this->deskripsi, 100); // Membatasi deskripsi hingga 100 karakter
        }
        
        return 'Tidak ada deskripsi'; // Default jika tidak ada deskripsi
    }

    // Mendapatkan harga dalam format numerik tanpa simbol
    public function getDisplayPrice()
    {
        $harga = floatval($this->harga ?? 0); // Mengubah harga ke float
        return number_format($harga, 0, ',', '.');
    }

    // Mendapatkan harga sebagai integer untuk perhitungan
    public function getPriceAsInt()
    {
        return intval($this->harga ?? 0); // Mengembalikan harga sebagai integer
    }

    // Memeriksa apakah kategori gratis
    public function isFree()
    {
        return floatval($this->harga ?? 0) <= 0; // True jika harga 0 atau kurang
    }

    // Mendapatkan kategori dengan harga terendah
    public static function getCheapest()
    {
        return self::where('harga', '>', 0)->orderBy('harga', 'asc')->first(); // Mengembalikan kategori termurah
    }

    // Mendapatkan kategori dengan harga tertinggi
    public static function getMostExpensive()
    {
        return self::orderBy('harga', 'desc')->first(); // Mengembalikan kategori termahal
    }

    // Mendapatkan rata-rata harga semua kategori
    public static function getAveragePrice()
    {
        $avg = self::avg('harga'); // Menghitung rata-rata harga
        return $avg ? floatval($avg) : 0; // Mengembalikan float atau 0 jika null
    }

    // Scope untuk memfilter kategori dalam anggaran tertentu
    public function scopeWithinBudget($query, $budget)
    {
        return $query->where('harga', '<=', floatval($budget)); // Filter kategori berdasarkan anggaran
    }

    // Mendapatkan kategori berdasarkan rentang harga
    public static function getByPriceRange($min, $max)
    {
        return self::whereBetween('harga', [floatval($min), floatval($max)])->get(); // Mengembalikan kategori dalam rentang harga
    }

    // Method boot untuk menangani event model
    protected static function boot()
    {
        parent::boot();

        // Event saat kategori dihapus
        static::deleting(function ($kategori) {
            if ($kategori->gambar && Storage::disk('public')->exists($kategori->gambar)) {
                Storage::disk('public')->delete($kategori->gambar); // Menghapus gambar dari storage
            }
        });

        // Event saat kategori disimpan
        static::saving(function ($kategori) {
            if ($kategori->harga === null) {
                $kategori->harga = 0; // Mengatur harga ke 0 jika null
            }
            
            if ($kategori->harga < 0) {
                $kategori->harga = 0; // Memastikan harga tidak negatif
            }
        });
    }

    // Mutator untuk memastikan harga selalu bertipe float
    public function setHargaAttribute($value)
    {
        $this->attributes['harga'] = $value !== null ? floatval($value) : 0; // Mengatur harga sebagai float
    }

    // Accessor untuk memastikan harga selalu bertipe float
    public function getHargaAttribute($value)
    {
        return $value !== null ? floatval($value) : 0; // Mengembalikan harga sebagai float
    }
}