<?php

use App\Models\Currency;
use App\Models\CurrencyExchangeRate;
use App\Services\ExchangeRateService;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\ExchangeRateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CurrencySeeder::class);
    $this->seed(ExchangeRateSeeder::class);
    $this->service = new ExchangeRateService();
});

test('getLatestRate retrieves the latest exchange rate', function () {
    $usd = Currency::where('code', 'USD')->first();
    $eur = Currency::where('code', 'EUR')->first();
    
    $rate = $this->service->getLatestRate($usd->id, $eur->id);
    
    expect($rate)->not->toBeNull();
    expect($rate->from_currency_id)->toBe($usd->id);
    expect($rate->to_currency_id)->toBe($eur->id);
});

test('getLatestRate returns null for non-existent currency pair', function () {
    $rate = $this->service->getLatestRate(9999, 9998);
    
    expect($rate)->toBeNull();
});

test('convert calculates currency conversion correctly', function () {
    $amount = 100.00;
    $usd = Currency::where('code', 'USD')->first();
    $eur = Currency::where('code', 'EUR')->first();
    
    $converted = $this->service->convert($amount, $usd->id, $eur->id);
    
    // USD to EUR rate is 0.92, so 100 USD = 92 EUR
    expect($converted)->toBe(92.00);
});

test('convert handles zero amount', function () {
    $usd = Currency::where('code', 'USD')->first();
    $eur = Currency::where('code', 'EUR')->first();
    
    $converted = $this->service->convert(0, $usd->id, $eur->id);
    
    expect($converted)->toBe(0.0);
});

test('convert returns null when rate not found', function () {
    $converted = $this->service->convert(100, 9999, 9998);
    
    expect($converted)->toBeNull();
});

test('convert handles same currency conversion', function () {
    $usd = Currency::where('code', 'USD')->first();
    
    $converted = $this->service->convert(100, $usd->id, $usd->id);
    
    expect($converted)->toBe(100.0);
});

test('getLatestRate caches results', function () {
    Cache::flush();
    
    $usd = Currency::where('code', 'USD')->first();
    $eur = Currency::where('code', 'EUR')->first();
    
    // First call - should hit database
    $rate1 = $this->service->getLatestRate($usd->id, $eur->id);
    
    // Second call - should hit cache
    $rate2 = $this->service->getLatestRate($usd->id, $eur->id);
    
    expect($rate1->rate)->toBe($rate2->rate);
    
    // Verify cache was used
    $cacheKey = "exchange_rate_{$usd->id}_{$eur->id}";
    expect(Cache::has($cacheKey))->toBeTrue();
});

test('getAllLatestRates returns latest rates for all currency pairs', function () {
    $rates = $this->service->getAllLatestRates();
    
    expect($rates)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($rates->count())->toBeGreaterThan(0);
    
    // Each rate should have from and to currency relationships
    $firstRate = $rates->first();
    expect($firstRate)->toHaveKey('from_currency_id');
    expect($firstRate)->toHaveKey('to_currency_id');
    expect($firstRate)->toHaveKey('rate');
});

test('BMD to USD conversion is 1:1', function () {
    $bmd = Currency::where('code', 'BMD')->first();
    $usd = Currency::where('code', 'USD')->first();
    
    $converted = $this->service->convert(100, $bmd->id, $usd->id);
    
    expect($converted)->toBe(100.0);
});

test('USD to BMD conversion is 1:1', function () {
    $usd = Currency::where('code', 'USD')->first();
    $bmd = Currency::where('code', 'BMD')->first();
    
    $converted = $this->service->convert(100, $usd->id, $bmd->id);
    
    expect($converted)->toBe(100.0);
});

test('convert rounds to 2 decimal places', function () {
    $usd = Currency::where('code', 'USD')->first();
    $jpy = Currency::where('code', 'JPY')->first();
    
    $converted = $this->service->convert(100, $usd->id, $jpy->id);
    
    // Should be 100 * 149.5 = 14950.00
    expect($converted)->toBe(14950.00);
});
