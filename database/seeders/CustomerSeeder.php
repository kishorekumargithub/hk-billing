<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Fetch available merchant IDs (or default to merchant_id = 1)
        $merchantIds = DB::table('merchants')->pluck('id')->toArray();

        if (empty($merchantIds)) {
            $this->command->warn('No merchants found! Creating a fallback merchant with ID = 1...');
            DB::table('merchants')->insert([
                'id' => 1,
                'name' => 'Default Merchant',
                'api_key' => Str::random(32),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $merchantIds = [1];
        }

        $totalCustomers = 2000;
        $batchSize = 500;
        $batch = [];

        DB::disableQueryLog();
        $this->command->info("Seeding {$totalCustomers} customers...");

        for ($i = 1; $i <= $totalCustomers; $i++) {
            $merchantId = $faker->randomElement($merchantIds);

            $batch[] = [
                'merchant_id'  => $merchantId,
                'name'         => $faker->name(),
                'email'        => $faker->unique()->safeEmail(),
                // Ensures uniqueness per merchant to satisfy unique(['merchant_id', 'external_ref'])
                'external_ref' => 'CUST-' . $merchantId . '-' . Str::padLeft($i, 5, '0'),
                'created_at'   => now(),
                'updated_at'   => now(),
            ];

            if (count($batch) === $batchSize) {
                DB::table('customers')->insert($batch);
                $batch = [];
            }
        }

        // Insert remaining records
        if (!empty($batch)) {
            DB::table('customers')->insert($batch);
        }

        $this->command->info('2,000 customers seeded successfully!');
    }
}
