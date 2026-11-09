<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the admin (safe to run more than once).
     */
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@example.com']);
        $admin->forceFill([
            'name' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
            'email_verified_at' => now(),
        ])->save();
    }
}
