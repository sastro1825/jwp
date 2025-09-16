<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Kategori extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama',
        'deskripsi',
        'harga',
        'gambar',
        'category_type',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'harga' => 'float', // Ubah dari decimal ke float untuk menghindari warning
    ];

    /**
     * Relationship dengan Produk
     */
    public function produks()
    {
        return $this->hasMany(Produk::class);
    }

    /**
     * Relationship dengan Keranjang
     */
    public function keranjangs()
    {
        return $this->hasMany(Keranjang::class);
    }

    /**
     * Get label untuk category type yang user-friendly
     */
    public function getCategoryTypeLabel()
    {
        $labels = [
            'alat-kesehatan' => 'Alat Kesehatan',
            'obat-obatan' => 'Obat-obatan',
            'suplemen-kesehatan' => 'Suplemen Kesehatan',
            'perawatan-kecantikan' => 'Perawatan & Kecantikan',
            'kesehatan-pribadi' => 'Kesehatan Pribadi',
        ];

        return $labels[$this->category_type] ?? ucwords(str_replace('-', ' ', $this->category_type));
    }

    /**
     * Get icon untuk category type
     */
    public function getCategoryIcon()
    {
        $icons = [
            'alat-kesehatan' => 'bi-heart-pulse',
            'obat-obatan' => 'bi-capsule',
            'suplemen-kesehatan' => 'bi-capsule-pill',
            'perawatan-kecantikan' => 'bi-palette',
            'kesehatan-pribadi' => 'bi-person-check',
        ];

        return $icons[$this->category_type] ?? 'bi-tag';
    }

    /**
     * Get warna badge untuk category type
     */
    public function getCategoryBadgeColor()
    {
        $colors = [
            'alat-kesehatan' => 'primary',
            'obat-obatan' => 'success',
            'suplemen-kesehatan' => 'info',
            'perawatan-kecantikan' => 'warning',
            'kesehatan-pribadi' => 'secondary',
        ];

        return $colors[$this->category_type] ?? 'dark';
    }

    /**
     * Scope untuk filter berdasarkan category type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('category_type', $type);
    }

    /**
     * Scope untuk kategori dengan harga dalam range tertentu
     */
    public function scopeInPriceRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('harga', '>=', floatval($min));
        }
        
        if ($max !== null) {
            $query->where('harga', '<=', floatval($max));
        }
        
        return $query;
    }

    /**
     * Scope untuk kategori yang memiliki gambar
     */
    public function scopeWithImage($query)
    {
        return $query->whereNotNull('gambar');
    }

    /**
     * Accessor untuk format harga dengan type safety
     */
    public function getFormattedHargaAttribute()
    {
        $harga = floatval($this->harga ?? 0);
        return 'Rp ' . number_format($harga, 0, ',', '.');
    }

    /**
     * Accessor untuk URL gambar
     */
    public function getImageUrlAttribute()
    {
        if ($this->gambar) {
            return asset('storage/' . $this->gambar);
        }
        
        return asset('images/no-image.png'); // Default image jika tidak ada
    }

    /**
     * Check apakah kategori memiliki produk
     */
    public function hasProducts()
    {
        return $this->produks()->exists();
    }

    /**
     * Get total produk dalam kategori
     */
    public function getTotalProducts()
    {
        return $this->produks()->count();
    }

    /**
     * Get range harga produk dalam kategori dengan type safety
     */
    public function getProductPriceRange()
    {
        $min = $this->produks()->min('harga');
        $max = $this->produks()->max('harga');
        
        // Type cast ke float untuk menghindari warning
        $minFloat = $min ? floatval($min) : 0;
        $maxFloat = $max ? floatval($max) : 0;
        
        return [
            'min' => $minFloat,
            'max' => $maxFloat,
            'formatted' => ($minFloat && $maxFloat) 
                ? 'Rp ' . number_format($minFloat, 0, ',', '.') . ' - Rp ' . number_format($maxFloat, 0, ',', '.')
                : null
        ];
    }

    /**
     * Get kategori populer berdasarkan jumlah di keranjang
     */
    public static function getPopularCategories($limit = 5)
    {
        return self::withCount('keranjangs')
            ->orderBy('keranjangs_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search kategori berdasarkan nama atau deskripsi
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%')
              ->orWhere('deskripsi', 'like', '%' . $search . '%');
        });
    }

    /**
     * Get kategori terbaru
     */
    public function scopeLatest($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Get kategori termurah sampai termahal
     */
    public function scopeOrderByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('harga', $direction);
    }

    /**
     * Get deskripsi singkat
     */
    public function getShortDescriptionAttribute()
    {
        if ($this->deskripsi) {
            return Str::limit($this->deskripsi, 100);
        }
        
        return 'Tidak ada deskripsi';
    }

    /**
     * Get formatted harga untuk display dengan null safety
     */
    public function getDisplayPrice()
    {
        $harga = floatval($this->harga ?? 0);
        return number_format($harga, 0, ',', '.');
    }

    /**
     * Get harga as integer untuk perhitungan
     */
    public function getPriceAsInt()
    {
        return intval($this->harga ?? 0);
    }

    /**
     * Check apakah kategori gratis
     */
    public function isFree()
    {
        return floatval($this->harga ?? 0) <= 0;
    }

    /**
     * Get kategori dengan harga terendah
     */
    public static function getCheapest()
    {
        return self::where('harga', '>', 0)->orderBy('harga', 'asc')->first();
    }

    /**
     * Get kategori dengan harga tertinggi
     */
    public static function getMostExpensive()
    {
        return self::orderBy('harga', 'desc')->first();
    }

    /**
     * Get average price dari semua kategori
     */
    public static function getAveragePrice()
    {
        $avg = self::avg('harga');
        return $avg ? floatval($avg) : 0;
    }

    /**
     * Scope untuk kategori dalam budget tertentu
     */
    public function scopeWithinBudget($query, $budget)
    {
        return $query->where('harga', '<=', floatval($budget));
    }

    /**
     * Get kategori berdasarkan range harga
     */
    public static function getByPriceRange($min, $max)
    {
        return self::whereBetween('harga', [floatval($min), floatval($max)])->get();
    }

    /**
     * Boot method untuk events
     */
    protected static function boot()
    {
        parent::boot();

        // Event saat kategori dihapus
        static::deleting(function ($kategori) {
            // Hapus gambar jika ada
            if ($kategori->gambar && Storage::disk('public')->exists($kategori->gambar)) {
                Storage::disk('public')->delete($kategori->gambar);
            }
        });

        // Event saat kategori disimpan
        static::saving(function ($kategori) {
            // Pastikan harga tidak null
            if ($kategori->harga === null) {
                $kategori->harga = 0;
            }
            
            // Pastikan harga tidak negatif
            if ($kategori->harga < 0) {
                $kategori->harga = 0;
            }
        });
    }

    /**
     * Mutator untuk harga - pastikan selalu float
     */
    public function setHargaAttribute($value)
    {
        $this->attributes['harga'] = $value !== null ? floatval($value) : 0;
    }

    /**
     * Accessor untuk harga - pastikan selalu float
     */
    public function getHargaAttribute($value)
    {
        return $value !== null ? floatval($value) : 0;
    }
}