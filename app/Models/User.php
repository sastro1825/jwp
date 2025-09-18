<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Kelas User untuk mengelola data pengguna dan autentikasi
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'name',         // Nama pengguna
        'email',        // Email pengguna
        'password',     // Kata sandi pengguna
        'role',         // Peran: admin/customer/pemilik_toko
        'dob',          // Tanggal lahir
        'gender',       // Jenis kelamin: Male/Female
        'address',      // Alamat lengkap
        'city',         // Kota
        'contact_no',   // Nomor telepon
        'paypal_id',    // ID PayPal
    ];

    // Atribut yang disembunyikan saat serialisasi
    protected $hidden = [
        'password',         // Kata sandi
        'remember_token',   // Token ingat sesi
    ];

    // Atribut yang di-cast ke tipe tertentu
    protected $casts = [
        'email_verified_at' => 'datetime',  // Cast waktu verifikasi email
        'dob' => 'date',                    // Cast tanggal lahir
        'password' => 'hashed',             // Hash otomatis kata sandi
    ];

    // Cek login dengan nama atau email
    public function isValidLogin($login)
    {
        return $this->name === $login || $this->email === $login;
    }

    // Mendapatkan nama lengkap dengan gelar
    public function getFullNameAttribute()
    {
        if (!$this->gender) {
            return $this->name; // Kembalikan nama jika gender kosong
        }
        
        $title = $this->gender === 'male' ? 'Mr.' : 'Ms.'; // Tentukan gelar
        return $title . ' ' . $this->name; // Gabungkan gelar dan nama
    }

    // Format tanggal lahir ke d/m/Y
    public function getFormattedDobAttribute()
    {
        return $this->dob && $this->dob instanceof \Carbon\Carbon 
            ? $this->dob->format('d/m/Y') // Format tanggal
            : null; // Kembalikan null jika tanggal lahir kosong
    }

    // Hitung umur dari tanggal lahir
    public function getAgeAttribute()
    {
        return $this->dob && $this->dob instanceof \Carbon\Carbon 
            ? $this->dob->age // Hitung umur
            : null; // Kembalikan null jika tanggal lahir kosong
    }

    // Relasi ke tabel transaksi
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class); // Satu user memiliki banyak transaksi
    }

    // Relasi ke tabel keranjang
    public function keranjangs()
    {
        return $this->hasMany(Keranjang::class); // Satu user memiliki banyak item keranjang
    }

    // Relasi ke tabel toko
    public function toko()
    {
        return $this->hasOne(Toko::class); // Satu user memiliki satu toko
    }

    // Relasi ke tabel guest book
    public function guestBooks()
    {
        return $this->hasMany(GuestBook::class); // Satu user memiliki banyak entri guest book
    }

    // Relasi ke tabel permohonan toko
    public function tokoRequests()
    {
        return $this->hasMany(TokoRequest::class); // Satu user memiliki banyak permohonan toko
    }

    // Scope untuk filter pengguna dengan role customer
    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    // Scope untuk filter pengguna dengan role admin
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // Scope untuk filter pengguna dengan role pemilik toko
    public function scopePemilikToko($query)
    {
        return $query->where('role', 'pemilik_toko');
    }

    // Cek apakah user adalah admin
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Cek apakah user adalah customer
    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    // Cek apakah user adalah pemilik toko
    public function isPemilikToko()
    {
        return $this->role === 'pemilik_toko';
    }

    // Cek apakah user memiliki permohonan toko yang masih pending
    public function hasPendingTokoRequest()
    {
        return $this->tokoRequests()->where('status', 'pending')->exists();
    }

    // Cek apakah user memiliki toko yang sudah disetujui
    public function hasApprovedToko()
    {
        return $this->tokoRequests()->where('status', 'approved')->exists();
    }

    // Dapatkan permohonan toko terbaru
    public function getLatestTokoRequest()
    {
        return $this->tokoRequests()->latest()->first();
    }

    // Hitung total transaksi user
    public function getTotalTransaksi()
    {
        return $this->transaksis()->count();
    }

    // Hitung total belanja user
    public function getTotalBelanja()
    {
        return $this->transaksis()->sum('total');
    }

    // Cek apakah user memiliki alamat lengkap
    public function hasCompleteAddress()
    {
        return !empty($this->address) && !empty($this->city);
    }

    // Dapatkan alamat lengkap
    public function getFullAddress()
    {
        return $this->hasCompleteAddress() 
            ? $this->address . ', ' . $this->city // Gabungkan alamat dan kota
            : ($this->address ?: $this->city ?: 'Alamat belum lengkap'); // Pesan jika alamat tidak lengkap
    }

    // Dapatkan status permohonan toko terbaru
    public function getTokoRequestStatus()
    {
        $latestRequest = $this->getLatestTokoRequest();
        return $latestRequest ? $latestRequest->status : null; // Kembalikan status atau null
    }

    // Cek apakah user dapat mengajukan permohonan toko baru
    public function canRequestNewToko()
    {
        if ($this->isPemilikToko() || $this->hasPendingTokoRequest() || $this->hasApprovedToko()) {
            return false; // Tidak bisa jika sudah pemilik toko, ada permohonan pending, atau sudah disetujui
        }
        return true; // Bisa mengajukan permohonan baru
    }

    // Dapatkan URL avatar default
    public function getAvatarUrlAttribute()
    {
        $initial = strtoupper(substr($this->name, 0, 1)); // Ambil huruf awal nama
        return "https://ui-avatars.com/api/?name={$initial}&background=007bff&color=white&size=100";
    }

    // Dapatkan nama untuk tampilan UI
    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    // Format nomor telepon ke format Indonesia
    public function getFormattedContactAttribute()
    {
        if ($this->contact_no) {
            $number = preg_replace('/[^0-9]/', '', $this->contact_no); // Bersihkan karakter non-angka
            return substr($number, 0, 1) === '0' 
                ? '+62' . substr($number, 1) // Tambahkan kode negara
                : '+62' . $number;
        }
        return null; // Kembalikan null jika nomor kosong
    }

    // Dapatkan statistik user
    public function getStatsAttribute()
    {
        return [
            'total_transaksi' => $this->getTotalTransaksi(), // Jumlah transaksi
            'total_belanja' => $this->getTotalBelanja(),     // Total belanja
            'total_keranjang' => $this->keranjangs()->count(), // Jumlah item keranjang
            'has_toko' => $this->isPemilikToko(),            // Status kepemilikan toko
            'toko_status' => $this->getTokoRequestStatus(),  // Status permohonan toko
        ];
    }

    // Scope untuk mencari user berdasarkan nama, email, atau nomor telepon
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%')
              ->orWhere('contact_no', 'like', '%' . $search . '%');
        });
    }

    // Scope untuk user dengan transaksi
    public function scopeWithTransactions($query)
    {
        return $query->whereHas('transaksis');
    }

    // Scope untuk user aktif (transaksi dalam 30 hari terakhir)
    public function scopeActive($query)
    {
        return $query->whereHas('transaksis', function($q) {
            $q->where('created_at', '>=', now()->subDays(30));
        });
    }

    // Dapatkan user berdasarkan role dengan statistik
    public static function getUsersByRoleWithStats($role = null)
    {
        $query = self::query();
        if ($role) {
            $query->where('role', $role); // Filter berdasarkan role jika ada
        }
        return $query->withCount(['transaksis', 'keranjangs', 'tokoRequests'])
                    ->with(['toko'])
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    // Cek apakah user memiliki item di keranjang
    public function hasCartItems()
    {
        return $this->keranjangs()->exists();
    }

    // Hitung total item di keranjang
    public function getCartItemsCount()
    {
        return $this->keranjangs()->sum('jumlah');
    }

    // Hitung total harga keranjang
    public function getCartTotal()
    {
        return $this->keranjangs()->get()->sum(function($item) {
            return $item->jumlah * $item->harga; // Jumlahkan harga per item
        });
    }

    // Hapus semua item di keranjang
    public function clearCart()
    {
        return $this->keranjangs()->delete();
    }

    // Event saat model diinisialisasi
    protected static function boot()
    {
        parent::boot();

        // Event saat user dibuat
        static::created(function ($user) {
            // Log atau tindakan lain saat user baru dibuat
        });

        // Event saat user dihapus
        static::deleting(function ($user) {
            $user->keranjangs()->delete(); // Hapus item keranjang terkait
        });
    }
}