<?php

use App\Models\DutyCategory;
use Database\Seeders\CalculationMethodTypeSeeder;
use Database\Seeders\DutyCategorySeeder;
use Database\Seeders\UnitTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CalculationMethodTypeSeeder::class);
    $this->seed(UnitTypeSeeder::class);
});

test('duty category seeder creates all default categories', function () {
    $this->seed(DutyCategorySeeder::class);
    
    expect(DutyCategory::count())->toBe(5);
    
    expect(DutyCategory::where('code', 'standard')->exists())->toBeTrue();
    expect(DutyCategory::where('code', 'alcohol')->exists())->toBeTrue();
    expect(DutyCategory::where('code', 'tobacco')->exists())->toBeTrue();
    expect(DutyCategory::where('code', 'cigars')->exists())->toBeTrue();
    expect(DutyCategory::where('code', 'cigarettes')->exists())->toBeTrue();
});

test('standard category has correct percentage calculation', function () {
    $this->seed(DutyCategorySeeder::class);
    
    $standard = DutyCategory::where('code', 'standard')->first();
    
    expect($standard->name)->toBe('Standard');
    expect($standard->duty_rate)->toBe('25.0000');
    expect($standard->calculationMethodType->code)->toBe('percentage');
    expect($standard->is_active)->toBeTrue();
});

test('alcohol category has correct per liter calculation with exemption', function () {
    $this->seed(DutyCategorySeeder::class);
    
    $alcohol = DutyCategory::where('code', 'alcohol')->first();
    
    expect($alcohol->name)->toBe('Alcohol');
    expect($alcohol->duty_rate)->toBe('15.0000');
    expect($alcohol->calculationMethodType->code)->toBe('per_liter');
    expect($alcohol->dutyUnitType->code)->toBe('liters');
    expect($alcohol->exemption_quantity)->toBe('1.00');
    expect($alcohol->exemptionUnitType->code)->toBe('liters');
    expect($alcohol->is_active)->toBeTrue();
});

test('tobacco category has correct per kilogram calculation', function () {
    $this->seed(DutyCategorySeeder::class);
    
    $tobacco = DutyCategory::where('code', 'tobacco')->first();
    
    expect($tobacco->name)->toBe('Tobacco');
    expect($tobacco->duty_rate)->toBe('50.0000');
    expect($tobacco->calculationMethodType->code)->toBe('per_kilogram');
    expect($tobacco->dutyUnitType->code)->toBe('kilograms');
    expect($tobacco->is_active)->toBeTrue();
});

test('cigars category has correct per unit calculation', function () {
    $this->seed(DutyCategorySeeder::class);
    
    $cigars = DutyCategory::where('code', 'cigars')->first();
    
    expect($cigars->name)->toBe('Cigars');
    expect($cigars->duty_rate)->toBe('2.5000');
    expect($cigars->calculationMethodType->code)->toBe('per_unit');
    expect($cigars->dutyUnitType->code)->toBe('cigars');
    expect($cigars->is_active)->toBeTrue();
});

test('cigarettes category has correct per unit calculation', function () {
    $this->seed(DutyCategorySeeder::class);
    
    $cigarettes = DutyCategory::where('code', 'cigarettes')->first();
    
    expect($cigarettes->name)->toBe('Cigarettes');
    expect($cigarettes->duty_rate)->toBe('0.5000');
    expect($cigarettes->calculationMethodType->code)->toBe('per_unit');
    expect($cigarettes->dutyUnitType->code)->toBe('cigarettes');
    expect($cigarettes->is_active)->toBeTrue();
});

test('seeder can be run multiple times without error', function () {
    $this->seed(DutyCategorySeeder::class);
    $this->seed(DutyCategorySeeder::class);
    
    expect(DutyCategory::count())->toBe(5);
});

test('active scope returns only active categories', function () {
    $this->seed(DutyCategorySeeder::class);
    
    $category = DutyCategory::first();
    $category->is_active = false;
    $category->save();
    
    expect(DutyCategory::active()->count())->toBe(4);
    expect(DutyCategory::count())->toBe(5);
});
