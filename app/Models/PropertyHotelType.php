<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyHotelType extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'hotel_type_id'];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }


    public function hoteltype()
    {
        return $this->belongsTo(HotelType::class, 'hotel_type_id');
    }
}

