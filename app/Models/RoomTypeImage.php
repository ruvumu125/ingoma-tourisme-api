<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypeImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'image_url',
        'is_main',
    ];

    public function roomtype()
    {
        return $this->belongsTo(RoomType::class);
    }
}

