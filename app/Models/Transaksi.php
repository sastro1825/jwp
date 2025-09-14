<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'metode_pembayaran',
        'status',
        'pdf_path',
    ];

    // Relasi ke user yang melakukan transaksi
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Relasi ke shipping order
    public function shippingOrder()
    {
        return $this->hasOne(ShippingOrder::class);
    }
}