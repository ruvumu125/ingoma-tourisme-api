<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HotelRuleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'rule_category' => $this->rule_category,
            'rule_description' => $this->rule_description,
        ];
    }
}
