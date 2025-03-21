<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestDetail extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id', 'first_name', 'last_name', 'phone', 'email'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
