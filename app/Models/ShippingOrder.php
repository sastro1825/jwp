<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingOrder extends Model
{
    use HasFactory;
    
    protected $table = 'shipping_orders'; // Nama tabel untuk shipping orders
    
    protected $fillable = [
        'transaksi_id',
        'tracking_number',
        'status',
        'courier', 
        'shipped_date',
        'delivered_date',
        'notes'
    ];
    
    protected $casts = [
        'shipped_date' => 'date', // Cast tanggal kirim ke date
        'delivered_date' => 'date' // Cast tanggal sampai ke date
    ];
    
    // Relasi ke transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}