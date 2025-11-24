<?php

namespace Database\Seeders;

use App\Models\CalculationMethodType;
use App\Models\DutyCategory;
use App\Models\UnitType;
use Illuminate\Database\Seeder;

class DutyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $percentageType = CalculationMethodType::where('code', 'percentage')->first();
        $perLiterType = CalculationMethodType::where('code', 'per_liter')->first();
        $perKilogramType = CalculationMethodType::where('code', 'per_kilogram')->first();
        $perUnitType = CalculationMethodType::where('code', 'per_unit')->first();

        $litersUnit = UnitType::where('code', 'liters')->first();
        $kilogramsUnit = UnitType::where('code', 'kilograms')->first();
        $cigarsUnit = UnitType::where('code', 'cigars')->first();
        $cigarettesUnit = UnitType::where('code', 'cigarettes')->first();

        $categories = [
            [
                'code' => 'standard',
                'name' => 'Standard',
                'description' => 'Standard duty rate applied to most goods',
                'icon_name' => 'package',
                'calculation_method_type_id' => $percentageType->id,
                'duty_rate' => 25.0000,
                'duty_unit_type_id' => null,
                'exemption_quantity' => null,
                'exemption_unit_type_id' => null,
                'is_active' => true,
                'effective_from' => null,
                'effective_to' => null,
            ],
            [
                'code' => 'alcohol',
                'name' => 'Alcohol',
                'description' => 'Duty on alcoholic beverages per liter with exemption',
                'icon_name' => 'wine',
                'calculation_method_type_id' => $perLiterType->id,
                'duty_rate' => 15.0000,
                'duty_unit_type_id' => $litersUnit->id,
                'exemption_quantity' => 1.00,
                'exemption_unit_type_id' => $litersUnit->id,
                'is_active' => true,
                'effective_from' => null,
                'effective_to' => null,
            ],
            [
                'code' => 'tobacco',
                'name' => 'Tobacco',
                'description' => 'Duty on tobacco products per kilogram',
                'icon_name' => 'smoking',
                'calculation_method_type_id' => $perKilogramType->id,
                'duty_rate' => 50.0000,
                'duty_unit_type_id' => $kilogramsUnit->id,
                'exemption_quantity' => null,
                'exemption_unit_type_id' => null,
                'is_active' => true,
                'effective_from' => null,
                'effective_to' => null,
            ],
            [
                'code' => 'cigars',
                'name' => 'Cigars',
                'description' => 'Duty on cigars per unit',
                'icon_name' => 'cigar',
                'calculation_method_type_id' => $perUnitType->id,
                'duty_rate' => 2.5000,
                'duty_unit_type_id' => $cigarsUnit->id,
                'exemption_quantity' => null,
                'exemption_unit_type_id' => null,
                'is_active' => true,
                'effective_from' => null,
                'effective_to' => null,
            ],
            [
                'code' => 'cigarettes',
                'name' => 'Cigarettes',
                'description' => 'Duty on cigarettes per unit',
                'icon_name' => 'cigarette',
                'calculation_method_type_id' => $perUnitType->id,
                'duty_rate' => 0.5000,
                'duty_unit_type_id' => $cigarettesUnit->id,
                'exemption_quantity' => null,
                'exemption_unit_type_id' => null,
                'is_active' => true,
                'effective_from' => null,
                'effective_to' => null,
            ],
        ];

        foreach ($categories as $category) {
            DutyCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}
