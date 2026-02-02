<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AIGeneratedTimesheet;
use App\Models\MonthlyTimesheet;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MonthlyAttendanceController extends Controller
{
    /**
     * Return monthly timesheet summaries with present/absent counts (Mon-Sat).
     */
    public function index(Request $request)
    {
        try {
            $monthStart = $this->parseMonthStart($request->input('month'));

            $this->syncMonthlyTimesheets($monthStart);

            $query = MonthlyTimesheet::orderBy('month_start_date', 'desc');
            if ($monthStart) {
                $query->where('month_start_date', $monthStart->format('Y-m-d'));
            }

            $monthlyTimesheets = $query->get();
            $monthlyTimesheets = $this->attachMonthlyAttendanceCounts($monthlyTimesheets);

            return response()->json([
                'status' => 'success',
                'data' => $monthlyTimesheets,
                'message' => 'Monthly attendance summary retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Monthly attendance API error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve monthly attendance summary'
            ], 500);
        }
    }

    protected function parseMonthStart(?string $month)
    {
        if (!$month) {
            return null;
        }

        try {
            return Carbon::parse($month)->startOfMonth();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Sync monthly timesheets from AI-generated weekly data.
     */
    protected function syncMonthlyTimesheets(?Carbon $monthStart)
    {
        $aiTimesheetsQuery = AIGeneratedTimesheet::select(
            'id',
            'employee_id',
            'employee_name',
            'department',
            'week_start_date',
            'total_hours',
            'overtime_hours'
        )
            ->where('status', 'approved')
            ->orderBy('week_start_date', 'desc');

        if ($monthStart) {
            $monthEnd = $monthStart->copy()->endOfMonth();
            $aiTimesheetsQuery->whereBetween('week_start_date', [
                $monthStart->format('Y-m-d'),
                $monthEnd->format('Y-m-d')
            ]);
        }

        $aiTimesheets = $aiTimesheetsQuery->get();

        if ($aiTimesheets->isEmpty()) {
            return;
        }

        $monthlyData = [];

        foreach ($aiTimesheets as $timesheet) {
            $monthStartDate = Carbon::parse($timesheet->week_start_date)->startOfMonth()->format('Y-m-d');
            $key = $timesheet->employee_id . '_' . $monthStartDate;

            if (!isset($monthlyData[$key])) {
                $monthlyData[$key] = [
                    'employee_id' => $timesheet->employee_id,
                    'employee_name' => $timesheet->employee_name,
                    'department' => $timesheet->department,
                    'month_start_date' => $monthStartDate,
                    'total_hours' => 0,
                    'overtime_hours' => 0,
                    'timesheet_count' => 0,
                    'source_timesheet_ids' => []
                ];
            }

            $monthlyData[$key]['total_hours'] += (float) ($timesheet->total_hours ?? 0);
            $monthlyData[$key]['overtime_hours'] += (float) ($timesheet->overtime_hours ?? 0);
            $monthlyData[$key]['timesheet_count']++;
            $monthlyData[$key]['source_timesheet_ids'][] = $timesheet->id;
        }

        foreach ($monthlyData as $summary) {
            $monthlyTimesheet = MonthlyTimesheet::firstOrNew([
                'employee_id' => $summary['employee_id'],
                'month_start_date' => $summary['month_start_date']
            ]);

            $monthlyTimesheet->employee_name = $summary['employee_name'];
            $monthlyTimesheet->department = $summary['department'];
            $monthlyTimesheet->total_hours = $summary['total_hours'];
            $monthlyTimesheet->overtime_hours = $summary['overtime_hours'];
            $monthlyTimesheet->timesheet_count = $summary['timesheet_count'];
            $monthlyTimesheet->source_timesheet_ids = $summary['source_timesheet_ids'];

            if (!$monthlyTimesheet->generated_at) {
                $monthlyTimesheet->generated_at = now();
            }

            $monthlyTimesheet->save();
        }
    }

    /**
     * Attach present/absent day counts (Mon-Sat) to monthly summaries.
     */
    protected function attachMonthlyAttendanceCounts($monthlyTimesheets)
    {
        if (!$monthlyTimesheets || $monthlyTimesheets->isEmpty()) {
            return $monthlyTimesheets;
        }

        $presentStatuses = ['present', 'late', 'on_break', 'clocked_out'];

        foreach ($monthlyTimesheets as $monthlyTimesheet) {
            if (!$monthlyTimesheet->employee_id || !$monthlyTimesheet->month_start_date) {
                $monthlyTimesheet->present_days = 0;
                $monthlyTimesheet->absent_days = 0;
                continue;
            }

            $monthStart = Carbon::parse($monthlyTimesheet->month_start_date)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $attendances = Attendance::where('employee_id', $monthlyTimesheet->employee_id)
                ->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                ->get();

            $weekdayAttendances = $attendances->filter(function ($attendance) {
                return !Carbon::parse($attendance->date)->isSunday();
            });

            $monthlyTimesheet->present_days = $weekdayAttendances
                ->whereIn('status', $presentStatuses)
                ->count();
            $monthlyTimesheet->absent_days = $weekdayAttendances
                ->where('status', 'absent')
                ->count();
        }

        return $monthlyTimesheets;
    }
}
