<?php

use App\Models\DutyCategory;
use App\Services\DutyCalculationService;
use Database\Seeders\CalculationMethodTypeSeeder;
use Database\Seeders\DutyCategorySeeder;
use Database\Seeders\UnitTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CalculationMethodTypeSeeder::class);
    $this->seed(UnitTypeSeeder::class);
    $this->seed(DutyCategorySeeder::class);
    $this->service = new DutyCalculationService();
});

test('calculatePercentageDuty calculates correctly for standard items', function () {
    $category = DutyCategory::where('code', 'standard')->first();
    $itemValue = 100.00;
    
    $duty = $this->service->calculatePercentageDuty($category, $itemValue);
    
    expect($duty)->toBe(25.00); // 25% of 100
});

test('calculatePercentageDuty handles zero value', function () {
    $category = DutyCategory::where('code', 'standard')->first();
    
    $duty = $this->service->calculatePercentageDuty($category, 0);
    
    expect($duty)->toBe(0.0);
});

test('calculatePerLiterDuty calculates correctly without exemption', function () {
    $category = DutyCategory::where('code', 'alcohol')->first();
    $quantity = 0.5; // 0.5 liters (under exemption)
    
    $duty = $this->service->calculatePerLiterDuty($category, $quantity);
    
    expect($duty)->toBe(0.0); // Under 1L exemption
});

test('calculatePerLiterDuty calculates correctly with exemption applied', function () {
    $category = DutyCategory::where('code', 'alcohol')->first();
    $quantity = 3.0; // 3 liters
    
    $duty = $this->service->calculatePerLiterDuty($category, $quantity);
    
    // 3L - 1L exemption = 2L taxable * $15/L = $30
    expect($duty)->toBe(30.00);
});

test('calculatePerLiterDuty handles exactly at exemption limit', function () {
    $category = DutyCategory::where('code', 'alcohol')->first();
    $quantity = 1.0; // Exactly 1 liter (exemption limit)
    
    $duty = $this->service->calculatePerLiterDuty($category, $quantity);
    
    expect($duty)->toBe(0.0); // At exemption limit
});

test('calculatePerKilogramDuty calculates correctly', function () {
    $category = DutyCategory::where('code', 'tobacco')->first();
    $quantity = 0.5; // 0.5 kg
    
    $duty = $this->service->calculatePerKilogramDuty($category, $quantity);
    
    // 0.5 kg * $50/kg = $25
    expect($duty)->toBe(25.00);
});

test('calculatePerKilogramDuty handles zero quantity', function () {
    $category = DutyCategory::where('code', 'tobacco')->first();
    
    $duty = $this->service->calculatePerKilogramDuty($category, 0);
    
    expect($duty)->toBe(0.0);
});

test('calculatePerUnitDuty calculates correctly for cigars', function () {
    $category = DutyCategory::where('code', 'cigars')->first();
    $quantity = 10; // 10 cigars
    
    $duty = $this->service->calculatePerUnitDuty($category, $quantity);
    
    // 10 * $2.50 = $25
    expect($duty)->toBe(25.00);
});

test('calculatePerUnitDuty calculates correctly for cigarettes', function () {
    $category = DutyCategory::where('code', 'cigarettes')->first();
    $quantity = 200; // 200 cigarettes (1 carton)
    
    $duty = $this->service->calculatePerUnitDuty($category, $quantity);
    
    // 200 * $0.50 = $100
    expect($duty)->toBe(100.00);
});

test('calculatePerUnitDuty handles zero quantity', function () {
    $category = DutyCategory::where('code', 'cigars')->first();
    
    $duty = $this->service->calculatePerUnitDuty($category, 0);
    
    expect($duty)->toBe(0.0);
});

test('calculate method routes to correct calculation based on method type', function () {
    $standardCategory = DutyCategory::where('code', 'standard')->first();
    $alcoholCategory = DutyCategory::where('code', 'alcohol')->first();
    $tobaccoCategory = DutyCategory::where('code', 'tobacco')->first();
    $cigarsCategory = DutyCategory::where('code', 'cigars')->first();
    
    expect($this->service->calculate($standardCategory, 100))->toBe(25.00);
    expect($this->service->calculate($alcoholCategory, 2))->toBe(15.00);
    expect($this->service->calculate($tobaccoCategory, 1))->toBe(50.00);
    expect($this->service->calculate($cigarsCategory, 5))->toBe(12.50);
});
