<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsageController extends Controller
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
    public function store(StoreUsageRequest $request)
    {
        $merchant = $request->attributes->get('merchant');
        $data = $request->validated();

        // Tenant check, cached for positive results only (a new customer must not 404 for minutes)
        $cacheKey = "cust:{$merchant->id}:{$data['customer_id']}";
        if (! Cache::get($cacheKey)) {
            $exists = Customer::where('merchant_id', $merchant->id)
                ->whereKey($data['customer_id'])->exists();
            abort_unless($exists, 404, 'Customer not found');
            Cache::put($cacheKey, true, 300);
        }

        // One narrow INSERT. The unique index (merchant_id, idempotency_key) makes retries safe:
        // insertOrIgnore returns 0 when the key already exists, so nothing is double-counted.
        $inserted = DB::table('usage_events')->insertOrIgnore([
            'merchant_id'     => $merchant->id,
            'customer_id'     => $data['customer_id'],
            'units'           => $data['units'],
            'usage_date'      => $data['usage_date'],
            'idempotency_key' => $data['idempotency_key'],
            'created_at'      => now(),
        ]);

        return response()->json(
            ['status' => 'ok', 'duplicate' => $inserted === 0],
            $inserted ? 201 : 200
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
