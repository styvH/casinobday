<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HouseAccount;

class HouseSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure a single house exists
        $house = HouseAccount::first();
        if (!$house) {
            HouseAccount::create([
                // 1,000 milliards € = 1,000,000,000,000 € => in cents: 100,000,000,000,000
                'starting_balance_cents' => 100000000000000,
                'balance_cents' => 100000000000000,
            ]);
        }
    }
}
