<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyDetailsResource extends JsonResource
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
                    'hotel_type_name' => optional($this->hotelType->hoteltype)->type_name,
                ];
            }),
            'guest_house_type' => $this->whenLoaded('guestHouseType', function () {
                return [
                    'property_id' => $this->guestHouseType->property_id,
                    'guest_house_type_id' => $this->guestHouseType->guest_house_type_id,
                    'guest_house_type_name' => optional($this->guestHouseType->guesthousetype)->type_name,
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
            'guest_house_variants' => GuestHouseVariantResource::collection($this->whenLoaded('guestHouseVariants')),
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y'),

            // Similar properties with first image, minimum price, and currency
            'similar_properties' => PropertyResource::collection($this->whenLoaded('similarProperties'))->map(function ($property) {
                return [
                    'id' => $property->id,
                    'name' => $property->name,
                    'property_type' => $property->property_type,
                    'city' => new CityResource($property->city),
                    'first_image' => optional($property->images->first())->image_url,
                    'min_price' => $this->getMinPrice($property)['price'],
                    'currency' => $this->getMinPrice($property)['currency'],
                ];
            }),
        ];
    }

    /**
     * Get the minimum price and currency for a property (either from RoomTypePlan or GuestHouseVariant)
     */
    private function getMinPrice($property)
    {
        // Initialize default values for price and currency
        $minPrice = 0;
        $currency = 'USD';  // Assuming USD as the default currency if not found

        // If the property is a guesthouse
        if ($property->property_type === 'guesthouse') {
            $variant = $property->guestHouseVariants->sortBy('price')->first(); // Get the variant with the minimum price
            if ($variant) {
                $minPrice = $variant->price;
                $currency = $variant->currency;
            }
        } else {
            // If the property is a hotel (uses RoomTypePlans for pricing)
            $plan = $property->roomtypes->flatMap->plans->sortBy('price')->first(); // Get the plan with the minimum price
            if ($plan) {
                $minPrice = $plan->price;
                $currency = $plan->currency;
            }
        }

        return ['price' => $minPrice, 'currency' => $currency];
    }
}
