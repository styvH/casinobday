<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // If you later add an 'is_admin' or 'role' column, update this seeder accordingly.
        $email = config('seed.admin_email', 'styvan.h@gmail.com');
        $password = config('seed.admin_password', 'password'); // Change in production!

        // Avoid duplicate creation
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'LeStyv',
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
            ]);
            // Create related account with default balance 100 000 € (10 000 000 cents)
            DB::table('user_accounts')->insert([
                'user_id' => $user->id,
                'balance_cents' => 10000000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('transactions')->insert([
                'user_id' => $user->id,
                'type' => 'initial_credit',
                'amount_cents' => 10000000,
                'balance_after_cents' => 10000000,
                'reference' => 'SEED-INIT',
                'meta' => json_encode(['note' => 'Solde initial']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
