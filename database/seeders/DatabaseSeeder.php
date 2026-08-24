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

        User::updateOrCreate(['email' => env('ADMIN_EMAIL', 'admin@storez.local')], [
            'name' => 'StoreZ Administrator',
            'password' => env('ADMIN_PASSWORD', 'change-me-local'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}
