<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'address', 'city_id', 'latitude','longitude','property_type',
        'whatsapp_number1', 'whatsapp_number2', 'rating','is_active'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function rules()
    {
        return $this->hasMany(PropertyRule::class);
    }

    public function landmarks()
    {
        return $this->hasMany(Landmark::class);
    }

    public function roomtypes()
    {
        return $this->hasMany(RoomType::class);
    }
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'property_amenity')
            ->withPivot('description')
            ->withTimestamps();
    }



    /**
     * Get the property hotel type (if any) for the property.
     */
    public function hotelType()
    {
        return $this->hasOne(PropertyHotelType::class, 'property_id');
    }

    /**
     * Get the property guest house type (if any) for the property.
     */
    public function guestHouseType()
    {
        return $this->hasOne(PropertyGuestHouseType::class, 'property_id', 'id');
    }


    /**
     * Get all guest house variants for the property.
     */
    public function guestHouseVariants()
    {
        return $this->hasManyThrough(
            GuestHouseVariant::class,
            PropertyGuestHouseType::class,
            'property_id', // Foreign key on property_guest_house_types table
            'property_guest_house_id', // Foreign key on guest_house_variants table
            'id', // Local key on properties table
            'id'  // Local key on property_guest_house_types table
        );
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeFilterByAmenitiesAndPrice($query, $amenities = [], $minPrice = null, $maxPrice = null)
    {
        if (!empty($amenities)) {
            $query->whereHas('amenities', function ($q) use ($amenities) {
                $q->whereIn('amenities.id', $amenities);
            });
        }

        if ($minPrice !== null || $maxPrice !== null) {
            $query->whereHas('roomtypes.plans', function ($q) use ($minPrice, $maxPrice) {
                if ($minPrice !== null) {
                    $q->where('price', '>=', $minPrice);
                }
                if ($maxPrice !== null) {
                    $q->where('price', '<=', $maxPrice);
                }
            });
        }

        return $query;
    }


}
