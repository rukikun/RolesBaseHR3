<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserRoleDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@jetlouge.com',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@jetlouge.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true
            ],
            [
                'name' => 'HR Manager',
                'email' => 'hrmanager@jetlouge.com',
                'password' => Hash::make('password123'),
                'role' => 'hr_manager',
                'is_active' => true
            ],
            [
                'name' => 'HR Scheduler',
                'email' => 'hrscheduler@jetlouge.com',
                'password' => Hash::make('password123'),
                'role' => 'hr_scheduler',
                'is_active' => true
            ],
            [
                'name' => 'Attendance Admin',
                'email' => 'attendanceadmin@jetlouge.com',
                'password' => Hash::make('password123'),
                'role' => 'attendance_admin',
                'is_active' => true
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }
    }
}
