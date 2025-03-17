<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'user_id', 'amount',
        'payment_method', 'payment_provider',
        'payment_status', 'transaction_id'
    ];

    public function booking()
    {
        return $this->hasOne(Booking::class, 'payment_id');
    }
}
