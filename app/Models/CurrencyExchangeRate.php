<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrencyExchangeRate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'effective_date',
        'source',
        'created_at',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('effective_date', 'desc');
    }

    public function scopeForDate($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        
        // For SQLite compatibility, use DATE() function to extract date part
        return $query->whereRaw('DATE(effective_date) <= ?', [$date])
            ->orderBy('effective_date', 'desc');
    }
}
