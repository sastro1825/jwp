<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',         // Role untuk admin/customer/pemilik_toko
        'dob',          // Date of birth
        'gender',       // Male/Female
        'address',      // Alamat lengkap
        'city',         // Kota
        'contact_no',   // No HP
        'paypal_id',    // PayPal ID
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'dob' => 'date',        // Cast DOB ke date
        'password' => 'hashed', // Auto hash password
    ];

    /**
     * Custom method untuk cek login dengan name atau email
     */
    public function isValidLogin($login)
    {
        return $this->name === $login || $this->email === $login;
    }

    /**
     * Accessor untuk mendapatkan full name dengan title
     */
    public function getFullNameAttribute()
    {
        if (!$this->gender) {
            return $this->name;
        }
        
        $title = $this->gender === 'male' ? 'Mr.' : 'Ms.';
        return $title . ' ' . $this->name;
    }

    /**
     * Accessor untuk format tanggal lahir
     */
    public function getFormattedDobAttribute()
    {
        if ($this->dob && $this->dob instanceof \Carbon\Carbon) {
            return $this->dob->format('d/m/Y');
        }
        
        return null;
    }

    /**
     * Accessor untuk mendapatkan umur dari tanggal lahir
     */
    public function getAgeAttribute()
    {
        if ($this->dob && $this->dob instanceof \Carbon\Carbon) {
            return $this->dob->age;
        }
        
        return null;
    }

    /**
     * Relasi ke transaksi
     */
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }

    /**
     * Relasi ke keranjang
     */
    public function keranjangs()
    {
        return $this->hasMany(Keranjang::class);
    }

    /**
     * Relasi ke toko (jika user punya toko)
     */
    public function toko()
    {
        return $this->hasOne(Toko::class);
    }

    /**
     * Relasi ke guest book entries
     */
    public function guestBooks()
    {
        return $this->hasMany(GuestBook::class);
    }

    /**
     * Relasi ke permohonan toko
     */
    public function tokoRequests()
    {
        return $this->hasMany(TokoRequest::class);
    }

    /**
     * Scope untuk filter berdasarkan role
     */
    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopePemilikToko($query)
    {
        return $query->where('role', 'pemilik_toko');
    }

    /**
     * Method untuk cek apakah user adalah admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Method untuk cek apakah user adalah customer
     */
    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    /**
     * Method untuk cek apakah user adalah pemilik toko
     */
    public function isPemilikToko()
    {
        return $this->role === 'pemilik_toko';
    }

    /**
     * Method untuk cek apakah user punya permohonan toko pending
     */
    public function hasPendingTokoRequest()
    {
        return $this->tokoRequests()->where('status', 'pending')->exists();
    }

    /**
     * Method untuk cek apakah user punya toko yang sudah approved
     */
    public function hasApprovedToko()
    {
        return $this->tokoRequests()->where('status', 'approved')->exists();
    }

    /**
     * Method untuk mendapatkan permohonan toko terbaru
     */
    public function getLatestTokoRequest()
    {
        return $this->tokoRequests()->latest()->first();
    }

    /**
     * Method untuk mendapatkan total transaksi user
     */
    public function getTotalTransaksi()
    {
        return $this->transaksis()->count();
    }

    /**
     * Method untuk mendapatkan total belanja user
     */
    public function getTotalBelanja()
    {
        return $this->transaksis()->sum('total');
    }

    /**
     * Method untuk cek apakah user memiliki alamat lengkap
     */
    public function hasCompleteAddress()
    {
        return !empty($this->address) && !empty($this->city);
    }

    /**
     * Method untuk mendapatkan alamat lengkap
     */
    public function getFullAddress()
    {
        if ($this->hasCompleteAddress()) {
            return $this->address . ', ' . $this->city;
        }
        
        return $this->address ?: $this->city ?: 'Alamat belum lengkap';
    }

    /**
     * Method untuk mendapatkan status permohonan toko terbaru
     */
    public function getTokoRequestStatus()
    {
        $latestRequest = $this->getLatestTokoRequest();
        return $latestRequest ? $latestRequest->status : null;
    }

    /**
     * Method untuk cek apakah user bisa mengajukan permohonan toko baru
     */
    public function canRequestNewToko()
    {
        // Tidak bisa jika sudah pemilik toko
        if ($this->isPemilikToko()) {
            return false;
        }

        // Tidak bisa jika ada permohonan pending
        if ($this->hasPendingTokoRequest()) {
            return false;
        }

        // Tidak bisa jika sudah ada yang approved
        if ($this->hasApprovedToko()) {
            return false;
        }

        return true;
    }

    /**
     * Get avatar URL (jika ada sistem avatar)
     */
    public function getAvatarUrlAttribute()
    {
        // Untuk sekarang return default avatar
        $initial = strtoupper(substr($this->name, 0, 1));
        return "https://ui-avatars.com/api/?name={$initial}&background=007bff&color=white&size=100";
    }

    /**
     * Get display name untuk UI
     */
    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get formatted contact number
     */
    public function getFormattedContactAttribute()
    {
        if ($this->contact_no) {
            // Format nomor HP Indonesia
            $number = preg_replace('/[^0-9]/', '', $this->contact_no);
            if (substr($number, 0, 1) === '0') {
                return '+62' . substr($number, 1);
            }
            return '+62' . $number;
        }
        
        return null;
    }

    /**
     * Get user statistics
     */
    public function getStatsAttribute()
    {
        return [
            'total_transaksi' => $this->getTotalTransaksi(),
            'total_belanja' => $this->getTotalBelanja(),
            'total_keranjang' => $this->keranjangs()->count(),
            'has_toko' => $this->isPemilikToko(),
            'toko_status' => $this->getTokoRequestStatus(),
        ];
    }

    /**
     * Scope untuk search user
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%')
              ->orWhere('contact_no', 'like', '%' . $search . '%');
        });
    }

    /**
     * Scope untuk user dengan transaksi
     */
    public function scopeWithTransactions($query)
    {
        return $query->whereHas('transaksis');
    }

    /**
     * Scope untuk user aktif (punya transaksi dalam 30 hari terakhir)
     */
    public function scopeActive($query)
    {
        return $query->whereHas('transaksis', function($q) {
            $q->where('created_at', '>=', now()->subDays(30));
        });
    }

    /**
     * Get user berdasarkan role dengan statistik
     */
    public static function getUsersByRoleWithStats($role = null)
    {
        $query = self::query();
        
        if ($role) {
            $query->where('role', $role);
        }
        
        return $query->withCount(['transaksis', 'keranjangs', 'tokoRequests'])
                    ->with(['toko'])
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Check apakah user punya item di keranjang
     */
    public function hasCartItems()
    {
        return $this->keranjangs()->exists();
    }

    /**
     * Get total item di keranjang
     */
    public function getCartItemsCount()
    {
        return $this->keranjangs()->sum('jumlah');
    }

    /**
     * Get total harga keranjang
     */
    public function getCartTotal()
    {
        return $this->keranjangs()->get()->sum(function($item) {
            return $item->jumlah * $item->harga;
        });
    }

    /**
     * Clear semua item di keranjang
     */
    public function clearCart()
    {
        return $this->keranjangs()->delete();
    }

    /**
     * Boot method untuk events
     */
    protected static function boot()
    {
        parent::boot();

        // Event saat user dibuat
        static::created(function ($user) {
            // Log atau action lain saat user baru dibuat
        });

        // Event saat user dihapus
        static::deleting(function ($user) {
            // Hapus data terkait jika diperlukan
            $user->keranjangs()->delete();
        });
    }
}