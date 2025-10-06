<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests.
     */
    public function index(Request $request)
    {
        try {
            $query = LeaveRequest::with(['employee', 'leaveType', 'approvedBy']);

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

            // Filter by emergency status
            if ($request->has('is_emergency')) {
                $query->where('is_emergency', $request->boolean('is_emergency'));
            }

            // Filter by date range if specified
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->inDateRange($request->start_date, $request->end_date);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $leaveRequests = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $leaveRequests,
                'message' => 'Leave requests retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Leave Requests API index error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve leave requests',
                'error' => config('app.debug') ? $e->getMessage() : null
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
                'comments' => 'nullable|string|max:1000',
                'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'is_emergency' => 'nullable|boolean',
                'deducted_days' => 'nullable|numeric|min:0|max:365'
            ]);

            // Calculate days if not provided or validate provided days
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $calculatedDays = $startDate->diffInDays($endDate) + 1;

            // Validate that requested days match calculated days (allow for half days)
            if (abs($validated['days_requested'] - $calculatedDays) > 0.5) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Days requested does not match the date range provided',
                    'calculated_days' => $calculatedDays,
                    'requested_days' => $validated['days_requested']
                ], 422);
            }

            // Handle medical certificate upload
            $medicalCertificatePath = null;
            if ($request->hasFile('medical_certificate')) {
                $medicalCertificatePath = $request->file('medical_certificate')->store('medical_certificates', 'public');
            }

            // Check for overlapping leave requests
            $overlappingRequests = LeaveRequest::where('employee_id', $validated['employee_id'])
                ->where('status', '!=', 'rejected')
                ->where(function($query) use ($validated) {
                    $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                          ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                          });
                })
                ->exists();

            if ($overlappingRequests) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You already have a leave request for overlapping dates'
                ], 422);
            }

            $leaveRequest = LeaveRequest::create([
                'employee_id' => $validated['employee_id'],
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'days_requested' => $validated['days_requested'],
                'reason' => $validated['reason'],
                'comments' => $validated['comments'] ?? null,
                'medical_certificate_path' => $medicalCertificatePath,
                'status' => 'pending',
                'is_emergency' => $validated['is_emergency'] ?? false,
                'deducted_days' => $validated['deducted_days'] ?? $validated['days_requested']
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
                'message' => 'Failed to create leave request',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified leave request.
     */
    public function show($id)
    {
        try {
            $leaveRequest = LeaveRequest::with(['employee', 'leaveType', 'approvedBy'])->findOrFail($id);

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
                'message' => 'Failed to retrieve leave request',
                'error' => config('app.debug') ? $e->getMessage() : null
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
                'comments' => 'nullable|string|max:1000',
                'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'is_emergency' => 'nullable|boolean',
                'deducted_days' => 'nullable|numeric|min:0|max:365'
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
                        'message' => 'Days requested does not match the date range provided',
                        'calculated_days' => $calculatedDays,
                        'requested_days' => $requestedDays
                    ], 422);
                }
                
                $validated['days_requested'] = $requestedDays;
            }

            // Handle medical certificate upload
            if ($request->hasFile('medical_certificate')) {
                // Delete old certificate if exists
                if ($leaveRequest->medical_certificate_path) {
                    Storage::disk('public')->delete($leaveRequest->medical_certificate_path);
                }
                $validated['medical_certificate_path'] = $request->file('medical_certificate')->store('medical_certificates', 'public');
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
                'message' => 'Failed to update leave request',
                'error' => config('app.debug') ? $e->getMessage() : null
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
                'comments' => 'nullable|string|max:1000',
                'deducted_days' => 'nullable|numeric|min:0|max:365'
            ]);

            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id() ?? 1, // Default to admin if no auth
                'approved_at' => now(),
                'comments' => $validated['comments'] ?? $leaveRequest->comments,
                'deducted_days' => $validated['deducted_days'] ?? $leaveRequest->deducted_days ?? $leaveRequest->days_requested
            ]);

            $leaveRequest->load(['employee', 'leaveType', 'approvedBy']);

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
                'message' => 'Failed to approve leave request',
                'error' => config('app.debug') ? $e->getMessage() : null
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
                'rejection_reason' => 'required|string|max:1000',
                'comments' => 'nullable|string|max:1000'
            ]);

            $leaveRequest->update([
                'status' => 'rejected',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
                'comments' => $validated['comments'] ?? $leaveRequest->comments
            ]);

            $leaveRequest->load(['employee', 'leaveType', 'approvedBy']);

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
                'message' => 'Failed to reject leave request',
                'error' => config('app.debug') ? $e->getMessage() : null
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

            // Delete medical certificate if exists
            if ($leaveRequest->medical_certificate_path) {
                Storage::disk('public')->delete($leaveRequest->medical_certificate_path);
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
                'message' => 'Failed to delete leave request',
                'error' => config('app.debug') ? $e->getMessage() : null
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
                'pending_requests' => (clone $query)->where('status', 'pending')->count(),
                'approved_requests' => (clone $query)->where('status', 'approved')->count(),
                'rejected_requests' => (clone $query)->where('status', 'rejected')->count(),
                'emergency_requests' => (clone $query)->where('is_emergency', true)->count(),
                'total_days_requested' => $query->sum('days_requested'),
                'approved_days' => (clone $query)->where('status', 'approved')->sum('days_requested'),
                'pending_days' => (clone $query)->where('status', 'pending')->sum('days_requested'),
                'deducted_days' => (clone $query)->where('status', 'approved')->sum('deducted_days'),
                'year' => $year
            ];

            // Get leave type breakdown
            $leaveTypeStats = (clone $query)->selectRaw('leave_type_id, COUNT(*) as count, SUM(days_requested) as total_days, SUM(CASE WHEN status = "approved" THEN deducted_days ELSE 0 END) as approved_days')
                ->with('leaveType')
                ->groupBy('leave_type_id')
                ->get()
                ->map(function($item) {
                    return [
                        'leave_type' => $item->leaveType->name ?? 'Unknown',
                        'requests_count' => $item->count,
                        'total_days' => $item->total_days,
                        'approved_days' => $item->approved_days
                    ];
                });

            $stats['leave_type_breakdown'] = $leaveTypeStats;

            // Monthly breakdown
            $monthlyStats = (clone $query)->selectRaw('MONTH(start_date) as month, COUNT(*) as count, SUM(days_requested) as total_days')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(function($item) {
                    return [
                        'month' => Carbon::create()->month($item->month)->format('F'),
                        'requests_count' => $item->count,
                        'total_days' => $item->total_days
                    ];
                });

            $stats['monthly_breakdown'] = $monthlyStats;

            return response()->json([
                'status' => 'success',
                'data' => $stats,
                'message' => 'Statistics retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Leave Requests API statistics error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics',
                'error' => config('app.debug') ? $e->getMessage() : null
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
                ->sum('deducted_days');

            // Get leave type balances
            $leaveTypes = LeaveType::where('is_active', true)->get();
            $balances = [];

            foreach ($leaveTypes as $leaveType) {
                $usedForType = LeaveRequest::where('employee_id', $employeeId)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('status', 'approved')
                    ->whereYear('start_date', $year)
                    ->sum('deducted_days');

                $pendingForType = LeaveRequest::where('employee_id', $employeeId)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('status', 'pending')
                    ->whereYear('start_date', $year)
                    ->sum('days_requested');

                $annualEntitlement = $leaveType->annual_entitlement ?? 0;
                $remainingDays = max(0, $annualEntitlement - $usedForType);

                $balances[] = [
                    'leave_type_id' => $leaveType->id,
                    'leave_type' => $leaveType->name,
                    'annual_entitlement' => $annualEntitlement,
                    'used_days' => $usedForType,
                    'pending_days' => $pendingForType,
                    'remaining_days' => $remainingDays,
                    'available_days' => max(0, $remainingDays - $pendingForType)
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
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
                'message' => 'Failed to retrieve leave balance',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk approve multiple leave requests.
     */
    public function bulkApprove(Request $request)
    {
        try {
            $validated = $request->validate([
                'leave_request_ids' => 'required|array',
                'leave_request_ids.*' => 'integer|exists:leave_requests,id',
                'comments' => 'nullable|string|max:1000'
            ]);

            $approvedCount = 0;
            $errors = [];

            foreach ($validated['leave_request_ids'] as $id) {
                try {
                    $leaveRequest = LeaveRequest::findOrFail($id);
                    
                    if ($leaveRequest->status === 'pending') {
                        $leaveRequest->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id() ?? 1,
                            'approved_at' => now(),
                            'comments' => $validated['comments'] ?? $leaveRequest->comments
                        ]);
                        $approvedCount++;
                    } else {
                        $errors[] = "Leave request ID {$id} is not pending";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to approve leave request ID {$id}: " . $e->getMessage();
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'approved_count' => $approvedCount,
                    'total_requested' => count($validated['leave_request_ids']),
                    'errors' => $errors
                ],
                'message' => "Successfully approved {$approvedCount} leave requests"
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Leave Requests API bulk approve error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to bulk approve leave requests',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}