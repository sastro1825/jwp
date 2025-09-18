<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas untuk mengelola transaksi
class Transaksi extends Model
{
    use HasFactory;

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'user_id', 
        'total',  
        'metode_pembayaran', 
        'status', 
        'pdf_path',
        'alamat_pengiriman', 
        'catatan',
    ];

    // Konversi tipe data untuk atribut tertentu
    protected $casts = [
        'total' => 'float',                      // Konversi total ke tipe float
        'created_at' => 'datetime:Y-m-d H:i:s', 
        'updated_at' => 'datetime:Y-m-d H:i:s', 
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class); // Mengembalikan relasi ke pengguna
    }
    
    // Relasi ke model ShippingOrder
    public function shippingOrder()
    {
        return $this->hasOne(ShippingOrder::class); // Mengembalikan relasi ke pesanan pengiriman
    }

    // Relasi ke model DetailTransaksi
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class); // Mengembalikan relasi ke detail transaksi
    }

    // Scope untuk filter transaksi berstatus pending
    public function scopePending($query)
    {
        return $query->where('status', 'pending'); // Mengembalikan query dengan status pending
    }

    // Scope untuk filter transaksi berstatus completed
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed'); // Mengembalikan query dengan status completed
    }

    // Scope untuk filter transaksi berstatus cancelled
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled'); // Mengembalikan query dengan status cancelled
    }

    // Mendapatkan HTML badge berdasarkan status
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',   // Badge untuk status pending
            'completed' => '<span class="badge bg-success">Completed</span>', // Badge untuk status completed
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>'  // Badge untuk status cancelled
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>'; // Mengembalikan badge atau default Unknown
    }

    // Mendapatkan total dalam format mata uang
    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.'); // Mengembalikan total dengan format Rupiah
    }

    // Memeriksa apakah transaksi memiliki pesanan pengiriman
    public function hasShippingOrder()
    {
        return $this->shippingOrder !== null; // Mengembalikan true jika ada pesanan pengiriman
    }

    // Mendapatkan nomor pelacakan dari pesanan pengiriman
    public function getTrackingNumberAttribute()
    {
        return $this->shippingOrder ? $this->shippingOrder->tracking_number : null; // Mengembalikan nomor pelacakan atau null
    }

    // Menghitung total dari detail transaksi
    public function getTotalFromDetails()
    {
        return $this->detailTransaksi()->sum('subtotal_item'); // Mengembalikan jumlah subtotal dari detail transaksi
    }

    // Mendapatkan tanggal pembuatan dalam format Indonesia
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB'; // Mengembalikan tanggal dengan zona waktu Jakarta
    }
}