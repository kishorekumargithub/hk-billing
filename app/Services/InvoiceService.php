<?php

namespace App\Services;

class InvoiceService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private BillingCalculator $calc)
    {
        //
    }

    public function generate(Customer $customer, CarbonImmutable $start, CarbonImmutable $end): ?Invoice
    {
        $start = $start->startOfDay();
        $end   = $end->startOfDay();
        $daysInCycle = (int) $start->diffInDays($end) + 1;

        return DB::transaction(function () use ($customer, $start, $end, $daysInCycle) {
            if (Invoice::where('customer_id', $customer->id)->where('period_start', $start->toDateString())->exists()) {
                return null;                                   // already billed, idempotent
            }

            $subs = Subscription::with('plan')
                ->where('customer_id', $customer->id)
                ->whereDate('starts_at', '<=', $end)
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $start))
                ->orderBy('starts_at')->get();

            if ($subs->isEmpty()) return null;

            $invoice = Invoice::create([
                'merchant_id' => $customer->merchant_id,
                'customer_id' => $customer->id,
                'period_start' => $start->toDateString(),
                'period_end'   => $end->toDateString(),
                'total'        => 0,
            ]);

            $total = 0;
            foreach ($subs as $sub) {
                $segStart = CarbonImmutable::parse(max($sub->starts_at, $start))->startOfDay();
                $segEnd   = CarbonImmutable::parse($sub->ends_at ? min($sub->ends_at, $end) : $end)->startOfDay();
                $daysActive = (int) $segStart->diffInDays($segEnd) + 1;

                // Usage is split by DATE, so before/after the change are priced on their own plan
                $usage = (int) DB::table('usage_daily')
                    ->where('customer_id', $customer->id)
                    ->whereBetween('usage_date', [$segStart->toDateString(), $segEnd->toDateString()])
                    ->sum('total_units');

                $plan = $sub->plan;
                $r = $this->calc->segment($plan->base_price, $plan->included_units,
                                          $plan->overage_rate, $daysActive, $daysInCycle, $usage);

                $invoice->lines()->create([
                    'subscription_id' => $sub->id,
                    'plan_snapshot'   => $plan->only(['id','name','base_price','included_units','overage_rate','version']),
                    'segment_start'   => $segStart->toDateString(),
                    'segment_end'     => $segEnd->toDateString(),
                    'days_active'     => $daysActive,
                    'days_in_cycle'   => $daysInCycle,
                    'usage_units'     => $usage,
                    'base_fee'        => $r['base_fee'],
                    'allowance'       => $r['allowance'],
                    'overage_units'   => $r['overage_units'],
                    'overage_charge'  => $r['overage_charge'],
                ]);
                $total += $r['subtotal'];
            }

            $invoice->update(['total' => $total]);
            return $invoice;
        });
    }

}
