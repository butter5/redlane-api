<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DutyCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'icon_name' => $this->icon_name,
            'calculation_method' => [
                'id' => $this->calculation_method_type_id,
                'code' => $this->calculationMethodType->code,
                'description' => $this->calculationMethodType->description,
            ],
            'duty_rate' => (float) $this->duty_rate,
            'duty_unit' => $this->dutyUnitType ? [
                'id' => $this->duty_unit_type_id,
                'code' => $this->dutyUnitType->code,
                'abbreviation' => $this->dutyUnitType->abbreviation,
                'description' => $this->dutyUnitType->description,
            ] : null,
            'exemption_quantity' => $this->exemption_quantity ? (float) $this->exemption_quantity : null,
            'exemption_unit' => $this->exemptionUnitType ? [
                'id' => $this->exemption_unit_type_id,
                'code' => $this->exemptionUnitType->code,
                'abbreviation' => $this->exemptionUnitType->abbreviation,
                'description' => $this->exemptionUnitType->description,
            ] : null,
            'is_active' => $this->is_active,
            'effective_from' => $this->effective_from?->toDateString(),
            'effective_to' => $this->effective_to?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
