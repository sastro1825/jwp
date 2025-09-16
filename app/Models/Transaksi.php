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
        'alamat_pengiriman', // Tambahan field yang digunakan di checkout
        'catatan', // Tambahan field yang digunakan di checkout
    ];

    protected $casts = [
        'total' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke user yang melakukan transaksi
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relasi ke shipping order
     */
    public function shippingOrder()
    {
        return $this->hasOne(ShippingOrder::class);
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Check if transaction has shipping order
     */
    public function hasShippingOrder()
    {
        return $this->shippingOrder !== null;
    }

    /**
     * Get tracking number from shipping order
     */
    public function getTrackingNumberAttribute()
    {
        return $this->shippingOrder ? $this->shippingOrder->tracking_number : null;
    }
}