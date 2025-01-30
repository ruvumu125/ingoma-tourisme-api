<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $fillable = ['name', 'amenity_category_id'];



    public function category()
    {
        return $this->belongsTo(AmenityCategory::class, 'amenity_category_id');
    }

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_amenity')
            ->withPivot('description')
            ->withTimestamps();
    }

    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class, 'room_type_amenity')
            ->withPivot('description')
            ->withTimestamps();
    }


}

