<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ShiftType;
use Illuminate\Support\Facades\DB;

class ShiftTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shiftTypes = [
            [
                'name' => 'Morning Shift',
                'code' => 'MORNING',
                'description' => 'Standard morning shift for regular operations',
                'default_start_time' => '08:00:00',
                'default_end_time' => '16:00:00',
                'break_duration' => 60,
                'hourly_rate' => 350.00,
                'color_code' => '#28a745',
                'type' => 'day',
                'is_active' => 1,
            ],
            [
                'name' => 'Afternoon Shift',
                'code' => 'AFTERNOON',
                'description' => 'Afternoon to evening coverage shift',
                'default_start_time' => '14:00:00',
                'default_end_time' => '22:00:00',
                'break_duration' => 45,
                'hourly_rate' => 385.00,
                'color_code' => '#ffc107',
                'type' => 'swing',
                'is_active' => 1,
            ],
            [
                'name' => 'Night Shift',
                'code' => 'NIGHT',
                'description' => 'Overnight shift with premium pay',
                'default_start_time' => '22:00:00',
                'default_end_time' => '06:00:00',
                'break_duration' => 60,
                'hourly_rate' => 450.00,
                'color_code' => '#6f42c1',
                'type' => 'night',
                'is_active' => 1,
            ],
            [
                'name' => 'Split Shift',
                'code' => 'SPLIT',
                'description' => 'Split shift with extended break period',
                'default_start_time' => '09:00:00',
                'default_end_time' => '17:00:00',
                'break_duration' => 120,
                'hourly_rate' => 335.00,
                'color_code' => '#17a2b8',
                'type' => 'split',
                'is_active' => 1,
            ],
            [
                'name' => 'Weekend Shift',
                'code' => 'WEEKEND',
                'description' => 'Weekend coverage with rotating schedule',
                'default_start_time' => '10:00:00',
                'default_end_time' => '18:00:00',
                'break_duration' => 45,
                'hourly_rate' => 420.00,
                'color_code' => '#fd7e14',
                'type' => 'rotating',
                'is_active' => 1,
            ],
        ];

        // Clear existing data first
        DB::table('shift_types')->truncate();
        
        // Insert all data
        foreach ($shiftTypes as $shiftType) {
            DB::table('shift_types')->insert(array_merge($shiftType, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
