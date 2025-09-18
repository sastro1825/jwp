<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = 'keranjangs'; // Nama tabel di database

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'user_id',        // ID pengguna yang memiliki keranjang
        'produk_id',      // ID produk untuk pembelian dari produk
        'kategori_id',    // ID kategori untuk pembelian dari kategori admin
        'nama_item',      // Nama item dari kategori, produk, atau toko_kategori
        'harga_item',     // Harga item
        'deskripsi_item', // Deskripsi item
        'item_type',      // Jenis item: 'kategori', 'produk', atau 'toko_kategori'
        'jumlah',         // Jumlah item yang dibeli
    ];

    // Atribut yang dikonversi tipe datanya
    protected $casts = [
        'harga_item' => 'float', // Konversi harga_item ke tipe float
        'jumlah' => 'integer',   // Konversi jumlah ke tipe integer
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class); // Menghubungkan ke pengguna
    }

    // Relasi ke model Kategori untuk item_type 'kategori'
    public function kategori()
    {
        return $this->belongsTo(Kategori::class); // Menghubungkan ke kategori
    }

    // Relasi ke model Produk untuk item_type 'produk'
    public function produk()
    {
        return $this->belongsTo(Produk::class); // Menghubungkan ke produk
    }

    // Accessor untuk mendapatkan nama item
    public function getNamaAttribute()
    {
        if ($this->nama_item) {
            return $this->nama_item; // Kembalikan nama_item jika ada
        }

        if ($this->kategori) {
            return $this->kategori->nama; // Ambil nama dari kategori jika tersedia
        }

        if ($this->produk) {
            return $this->produk->nama; // Ambil nama dari produk jika tersedia
        }

        return 'Item Tidak Diketahui'; // Nama default jika tidak ada data
    }

    // Accessor untuk mendapatkan harga item
    public function getHargaAttribute()
    {
        if ($this->harga_item) {
            return (float) $this->harga_item; // Kembalikan harga_item sebagai float
        }

        if ($this->kategori) {
            return (float) $this->kategori->harga; // Ambil harga dari kategori
        }

        if ($this->produk) {
            return (float) $this->produk->harga; // Ambil harga dari produk
        }

        return 0.0; // Kembalikan 0 jika tidak ada harga
    }

    // Accessor untuk mendapatkan deskripsi item
    public function getDeskripsiAttribute()
    {
        if ($this->deskripsi_item) {
            return $this->deskripsi_item; // Kembalikan deskripsi_item jika ada
        }

        if ($this->kategori) {
            return $this->kategori->deskripsi; // Ambil deskripsi dari kategori
        }

        if ($this->produk) {
            return $this->produk->deskripsi; // Ambil deskripsi dari produk
        }

        return ''; // Kembalikan string kosong jika tidak ada deskripsi
    }

    // Accessor untuk menghitung subtotal
    public function getSubtotalAttribute()
    {
        return (float) $this->jumlah * $this->getHargaAttribute(); // Hitung subtotal (jumlah * harga)
    }

    // Accessor untuk format harga dengan Rupiah
    public function getHargaFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.'); // Format harga ke format Rupiah
    }

    // Accessor untuk format subtotal dengan Rupiah
    public function getSubtotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.'); // Format subtotal ke format Rupiah
    }

    // Accessor untuk ID item untuk tampilan
    public function getItemIdDisplayAttribute()
    {
        if ($this->kategori) {
            return 'KAT-' . $this->kategori_id; // Format ID untuk kategori
        }

        if ($this->produk) {
            return 'PRD-' . $this->produk_id; // Format ID untuk produk
        }

        return 'UNKNOWN'; // ID default jika tidak diketahui
    }

    // Accessor untuk nama produk di view
    public function getNamaProdukAttribute()
    {
        return $this->nama; // Menggunakan accessor getNamaAttribute
    }

    // Scope untuk filter item berdasarkan kategori
    public function scopeKategori($query)
    {
        return $query->where('item_type', 'kategori'); // Filter item dengan item_type 'kategori'
    }

    // Scope untuk filter item berdasarkan produk
    public function scopeProduk($query)
    {
        return $query->where('item_type', 'produk'); // Filter item dengan item_type 'produk'
    }

    // Membuat item keranjang dari kategori
    public static function createFromKategori($user_id, $kategori, $jumlah = 1)
    {
        return self::create([
            'user_id' => $user_id,
            'kategori_id' => $kategori->id,
            'produk_id' => null, // Set null untuk produk
            'nama_item' => $kategori->nama,
            'harga_item' => $kategori->harga,
            'deskripsi_item' => $kategori->deskripsi,
            'item_type' => 'kategori',
            'jumlah' => $jumlah,
        ]); // Buat entri keranjang dari data kategori
    }

    // Membuat item keranjang dari produk
    public static function createFromProduk($user_id, $produk, $jumlah = 1)
    {
        return self::create([
            'user_id' => $user_id,
            'kategori_id' => null, // Set null untuk kategori
            'produk_id' => $produk->id,
            'nama_item' => $produk->nama,
            'harga_item' => $produk->harga,
            'deskripsi_item' => $produk->deskripsi,
            'item_type' => 'produk',
            'jumlah' => $jumlah,
        ]); // Buat entri keranjang dari data produk
    }

    // Cek apakah item adalah kategori
    public function isKategori()
    {
        return $this->item_type === 'kategori'; // Kembalikan true jika item_type adalah 'kategori'
    }

    // Cek apakah item adalah produk
    public function isProduk()
    {
        return $this->item_type === 'produk'; // Kembalikan true jika item_type adalah 'produk'
    }

    // Cek apakah item adalah toko kategori
    public function isTokoKategori()
    {
        return $this->item_type === 'toko_kategori'; // Kembalikan true jika item_type adalah 'toko_kategori'
    }

    // Accessor untuk mendapatkan gambar item
    public function getGambarAttribute()
    {
        if ($this->kategori && $this->kategori->gambar) {
            return $this->kategori->gambar; // Ambil gambar dari kategori
        }

        if ($this->produk && $this->produk->gambar) {
            return $this->produk->gambar; // Ambil gambar dari produk
        }

        return null; // Kembalikan null jika tidak ada gambar
    }

    // Accessor untuk mendapatkan URL gambar
    public function getImageUrlAttribute()
    {
        if ($this->gambar) {
            return asset('storage/' . $this->gambar); // Kembalikan URL lengkap gambar
        }

        return null; // Kembalikan null jika tidak ada gambar
    }

    // Accessor untuk mendapatkan sumber item
    public function getSumberAttribute()
    {
        if ($this->isKategori()) {
            return 'Kategori Admin'; // Sumber dari kategori admin
        }

        if ($this->isProduk() && $this->produk && $this->produk->toko) {
            return 'Toko: ' . $this->produk->toko->nama; // Sumber dari toko produk
        }

        if ($this->isTokoKategori()) {
            return 'Kategori Toko Mitra'; // Sumber dari toko kategori
        }

        return 'Tidak Diketahui'; // Sumber default jika tidak diketahui
    }
}