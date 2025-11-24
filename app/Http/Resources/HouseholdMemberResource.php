<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HouseholdMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth->format('Y-m-d'),
            'age' => $this->age,
            'relationship_type' => [
                'id' => $this->relationshipType->id,
                'code' => $this->relationshipType->code,
                'description' => $this->relationshipType->description,
            ],
            'is_primary_declarant' => $this->is_primary_declarant,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
