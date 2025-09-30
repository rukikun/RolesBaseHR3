<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ShiftType;

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
                'type' => 'day',
                'default_start_time' => '08:00',
                'default_end_time' => '16:00',
                'break_duration' => 60,
                'hourly_rate' => 25.00,
                'description' => 'Standard morning shift for regular operations',
                'is_active' => true,
                'color_code' => '#28a745'
            ],
            [
                'name' => 'Afternoon Shift',
                'code' => 'AFTERNOON',
                'type' => 'swing',
                'default_start_time' => '14:00',
                'default_end_time' => '22:00',
                'break_duration' => 45,
                'hourly_rate' => 27.50,
                'description' => 'Afternoon to evening coverage shift',
                'is_active' => true,
                'color_code' => '#ffc107'
            ],
            [
                'name' => 'Night Shift',
                'code' => 'NIGHT',
                'type' => 'night',
                'default_start_time' => '22:00',
                'default_end_time' => '06:00',
                'break_duration' => 60,
                'hourly_rate' => 32.00,
                'description' => 'Overnight shift with premium pay',
                'is_active' => true,
                'color_code' => '#6f42c1'
            ],
            [
                'name' => 'Split Shift',
                'code' => 'SPLIT',
                'type' => 'split',
                'default_start_time' => '09:00',
                'default_end_time' => '17:00',
                'break_duration' => 120,
                'hourly_rate' => 24.00,
                'description' => 'Split shift with extended break period',
                'is_active' => true,
                'color_code' => '#17a2b8'
            ],
            [
                'name' => 'Weekend Shift',
                'code' => 'WEEKEND',
                'type' => 'rotating',
                'default_start_time' => '10:00',
                'default_end_time' => '18:00',
                'break_duration' => 45,
                'hourly_rate' => 30.00,
                'description' => 'Weekend coverage with rotating schedule',
                'is_active' => true,
                'color_code' => '#fd7e14'
            ]
        ];

        foreach ($shiftTypes as $shiftType) {
            ShiftType::create($shiftType);
        }
    }
}
