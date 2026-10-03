<?php

namespace App\Services;

class BillingCalculator
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * @param int          $basePaise     plan base price for the full cycle
     * @param int          $included      plan included units for the full cycle
     * @param float|string $ratePaise     overage rate (paise per unit)
     * @param int          $daysActive    days this plan was active in the cycle
     * @param int          $daysInCycle   total days in the cycle
     * @param int          $usage         units used while on this plan
     */
    public function segment(int $basePaise, int $included, float|string $ratePaise,
                            int $daysActive, int $daysInCycle, int $usage): array
    {
        $baseFee       = (int) round($basePaise * $daysActive / $daysInCycle);   // half up
        $allowance     = intdiv($included * $daysActive, $daysInCycle);          // floor
        $overageUnits  = max(0, $usage - $allowance);
        $overageCharge = (int) round($overageUnits * (float) $ratePaise);

        return [
            'base_fee'       => $baseFee,
            'allowance'      => $allowance,
            'overage_units'  => $overageUnits,
            'overage_charge' => $overageCharge,
            'subtotal'       => $baseFee + $overageCharge,
        ];
    }

}
