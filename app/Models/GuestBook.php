<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GuestBook extends Model
{
    use HasFactory;
    
    protected $table = 'guest_books'; // Nama tabel untuk guest book entries
    
    protected $fillable = [
        'name',
        'email', 
        'message',
        'status'
    ];
    
    // Relasi ke user berdasarkan email
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}