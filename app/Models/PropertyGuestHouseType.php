<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyGuestHouseType extends Model
{
    protected $fillable = ['property_id'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function guestHouseVariants()
    {
        return $this->hasMany(GuestHouseVariant::class, 'property_guest_house_id');
    }
}
