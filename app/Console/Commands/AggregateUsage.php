<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AggregateUsage extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage:aggregate {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate raw usage events into daily totals per customer chunk';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ?: now()->toDateString();
        $jobs = [];

        Customer::query()->select('id')->orderBy('id')->chunkById(5000, function ($chunk) use ($date, &$jobs) {
            $jobs[] = new AggregateUsageChunkJob($date, $chunk->first()->id, $chunk->last()->id);
        });

        Bus::batch($jobs)->name("aggregate-$date")->allowFailures()->dispatch();
        $this->info(count($jobs).' chunk jobs dispatched for '.$date);
        return self::SUCCESS;
    }
}
