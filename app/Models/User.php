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
        'role', // Role untuk admin/customer
        'dob', // Date of birth
        'gender', // Male/Female
        'address', // Alamat
        'city', // Kota
        'contact_no', // No HP
        'paypal_id', // PayPal ID
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
        'dob' => 'date', // Cast DOB ke date
    ];

    // Custom method untuk cek login dengan name atau email
    public function isValidLogin($login)
    {
        return $this->name === $login || $this->email === $login;
    }
}