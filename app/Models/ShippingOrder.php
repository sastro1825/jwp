<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ShippingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaksi_id',
        'tracking_number',
        'status',
        'courier',
        'shipped_date',
        'delivered_date',
        'notes',
    ];

    protected $casts = [
        'shipped_date' => 'datetime', // Ubah dari 'date' ke 'datetime'
        'delivered_date' => 'datetime', // Ubah dari 'date' ke 'datetime'
    ];

    /**
     * Relasi ke transaksi
     */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
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
            'shipped' => '<span class="badge bg-info">Shipped</span>',
            'delivered' => '<span class="badge bg-success">Delivered</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get status color for CSS
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'shipped' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Check if shipping is completed
     */
    public function isCompleted()
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if shipping is in progress
     */
    public function isInProgress()
    {
        return $this->status === 'shipped';
    }

    /**
     * Mark as shipped
     */
    public function markAsShipped()
    {
        $this->update([
            'status' => 'shipped',
            'shipped_date' => now() // Laravel akan auto-cast ke datetime
        ]);
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_date' => now() // Laravel akan auto-cast ke datetime
        ]);
    }

    /**
     * Get formatted shipped date - PERBAIKAN ERROR
     */
    public function getFormattedShippedDateAttribute()
    {
        // Pastikan shipped_date ada dan merupakan Carbon instance
        if ($this->shipped_date && $this->shipped_date instanceof Carbon) {
            return $this->shipped_date->format('d/m/Y');
        }
        
        return '-';
    }

    /**
     * Get formatted delivered date - PERBAIKAN ERROR
     */
    public function getFormattedDeliveredDateAttribute()
    {
        // Pastikan delivered_date ada dan merupakan Carbon instance
        if ($this->delivered_date && $this->delivered_date instanceof Carbon) {
            return $this->delivered_date->format('d/m/Y');
        }
        
        return '-';
    }

    /**
     * Get formatted shipped date with time
     */
    public function getShippedDateTimeAttribute()
    {
        if ($this->shipped_date && $this->shipped_date instanceof Carbon) {
            return $this->shipped_date->format('d/m/Y H:i');
        }
        
        return null;
    }

    /**
     * Get formatted delivered date with time
     */
    public function getDeliveredDateTimeAttribute()
    {
        if ($this->delivered_date && $this->delivered_date instanceof Carbon) {
            return $this->delivered_date->format('d/m/Y H:i');
        }
        
        return null;
    }

    /**
     * Get shipping duration in days
     */
    public function getShippingDurationAttribute()
    {
        if ($this->shipped_date && $this->delivered_date) {
            return $this->shipped_date->diffInDays($this->delivered_date);
        }
        
        return null;
    }

    /**
     * Scope untuk mencari berdasarkan tracking number
     */
    public function scopeByTracking($query, $trackingNumber)
    {
        return $query->where('tracking_number', $trackingNumber);
    }

    /**
     * Get courier label yang lebih readable
     */
    public function getCourierLabelAttribute()
    {
        $labels = [
            'Express Courier' => 'Kurir Express',
            'COD Service' => 'Bayar di Tempat (COD)',
            'Standard' => 'Pengiriman Standard',
        ];

        return $labels[$this->courier] ?? $this->courier;
    }
}