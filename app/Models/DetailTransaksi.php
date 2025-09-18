<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas untuk mengelola detail transaksi
class DetailTransaksi extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'detail_transaksi';

    // Kolom yang dapat diisi secara massal
    protected $fillable = [
        'transaksi_id',
        'nama_item',
        'harga_item', 
        'jumlah',
        'subtotal_item',
        'item_type',
        'deskripsi_item'
    ];

    // Konversi tipe data untuk kolom tertentu
    protected $casts = [
        'harga_item' => 'float',
        'subtotal_item' => 'float',
        'jumlah' => 'integer',
    ];

    // Relasi ke model Transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    // Cek apakah item berasal dari toko_kategori
    public function isFromTokoKategori()
    {
        return $this->item_type === 'toko_kategori';
    }

    // Format harga item dengan prefix Rp dan pemisah ribuan
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format((float)$this->harga_item, 0, ',', '.');
    }

    // Format subtotal item dengan prefix Rp dan pemisah ribuan
    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format((float)$this->subtotal_item, 0, ',', '.');
    }

    // Menampilkan nama item dengan fallback jika null
    public function getNamaItemDisplayAttribute()
    {
        return $this->nama_item ?? 'Item tidak diketahui';
    }

    // Menampilkan tipe item dalam format yang lebih mudah dibaca
    public function getItemTypeDisplayAttribute()
    {
        $types = [
            'kategori' => 'Kategori Admin',
            'toko_kategori' => 'Kategori Toko',
            'produk' => 'Produk'
        ];
        
        return $types[$this->item_type] ?? 'Tidak Diketahui';
    }
}