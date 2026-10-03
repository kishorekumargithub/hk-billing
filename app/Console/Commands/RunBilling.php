<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:run {--month=}';   // e.g. 2026-09, defaults to the previous month

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month') ? CarbonImmutable::parse($this->option('month').'-01') : now()->subMonthNoOverflow()->startOfMonth()->toImmutable();
        $start = $month->startOfMonth();
        $end   = $month->endOfMonth();

        Customer::query()->chunkById(500, function ($customers) use ($start, $end) {
            Bus::batch($customers->map(fn ($c) => new GenerateInvoiceJob($c->id, $start->toDateString(), $end->toDateString()))->all())
                ->name('billing-'.$start->format('Y-m'))->allowFailures()->dispatch();
        });
        return self::SUCCESS;
    }
}
