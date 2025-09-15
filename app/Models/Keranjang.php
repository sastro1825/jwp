<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = 'keranjangs'; // Nama tabel keranjang

    /**
     * The attributes that are mass assignable - untuk keranjang yang support kategori
     */
    protected $fillable = [
        'user_id',        // ID user yang memiliki keranjang
        'kategori_id',    // ID kategori (untuk buy dari kategori)
        'nama_item',      // Nama item (dari kategori)
        'harga_item',     // Harga item (dari kategori)
        'deskripsi_item', // Deskripsi item (dari kategori)
        'item_type',      // Jenis item: 'kategori' (hanya kategori yang digunakan)
        'jumlah',         // Jumlah item yang dibeli
    ];

    /**
     * The attributes that should be cast - untuk konversi data type
     */
    protected $casts = [
        'harga_item' => 'decimal:2', // Cast harga ke decimal
        'jumlah' => 'integer',       // Cast jumlah ke integer
    ];

    /**
     * Relasi ke user yang memiliki keranjang
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke kategori (untuk item_type = 'kategori')
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Accessor untuk mendapatkan nama item
     * Otomatis ambil dari kategori jika nama_item kosong
     */
    public function getNamaAttribute()
    {
        if ($this->nama_item) {
            return $this->nama_item;
        }

        if ($this->kategori) {
            return $this->kategori->nama;
        }

        return 'Item Tidak Diketahui';
    }

    /**
     * Accessor untuk mendapatkan harga item
     * Otomatis ambil dari kategori jika harga_item kosong
     */
    public function getHargaAttribute()
    {
        if ($this->harga_item) {
            return $this->harga_item;
        }

        if ($this->kategori) {
            return $this->kategori->harga;
        }

        return 0;
    }

    /**
     * Accessor untuk mendapatkan deskripsi item
     * Otomatis ambil dari kategori jika deskripsi_item kosong
     */
    public function getDeskripsiAttribute()
    {
        if ($this->deskripsi_item) {
            return $this->deskripsi_item;
        }

        if ($this->kategori) {
            return $this->kategori->deskripsi;
        }

        return '';
    }

    /**
     * Accessor untuk mendapatkan subtotal item
     * Usage: $item->subtotal
     */
    public function getSubtotalAttribute()
    {
        return $this->jumlah * $this->harga;
    }

    /**
     * Accessor untuk mendapatkan harga terformat
     * Usage: $item->harga_formatted
     */
    public function getHargaFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    /**
     * Accessor untuk mendapatkan subtotal terformat
     * Usage: $item->subtotal_formatted
     */
    public function getSubtotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Accessor untuk mendapatkan ID kategori untuk tampilan
     * Usage: $item->item_id_kategori (untuk tampilan)
     */
    public function getItemIdKategoriAttribute()
    {
        if ($this->kategori) {
            return 'KAT-' . $this->kategori_id;
        }

        return 'UNKNOWN';
    }

    /**
     * Scope untuk filter berdasarkan kategori
     * Usage: Keranjang::kategori()->get()
     */
    public function scopeKategori($query)
    {
        return $query->where('item_type', 'kategori');
    }

    /**
     * Static method untuk membuat item keranjang dari kategori
     * Usage: Keranjang::createFromKategori($user_id, $kategori, $jumlah)
     */
    public static function createFromKategori($user_id, $kategori, $jumlah = 1)
    {
        return self::create([
            'user_id' => $user_id,
            'kategori_id' => $kategori->id,
            'nama_item' => $kategori->nama,
            'harga_item' => $kategori->harga,
            'deskripsi_item' => $kategori->deskripsi,
            'item_type' => 'kategori',
            'jumlah' => $jumlah,
        ]);
    }

    /**
     * Method untuk cek apakah item adalah kategori
     */
    public function isKategori()
    {
        return $this->item_type === 'kategori';
    }
}