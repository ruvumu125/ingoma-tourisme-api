<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestHouseVariant extends Model
{
    protected $fillable = ['property_guest_house_id', 'variant', 'price'];

    public function propertyGuestHouseType()
    {
        return $this->belongsTo(PropertyGuestHouseType::class, 'property_guest_house_id');
    }
}

