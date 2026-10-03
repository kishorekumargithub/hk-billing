<?php

namespace App\Services;

class SubscriptionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function changePlan(Customer $customer, Plan $newPlan, CarbonImmutable $effective): Subscription
    {
        return DB::transaction(function () use ($customer, $newPlan, $effective) {
            $current = Subscription::where('customer_id', $customer->id)->whereNull('ends_at')->lockForUpdate()->firstOrFail();

            if ($effective->lte($current->starts_at)) {
                // change on the same day the subscription started: replace instead of splitting
                $current->update(['plan_id' => $newPlan->id]);
                return $current;
            }

            $current->update(['ends_at' => $effective->subDay()->toDateString()]);   // old plan: up to the day before
            return Subscription::create([                                           // new plan: from the effective day
                'customer_id' => $customer->id,
                'plan_id'     => $newPlan->id,
                'starts_at'   => $effective->toDateString(),
            ]);
        });
    }
}
