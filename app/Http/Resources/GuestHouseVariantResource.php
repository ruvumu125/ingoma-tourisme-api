<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuestHouseVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'property_guest_house_id' => $this->property_guest_house_id,
            'variant' => $this->variant, // E.g., 'per_night', 'per_week'
            'price' => $this->price,
            'currency' => $this->currency,
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y'),
        ];
    }
}
