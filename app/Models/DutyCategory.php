<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'icon_name',
        'calculation_method_type_id',
        'duty_rate',
        'duty_unit_type_id',
        'exemption_quantity',
        'exemption_unit_type_id',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'duty_rate' => 'decimal:4',
        'exemption_quantity' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function calculationMethodType(): BelongsTo
    {
        return $this->belongsTo(CalculationMethodType::class);
    }

    public function dutyUnitType(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'duty_unit_type_id');
    }

    public function exemptionUnitType(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'exemption_unit_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        
        return $query->where(function ($q) use ($date) {
            $q->whereNull('effective_from')
              ->orWhere('effective_from', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('effective_to')
              ->orWhere('effective_to', '>=', $date);
        });
    }
}
