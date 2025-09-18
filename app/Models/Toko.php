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
        'deskripsi',       
        'kategori_usaha',  
        'no_telepon',      
    ];

    // Konversi tipe data untuk kolom tertentu
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke model User (pemilik toko)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke model Produk (produk dalam toko)
    public function produks()
    {
        return $this->hasMany(Produk::class);
    }

    // Relasi ke model TokoKategori (kategori toko)
    public function tokoKategoris()
    {
        return $this->hasMany(TokoKategori::class);
    }

    // Relasi ke model Transaksi melalui User
    public function transaksis()
    {
        return $this->hasManyThrough(Transaksi::class, User::class, 'id', 'user_id', 'user_id', 'id');
    }

    // Scope untuk toko berstatus approved
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope untuk toko berstatus pending
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope untuk toko berstatus rejected
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Accessor untuk badge status dalam format HTML
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    // Accessor untuk warna status (CSS class)
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    // Accessor untuk label kategori usaha yang mudah dibaca
    public function getKategoriUsahaLabelAttribute()
    {
        $labels = [
            'alat-kesehatan' => 'Alat Kesehatan',
            'obat-obatan' => 'Obat-obatan',
            'suplemen-kesehatan' => 'Suplemen Kesehatan',
            'perawatan-kecantikan' => 'Perawatan & Kecantikan',
            'kesehatan-pribadi' => 'Kesehatan Pribadi',
        ];

        return $labels[$this->kategori_usaha] ?? ucwords(str_replace('-', ' ', $this->kategori_usaha ?? ''));
    }

    // Accessor untuk alamat dalam format rapi
    public function getFormattedAlamatAttribute()
    {
        return $this->alamat ?: 'Alamat belum diisi';
    }

    // Accessor untuk nomor telepon dalam format internasional
    public function getFormattedTeleponAttribute()
    {
        if ($this->no_telepon) {
            $number = preg_replace('/[^0-9]/', '', $this->no_telepon);
            return substr($number, 0, 1) === '0' ? '+62' . substr($number, 1) : '+62' . $number;
        }
        
        return 'Tidak ada nomor telepon';
    }

    // Cek apakah toko aktif (approved)
    public function isActive()
    {
        return $this->status === 'approved';
    }

    // Cek apakah toko masih pending
    public function isPending()
    {
        return $this->status === 'pending';
    }

    // Cek apakah toko ditolak
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    // Menghitung total kategori toko
    public function getTotalKategoris()
    {
        return $this->tokoKategoris()->count();
    }

    // Menghitung total produk toko
    public function getTotalProduk()
    {
        return $this->produks()->count();
    }

    // Mendapatkan statistik toko
    public function getStats()
    {
        return [
            'total_kategoris' => $this->getTotalKategoris(),
            'total_produk' => $this->getTotalProduk(),
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'created_at' => $this->created_at->format('d/m/Y'),
        ];
    }

    // Scope untuk mencari toko berdasarkan nama, deskripsi, atau alamat
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%')
              ->orWhere('deskripsi', 'like', '%' . $search . '%')
              ->orWhere('alamat', 'like', '%' . $search . '%');
        });
    }

    // Scope untuk filter berdasarkan kategori usaha
    public function scopeByKategoriUsaha($query, $kategori)
    {
        return $query->where('kategori_usaha', $kategori);
    }

    // Scope untuk mengambil toko beserta data user
    public function scopeWithUser($query)
    {
        return $query->with('user');
    }

    // Scope untuk mengambil toko dengan statistik
    public function scopeWithStats($query)
    {
        return $query->withCount(['tokoKategoris', 'produks']);
    }

    // Event model untuk logging dan aksi tertentu
    protected static function boot()
    {
        parent::boot();

        // Log saat toko baru dibuat
        static::created(function ($toko) {
            \Log::info('New toko created', [
                'toko_id' => $toko->id,
                'nama' => $toko->nama,
                'user_id' => $toko->user_id
            ]);
        });

        // Log saat status toko berubah
        static::updated(function ($toko) {
            if ($toko->isDirty('status')) {
                \Log::info('Toko status changed', [
                    'toko_id' => $toko->id,
                    'nama' => $toko->nama,
                    'old_status' => $toko->getOriginal('status'),
                    'new_status' => $toko->status
                ]);
            }
        });

        // Hapus kategori toko dan log saat toko dihapus
        static::deleting(function ($toko) {
            $toko->tokoKategoris()->delete();
            \Log::info('Toko deleted', [
                'toko_id' => $toko->id,
                'nama' => $toko->nama
            ]);
        });
    }
}