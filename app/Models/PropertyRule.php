<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyRule extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'rule_description'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
