<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Constants\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'firstname' => 'Admin',
            'lastname' => 'Hoch',
            'email' => 'test@example.com',
            'role' => UserRole::ADMIN,
            'password' => app('hash')->make('password'),
        ]);
    }
}
