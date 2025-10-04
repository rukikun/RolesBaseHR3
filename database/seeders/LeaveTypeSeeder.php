<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'AL',
                'description' => 'Annual vacation leave',
                'days_allowed' => 0,
                'max_days_per_year' => 21,
                'carry_forward' => 1,
                'requires_approval' => 1,
                'status' => 'active',
                'is_active' => 1,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'description' => 'Medical sick leave',
                'days_allowed' => 0,
                'max_days_per_year' => 10,
                'carry_forward' => 0,
                'requires_approval' => 0,
                'status' => 'active',
                'is_active' => 1,
            ],
            [
                'name' => 'Emergency Leave',
                'code' => 'EL',
                'description' => 'Emergency family leave',
                'days_allowed' => 0,
                'max_days_per_year' => 5,
                'carry_forward' => 0,
                'requires_approval' => 1,
                'status' => 'active',
                'is_active' => 1,
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'description' => 'Maternity leave',
                'days_allowed' => 0,
                'max_days_per_year' => 90,
                'carry_forward' => 0,
                'requires_approval' => 1,
                'status' => 'active',
                'is_active' => 1,
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PL',
                'description' => 'Paternity leave',
                'days_allowed' => 0,
                'max_days_per_year' => 7,
                'carry_forward' => 0,
                'requires_approval' => 1,
                'status' => 'active',
                'is_active' => 1,
            ],
        ];

        // Clear existing data first
        DB::table('leave_types')->truncate();
        
        // Insert all data
        foreach ($leaveTypes as $leaveType) {
            DB::table('leave_types')->insert(array_merge($leaveType, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
