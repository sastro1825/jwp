<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'user_id',
        'status',
        'alamat',
        'deskripsi',        // Deskripsi toko
        'kategori_usaha',   // Kategori usaha
        'no_telepon',       // No telepon toko
    ];

    /**
     * Relasi ke user pemilik toko
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke produk-produk dalam toko
     */
    public function produks()
    {
        return $this->hasMany(Produk::class);
    }

    /**
     * Scope untuk toko yang sudah approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Accessor untuk status badge
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }
}