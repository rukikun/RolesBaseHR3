<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIServiceFactory;
use App\Services\ClockifyService;
use App\Models\TimeEntry;
use App\Models\Claim;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AIController extends Controller
{
    private $aiService;
    private $clockifyService;

    public function __construct()
    {
        $this->aiService = AIServiceFactory::create();
        $this->clockifyService = new ClockifyService();
    }

    /**
     * Test AI and Clockify connections
     */
    public function testConnections()
    {
        $aiStatus = $this->aiService->testConnection();
        $clockifyStatus = $this->clockifyService->testConnection();

        return response()->json([
            'openai' => $aiStatus,
            'clockify' => $clockifyStatus,
            'overall_status' => $aiStatus['success'] && $clockifyStatus['success']
        ]);
    }

    /**
     * Analyze time entry with AI
     */
    public function analyzeTimeEntry(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'duration' => 'nullable|string',
            'project' => 'nullable|string'
        ]);

        $analysis = $this->aiService->categorizeTimeEntry(
            $request->description,
            $request->project
        );

        return response()->json($analysis);
    }

    /**
     * Get AI insights for time entries
     */
    public function getTimeInsights(Request $request)
    {
        $employeeId = $request->get('employee_id', Auth::id());
        $days = $request->get('days', 7);

        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        $timeEntries = TimeEntry::where('employee_id', $employeeId)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get()
            ->map(function ($entry) {
                return [
                    'date' => $entry->entry_date->format('Y-m-d'),
                    'duration' => $entry->total_hours . ' hours',
                    'description' => $entry->notes ?? 'Work session'
                ];
            })
            ->toArray();

        if (empty($timeEntries)) {
            return response()->json([
                'success' => false,
                'message' => 'No time entries found for analysis'
            ]);
        }

        $insights = $this->aiService->generateTimeInsights($timeEntries);

        return response()->json($insights);
    }

    /**
     * Validate claim with AI
     */
    public function validateClaim(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'description' => 'required|string',
            'date' => 'required|date'
        ]);

        $claimData = [
            'type' => $request->type,
            'amount' => '$' . number_format($request->amount, 2),
            'description' => $request->description,
            'date' => $request->date
        ];

        $validation = $this->aiService->validateClaim($claimData);

        return response()->json($validation);
    }

    /**
     * Generate timesheet summary with AI
     */
    public function generateTimesheetSummary(Request $request)
    {
        $employeeId = $request->get('employee_id', Auth::id());
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek());
        $endDate = $request->get('end_date', Carbon::now()->endOfWeek());

        $timesheetData = TimeEntry::where('employee_id', $employeeId)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get()
            ->map(function ($entry) {
                return [
                    'date' => $entry->entry_date->format('Y-m-d'),
                    'hours' => $entry->total_hours,
                    'task' => $entry->notes ?? 'Work session'
                ];
            })
            ->toArray();

        if (empty($timesheetData)) {
            return response()->json([
                'success' => false,
                'message' => 'No timesheet data found'
            ]);
        }

        $summary = $this->aiService->generateTimesheetSummary($timesheetData);

        return response()->json($summary);
    }

    /**
     * Get AI-powered schedule suggestions
     */
    public function getScheduleSuggestions(Request $request)
    {
        $employeeId = $request->get('employee_id', Auth::id());
        $days = $request->get('days', 14);

        // Get productivity data from local database
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        $productivityData = TimeEntry::where('employee_id', $employeeId)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get()
            ->map(function ($entry) {
                return [
                    'hour' => $entry->clock_in ? Carbon::parse($entry->clock_in)->hour : 9,
                    'day_of_week' => $entry->entry_date->dayOfWeek,
                    'duration' => $entry->total_hours . ' hours',
                    'productivity_score' => rand(6, 10) // Mock productivity score
                ];
            })
            ->toArray();

        if (empty($productivityData)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient data for schedule analysis'
            ]);
        }

        $suggestions = $this->aiService->suggestOptimalSchedule($productivityData);

        return response()->json($suggestions);
    }

    /**
     * Generate project time estimates
     */
    public function estimateProjectTime(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string',
            'description' => 'required|string',
            'requirements' => 'nullable|array'
        ]);

        $estimates = $this->aiService->estimateProjectTime(
            $request->description,
            $request->requirements ?? []
        );

        return response()->json($estimates);
    }

    /**
     * Analyze team productivity
     */
    public function analyzeTeamProductivity(Request $request)
    {
        $days = $request->get('days', 30);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        $teamData = Employee::with(['timeEntries' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('entry_date', [$startDate, $endDate]);
        }])->get()->map(function ($employee) {
            $totalHours = $employee->timeEntries->sum('total_hours');
            $avgHours = $employee->timeEntries->count() > 0 ? 
                $totalHours / $employee->timeEntries->count() : 0;

            return [
                'employee' => $employee->first_name . ' ' . $employee->last_name,
                'total_hours' => $totalHours,
                'avg_daily_hours' => round($avgHours, 2),
                'entries_count' => $employee->timeEntries->count()
            ];
        })->toArray();

        $analysis = $this->aiService->analyzeTeamProductivity($teamData);

        return response()->json($analysis);
    }

    /**
     * Generate smart reminders
     */
    public function generateSmartReminder(Request $request)
    {
        $request->validate([
            'type' => 'required|in:timesheet,break,deadline,meeting',
            'context' => 'required|string'
        ]);

        $reminder = $this->aiService->generateSmartReminder(
            $request->context,
            $request->type
        );

        return response()->json($reminder);
    }

    /**
     * Get AI dashboard data
     */
    public function getDashboardData()
    {
        $userId = Auth::id();
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Get today's time entries
        $todayEntries = TimeEntry::where('employee_id', $userId)
            ->whereDate('entry_date', $today)
            ->get();

        // Get week's productivity data
        $weekEntries = TimeEntry::where('employee_id', $userId)
            ->whereBetween('entry_date', [$weekStart, $weekEnd])
            ->get();

        // Calculate productivity metrics
        $totalHours = $weekEntries->sum('total_hours');
        $avgDailyHours = $weekEntries->count() > 0 ? $totalHours / 7 : 0;
        $todayHours = $todayEntries->sum('total_hours');

        // Get AI insights for the week
        $weekData = $weekEntries->map(function ($entry) {
            return [
                'date' => $entry->entry_date->format('Y-m-d'),
                'duration' => $entry->total_hours . ' hours',
                'description' => $entry->notes ?? 'Work session'
            ];
        })->toArray();

        $aiInsights = null;
        if (!empty($weekData)) {
            $aiInsights = $this->aiService->generateTimeInsights($weekData);
        }

        // Get pending claims for AI analysis
        $pendingClaims = Claim::where('employee_id', $userId)
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'productivity_metrics' => [
                'today_hours' => round($todayHours, 2),
                'week_total_hours' => round($totalHours, 2),
                'avg_daily_hours' => round($avgDailyHours, 2),
                'productivity_trend' => $avgDailyHours > 7 ? 'up' : ($avgDailyHours > 5 ? 'stable' : 'down')
            ],
            'ai_insights' => $aiInsights,
            'pending_claims' => $pendingClaims,
            'recommendations' => [
                'schedule_optimization' => $avgDailyHours < 6,
                'break_reminder' => $todayHours > 6,
                'timesheet_completion' => $todayEntries->isEmpty()
            ]
        ]);
    }

    /**
     * Start AI-enhanced Clockify timer
     */
    public function startAITimer(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'project_id' => 'nullable|string'
        ]);

        // Get Clockify user ID (this should be stored in user profile)
        $clockifyUserId = Auth::user()->clockify_user_id ?? null;
        
        if (!$clockifyUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Clockify user ID not configured'
            ]);
        }

        $result = $this->clockifyService->startAITimeEntry(
            $clockifyUserId,
            $request->description,
            $request->project_id
        );

        return response()->json($result);
    }

    /**
     * Stop Clockify timer
     */
    public function stopTimer(Request $request)
    {
        $clockifyUserId = Auth::user()->clockify_user_id ?? null;
        
        if (!$clockifyUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Clockify user ID not configured'
            ]);
        }

        $result = $this->clockifyService->stopTimeEntry($clockifyUserId);

        return response()->json($result);
    }

    /**
     * Generate comprehensive AI report
     */
    public function generateAIReport(Request $request)
    {
        $request->validate([
            'type' => 'required|in:productivity,timesheet,claims,team',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'employee_id' => 'nullable|integer'
        ]);

        $employeeId = $request->employee_id ?? Auth::id();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $data = [];

        switch ($request->type) {
            case 'productivity':
                $data = TimeEntry::where('employee_id', $employeeId)
                    ->whereBetween('entry_date', [$startDate, $endDate])
                    ->get()
                    ->toArray();
                break;
            case 'claims':
                $data = Claim::where('employee_id', $employeeId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get()
                    ->toArray();
                break;
        }

        $report = $this->aiService->generateReport($data, $request->type);

        return response()->json([
            'report' => $report,
            'generated_at' => Carbon::now()->toISOString(),
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ]);
    }
}
