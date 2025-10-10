<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateTypeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate claim types, leave types, and shift types with data from SQL file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting type data population...');

        try {
            // Populate Claim Types
            $this->info('Populating claim_types...');
            // DB::table('claim_types')->truncate(); // Skip truncate for now
            
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
            $this->info('✓ Claim types populated successfully');

            // Populate Leave Types
            $this->info('Populating leave_types...');
            // DB::table('leave_types')->truncate(); // Skip truncate for now
            
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
            $this->info('✓ Leave types populated successfully');

            // Populate Shift Types
            $this->info('Populating shift_types...');
            // DB::table('shift_types')->truncate(); // Skip truncate for now
            
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
            $this->info('✓ Shift types populated successfully');

            // Verify data
            $this->info('');
            $this->info('Verification:');
            $this->info('Claim Types: ' . DB::table('claim_types')->count() . ' records');
            $this->info('Leave Types: ' . DB::table('leave_types')->count() . ' records');
            $this->info('Shift Types: ' . DB::table('shift_types')->count() . ' records');

            $this->info('');
            $this->info('✅ All type tables populated successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
