<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()
        ->count(4)->sequence(
            [
                'name' => 'Kishore Kumar',
                'email' => 'kishoremdu.kumar@gmail.com',
            ],
            [
                'name' => 'Rama Krishnan',
                'email' => 'rama@krishnan.com',
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
        )->create();
    }
}
