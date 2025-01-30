<?php

namespace App\Http\Resources;

use App\Models\RoomTypeImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomTypeResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type_name' => $this->type_name,
            'property_id'=> $this->property_id,
            'property' => $this->whenLoaded('property', function () {
                return [
                    'property_id' => $this->property->id,
                    'property_name' => $this->property->name,
                    'property_type' => $this->property->property_type,
                ];
            }),
            'room_size' => $this->room_size,
            'bed_type' => $this->bed_type,
            'max_guests' => $this->max_guests,
            'description' => $this->description,
            'images' => RoomImageResource::collection($this->whenLoaded('images')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'plans' => RoomPlanResource::collection($this->whenLoaded('plans')),
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y'),
        ];
    }
}
