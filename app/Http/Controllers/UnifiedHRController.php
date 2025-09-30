<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\Shift;
use App\Models\ShiftType;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Claim;
use App\Models\ClaimType;

class UnifiedHRController extends Controller
{
    /**
     * Get unified statistics for all HR modules
     */
    public function getUnifiedStats()
    {
        try {
            $stats = [
                'employees' => [
                    'total' => Employee::count(),
                    'active' => Employee::where('status', 'active')->count(),
                    'online' => Employee::where('online_status', 'online')->count(),
                    'departments' => Employee::distinct('department')->count('department')
                ],
                'timesheets' => [
                    'total' => TimeEntry::count(),
                    'pending' => TimeEntry::where('status', 'pending')->count(),
                    'approved' => TimeEntry::where('status', 'approved')->count(),
                    'this_week' => TimeEntry::whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])->count()
                ],
                'shifts' => [
                    'total' => Shift::count(),
                    'today' => Shift::whereDate('shift_date', today())->count(),
                    'scheduled' => Shift::where('status', 'scheduled')->count(),
                    'shift_types' => ShiftType::where('is_active', true)->count()
                ],
                'leaves' => [
                    'total' => LeaveRequest::count(),
                    'pending' => LeaveRequest::where('status', 'pending')->count(),
                    'approved' => LeaveRequest::where('status', 'approved')->count(),
                    'this_month' => LeaveRequest::whereMonth('start_date', now()->month)->count()
                ],
                'claims' => [
                    'total' => Claim::count(),
                    'pending' => Claim::where('status', 'pending')->count(),
                    'approved' => Claim::where('status', 'approved')->count(),
                    'total_amount' => Claim::where('status', 'approved')->sum('amount')
                ]
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }

    /**
     * Get navigation cards data for all modules
     */
    public function getNavigationCards()
    {
        try {
            $cards = [
                [
                    'id' => 'employees',
                    'title' => 'Employee Management',
                    'description' => 'Manage employee records and information',
                    'icon' => 'fas fa-users',
                    'color' => 'primary',
                    'route' => 'employees',
                    'count' => Employee::where('status', 'active')->count(),
                    'label' => 'Active Employees'
                ],
                [
                    'id' => 'timesheets',
                    'title' => 'Timesheet Management',
                    'description' => 'Track and manage employee work hours',
                    'icon' => 'fas fa-clock',
                    'color' => 'success',
                    'route' => 'timesheet-management',
                    'count' => TimeEntry::where('status', 'pending')->count(),
                    'label' => 'Pending Timesheets'
                ],
                [
                    'id' => 'shifts',
                    'title' => 'Shift Management',
                    'description' => 'Schedule and manage employee shifts',
                    'icon' => 'fas fa-calendar-alt',
                    'color' => 'info',
                    'route' => 'shift-schedule-management',
                    'count' => Shift::whereDate('shift_date', today())->count(),
                    'label' => 'Today\'s Shifts'
                ],
                [
                    'id' => 'leaves',
                    'title' => 'Leave Management',
                    'description' => 'Handle leave requests and approvals',
                    'icon' => 'fas fa-umbrella-beach',
                    'color' => 'warning',
                    'route' => 'leave-management',
                    'count' => LeaveRequest::where('status', 'pending')->count(),
                    'label' => 'Pending Requests'
                ],
                [
                    'id' => 'claims',
                    'title' => 'Claims & Reimbursement',
                    'description' => 'Process expense claims and reimbursements',
                    'icon' => 'fas fa-receipt',
                    'color' => 'danger',
                    'route' => 'claims-reimbursement',
                    'count' => Claim::where('status', 'pending')->count(),
                    'label' => 'Pending Claims'
                ]
            ];

            return response()->json($cards);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch navigation cards'], 500);
        }
    }

