<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing shift requests
        DB::table('shift_requests')->truncate();

        $shiftRequests = [
            // Pending requests
            [
                'employee_id' => 1,
                'request_type' => 'shift_change',
                'requested_date' => '2025-09-15',
                'requested_start_time' => '16:00:00',
                'requested_end_time' => '00:00:00',
                'reason' => 'Need to switch to evening shift due to childcare arrangements',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(4),
            ],
            [
                'employee_id' => 2,
                'request_type' => 'time_off',
                'requested_date' => '2025-09-18',
                'requested_start_time' => null,
                'requested_end_time' => null,
                'reason' => 'Doctor appointment - need the day off',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'employee_id' => 3,
                'request_type' => 'overtime',
                'requested_date' => '2025-09-20',
                'requested_start_time' => '08:00:00',
                'requested_end_time' => '20:00:00',
                'reason' => 'Willing to work extended hours for project deadline',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'employee_id' => 4,
                'request_type' => 'swap',
                'requested_date' => '2025-09-22',
                'requested_start_time' => '08:00:00',
                'requested_end_time' => '16:00:00',
                'reason' => 'Want to swap weekend shift with weekday shift',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'employee_id' => 5,
                'request_type' => 'shift_change',
                'requested_date' => '2025-09-25',
                'requested_start_time' => '06:00:00',
                'requested_end_time' => '14:00:00',
                'reason' => 'Prefer early morning shift for better work-life balance',
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Approved requests
            [
                'employee_id' => 6,
                'request_type' => 'time_off',
                'requested_date' => '2025-09-12',
                'requested_start_time' => null,
                'requested_end_time' => null,
                'reason' => 'Family vacation - pre-approved',
                'status' => 'approved',
                'approved_by' => 3, // Mike Johnson (Operations Manager)
                'approved_at' => Carbon::now()->subDays(10),
                'created_at' => Carbon::now()->subDays(11),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            [
                'employee_id' => 7,
                'request_type' => 'shift_change',
                'requested_date' => '2025-09-16',
                'requested_start_time' => '16:00:00',
                'requested_end_time' => '00:00:00',
                'reason' => 'Requested evening shift for school schedule',
                'status' => 'approved',
                'approved_by' => 3,
                'approved_at' => Carbon::now()->subDays(8),
                'created_at' => Carbon::now()->subDays(9),
                'updated_at' => Carbon::now()->subDays(8),
            ],
            [
                'employee_id' => 8,
                'request_type' => 'overtime',
                'requested_date' => '2025-09-19',
                'requested_start_time' => '08:00:00',
                'requested_end_time' => '18:00:00',
                'reason' => 'Extra coverage needed for busy period',
                'status' => 'approved',
                'approved_by' => 3,
                'approved_at' => Carbon::now()->subDays(4),
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(4),
            ],

            // Rejected requests
            [
                'employee_id' => 1,
                'request_type' => 'time_off',
                'requested_date' => '2025-09-10',
                'requested_start_time' => null,
                'requested_end_time' => null,
                'reason' => 'Personal day request - insufficient coverage',
                'status' => 'rejected',
                'approved_by' => 3,
                'approved_at' => Carbon::now()->subDays(6),
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(6),
            ],
            [
                'employee_id' => 3,
                'request_type' => 'swap',
                'requested_date' => '2025-09-14',
                'requested_start_time' => '16:00:00',
                'requested_end_time' => '00:00:00',
                'reason' => 'Shift swap request - no available replacement',
                'status' => 'rejected',
                'approved_by' => 3,
                'approved_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(3),
            ],

            // Recent requests
            [
                'employee_id' => 2,
                'request_type' => 'shift_change',
                'requested_date' => '2025-09-28',
                'requested_start_time' => '12:00:00',
                'requested_end_time' => '20:00:00',
                'reason' => 'Request afternoon shift for better commute',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2),
            ],
            [
                'employee_id' => 4,
                'request_type' => 'overtime',
                'requested_date' => '2025-09-30',
                'requested_start_time' => '08:00:00',
                'requested_end_time' => '19:00:00',
                'reason' => 'Available for month-end processing overtime',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHour(),
                'updated_at' => Carbon::now()->subHour(),
            ],
        ];

        DB::table('shift_requests')->insert($shiftRequests);

        $this->command->info('Shift requests seeded successfully!');
        $this->command->info('Created ' . count($shiftRequests) . ' shift requests');
        
        // Show summary
        $summary = DB::table('shift_requests')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
            
        $this->command->info('Summary by status:');
        foreach ($summary as $item) {
            $this->command->info("- {$item->status}: {$item->count}");
        }
    }
}
