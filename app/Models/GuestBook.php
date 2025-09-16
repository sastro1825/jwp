<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email', 
        'message',
        'status',
        'user_id', // Tambahan untuk customer feedback
    ];

    /**
     * Relationship dengan User untuk customer feedback
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cek apakah feedback dari visitor atau customer
     */
    public function isFromCustomer()
    {
        return !is_null($this->user_id);
    }

    /**
     * Get nama pengirim feedback
     */
    public function getSenderName()
    {
        if ($this->isFromCustomer() && $this->user) {
            return $this->user->name;
        }
        return $this->name;
    }

    /**
     * Get email pengirim feedback
     */
    public function getSenderEmail()
    {
        if ($this->isFromCustomer() && $this->user) {
            return $this->user->email;
        }
        return $this->email;
    }
}