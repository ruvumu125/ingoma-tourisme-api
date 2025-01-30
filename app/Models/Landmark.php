<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Landmark extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'name', 'distance'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
