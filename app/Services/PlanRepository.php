<?php

namespace App\Services;

use App\Models\Plan;
use Illuminate\Support\Facades\Cache;

class PlanRepository
{

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function find(int $planId): Plan
    {
        return Cache::remember("plan:$planId", 600, fn () => Plan::findOrFail($planId));
    }

    public static function forget(int $planId): void
    {
        Cache::forget("plan:$planId");
    }
}
