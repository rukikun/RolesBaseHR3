<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SetupPesoDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:peso-database {--fresh : Run fresh migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup database with Philippine Peso currency and employee data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up HR3 System with Philippine Peso currency...');

        try {
            // Run fresh migration if requested
            if ($this->option('fresh')) {
                $this->info('🔄 Running fresh migration...');
                Artisan::call('migrate:fresh');
                $this->info('✅ Fresh migration completed');
            }

            // Populate type data with peso amounts
            $this->info('💰 Populating type data with Peso amounts...');
            $this->populateClaimTypes();
            $this->populateLeaveTypes();
            $this->populateShiftTypes();

            // Populate employees with peso salaries
            $this->info('👥 Creating employees with Peso salaries...');
            $this->populateEmployees();

            // Create sample claims with peso amounts
            $this->info('📋 Creating sample claims with Peso amounts...');
            $this->populateClaims();

            // Create sample timesheet entries
            $this->info('⏰ Creating sample timesheet entries...');
            $this->populateTimesheets();

            $this->info('');
            $this->info('🎉 Database setup completed successfully!');
            $this->info('💱 All amounts are now in Philippine Peso (₱)');
            
            // Display summary
            $this->displaySummary();

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function populateClaimTypes()
    {
        $claimTypes = [
            ['name' => 'Travel Expenses', 'code' => 'TRAVEL', 'description' => 'Business travel related expenses', 'max_amount' => 15000.00, 'requires_attachment' => 1, 'auto_approve' => 0, 'is_active' => 1],
            ['name' => 'Office Supplies', 'code' => 'OFFICE', 'description' => 'Office supplies and equipment', 'max_amount' => 5000.00, 'requires_attachment' => 1, 'auto_approve' => 0, 'is_active' => 1],
            ['name' => 'Meal Allowance', 'code' => 'MEAL', 'description' => 'Business meal expenses', 'max_amount' => 2000.00, 'requires_attachment' => 1, 'auto_approve' => 0, 'is_active' => 1],
            ['name' => 'Training Costs', 'code' => 'TRAINING', 'description' => 'Professional development and training', 'max_amount' => 25000.00, 'requires_attachment' => 1, 'auto_approve' => 0, 'is_active' => 1],
            ['name' => 'Medical Expenses', 'code' => 'MEDICAL', 'description' => 'Medical and health related expenses', 'max_amount' => 10000.00, 'requires_attachment' => 1, 'auto_approve' => 0, 'is_active' => 1],
        ];

        foreach ($claimTypes as $claimType) {
            DB::table('claim_types')->insert(array_merge($claimType, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    private function populateLeaveTypes()
    {
        $leaveTypes = [
            ['name' => 'Annual Leave', 'code' => 'AL', 'description' => 'Annual vacation leave', 'days_allowed' => 0, 'max_days_per_year' => 21, 'carry_forward' => 1, 'requires_approval' => 1, 'status' => 'active', 'is_active' => 1],
            ['name' => 'Sick Leave', 'code' => 'SL', 'description' => 'Medical sick leave', 'days_allowed' => 0, 'max_days_per_year' => 10, 'carry_forward' => 0, 'requires_approval' => 0, 'status' => 'active', 'is_active' => 1],
            ['name' => 'Emergency Leave', 'code' => 'EL', 'description' => 'Emergency family leave', 'days_allowed' => 0, 'max_days_per_year' => 5, 'carry_forward' => 0, 'requires_approval' => 1, 'status' => 'active', 'is_active' => 1],
            ['name' => 'Maternity Leave', 'code' => 'ML', 'description' => 'Maternity leave', 'days_allowed' => 0, 'max_days_per_year' => 90, 'carry_forward' => 0, 'requires_approval' => 1, 'status' => 'active', 'is_active' => 1],
            ['name' => 'Paternity Leave', 'code' => 'PL', 'description' => 'Paternity leave', 'days_allowed' => 0, 'max_days_per_year' => 7, 'carry_forward' => 0, 'requires_approval' => 1, 'status' => 'active', 'is_active' => 1],
        ];

        foreach ($leaveTypes as $leaveType) {
            DB::table('leave_types')->insert(array_merge($leaveType, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    private function populateShiftTypes()
    {
        $shiftTypes = [
            ['name' => 'Morning Shift', 'code' => 'MORNING', 'description' => 'Standard morning shift for regular operations', 'default_start_time' => '08:00:00', 'default_end_time' => '16:00:00', 'break_duration' => 60, 'hourly_rate' => 350.00, 'color_code' => '#28a745', 'type' => 'day', 'is_active' => 1],
            ['name' => 'Afternoon Shift', 'code' => 'AFTERNOON', 'description' => 'Afternoon to evening coverage shift', 'default_start_time' => '14:00:00', 'default_end_time' => '22:00:00', 'break_duration' => 45, 'hourly_rate' => 385.00, 'color_code' => '#ffc107', 'type' => 'swing', 'is_active' => 1],
            ['name' => 'Night Shift', 'code' => 'NIGHT', 'description' => 'Overnight shift with premium pay', 'default_start_time' => '22:00:00', 'default_end_time' => '06:00:00', 'break_duration' => 60, 'hourly_rate' => 450.00, 'color_code' => '#6f42c1', 'type' => 'night', 'is_active' => 1],
            ['name' => 'Split Shift', 'code' => 'SPLIT', 'description' => 'Split shift with extended break period', 'default_start_time' => '09:00:00', 'default_end_time' => '17:00:00', 'break_duration' => 120, 'hourly_rate' => 335.00, 'color_code' => '#17a2b8', 'type' => 'split', 'is_active' => 1],
            ['name' => 'Weekend Shift', 'code' => 'WEEKEND', 'description' => 'Weekend coverage with rotating schedule', 'default_start_time' => '10:00:00', 'default_end_time' => '18:00:00', 'break_duration' => 45, 'hourly_rate' => 420.00, 'color_code' => '#fd7e14', 'type' => 'rotating', 'is_active' => 1],
        ];

        foreach ($shiftTypes as $shiftType) {
            DB::table('shift_types')->insert(array_merge($shiftType, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    private function populateEmployees()
    {
        $employees = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john.doe@jetlouge.com', 'phone' => '+63 912 345 6789', 'position' => 'Software Developer', 'department' => 'IT', 'hire_date' => '2024-01-15', 'salary' => 95000.00, 'status' => 'active'],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane.smith@jetlouge.com', 'phone' => '+63 917 234 5678', 'position' => 'HR Manager', 'department' => 'Human Resources', 'hire_date' => '2023-06-10', 'salary' => 85000.00, 'status' => 'active'],
            ['first_name' => 'Mike', 'last_name' => 'Johnson', 'email' => 'mike.johnson@jetlouge.com', 'phone' => '+63 918 345 6789', 'position' => 'Marketing Specialist', 'department' => 'Marketing', 'hire_date' => '2024-03-20', 'salary' => 75000.00, 'status' => 'active'],
            ['first_name' => 'Sarah', 'last_name' => 'Wilson', 'email' => 'sarah.wilson@jetlouge.com', 'phone' => '+63 919 456 7890', 'position' => 'Accountant', 'department' => 'Finance', 'hire_date' => '2023-11-05', 'salary' => 80000.00, 'status' => 'active'],
            ['first_name' => 'David', 'last_name' => 'Brown', 'email' => 'david.brown@jetlouge.com', 'phone' => '+63 920 567 8901', 'position' => 'Operations Manager', 'department' => 'Operations', 'hire_date' => '2024-02-01', 'salary' => 90000.00, 'status' => 'active'],
            ['first_name' => 'Super', 'last_name' => 'Admin', 'email' => 'admin@jetlouge.com', 'phone' => '+63 912 345 6789', 'position' => 'System Administrator', 'department' => 'IT', 'hire_date' => '2023-01-15', 'salary' => 120000.00, 'status' => 'active'],
            ['first_name' => 'Maria', 'last_name' => 'Garcia', 'email' => 'maria.garcia@jetlouge.com', 'phone' => '+63 921 678 9012', 'position' => 'Travel Consultant', 'department' => 'Sales', 'hire_date' => '2024-04-10', 'salary' => 65000.00, 'status' => 'active'],
            ['first_name' => 'Carlos', 'last_name' => 'Rodriguez', 'email' => 'carlos.rodriguez@jetlouge.com', 'phone' => '+63 922 789 0123', 'position' => 'Finance Manager', 'department' => 'Finance', 'hire_date' => '2023-08-15', 'salary' => 95000.00, 'status' => 'active'],
            ['first_name' => 'Ana', 'last_name' => 'Santos', 'email' => 'ana.santos@jetlouge.com', 'phone' => '+63 923 890 1234', 'position' => 'HR Scheduler', 'department' => 'Human Resources', 'hire_date' => '2024-05-20', 'salary' => 65000.00, 'status' => 'active'],
            ['first_name' => 'Roberto', 'last_name' => 'Cruz', 'email' => 'roberto.cruz@jetlouge.com', 'phone' => '+63 924 901 2345', 'position' => 'Customer Service Representative', 'department' => 'Operations', 'hire_date' => '2024-06-01', 'salary' => 45000.00, 'status' => 'active'],
        ];

        foreach ($employees as $employee) {
            DB::table('employees')->insert(array_merge($employee, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    private function populateClaims()
    {
        $claims = [
            ['employee_id' => 1, 'claim_type_id' => 1, 'amount' => 12500.00, 'claim_date' => now()->subDays(5)->format('Y-m-d'), 'description' => 'Business trip to client meeting in Cebu - flight and hotel expenses', 'status' => 'pending'],
            ['employee_id' => 2, 'claim_type_id' => 2, 'amount' => 3200.00, 'claim_date' => now()->subDays(3)->format('Y-m-d'), 'description' => 'Office supplies: printer paper, pens, and folders', 'status' => 'approved'],
            ['employee_id' => 3, 'claim_type_id' => 3, 'amount' => 1800.00, 'claim_date' => now()->subDays(2)->format('Y-m-d'), 'description' => 'Lunch meeting with potential client', 'status' => 'approved'],
            ['employee_id' => 4, 'claim_type_id' => 4, 'amount' => 18000.00, 'claim_date' => now()->subDays(7)->format('Y-m-d'), 'description' => 'AWS Cloud Certification training course', 'status' => 'pending'],
            ['employee_id' => 5, 'claim_type_id' => 5, 'amount' => 4500.00, 'claim_date' => now()->subDays(4)->format('Y-m-d'), 'description' => 'Annual health checkup as required by company policy', 'status' => 'approved'],
        ];

        foreach ($claims as $claim) {
            DB::table('claims')->insert(array_merge($claim, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    private function populateTimesheets()
    {
        $timesheets = [
            ['employee_id' => 1, 'work_date' => now()->format('Y-m-d'), 'hours_worked' => 8.0, 'overtime_hours' => 0.0, 'status' => 'approved', 'description' => 'Regular work day'],
            ['employee_id' => 2, 'work_date' => now()->format('Y-m-d'), 'hours_worked' => 8.0, 'overtime_hours' => 1.5, 'status' => 'pending', 'description' => 'Overtime for project deadline'],
            ['employee_id' => 3, 'work_date' => now()->format('Y-m-d'), 'hours_worked' => 7.5, 'overtime_hours' => 0.0, 'status' => 'approved', 'description' => 'Marketing campaign work'],
            ['employee_id' => 4, 'work_date' => now()->format('Y-m-d'), 'hours_worked' => 8.0, 'overtime_hours' => 0.5, 'status' => 'approved', 'description' => 'Finance reporting tasks'],
            ['employee_id' => 5, 'work_date' => now()->format('Y-m-d'), 'hours_worked' => 8.0, 'overtime_hours' => 2.0, 'status' => 'pending', 'description' => 'Operations management overtime'],
        ];

        foreach ($timesheets as $timesheet) {
            DB::table('time_entries')->insert(array_merge($timesheet, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    private function displaySummary()
    {
        $this->info('📊 Database Summary:');
        $this->info('👥 Employees: ' . DB::table('employees')->count() . ' records');
        $this->info('📋 Claim Types: ' . DB::table('claim_types')->count() . ' records');
        $this->info('🏖️ Leave Types: ' . DB::table('leave_types')->count() . ' records');
        $this->info('⏰ Shift Types: ' . DB::table('shift_types')->count() . ' records');
        $this->info('💰 Claims: ' . DB::table('claims')->count() . ' records');
        $this->info('📝 Time Entries: ' . DB::table('time_entries')->count() . ' records');
        $this->info('');
        $this->info('💡 All amounts are now in Philippine Peso (₱)');
        $this->info('🚀 Your HR3 System is ready to use!');
    }
}
