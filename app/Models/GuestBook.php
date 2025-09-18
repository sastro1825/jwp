<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas untuk mengelola data buku tamu
class GuestBook extends Model
{
    use HasFactory;

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'name',
        'email',
        'message', 
        'status',
        'user_id', 
    ];

    // Relasi dengan model User untuk customer feedback
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Cek apakah feedback berasal dari customer
    public function isFromCustomer()
    {
        return !is_null($this->user_id); // Mengembalikan true jika user_id tidak null
    }

    // Mendapatkan nama pengirim feedback
    public function getSenderName()
    {
        if ($this->isFromCustomer() && $this->user) { // Jika feedback dari customer dan user ada
            return $this->user->name; // Kembalikan nama user
        }
        return $this->name; // Kembalikan nama dari atribut name
    }

    // Mendapatkan email pengirim feedback
    public function getSenderEmail()
    {
        if ($this->isFromCustomer() && $this->user) { // Jika feedback dari customer dan user ada
            return $this->user->email; // Kembalikan email user
        }
        return $this->email; // Kembalikan email dari atribut email
    }
}