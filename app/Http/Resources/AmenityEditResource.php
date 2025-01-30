<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AmenityEditResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'amenity_id' => $this->id,
            'name' => $this->name,
            'amenity_category_id' => $this->amenity_category_id,
            'category' => new AmenityCategoryResource($this->whenLoaded('category')),
            'description' => $this->pivot->description ?? null, // Include description from pivot table
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y'),
        ];
    }
}
