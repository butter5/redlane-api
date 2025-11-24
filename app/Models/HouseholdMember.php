<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseholdMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'address_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'relationship_type_id',
        'is_primary_declarant',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_primary_declarant' => 'boolean',
        ];
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function relationshipType(): BelongsTo
    {
        return $this->belongsTo(RelationshipType::class);
    }

    public function getAgeAttribute(): int
    {
        return Carbon::parse($this->date_of_birth)->age;
    }
}
