<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokoRequest extends Model
{
    use HasFactory;

    protected $table = 'toko_requests'; // Nama tabel untuk permohonan toko

    /**
     * The attributes that are mass assignable - untuk permohonan pembukaan toko
     */
    protected $fillable = [
        'user_id',           // ID customer yang mengajukan
        'nama_toko',         // Nama toko yang diinginkan
        'deskripsi_toko',    // Deskripsi toko
        'kategori_usaha',    // Kategori usaha (kesehatan, dll)
        'alamat_toko',       // Alamat toko fisik
        'no_telepon',        // No telepon toko
        'alasan_permohonan', // Alasan mengajukan permohonan
        'status',            // Status: pending, approved, rejected
        'catatan_admin'      // Catatan admin saat review
    ];

    /**
     * Relasi ke user yang mengajukan permohonan
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Accessor untuk mendapatkan status badge
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

    /**
     * Accessor untuk format tanggal pengajuan
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }
}