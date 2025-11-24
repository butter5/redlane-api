<?php

use App\Models\Currency;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('currency seeder creates all default currencies', function () {
    $this->seed(CurrencySeeder::class);
    
    expect(Currency::count())->toBe(6);
    
    expect(Currency::where('code', 'USD')->exists())->toBeTrue();
    expect(Currency::where('code', 'BMD')->exists())->toBeTrue();
    expect(Currency::where('code', 'EUR')->exists())->toBeTrue();
    expect(Currency::where('code', 'GBP')->exists())->toBeTrue();
    expect(Currency::where('code', 'CAD')->exists())->toBeTrue();
    expect(Currency::where('code', 'JPY')->exists())->toBeTrue();
});

test('USD currency has correct details', function () {
    $this->seed(CurrencySeeder::class);
    
    $usd = Currency::where('code', 'USD')->first();
    
    expect($usd->name)->toBe('US Dollar');
    expect($usd->symbol)->toBe('$');
    expect($usd->is_active)->toBeTrue();
});

test('BMD currency has correct details', function () {
    $this->seed(CurrencySeeder::class);
    
    $bmd = Currency::where('code', 'BMD')->first();
    
    expect($bmd->name)->toBe('Bermudian Dollar');
    expect($bmd->symbol)->toBe('BD$');
    expect($bmd->is_active)->toBeTrue();
});

test('seeder can be run multiple times without error', function () {
    $this->seed(CurrencySeeder::class);
    $this->seed(CurrencySeeder::class);
    
    expect(Currency::count())->toBe(6);
});

test('active scope returns only active currencies', function () {
    $this->seed(CurrencySeeder::class);
    
    $currency = Currency::first();
    $currency->is_active = false;
    $currency->save();
    
    expect(Currency::active()->count())->toBe(5);
    expect(Currency::count())->toBe(6);
});
