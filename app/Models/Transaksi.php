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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}