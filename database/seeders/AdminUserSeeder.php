<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // If you later add an 'is_admin' or 'role' column, update this seeder accordingly.
        $email = config('seed.admin_email', 'admin@example.com');
        $password = config('seed.admin_password', 'password'); // Change in production!

        // Avoid duplicate creation
        $user = User::where('email', $email)->first();
        if (!$user) {
            User::create([
                'name' => 'Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
            ]);
        }
    }
}
