<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmenityCategory extends Model
{
    protected $fillable = [
        'name',
        'type',
    ];

    public function amenities()
    {
        return $this->hasMany(Amenity::class);
    }
}

