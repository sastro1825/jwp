<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TokoKategori extends Model
{
    use HasFactory;

    protected $fillable = [
        'toko_id',
        'nama',
        'deskripsi', 
        'harga',
        'gambar',
        'category_type',
    ];

    protected $casts = [
        'harga' => 'float',
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }

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

    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    public function getShortDescriptionAttribute()
    {
        if ($this->deskripsi) {
            return Str::limit($this->deskripsi, 100);
        }
        return 'Tidak ada deskripsi';
    }
}