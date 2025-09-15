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
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',         // Role untuk admin/customer
        'dob',          // Date of birth
        'gender',       // Male/Female
        'address',      // Alamat lengkap
        'city',         // Kota
        'contact_no',   // No HP
        'paypal_id',    // PayPal ID
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'dob' => 'date',        // Cast DOB ke date
        'password' => 'hashed', // Auto hash password
    ];

    /**
     * Custom method untuk cek login dengan name atau email
     * Untuk support login dengan username atau email
     */
    public function isValidLogin($login)
    {
        return $this->name === $login || $this->email === $login;
    }

    /**
     * Accessor untuk mendapatkan full name dengan title
     * Perbaikan: Cek null value dan gunakan attribute yang benar
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
     * Perbaikan: Cek null value dan gunakan Carbon instance yang benar
     */
    public function getFormattedDobAttribute()
    {
        // Cek apakah dob ada dan merupakan Carbon instance
        if ($this->dob && $this->dob instanceof \Carbon\Carbon) {
            return $this->dob->format('d/m/Y');
        }
        
        // Jika dob null atau bukan Carbon instance
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
        return $this->hasMany(GuestBook::class, 'email', 'email');
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
}