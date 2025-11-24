<?php

namespace App\Services;

use App\Models\CurrencyExchangeRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ExchangeRateService
{
    /**
     * Cache duration in seconds (24 hours).
     */
    private const CACHE_DURATION = 86400;

    /**
     * Get the latest exchange rate between two currencies.
     */
    public function getLatestRate(int $fromCurrencyId, int $toCurrencyId): ?CurrencyExchangeRate
    {
        // Same currency = 1:1 rate (no need to query)
        if ($fromCurrencyId === $toCurrencyId) {
            return null; // Handled in convert method
        }

        $cacheKey = "exchange_rate_{$fromCurrencyId}_{$toCurrencyId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($fromCurrencyId, $toCurrencyId) {
            return CurrencyExchangeRate::where('from_currency_id', $fromCurrencyId)
                ->where('to_currency_id', $toCurrencyId)
                ->forDate()
                ->first();
        });
    }

    /**
     * Convert an amount from one currency to another.
     */
    public function convert(float $amount, int $fromCurrencyId, int $toCurrencyId): ?float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        // Same currency = no conversion needed
        if ($fromCurrencyId === $toCurrencyId) {
            return round($amount, 2);
        }

        $rate = $this->getLatestRate($fromCurrencyId, $toCurrencyId);
        
        if (!$rate) {
            return null;
        }

        $convertedAmount = $amount * (float) $rate->rate;
        
        return round($convertedAmount, 2);
    }

    /**
     * Get all latest exchange rates.
     */
    public function getAllLatestRates(): Collection
    {
        return Cache::remember('all_latest_exchange_rates', self::CACHE_DURATION, function () {
            // Get the latest rate for each currency pair
            $rates = CurrencyExchangeRate::forDate()
                ->with(['fromCurrency', 'toCurrency'])
                ->get()
                ->groupBy(function ($rate) {
                    return $rate->from_currency_id . '_' . $rate->to_currency_id;
                })
                ->map(function ($group) {
                    return $group->sortByDesc('effective_date')->first();
                })
                ->values();
                
            return $rates;
        });
    }
}
