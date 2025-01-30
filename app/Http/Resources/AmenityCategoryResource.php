<?php


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AmenityCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,  // Include the type of the category (hotel or room)
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y'),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),  // Include related amenities if loaded
        ];
    }
}



