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
        DB::table('employee_notifications')->delete();
        DB::table('employees')->delete();
        
        // Insert sample employees
        $employees = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@jetlouge.com',
                'phone' => '+63 912 345 6789',
                'position' => 'Software Developer',
                'department' => 'IT',
                'hire_date' => '2023-01-15',
                'salary' => 50000.00,
                'status' => 'active',
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
                'salary' => 60000.00,
                'status' => 'active',
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
                'position' => 'Accountant',
                'department' => 'Finance',
                'hire_date' => '2023-03-20',
                'salary' => 45000.00,
                'status' => 'active',
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
                'position' => 'Marketing Specialist',
                'department' => 'Marketing',
                'hire_date' => '2023-08-05',
                'salary' => 42000.00,
                'status' => 'active',
                'online_status' => 'online',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@jetlouge.com',
                'phone' => '+63 920 567 8901',
                'position' => 'Sales Representative',
                'department' => 'Sales',
                'hire_date' => '2022-11-12',
                'salary' => 40000.00,
                'status' => 'inactive',
                'online_status' => 'offline',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($employees as $employee) {
            $employeeId = DB::table('employees')->insertGetId($employee);
            
            // Add sample notifications for each employee
            DB::table('employee_notifications')->insert([
                [
                    'employee_id' => $employeeId,
                    'type' => 'info',
                    'title' => 'Welcome to HR System',
                    'message' => 'Welcome to the Jetlouge HR Management System. Please update your profile information.',
                    'sent_at' => now(),
                    'priority' => 'medium',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'employee_id' => $employeeId,
                    'type' => 'reminder',
                    'title' => 'Timesheet Reminder',
                    'message' => 'Please submit your timesheet for this week.',
                    'sent_at' => now()->subDays(1),
                    'priority' => 'high',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
    }
}
