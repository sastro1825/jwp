<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'id_produk',
        'harga',
        'deskripsi',
        'kategori_id',
        'toko_id',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }
}