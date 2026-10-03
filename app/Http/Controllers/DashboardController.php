<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id)
    {
        $merchant = $request->attributes->get('merchant');
        abort_unless($merchant->id === $id, 403);

        return Cache::remember("dashboard:$id", 120, fn () => $this->build($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * 
     */
    private function build(int $merchantId): array
    {
        $today     = CarbonImmutable::today();
        $monthStart = $today->startOfMonth();
        $daysInMonth = $today->daysInMonth;
        $daysElapsed = $today->day;

        // 1) Top 5 customers by usage this month
        $top = DB::table('usage_daily as u')
            ->join('customers as c', 'c.id', '=', 'u.customer_id')
            ->where('c.merchant_id', $merchantId)
            ->whereBetween('u.usage_date', [$monthStart->toDateString(), $today->toDateString()])
            ->groupBy('c.id', 'c.name')
            ->selectRaw('c.id, c.name, SUM(u.total_units) AS total')
            ->orderByDesc('total')->limit(5)->get();

        // 2) Projected overage revenue (run-rate): usage so far / days elapsed * days in month
        $perCustomer = DB::table('usage_daily as u')
            ->join('customers as c', 'c.id', '=', 'u.customer_id')
            ->join('subscriptions as s', fn ($j) => $j->on('s.customer_id', '=', 'c.id')->whereNull('s.ends_at'))
            ->join('plans as p', 'p.id', '=', 's.plan_id')
            ->where('c.merchant_id', $merchantId)
            ->whereBetween('u.usage_date', [$monthStart->toDateString(), $today->toDateString()])
            ->groupBy('c.id', 'p.included_units', 'p.overage_rate')
            ->selectRaw('c.id, SUM(u.total_units) AS used, p.included_units, p.overage_rate')
            ->get();

        $projectedOveragePaise = $perCustomer->sum(function ($r) use ($daysInMonth, $daysElapsed) {
            $projected = $r->used / $daysElapsed * $daysInMonth;
            return max(0, $projected - $r->included_units) * (float) $r->overage_rate;
        });

        // 3) Churn risk: month-to-date vs the SAME number of days last month
        $prevStart = $monthStart->subMonthNoOverflow();
        $prevEnd   = $prevStart->addDays($daysElapsed - 1);

        $sub = DB::table('usage_daily as u')
            ->join('customers as c', 'c.id', '=', 'u.customer_id')
            ->where('c.merchant_id', $merchantId)
            ->whereBetween('u.usage_date', [$prevStart->toDateString(), $today->toDateString()])
            ->groupBy('c.id', 'c.name')
            ->selectRaw(
                'c.id, c.name,
                SUM(CASE WHEN u.usage_date >= ? THEN u.total_units ELSE 0 END) AS cur_units,
                SUM(CASE WHEN u.usage_date BETWEEN ? AND ? THEN u.total_units ELSE 0 END) AS prev_units',
                [$monthStart->toDateString(), $prevStart->toDateString(), $prevEnd->toDateString()]
            );

        $churn = DB::query()->fromSub($sub, 't')
            ->where('prev_units', '>', 0)
            ->whereRaw('cur_units < prev_units * 0.5')
            ->get()
            ->map(fn ($r) => ['customer' => $r->name,
                            'drop_percent' => (int) round((1 - $r->cur_units / $r->prev_units) * 100)]);

        // 4) 30-day trend
        $trend = DB::table('usage_daily as u')
            ->join('customers as c', 'c.id', '=', 'u.customer_id')
            ->where('c.merchant_id', $merchantId)
            ->where('u.usage_date', '>=', $today->subDays(29)->toDateString())
            ->groupBy('u.usage_date')->orderBy('u.usage_date')
            ->selectRaw('u.usage_date AS date, SUM(u.total_units) AS units')->get();

        return [
            'top_customers'             => $top,
            'projected_overage_revenue' => round($projectedOveragePaise / 100, 2),   // rupees
            'churn_risk'                => $churn,
            'daily_trend'               => $trend,
        ];
    }

}
