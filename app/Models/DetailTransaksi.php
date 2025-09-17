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
        'item_type', // kategori, produk, toko_kategori
        'deskripsi_item'
    ];

    protected $casts = [
        'harga_item' => 'float', // Cast ke float untuk menghindari error number_format
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
     * Accessor untuk harga yang terformat - FUNGSI HELPER
     */
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format((float)$this->harga_item, 0, ',', '.');
    }

    /**
     * Accessor untuk subtotal yang terformat - FUNGSI HELPER
     */
    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format((float)$this->subtotal_item, 0, ',', '.');
    }
}