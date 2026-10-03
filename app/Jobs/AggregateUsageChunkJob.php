<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AggregateUsageChunkJob implements ShouldQueue
{

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $date,
        public int $minCustomerId,
        public int $maxCustomerId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $rows = DB::table('usage_events')
            ->selectRaw('customer_id, usage_date, SUM(units) AS total_units')
            ->where('usage_date', $this->date)
            ->whereBetween('customer_id', [$this->minCustomerId, $this->maxCustomerId])
            ->groupBy('customer_id', 'usage_date')
            ->get()
            ->map(fn ($r) => [
                'customer_id' => $r->customer_id,
                'usage_date'  => $r->usage_date,
                'total_units' => $r->total_units,
                'created_at'  => now(),
                'updated_at'  => now(),
            ])->all();

        if ($rows) {
            // SET (not add) => safe to re-run any number of times idempotently
            DB::table('usage_daily')->upsert(
                $rows,
                ['customer_id', 'usage_date'],
                ['total_units', 'updated_at']
            );
        }
    }
}
