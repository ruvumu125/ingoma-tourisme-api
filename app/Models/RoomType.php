<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
       'type_name','property_id', 'room_size', 'bed_type', 'max_guests', 'description'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    public function plans()
    {
        return $this->hasMany(RoomTypePlan::class);
    }
    // Relationship with RoomTypeImage model
    public function images()
    {
        return $this->hasMany(RoomTypeImage::class);
    }

    // Relationship with Amenity model for room amenities
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_type_amenity')
            ->withPivot('description')
            ->withTimestamps();
    }
}

