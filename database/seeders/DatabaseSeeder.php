<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@internestate.com',
            ],
            [
                'name' => 'System Administrator',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'investor@internestate.com'],
            [
                'name' => 'Demo Investor',
                'role' => 'investor',
                'password' => Hash::make('investor123'),
            ]
        );
    }
}
