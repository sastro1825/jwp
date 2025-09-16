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

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Relasi ke kategori toko (database terpisah dari kategori admin)
     */
    public function tokoKategoris()
    {
        return $this->hasMany(TokoKategori::class);
    }

    /**
     * Relasi ke transaksi yang terkait dengan toko (jika ada)
     */
    public function transaksis()
    {
        return $this->hasManyThrough(Transaksi::class, User::class, 'id', 'user_id', 'user_id', 'id');
    }

    /**
     * Scope untuk toko yang sudah approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope untuk toko yang pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk toko yang rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Accessor untuk status badge HTML
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
     * Accessor untuk status color (untuk CSS class)
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Accessor untuk label kategori usaha yang readable
     */
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

    /**
     * Accessor untuk alamat lengkap dengan format yang rapi
     */
    public function getFormattedAlamatAttribute()
    {
        if ($this->alamat) {
            return $this->alamat;
        }
        
        return 'Alamat belum diisi';
    }

    /**
     * Accessor untuk nomor telepon yang diformat
     */
    public function getFormattedTeleponAttribute()
    {
        if ($this->no_telepon) {
            $number = preg_replace('/[^0-9]/', '', $this->no_telepon);
            if (substr($number, 0, 1) === '0') {
                return '+62' . substr($number, 1);
            }
            return '+62' . $number;
        }
        
        return 'Tidak ada nomor telepon';
    }

    /**
     * Method untuk cek apakah toko aktif (approved)
     */
    public function isActive()
    {
        return $this->status === 'approved';
    }

    /**
     * Method untuk cek apakah toko masih pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Method untuk cek apakah toko ditolak
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Method untuk mendapatkan total kategori dalam toko
     */
    public function getTotalKategoris()
    {
        return $this->tokoKategoris()->count();
    }

    /**
     * Method untuk mendapatkan total produk dalam toko
     */
    public function getTotalProduk()
    {
        return $this->produks()->count();
    }

    /**
     * Method untuk mendapatkan statistik toko
     */
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

    /**
     * Method untuk search toko berdasarkan nama, deskripsi, atau alamat
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%')
              ->orWhere('deskripsi', 'like', '%' . $search . '%')
              ->orWhere('alamat', 'like', '%' . $search . '%');
        });
    }

    /**
     * Method untuk filter berdasarkan kategori usaha
     */
    public function scopeByKategoriUsaha($query, $kategori)
    {
        return $query->where('kategori_usaha', $kategori);
    }

    /**
     * Method untuk mendapatkan toko dengan user
     */
    public function scopeWithUser($query)
    {
        return $query->with('user');
    }

    /**
     * Method untuk mendapatkan toko dengan statistik
     */
    public function scopeWithStats($query)
    {
        return $query->withCount(['tokoKategoris', 'produks']);
    }

    /**
     * Boot method untuk events
     */
    protected static function boot()
    {
        parent::boot();

        // Event saat toko dibuat
        static::created(function ($toko) {
            \Log::info('New toko created', [
                'toko_id' => $toko->id,
                'nama' => $toko->nama,
                'user_id' => $toko->user_id
            ]);
        });

        // Event saat toko diupdate
        static::updated(function ($toko) {
            // Log perubahan status
            if ($toko->isDirty('status')) {
                \Log::info('Toko status changed', [
                    'toko_id' => $toko->id,
                    'nama' => $toko->nama,
                    'old_status' => $toko->getOriginal('status'),
                    'new_status' => $toko->status
                ]);
            }
        });

        // Event saat toko dihapus
        static::deleting(function ($toko) {
            // Hapus kategori toko terkait
            $toko->tokoKategoris()->delete();
            
            \Log::info('Toko deleted', [
                'toko_id' => $toko->id,
                'nama' => $toko->nama
            ]);
        });
    }
}