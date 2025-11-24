<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\CurrencyExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usd = Currency::where('code', 'USD')->first();
        $bmd = Currency::where('code', 'BMD')->first();
        $eur = Currency::where('code', 'EUR')->first();
        $gbp = Currency::where('code', 'GBP')->first();
        $cad = Currency::where('code', 'CAD')->first();
        $jpy = Currency::where('code', 'JPY')->first();

        $today = now()->toDateString();

        $rates = [
            // USD to other currencies
            ['from' => $usd->id, 'to' => $bmd->id, 'rate' => 1.000000, 'date' => $today, 'source' => 'manual'],
            ['from' => $usd->id, 'to' => $eur->id, 'rate' => 0.920000, 'date' => $today, 'source' => 'manual'],
            ['from' => $usd->id, 'to' => $gbp->id, 'rate' => 0.790000, 'date' => $today, 'source' => 'manual'],
            ['from' => $usd->id, 'to' => $cad->id, 'rate' => 1.350000, 'date' => $today, 'source' => 'manual'],
            ['from' => $usd->id, 'to' => $jpy->id, 'rate' => 149.500000, 'date' => $today, 'source' => 'manual'],
            
            // BMD to other currencies (BMD = USD)
            ['from' => $bmd->id, 'to' => $usd->id, 'rate' => 1.000000, 'date' => $today, 'source' => 'manual'],
            ['from' => $bmd->id, 'to' => $eur->id, 'rate' => 0.920000, 'date' => $today, 'source' => 'manual'],
            ['from' => $bmd->id, 'to' => $gbp->id, 'rate' => 0.790000, 'date' => $today, 'source' => 'manual'],
            ['from' => $bmd->id, 'to' => $cad->id, 'rate' => 1.350000, 'date' => $today, 'source' => 'manual'],
            ['from' => $bmd->id, 'to' => $jpy->id, 'rate' => 149.500000, 'date' => $today, 'source' => 'manual'],
            
            // Other currencies to USD
            ['from' => $eur->id, 'to' => $usd->id, 'rate' => 1.086957, 'date' => $today, 'source' => 'manual'],
            ['from' => $gbp->id, 'to' => $usd->id, 'rate' => 1.265823, 'date' => $today, 'source' => 'manual'],
            ['from' => $cad->id, 'to' => $usd->id, 'rate' => 0.740741, 'date' => $today, 'source' => 'manual'],
            ['from' => $jpy->id, 'to' => $usd->id, 'rate' => 0.006689, 'date' => $today, 'source' => 'manual'],
        ];

        foreach ($rates as $rate) {
            CurrencyExchangeRate::firstOrCreate(
                [
                    'from_currency_id' => $rate['from'],
                    'to_currency_id' => $rate['to'],
                    'effective_date' => $rate['date'],
                ],
                [
                    'rate' => $rate['rate'],
                    'source' => $rate['source'],
                    'created_at' => now(),
                ]
            );
        }
    }
}
