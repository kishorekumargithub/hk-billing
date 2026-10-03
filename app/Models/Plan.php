<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{

    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'overage_rate' => 'decimal:4',
        ];
    }

    /**
     * 
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * 
     */
    protected static function booted(): void
    {
        static::saved(fn (Plan $p) => PlanRepository::forget($p->id));
        static::deleted(fn (Plan $p) => PlanRepository::forget($p->id));
    }

}
