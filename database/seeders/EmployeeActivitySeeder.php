<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\EmployeeActivity;
use Illuminate\Support\Facades\DB;

class EmployeeActivitySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Clear existing activities
        DB::table('employee_activities')->delete();
        
        // Get all employees
        $employees = Employee::all();
        
        if ($employees->isEmpty()) {
            $this->command->info('No employees found. Please run EmployeeSeeder first.');
            return;
        }
        
        foreach ($employees as $employee) {
            // Create login activities
            EmployeeActivity::create([
                'employee_id' => $employee->id,
                'activity_type' => 'login',
                'description' => 'Employee logged in successfully',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'metadata' => ['login_method' => 'web_form'],
                'performed_at' => now()->subHours(rand(1, 48))
            ]);
            
            EmployeeActivity::create([
                'employee_id' => $employee->id,
                'activity_type' => 'login',
                'description' => 'Employee logged in successfully',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'metadata' => ['login_method' => 'web_form'],
                'performed_at' => now()->subDays(rand(1, 7))
            ]);
            
            // Create profile update activity
            EmployeeActivity::create([
                'employee_id' => $employee->id,
                'activity_type' => 'profile_update',
                'description' => 'Profile information updated',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'metadata' => [
                    'changes' => [
                        'phone' => ['from' => 'old_phone', 'to' => $employee->phone],
                        'department' => ['from' => 'old_dept', 'to' => $employee->department]
                    ]
                ],
                'performed_at' => now()->subHours(rand(6, 72))
            ]);
            
            // Create logout activity
            EmployeeActivity::create([
                'employee_id' => $employee->id,
                'activity_type' => 'logout',
                'description' => 'Employee logged out',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'metadata' => ['session_duration' => rand(30, 480) . ' minutes'],
                'performed_at' => now()->subHours(rand(2, 24))
            ]);
        }
        
        $this->command->info('Employee activities seeded successfully!');
    }
}
