<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = 'keranjangs'; // Nama tabel keranjang

    /**
     * The attributes that are mass assignable - DIPERBAIKI untuk support kategori, produk, dan toko_kategori
     */
    protected $fillable = [
        'user_id',        // ID user yang memiliki keranjang
        'produk_id',      // ID produk (untuk buy dari produk)
        'kategori_id',    // ID kategori (untuk buy dari kategori admin)
        'nama_item',      // Nama item (dari kategori/produk/toko_kategori)
        'harga_item',     // Harga item
        'deskripsi_item', // Deskripsi item
        'item_type',      // Jenis item: 'kategori', 'produk', 'toko_kategori' - DITAMBAH SUPPORT TOKO_KATEGORI
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
     * Relasi ke produk (untuk item_type = 'produk') - DITAMBAHKAN
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Accessor untuk mendapatkan nama item - DIPERBAIKI
     * Otomatis ambil dari kategori/produk jika nama_item kosong
     */
    public function getNamaAttribute()
    {
        if ($this->nama_item) {
            return $this->nama_item;
        }

        if ($this->kategori) {
            return $this->kategori->nama;
        }

        if ($this->produk) {
            return $this->produk->nama;
        }

        return 'Item Tidak Diketahui';
    }

    /**
     * Accessor untuk mendapatkan harga item - DIPERBAIKI
     * Otomatis ambil dari kategori/produk jika harga_item kosong
     */
    public function getHargaAttribute()
    {
        if ($this->harga_item) {
            return $this->harga_item;
        }

        if ($this->kategori) {
            return $this->kategori->harga;
        }

        if ($this->produk) {
            return $this->produk->harga;
        }

        return 0;
    }

    /**
     * Accessor untuk mendapatkan deskripsi item - DIPERBAIKI
     * Otomatis ambil dari kategori/produk jika deskripsi_item kosong
     */
    public function getDeskripsiAttribute()
    {
        if ($this->deskripsi_item) {
            return $this->deskripsi_item;
        }

        if ($this->kategori) {
            return $this->kategori->deskripsi;
        }

        if ($this->produk) {
            return $this->produk->deskripsi;
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
     * Accessor untuk mendapatkan ID item untuk tampilan - DIPERBAIKI
     * Usage: $item->item_id_display (untuk tampilan)
     */
    public function getItemIdDisplayAttribute()
    {
        if ($this->kategori) {
            return 'KAT-' . $this->kategori_id;
        }

        if ($this->produk) {
            return 'PRD-' . $this->produk_id;
        }

        return 'UNKNOWN';
    }

    /**
     * Accessor untuk nama produk yang digunakan di view - DITAMBAHKAN
     * Untuk kompatibilitas dengan view yang menggunakan nama_produk
     */
    public function getNamaProdukAttribute()
    {
        return $this->nama; // Menggunakan accessor getNamaAttribute()
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
     * Scope untuk filter berdasarkan produk - DITAMBAHKAN
     * Usage: Keranjang::produk()->get()
     */
    public function scopeProduk($query)
    {
        return $query->where('item_type', 'produk');
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
            'produk_id' => null, // NULL untuk kategori
            'nama_item' => $kategori->nama,
            'harga_item' => $kategori->harga,
            'deskripsi_item' => $kategori->deskripsi,
            'item_type' => 'kategori',
            'jumlah' => $jumlah,
        ]);
    }

    /**
     * Static method untuk membuat item keranjang dari produk - DITAMBAHKAN
     * Usage: Keranjang::createFromProduk($user_id, $produk, $jumlah)
     */
    public static function createFromProduk($user_id, $produk, $jumlah = 1)
    {
        return self::create([
            'user_id' => $user_id,
            'kategori_id' => null, // NULL untuk produk
            'produk_id' => $produk->id,
            'nama_item' => $produk->nama,
            'harga_item' => $produk->harga,
            'deskripsi_item' => $produk->deskripsi,
            'item_type' => 'produk',
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

    /**
     * Method untuk cek apakah item adalah produk - DITAMBAHKAN
     */
    public function isProduk()
    {
        return $this->item_type === 'produk';
    }

    /**
     * Method untuk cek apakah item adalah toko kategori - FUNGSI BARU
     */
    public function isTokoKategori()
    {
        return $this->item_type === 'toko_kategori';
    }

    /**
     * Method untuk mendapatkan gambar item - DITAMBAHKAN
     * Untuk kompatibilitas dengan view yang menampilkan gambar
     */
    public function getGambarAttribute()
    {
        if ($this->kategori && $this->kategori->gambar) {
            return $this->kategori->gambar;
        }

        if ($this->produk && $this->produk->gambar) {
            return $this->produk->gambar;
        }

        return null;
    }

    /**
     * Method untuk mendapatkan URL gambar lengkap - DITAMBAHKAN
     */
    public function getImageUrlAttribute()
    {
        if ($this->gambar) {
            return asset('storage/' . $this->gambar);
        }

        return null;
    }

    /**
     * Method untuk mendapatkan sumber item - DIPERBAIKI dengan toko kategori
     */
    public function getSumberAttribute()
    {
        if ($this->isKategori()) {
            return 'Kategori Admin';
        }

        if ($this->isProduk() && $this->produk && $this->produk->toko) {
            return 'Toko: ' . $this->produk->toko->nama;
        }
        
        // Support untuk toko kategori - BARU
        if ($this->isTokoKategori()) {
            return 'Kategori Toko Mitra';
        }

        return 'Tidak Diketahui';
    }
}