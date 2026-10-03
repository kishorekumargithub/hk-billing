<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Merchant;
use Illuminate\Support\Str;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Generate unhashed key with prefix
        $plainKey = 'hk_' . Str::random(40);

        // 2. Hash key with SHA-256
        $hashedKey = hash('sha256', $plainKey);

        // 3. Store only the hashed key in the database
        $merchant = Merchant::create([
            'name' => 'Acme Corporation',
            'api_key_hash' => $hashedKey,
        ]);

        // 4. Output the plain key to console (printed only once)
        $this->command->info("Merchant created successfully!");
        $this->command->warn("--------------------------------------------------");
        $this->command->warn("Merchant ID   : {$merchant->id}");
        $this->command->warn("Merchant Name : {$merchant->name}");
        $this->command->warn("Plain API Key : {$plainKey}");
        $this->command->warn("--------------------------------------------------");
        $this->command->error("SAVE THIS KEY NOW! It will not be shown again.");
    }
}
