<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;

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
                'description' => 'Yearly vacation leave for rest and recreation',
                'max_days_per_year' => 21,
                'carry_forward' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'description' => 'Medical leave for illness or medical appointments',
                'max_days_per_year' => 10,
                'carry_forward' => false,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Personal Leave',
                'code' => 'PL',
                'description' => 'Personal time off for family matters or personal business',
                'max_days_per_year' => 5,
                'carry_forward' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'description' => 'Leave for new mothers after childbirth',
                'max_days_per_year' => 90,
                'carry_forward' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PTL',
                'description' => 'Leave for new fathers after childbirth',
                'max_days_per_year' => 14,
                'carry_forward' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Compassionate Leave',
                'code' => 'CL',
                'description' => 'Leave for bereavement or family emergencies',
                'max_days_per_year' => 7,
                'carry_forward' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::create($leaveType);
        }
    }
}
