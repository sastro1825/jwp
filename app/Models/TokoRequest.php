<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas untuk mengelola permohonan pembukaan toko
class TokoRequest extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan untuk menyimpan data permohonan toko
    protected $table = 'toko_requests';

    // Atribut yang dapat diisi secara massal untuk permohonan toko
    protected $fillable = [
        'user_id', 
        'nama_toko',
        'deskripsi_toko', 
        'kategori_usaha',  
        'alamat_toko',     
        'no_telepon',      
        'alasan_permohonan',
        'status',   
        'catatan_admin'    
    ];

    // Relasi ke model User yang mengajukan permohonan
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope untuk memfilter permohonan dengan status pending
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope untuk memfilter permohonan dengan status approved
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope untuk memfilter permohonan dengan status rejected
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Accessor untuk mendapatkan badge status dalam format HTML
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    // Accessor untuk memformat tanggal pembuatan permohonan
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }
}