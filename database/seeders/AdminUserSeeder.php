<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@jetlouge.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567890',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create additional admin roles for testing
        User::create([
            'name' => 'HR Manager',
            'email' => 'hr.manager@jetlouge.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567891',
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'HR Scheduler',
            'email' => 'hr.scheduler@jetlouge.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567892',
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Attendance Admin',
            'email' => 'attendance.admin@jetlouge.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567893',
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);
    }
}
