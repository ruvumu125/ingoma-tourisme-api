<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomPlanResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'room_type_id' => $this->room_type_id,
            'plan_type' => $this->plan_type, // Changed field name to match the model
            'price' => $this->price,
            'currency' => $this->currency,
            'description' => $this->description,
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y'),
        ];
    }
}
