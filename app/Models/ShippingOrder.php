<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

// Kelas untuk mengelola data pengiriman
class ShippingOrder extends Model
{
    use HasFactory;

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'transaksi_id',
        'tracking_number',
        'status',
        'courier',
        'shipped_date',
        'delivered_date',
        'notes',
    ];

    // Konversi tipe data untuk kolom tanggal
    protected $casts = [
        'shipped_date' => 'datetime', // Kolom shipped_date di-cast sebagai datetime
        'delivered_date' => 'datetime', // Kolom delivered_date di-cast sebagai datetime
    ];

    // Relasi ke model Transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class); // Mengembalikan relasi belongsTo ke Transaksi
    }

    // Scope untuk filter status pending
    public function scopePending($query)
    {
        return $query->where('status', 'pending'); // Mengembalikan query dengan status pending
    }

    // Scope untuk filter status shipped
    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped'); // Mengembalikan query dengan status shipped
    }

    // Scope untuk filter status delivered
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered'); // Mengembalikan query dengan status delivered
    }

    // Scope untuk filter status cancelled
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled'); // Mengembalikan query dengan status cancelled
    }

    // Mendapatkan HTML badge untuk status
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'shipped' => '<span class="badge bg-info">Shipped</span>',
            'delivered' => '<span class="badge bg-success">Delivered</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>'; // Mengembalikan badge berdasarkan status
    }

    // Mendapatkan warna status untuk CSS
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'shipped' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary'; // Mengembalikan warna berdasarkan status
    }

    // Memeriksa apakah pengiriman selesai
    public function isCompleted()
    {
        return $this->status === 'delivered'; // Mengembalikan true jika status delivered
    }

    // Memeriksa apakah pengiriman sedang berlangsung
    public function isInProgress()
    {
        return $this->status === 'shipped'; // Mengembalikan true jika status shipped
    }

    // Menandai pengiriman sebagai shipped
    public function markAsShipped()
    {
        $this->update([
            'status' => 'shipped',
            'shipped_date' => now() // Mengatur status shipped dan tanggal saat ini
        ]);
    }

    // Menandai pengiriman sebagai delivered
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_date' => now() // Mengatur status delivered dan tanggal saat ini
        ]);
    }

    // Mendapatkan format tanggal pengiriman
    public function getFormattedShippedDateAttribute()
    {
        // Memeriksa apakah shipped_date valid dan merupakan instance Carbon
        if ($this->shipped_date && $this->shipped_date instanceof Carbon) {
            return $this->shipped_date->format('d/m/Y'); // Mengembalikan tanggal dalam format d/m/Y
        }
        
        return '-'; // Mengembalikan tanda - jika tanggal tidak ada
    }

    // Mendapatkan format tanggal pengiriman selesai
    public function getFormattedDeliveredDateAttribute()
    {
        // Memeriksa apakah delivered_date valid dan merupakan instance Carbon
        if ($this->delivered_date && $this->delivered_date instanceof Carbon) {
            return $this->delivered_date->format('d/m/Y'); // Mengembalikan tanggal dalam format d/m/Y
        }
        
        return '-'; // Mengembalikan tanda - jika tanggal tidak ada
    }

    // Mendapatkan format tanggal dan waktu pengiriman
    public function getShippedDateTimeAttribute()
    {
        // Memeriksa apakah shipped_date valid dan merupakan instance Carbon
        if ($this->shipped_date && $this->shipped_date instanceof Carbon) {
            return $this->shipped_date->format('d/m/Y H:i'); // Mengembalikan tanggal dan waktu dalam format d/m/Y H:i
        }
        
        return null; // Mengembalikan null jika tanggal tidak ada
    }

    // Mendapatkan format tanggal dan waktu pengiriman selesai
    public function getDeliveredDateTimeAttribute()
    {
        // Memeriksa apakah delivered_date valid dan merupakan instance Carbon
        if ($this->delivered_date && $this->delivered_date instanceof Carbon) {
            return $this->delivered_date->format('d/m/Y H:i'); // Mengembalikan tanggal dan waktu dalam format d/m/Y H:i
        }
        
        return null; // Mengembalikan null jika tanggal tidak ada
    }

    // Mendapatkan durasi pengiriman dalam hari
    public function getShippingDurationAttribute()
    {
        // Memeriksa apakah shipped_date dan delivered_date tersedia
        if ($this->shipped_date && $this->delivered_date) {
            return $this->shipped_date->diffInDays($this->delivered_date); // Mengembalikan selisih hari
        }
        
        return null; // Mengembalikan null jika salah satu tanggal tidak ada
    }

    // Scope untuk mencari berdasarkan nomor pelacakan
    public function scopeByTracking($query, $trackingNumber)
    {
        return $query->where('tracking_number', $trackingNumber); // Mengembalikan query berdasarkan nomor pelacakan
    }

    // Mendapatkan label kurir yang lebih mudah dibaca
    public function getCourierLabelAttribute()
    {
        $labels = [
            'Express Courier' => 'Kurir Express',
            'COD Service' => 'Bayar di Tempat (COD)',
            'Standard' => 'Pengiriman Standard',
        ];

        return $labels[$this->courier] ?? $this->courier; // Mengembalikan label kurir atau nilai asli jika tidak ada
    }
}