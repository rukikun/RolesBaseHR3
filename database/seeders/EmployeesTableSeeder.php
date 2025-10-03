<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if employees already exist to avoid duplicates
        $existingCount = DB::table('employees')->count();
        
        if ($existingCount > 0) {
            $this->command->info('Employees table already has data. Skipping seeding.');
            return;
        }

        $employees = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@jetlouge.com',
                'phone' => '+63 912 345 6789',
                'position' => 'Software Developer',
                'department' => 'Information Technology',
                'hire_date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'salary' => 75000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@jetlouge.com',
                'phone' => '+63 912 345 6790',
                'position' => 'HR Manager',
                'department' => 'Human Resources',
                'hire_date' => Carbon::now()->subMonths(12)->format('Y-m-d'),
                'salary' => 85000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@jetlouge.com',
                'phone' => '+63 912 345 6791',
                'position' => 'Accountant',
                'department' => 'Finance',
                'hire_date' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                'salary' => 65000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Wilson',
                'email' => 'sarah.wilson@jetlouge.com',
                'phone' => '+63 912 345 6792',
                'position' => 'Marketing Specialist',
                'department' => 'Marketing',
                'hire_date' => Carbon::now()->subMonths(4)->format('Y-m-d'),
                'salary' => 60000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@jetlouge.com',
                'phone' => '+63 912 345 6793',
                'position' => 'Sales Representative',
                'department' => 'Sales',
                'hire_date' => Carbon::now()->subMonths(10)->format('Y-m-d'),
                'salary' => 55000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Garcia',
                'email' => 'lisa.garcia@jetlouge.com',
                'phone' => '+63 912 345 6794',
                'position' => 'Operations Manager',
                'department' => 'Operations',
                'hire_date' => Carbon::now()->subMonths(15)->format('Y-m-d'),
                'salary' => 90000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('employees')->insert($employees);
        
        $this->command->info('Successfully seeded ' . count($employees) . ' employees.');
    }
}
