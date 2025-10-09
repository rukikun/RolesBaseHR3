<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('employees')->delete();
        
        // Insert sample employees with different roles
        $employees = [
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'admin@jetlouge.com',
                'phone' => '+63 912 345 6789',
                'position' => 'System Administrator',
                'department' => 'IT',
                'hire_date' => '2023-01-15',
                'salary' => 120000.00,
                'status' => 'active',
                'role' => 'super_admin',
                'online_status' => 'offline',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@jetlouge.com',
                'phone' => '+63 917 234 5678',
                'position' => 'HR Manager',
                'department' => 'Human Resources',
                'hire_date' => '2022-06-10',
                'salary' => 85000.00,
                'status' => 'active',
                'role' => 'hr_manager',
                'online_status' => 'online',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@jetlouge.com',
                'phone' => '+63 918 345 6789',
                'position' => 'Admin',
                'department' => 'Administration',
                'hire_date' => '2023-03-20',
                'salary' => 75000.00,
                'status' => 'active',
                'role' => 'admin',
                'online_status' => 'offline',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Wilson',
                'email' => 'sarah.wilson@jetlouge.com',
                'phone' => '+63 919 456 7890',
                'position' => 'HR Scheduler',
                'department' => 'Human Resources',
                'hire_date' => '2023-08-05',
                'salary' => 65000.00,
                'status' => 'active',
                'role' => 'hr_scheduler',
                'online_status' => 'online',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@jetlouge.com',
                'phone' => '+63 920 567 8901',
                'position' => 'Software Developer',
                'department' => 'IT',
                'hire_date' => '2022-11-12',
                'salary' => 70000.00,
                'status' => 'active',
                'role' => 'employee',
                'online_status' => 'offline',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($employees as $employee) {
            DB::table('employees')->insert($employee);
        }
    }
}
