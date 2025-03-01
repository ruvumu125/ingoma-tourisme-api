<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypePlan extends Model
{
    use HasFactory;

    protected $fillable = ['room_type_id', 'plan_type', 'price', 'currency'];

    public function roomtype()
    {
        return $this->belongsTo(RoomType::class);
    }
}
