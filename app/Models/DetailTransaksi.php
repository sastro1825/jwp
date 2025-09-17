<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';

    protected $fillable = [
        'transaksi_id',
        'nama_item',
        'harga_item', 
        'jumlah',
        'subtotal_item',
        'item_type',
        'deskripsi_item'
    ];

    protected $casts = [
        'harga_item' => 'float',
        'subtotal_item' => 'float',
        'jumlah' => 'integer',
    ];

    /**
     * Relasi ke transaksi
     */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    /**
     * Method untuk cek tipe item
     */
    public function isFromTokoKategori()
    {
        return $this->item_type === 'toko_kategori';
    }

    /**
     * Accessor untuk harga yang terformat dengan type safety
     */
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format((float)$this->harga_item, 0, ',', '.');
    }

    /**
     * Accessor untuk subtotal yang terformat dengan type safety
     */
    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format((float)$this->subtotal_item, 0, ',', '.');
    }

    /**
     * Accessor untuk nama item dengan fallback
     */
    public function getNamaItemDisplayAttribute()
    {
        return $this->nama_item ?? 'Item tidak diketahui';
    }

    /**
     * Accessor untuk tipe item yang readable
     */
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