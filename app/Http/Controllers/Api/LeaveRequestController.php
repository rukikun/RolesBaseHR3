<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests.
     */
    public function index(Request $request)
    {
        try {
            $query = LeaveRequest::with(['employee', 'leaveType', 'approver']);

            // Filter by employee if specified
            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            // Filter by status if specified
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by leave type if specified
            if ($request->has('leave_type_id')) {
                $query->where('leave_type_id', $request->leave_type_id);
            }

            // Filter by date range if specified
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->where(function($q) use ($request) {
                    $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function($subQ) use ($request) {
                          $subQ->where('start_date', '<=', $request->start_date)
                               ->where('end_date', '>=', $request->end_date);
                      });
                });
            }

            $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'status' => 'success',
                'data' => $leaveRequests,
                'message' => 'Leave requests retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Leave Requests API index error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve leave requests'
            ], 500);
        }
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|integer|exists:employees,id',
                'leave_type_id' => 'required|integer|exists:leave_types,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'days_requested' => 'required|numeric|min:0.5|max:365',
                'reason' => 'required|string|max:1000',
                'admin_notes' => 'nullable|string|max:500'
            ]);

            // Calculate days if not provided or validate provided days
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $calculatedDays = $startDate->diffInDays($endDate) + 1;

            // Validate that requested days match calculated days (allow for half days)
            if (abs($validated['days_requested'] - $calculatedDays) > 0.5) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Days requested does not match the date range provided'
                ], 422);
            }

            $leaveRequest = LeaveRequest::create([
                'employee_id' => $validated['employee_id'],
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'days_requested' => $validated['days_requested'],
                'reason' => $validated['reason'],
                'status' => 'pending',
                'admin_notes' => $validated['admin_notes'] ?? null
            ]);

            // Load relationships for response
            $leaveRequest->load(['employee', 'leaveType']);

            return response()->json([
                'status' => 'success',
                'data' => $leaveRequest,
                'message' => 'Leave request submitted successfully'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Leave Requests API store error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create leave request'
            ], 500);
        }
    }

    /**
     * Display the specified leave request.
     */
    public function show($id)
    {
        try {
            $leaveRequest = LeaveRequest::with(['employee', 'leaveType', 'approver'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $leaveRequest,
                'message' => 'Leave request retrieved successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave request not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Leave Requests API show error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve leave request'
            ], 500);
        }
    }

    /**
     * Update the specified leave request.
     */
    public function update(Request $request, $id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            // Only allow updates if leave request is pending
            if ($leaveRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update leave request that is not pending'
                ], 400);
            }

            $validated = $request->validate([
                'leave_type_id' => 'sometimes|integer|exists:leave_types,id',
                'start_date' => 'sometimes|date|after_or_equal:today',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'days_requested' => 'sometimes|numeric|min:0.5|max:365',
                'reason' => 'sometimes|string|max:1000',
                'admin_notes' => 'nullable|string|max:500'
            ]);

            // Recalculate days if dates are updated
            if (isset($validated['start_date']) || isset($validated['end_date'])) {
                $startDate = Carbon::parse($validated['start_date'] ?? $leaveRequest->start_date);
                $endDate = Carbon::parse($validated['end_date'] ?? $leaveRequest->end_date);
                $calculatedDays = $startDate->diffInDays($endDate) + 1;
                
                $requestedDays = $validated['days_requested'] ?? $calculatedDays;
                if (abs($requestedDays - $calculatedDays) > 0.5) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Days requested does not match the date range provided'
                    ], 422);
                }
                
                $validated['days_requested'] = $requestedDays;
            }

            $leaveRequest->update($validated);
            $leaveRequest->load(['employee', 'leaveType']);

            return response()->json([
                'status' => 'success',
                'data' => $leaveRequest,
                'message' => 'Leave request updated successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave request not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Leave Requests API update error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update leave request'
            ], 500);
        }
    }

    /**
     * Approve a leave request.
     */
    public function approve(Request $request, $id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            if ($leaveRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Can only approve pending leave requests'
                ], 400);
            }

            $validated = $request->validate([
                'admin_notes' => 'nullable|string|max:500'
            ]);

            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id() ?? 1, // Default to admin if no auth
                'approved_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? $leaveRequest->admin_notes
            ]);

            $leaveRequest->load(['employee', 'leaveType', 'approver']);

            return response()->json([
                'status' => 'success',
                'data' => $leaveRequest,
                'message' => 'Leave request approved successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave request not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Leave Requests API approve error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve leave request'
            ], 500);
        }
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, $id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            if ($leaveRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Can only reject pending leave requests'
                ], 400);
            }

            $validated = $request->validate([
                'admin_notes' => 'required|string|max:500'
            ]);

            $leaveRequest->update([
                'status' => 'rejected',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'admin_notes' => $validated['admin_notes']
            ]);

            $leaveRequest->load(['employee', 'leaveType', 'approver']);

            return response()->json([
                'status' => 'success',
                'data' => $leaveRequest,
                'message' => 'Leave request rejected successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave request not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Leave Requests API reject error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject leave request'
            ], 500);
        }
    }

    /**
     * Remove the specified leave request.
     */
    public function destroy($id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            // Only allow deletion of pending requests
            if ($leaveRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Can only delete pending leave requests'
                ], 400);
            }

            $leaveRequest->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Leave request deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave request not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Leave Requests API destroy error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete leave request'
            ], 500);
        }
    }

    /**
     * Get leave request statistics.
     */
    public function statistics(Request $request)
    {
        try {
            $employeeId = $request->get('employee_id');
            $year = $request->get('year', date('Y'));
            
            $query = LeaveRequest::query();

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            // Filter by year
            $query->whereYear('start_date', $year);

            $stats = [
                'total_requests' => $query->count(),
                'pending_requests' => $query->where('status', 'pending')->count(),
                'approved_requests' => $query->where('status', 'approved')->count(),
                'rejected_requests' => $query->where('status', 'rejected')->count(),
                'total_days_requested' => $query->sum('days_requested'),
                'approved_days' => $query->where('status', 'approved')->sum('days_requested'),
                'pending_days' => $query->where('status', 'pending')->sum('days_requested'),
                'year' => $year
            ];

            // Get leave type breakdown
            $leaveTypeStats = $query->selectRaw('leave_type_id, COUNT(*) as count, SUM(days_requested) as total_days')
                ->with('leaveType')
                ->groupBy('leave_type_id')
                ->get()
                ->map(function($item) {
                    return [
                        'leave_type' => $item->leaveType->name ?? 'Unknown',
                        'requests_count' => $item->count,
                        'total_days' => $item->total_days
                    ];
                });

            $stats['leave_type_breakdown'] = $leaveTypeStats;

            return response()->json([
                'status' => 'success',
                'data' => $stats,
                'message' => 'Statistics retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Leave Requests API statistics error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }

    /**
     * Get employee leave balance.
     */
    public function balance(Request $request, $employeeId)
    {
        try {
            $year = $request->get('year', date('Y'));
            
            // Get employee
            $employee = Employee::findOrFail($employeeId);
            
            // Calculate used leave days for the year
            $usedDays = LeaveRequest::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->whereYear('start_date', $year)
                ->sum('days_requested');

            // Get leave type balances (assuming there's a leave entitlement system)
            $leaveTypes = LeaveType::where('is_active', true)->get();
            $balances = [];

            foreach ($leaveTypes as $leaveType) {
                $usedForType = LeaveRequest::where('employee_id', $employeeId)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('status', 'approved')
                    ->whereYear('start_date', $year)
                    ->sum('days_requested');

                $balances[] = [
                    'leave_type' => $leaveType->name,
                    'annual_entitlement' => $leaveType->annual_entitlement ?? 0,
                    'used_days' => $usedForType,
                    'remaining_days' => max(0, ($leaveType->annual_entitlement ?? 0) - $usedForType)
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'employee' => $employee->first_name . ' ' . $employee->last_name,
                    'year' => $year,
                    'total_used_days' => $usedDays,
                    'leave_balances' => $balances
                ],
                'message' => 'Leave balance retrieved successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Leave Requests API balance error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve leave balance'
            ], 500);
        }
    }
}
