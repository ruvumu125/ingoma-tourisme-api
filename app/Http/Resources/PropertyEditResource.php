<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyEditResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'property_type' => $this->property_type,
            'description' => $this->description,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'city_id' => $this->city_id,
            'hotel_type' => $this->whenLoaded('hotelType', function () {
                return $this->hotelType->hotel_type_id;
            }),
            'whatsapp_number1' => $this->whatsapp_number1,
            'whatsapp_number2' => $this->whatsapp_number2,
            'rating' => $this->rating,
            'amenities' => AmenityEditResource::collection($this->whenLoaded('amenities')),
            'landmarks' => LandmarkResource::collection($this->whenLoaded('landmarks')),
            'rules' => HotelRuleResource::collection($this->whenLoaded('rules')),
            'images' => HotelImageResource::collection($this->whenLoaded('images')),
            'guest_house_variants' => GuestHouseVariantResource::collection($this->whenLoaded('guestHouseVariants'))
        ];
    }
}
