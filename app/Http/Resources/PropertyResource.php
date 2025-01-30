<?php

namespace App\Http\Resources;


namespace App\Http\Resources;

use App\Models\PropertyHotelType;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'property_type' => $this->property_type,
            'description' => $this->description,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'city' => new CityResource($this->whenLoaded('city')),
            'hotel_type' => $this->whenLoaded('hotelType', function () {
                return [
                    'property_id' => $this->hotelType->property_id,
                    'hotel_type_id' => $this->hotelType->hotel_type_id,
                    'hotel_type_name' => optional($this->hotelType->hoteltype)->type_name, // Safe retrieval
                ];
            }),
            'whatsapp_number1' => $this->whatsapp_number1,
            'whatsapp_number2' => $this->whatsapp_number2,
            'rating' => $this->rating,
            'is_active' => $this->is_active,
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'landmarks' => LandmarkResource::collection($this->whenLoaded('landmarks')),
            'rules' => HotelRuleResource::collection($this->whenLoaded('rules')),
            'images' => HotelImageResource::collection($this->whenLoaded('images')),
            'guest_house_variants' => GuestHouseVariantResource::collection($this->whenLoaded('guestHouseVariants')), // New field
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y'),
        ];
    }
}
