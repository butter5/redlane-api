<?php

namespace App\Services;

use App\Models\DutyCategory;

class DutyCalculationService
{
    /**
     * Calculate duty based on the category's calculation method.
     */
    public function calculate(DutyCategory $category, float $quantityOrValue): float
    {
        $methodCode = $category->calculationMethodType->code;
        
        return match ($methodCode) {
            'percentage' => $this->calculatePercentageDuty($category, $quantityOrValue),
            'per_liter' => $this->calculatePerLiterDuty($category, $quantityOrValue),
            'per_kilogram' => $this->calculatePerKilogramDuty($category, $quantityOrValue),
            'per_unit' => $this->calculatePerUnitDuty($category, $quantityOrValue),
            default => throw new \InvalidArgumentException("Unknown calculation method: {$methodCode}"),
        };
    }

    /**
     * Calculate duty as a percentage of the item value.
     */
    public function calculatePercentageDuty(DutyCategory $category, float $itemValue): float
    {
        if ($itemValue <= 0) {
            return 0.0;
        }
        
        $rate = (float) $category->duty_rate;
        
        return round($itemValue * ($rate / 100), 2);
    }

    /**
     * Calculate duty per liter with optional exemption.
     */
    public function calculatePerLiterDuty(DutyCategory $category, float $liters): float
    {
        if ($liters <= 0) {
            return 0.0;
        }
        
        $exemption = $category->exemption_quantity ? (float) $category->exemption_quantity : 0;
        $taxableQuantity = max(0, $liters - $exemption);
        
        if ($taxableQuantity <= 0) {
            return 0.0;
        }
        
        $rate = (float) $category->duty_rate;
        
        return round($taxableQuantity * $rate, 2);
    }

    /**
     * Calculate duty per kilogram.
     */
    public function calculatePerKilogramDuty(DutyCategory $category, float $kilograms): float
    {
        if ($kilograms <= 0) {
            return 0.0;
        }
        
        $rate = (float) $category->duty_rate;
        
        return round($kilograms * $rate, 2);
    }

    /**
     * Calculate duty per unit.
     */
    public function calculatePerUnitDuty(DutyCategory $category, float $units): float
    {
        if ($units <= 0) {
            return 0.0;
        }
        
        $rate = (float) $category->duty_rate;
        
        return round($units * $rate, 2);
    }
}
