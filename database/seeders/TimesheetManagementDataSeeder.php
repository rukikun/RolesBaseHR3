<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimesheetManagementDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Timesheet Management Data Seeding...');

        // Get existing employees
        $employees = DB::table('employees')->where('status', 'active')->get();
        
        if ($employees->isEmpty()) {
            $this->command->error('❌ No active employees found. Please run EmployeeSeeder first.');
            return;
        }

        $this->command->info('👥 Found ' . $employees->count() . ' active employees');

        // Ensure we have shift types
        $this->seedShiftTypes();
        
        // Ensure we have claim types  
        $this->seedClaimTypes();
        
        // Create shift assignments
        $this->seedShiftAssignments($employees);
        
        // Create additional attendance records
        $this->seedAttendanceRecords($employees);
        
        // Create sample claims
        $this->seedClaims($employees);
        
        // Create leave requests
        $this->seedLeaveRequests($employees);

        $this->command->info('✅ Timesheet Management Data Seeding completed successfully!');
    }

    private function seedShiftTypes()
    {
        $this->command->info('📋 Seeding shift types...');
        
        $shiftTypes = [
            [
                'name' => 'Morning Shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'hourly_rate' => 350.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Afternoon Shift', 
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'hourly_rate' => 385.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '22:00:00', 
                'end_time' => '06:00:00',
                'hourly_rate' => 450.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($shiftTypes as $shiftType) {
            DB::table('shift_types')->updateOrInsert(
                ['name' => $shiftType['name']],
                $shiftType
            );
        }
    }

    private function seedClaimTypes()
    {
        $this->command->info('💰 Seeding claim types...');
        
        $claimTypes = [
            [
                'name' => 'Travel Expenses',
                'code' => 'TRAVEL',
                'description' => 'Business travel and transportation expenses',
                'max_amount' => 15000.00,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'OFFICE',
                'description' => 'Office supplies and equipment',
                'max_amount' => 5000.00,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Meal Allowance',
                'code' => 'MEAL',
                'description' => 'Meal expenses during work',
                'max_amount' => 2000.00,
                'requires_attachment' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($claimTypes as $claimType) {
            DB::table('claim_types')->updateOrInsert(
                ['code' => $claimType['code']],
                $claimType
            );
        }
    }

    private function seedShiftAssignments($employees)
    {
        $this->command->info('⏰ Seeding shift assignments...');
        
        // Get shift types
        $shiftTypes = DB::table('shift_types')->where('is_active', true)->get();
        
        if ($shiftTypes->isEmpty()) {
            $this->command->warn('⚠️ No shift types found');
            return;
        }

        // Create shifts for the past 30 days and next 7 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->addDays(7);
        
        $shiftsCreated = 0;

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Skip weekends for most employees
            if ($date->dayOfWeek == 0 || $date->dayOfWeek == 6) {
                continue;
            }

            foreach ($employees->take(3) as $employee) { // Create shifts for first 3 employees
                $shiftType = $shiftTypes->random();
                
                // Check if shift already exists
                $existingShift = DB::table('shifts')
                    ->where('employee_id', $employee->id)
                    ->where('shift_date', $date->format('Y-m-d'))
                    ->first();
                
                if (!$existingShift) {
                    DB::table('shifts')->insert([
                        'employee_id' => $employee->id,
                        'shift_type_id' => $shiftType->id,
                        'shift_date' => $date->format('Y-m-d'),
                        'start_time' => $shiftType->start_time,
                        'end_time' => $shiftType->end_time,
                        'location' => 'Main Office',
                        'break_duration' => 60, // 60 minutes
                        'status' => $date->isPast() ? 'completed' : 'scheduled',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $shiftsCreated++;
                }
            }
        }

        $this->command->info("📅 Created {$shiftsCreated} shift assignments");
    }

    private function seedAttendanceRecords($employees)
    {
        $this->command->info('🕐 Seeding additional attendance records...');
        
        $attendanceCreated = 0;
        
        // Create attendance for the past 14 days
        for ($i = 14; $i >= 1; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Skip weekends
            if ($date->dayOfWeek == 0 || $date->dayOfWeek == 6) {
                continue;
            }
            
            foreach ($employees->take(5) as $employee) { // Create attendance for first 5 employees
                // Check if attendance already exists
                $existingAttendance = DB::table('attendances')
                    ->where('employee_id', $employee->id)
                    ->where('date', $date->format('Y-m-d'))
                    ->first();
                
                if (!$existingAttendance) {
                    // Generate realistic times
                    $clockIn = $date->copy()->setTime(8, rand(0, 30), 0);
                    $clockOut = $date->copy()->setTime(17, rand(0, 60), 0);
                    $totalHours = $clockOut->diffInHours($clockIn) - 1; // Subtract 1 hour for lunch
                    $overtimeHours = max(0, $totalHours - 8);
                    
                    DB::table('attendances')->insert([
                        'employee_id' => $employee->id,
                        'date' => $date->format('Y-m-d'),
                        'clock_in_time' => $clockIn,
                        'clock_out_time' => $clockOut,
                        'total_hours' => $totalHours,
                        'overtime_hours' => $overtimeHours,
                        'status' => 'clocked_out',
                        'location' => 'Main Office',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $attendanceCreated++;
                }
            }
        }

        $this->command->info("⏱️ Created {$attendanceCreated} attendance records");
    }

    private function seedClaims($employees)
    {
        $this->command->info('💳 Seeding sample claims...');
        
        $claimTypes = DB::table('claim_types')->where('is_active', true)->get();
        
        if ($claimTypes->isEmpty()) {
            $this->command->warn('⚠️ No claim types found');
            return;
        }

        $claimsCreated = 0;
        $statuses = ['pending', 'approved', 'rejected', 'paid'];
        
        foreach ($employees->take(4) as $employee) { // Create claims for first 4 employees
            for ($i = 0; $i < rand(2, 5); $i++) {
                $claimType = $claimTypes->random();
                $amount = rand(500, min(5000, $claimType->max_amount ?? 5000));
                
                DB::table('claims')->insert([
                    'employee_id' => $employee->id,
                    'claim_type_id' => $claimType->id,
                    'amount' => $amount,
                    'claim_date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                    'description' => "Sample {$claimType->name} claim for business purposes",
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $claimsCreated++;
            }
        }

        $this->command->info("💰 Created {$claimsCreated} sample claims");
    }

    private function seedLeaveRequests($employees)
    {
        $this->command->info('🏖️ Seeding leave requests...');
        
        // Ensure we have leave types
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'max_days_per_year' => 15,
                'is_paid' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Sick Leave',
                'max_days_per_year' => 10,
                'is_paid' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Personal Leave',
                'max_days_per_year' => 5,
                'is_paid' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($leaveTypes as $leaveType) {
            DB::table('leave_types')->updateOrInsert(
                ['name' => $leaveType['name']],
                $leaveType
            );
        }

        $leaveTypes = DB::table('leave_types')->where('is_active', true)->get();
        $leaveRequestsCreated = 0;
        $statuses = ['pending', 'approved', 'rejected'];
        
        foreach ($employees->take(3) as $employee) { // Create leave requests for first 3 employees
            for ($i = 0; $i < rand(1, 3); $i++) {
                $leaveType = $leaveTypes->random();
                $startDate = Carbon::now()->addDays(rand(1, 60));
                $endDate = $startDate->copy()->addDays(rand(1, 5));
                
                DB::table('leave_requests')->insert([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'days_requested' => $startDate->diffInDays($endDate) + 1,
                    'reason' => "Sample {$leaveType->name} request",
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $leaveRequestsCreated++;
            }
        }

        $this->command->info("📋 Created {$leaveRequestsCreated} leave requests");
    }
}