    /**
     * Get cross-module data for employee
     */
    public function getEmployeeModuleData($employeeId)
    {
        try {
            $employee = Employee::findOrFail($employeeId);
            
            $data = [
                'employee' => $employee,
                'timesheets' => TimeEntry::where('employee_id', $employeeId)
                    ->orderBy('work_date', 'desc')
                    ->limit(10)
                    ->get(),
                'shifts' => Shift::with('shiftType')
                    ->where('employee_id', $employeeId)
                    ->orderBy('shift_date', 'desc')
                    ->limit(10)
                    ->get(),
                'leaves' => LeaveRequest::with('leaveType')
                    ->where('employee_id', $employeeId)
                    ->orderBy('start_date', 'desc')
                    ->limit(10)
                    ->get(),
                'claims' => Claim::with('claimType')
                    ->where('employee_id', $employeeId)
                    ->orderBy('claim_date', 'desc')
                    ->limit(10)
                    ->get(),
                'stats' => [
                    'total_hours_this_month' => TimeEntry::where('employee_id', $employeeId)
                        ->whereMonth('work_date', now()->month)
                        ->sum('hours_worked'),
                    'shifts_this_month' => Shift::where('employee_id', $employeeId)
                        ->whereMonth('shift_date', now()->month)
                        ->count(),
                    'pending_leaves' => LeaveRequest::where('employee_id', $employeeId)
                        ->where('status', 'pending')
                        ->count(),
                    'pending_claims' => Claim::where('employee_id', $employeeId)
                        ->where('status', 'pending')
                        ->count()
                ]
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch employee data'], 500);
        }
    }

    /**
     * Get dashboard overview data
     */
    public function getDashboardOverview()
    {
        try {
            $overview = [
                'quick_stats' => [
                    'employees_online' => Employee::where('online_status', 'online')->count(),
                    'pending_approvals' => TimeEntry::where('status', 'pending')->count() + 
                                         LeaveRequest::where('status', 'pending')->count() + 
                                         Claim::where('status', 'pending')->count(),
                    'shifts_today' => Shift::whereDate('shift_date', today())->count(),
                    'total_hours_today' => TimeEntry::whereDate('work_date', today())->sum('hours_worked')
                ],
                'recent_activities' => $this->getRecentActivities(),
                'pending_items' => $this->getPendingItems()
            ];

            return response()->json($overview);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch dashboard overview'], 500);
        }
    }

    /**
     * Get recent activities across all modules
     */
    private function getRecentActivities()
    {
        $activities = collect();

        // Recent timesheets
        $timesheets = TimeEntry::with('employee')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'timesheet',
                    'message' => "{$item->employee->first_name} {$item->employee->last_name} submitted timesheet for " . date('M d, Y', strtotime($item->work_date)),
                    'time' => $item->created_at->diffForHumans(),
                    'icon' => 'fas fa-clock',
                    'color' => 'primary'
                ];
            });

        // Recent leave requests
        $leaves = LeaveRequest::with('employee', 'leaveType')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'leave',
                    'message' => "{$item->employee->first_name} {$item->employee->last_name} requested {$item->leaveType->name}",
                    'time' => $item->created_at->diffForHumans(),
                    'icon' => 'fas fa-umbrella-beach',
                    'color' => 'warning'
                ];
            });

        // Recent claims
        $claims = Claim::with('employee', 'claimType')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'claim',
                    'message' => "{$item->employee->first_name} {$item->employee->last_name} submitted {$item->claimType->name} claim",
                    'time' => $item->created_at->diffForHumans(),
                    'icon' => 'fas fa-receipt',
                    'color' => 'danger'
                ];
            });

        return $activities->merge($timesheets)
                         ->merge($leaves)
                         ->merge($claims)
                         ->sortByDesc('time')
                         ->take(10)
                         ->values();
    }

    /**
     * Get pending items across all modules
     */
    private function getPendingItems()
    {
        return [
            'timesheets' => TimeEntry::with('employee')
                ->where('status', 'pending')
                ->orderBy('work_date', 'desc')
                ->limit(5)
                ->get(),
            'leaves' => LeaveRequest::with('employee', 'leaveType')
                ->where('status', 'pending')
                ->orderBy('start_date', 'asc')
                ->limit(5)
                ->get(),
            'claims' => Claim::with('employee', 'claimType')
                ->where('status', 'pending')
                ->orderBy('claim_date', 'desc')
                ->limit(5)
                ->get()
        ];
    }
}
