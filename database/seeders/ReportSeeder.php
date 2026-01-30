<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Report::count() > 0) {
            return;
        }

        $now = Carbon::now();

        Report::create([
            'title' => 'Attendance Compliance Summary',
            'category' => 'Attendance',
            'period_start' => $now->copy()->subWeeks(4)->startOfWeek(),
            'period_end' => $now->copy()->subWeeks(1)->endOfWeek(),
            'status' => 'generated',
            'generated_by' => 'HR Manager',
            'generated_at' => $now->copy()->subDays(2),
            'summary' => 'Weekly compliance rate, attendance streaks, and late arrivals overview.',
            'total_records' => 184
        ]);

        Report::create([
            'title' => 'Payroll Cost Breakdown',
            'category' => 'Payroll',
            'period_start' => $now->copy()->subMonths(2)->startOfMonth(),
            'period_end' => $now->copy()->subMonths(1)->endOfMonth(),
            'status' => 'generated',
            'generated_by' => 'Finance Lead',
            'generated_at' => $now->copy()->subDays(5),
            'summary' => 'Monthly payroll totals, overtime distribution, and department cost analysis.',
            'total_records' => 96
        ]);

        Report::create([
            'title' => 'Leave Utilization Snapshot',
            'category' => 'Leave',
            'period_start' => $now->copy()->startOfMonth(),
            'period_end' => $now->copy()->endOfMonth(),
            'status' => 'scheduled',
            'generated_by' => 'HR Scheduler',
            'generated_at' => $now->copy()->addDays(3),
            'summary' => 'Upcoming leave usage, approvals, and remaining balances by department.',
            'total_records' => 62
        ]);

        Report::create([
            'title' => 'Employee Performance Digest',
            'category' => 'Performance',
            'period_start' => $now->copy()->subMonth()->startOfMonth(),
            'period_end' => $now->copy()->subMonth()->endOfMonth(),
            'status' => 'draft',
            'generated_by' => 'System',
            'summary' => 'Draft insights on productivity scores, quality trends, and overtime spikes.',
            'total_records' => 48
        ]);
    }
}
