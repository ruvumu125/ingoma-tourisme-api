<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelType extends Model
{
    use HasFactory;

    protected $fillable = ['type_name', 'description'];

    public function properties()
    {
        return $this->hasMany(PropertyHotelType::class, 'hotel_type_id');
    }

}

