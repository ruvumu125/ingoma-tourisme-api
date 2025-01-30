<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyAmenity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'property_amenity';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property_id',
        'amenity_id',
        'description',
    ];

    /**
     * Get the property associated with this property amenity.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the amenity associated with this property amenity.
     */
    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }
}
