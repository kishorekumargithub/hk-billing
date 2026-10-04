<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScaleProofSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding 5,000,000 usage events...');

        DB::disableQueryLog();
        $batch = [];
        $totalRows = 5000000;
        $batchSize = 5000;

        for ($i = 1; $i <= $totalRows; $i++) {
            $batch[] = [
                'merchant_id' => 1,
                'customer_id' => random_int(1, 2000),
                'units' => random_int(1, 50),
                'usage_date' => now()->subDays(random_int(0, 60))->toDateString(),
                'idempotency_key' => (string) Str::uuid(),
                'created_at' => now(),
            ];

            if (count($batch) === $batchSize) {
                DB::table('usage_events')->insert($batch);
                $batch = [];

                if ($i % 500_000 === 0) {
                    $this->command->info("Inserted {$i} / {$totalRows} rows...");
                }
            }
        }

        $this->command->info('Scale proof seeding completed!');
    }
}
