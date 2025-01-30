<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypeAmenity extends Model
{
    use HasFactory;

    protected $table = 'room_type_amenity';


    protected $fillable = [
        'room_type_id',
        'amenity_id',
        'description',
    ];


    public function room()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }
}
