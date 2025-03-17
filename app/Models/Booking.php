<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number', 'user_id', 'property_id',
        'check_in_date', 'check_out_date', 'booking_date',
        'unit_price', 'pricing_type', 'duration',
        'total_price', 'currency', 'booking_type','status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    public function guestDetail()
    {
        return $this->hasOne(GuestDetail::class, 'booking_id');
    }
    public function hotelBooking()
    {
        return $this->hasOne(HotelBooking::class, 'booking_id');
    }
    public function guestHouseBooking()
    {
        return $this->hasOne(GuestHouseBooking::class, 'booking_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
