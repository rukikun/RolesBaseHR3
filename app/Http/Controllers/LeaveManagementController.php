<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Traits\DatabaseConnectionTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveManagementController extends Controller
{
    use DatabaseConnectionTrait;

    /**
     * Display a listing of leave requests
     */
    public function index(Request $request)
    {
        try {
            $query = LeaveRequest::with(['employee', 'leaveType', 'approvedBy']);

            // Apply filters
            if ($request->has('employee_id') && $request->employee_id != '') {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->has('leave_type_id') && $request->leave_type_id != '') {
                $query->where('leave_type_id', $request->leave_type_id);
            }

            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            if ($request->has('start_date') && $request->start_date != '') {
                $query->where('start_date', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date != '') {
                $query->where('end_date', '<=', $request->end_date);
            }

            // Default to current year if no date filter
            if (!$request->has('start_date') && !$request->has('end_date')) {
                $query->whereYear('start_date', Carbon::now()->year);
            }

            $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(20);

            // Get data for filters
            $employees = Employee::active()->orderBy('first_name')->get();
            $leaveTypes = LeaveType::active()->orderBy('name')->get();

            return view('admin.leave.index', compact('leaveRequests', 'employees', 'leaveTypes'));
            
        } catch (\Exception $e) {
            Log::error('Error fetching leave requests: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading leave requests: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new leave request
     */
    public function create()
    {
        $employees = Employee::active()->orderBy('first_name')->get();
        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        
        return view('admin.leave.create', compact('employees', 'leaveTypes'));
    }

    /**
     * Store a newly created leave request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'comments' => 'nullable|string|max:1000',
            'is_emergency' => 'boolean',
            'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $daysRequested = $startDate->diffInDays($endDate) + 1;

            // Check for overlapping leave requests
            $overlapping = LeaveRequest::where('employee_id', $request->employee_id)
                ->where('status', '!=', 'rejected')
                ->where('status', '!=', 'cancelled')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function($q) use ($startDate, $endDate) {
                              $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                          });
                })
                ->exists();

            if ($overlapping) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Employee already has leave scheduled during this period.');
            }

            // Handle file upload
            $medicalCertificatePath = null;
            if ($request->hasFile('medical_certificate')) {
                $medicalCertificatePath = $request->file('medical_certificate')
                    ->store('medical_certificates', 'public');
            }

            $leaveRequest = LeaveRequest::create([
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days_requested' => $daysRequested,
                'reason' => $request->reason,
                'comments' => $request->comments,
                'medical_certificate_path' => $medicalCertificatePath,
                'is_emergency' => $request->boolean('is_emergency'),
                'status' => 'pending'
            ]);

            Log::info('Leave request created successfully: ' . $leaveRequest->id);
            return redirect()->route('leave.index')
                ->with('success', 'Leave request created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating leave request: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating leave request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified leave request
     */
    public function show(LeaveRequest $leave)
    {
        try {
            $leave->load(['employee', 'leaveType', 'approvedBy']);
            return view('admin.leave.show', compact('leave'));
        } catch (\Exception $e) {
            Log::error('Error showing leave request: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading leave request details.');
        }
    }

    /**
     * Show the form for editing the specified leave request
     */
    public function edit(LeaveRequest $leave)
    {
        if (!$leave->canBeModified()) {
            return redirect()->route('leave.index')
                ->with('error', 'This leave request cannot be modified.');
        }

        $employees = Employee::active()->orderBy('first_name')->get();
        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        
        return view('admin.leave.edit', compact('leave', 'employees', 'leaveTypes'));
    }

    /**
     * Update the specified leave request
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        if (!$leave->canBeModified()) {
            return redirect()->route('leave.index')
                ->with('error', 'This leave request cannot be modified.');
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'comments' => 'nullable|string|max:1000',
            'is_emergency' => 'boolean',
            'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $daysRequested = $startDate->diffInDays($endDate) + 1;

            // Check for overlapping leave requests (excluding current one)
            $overlapping = LeaveRequest::where('employee_id', $request->employee_id)
                ->where('id', '!=', $leave->id)
                ->where('status', '!=', 'rejected')
                ->where('status', '!=', 'cancelled')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function($q) use ($startDate, $endDate) {
                              $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                          });
                })
                ->exists();

            if ($overlapping) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Employee already has leave scheduled during this period.');
            }

            $updateData = [
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days_requested' => $daysRequested,
                'reason' => $request->reason,
                'comments' => $request->comments,
                'is_emergency' => $request->boolean('is_emergency')
            ];

            // Handle file upload
            if ($request->hasFile('medical_certificate')) {
                $updateData['medical_certificate_path'] = $request->file('medical_certificate')
                    ->store('medical_certificates', 'public');
            }

            $leave->update($updateData);

            Log::info('Leave request updated successfully: ' . $leave->id);
            return redirect()->route('leave.index')
                ->with('success', 'Leave request updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating leave request: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating leave request: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified leave request
     */
    public function destroy(LeaveRequest $leave)
    {
        try {
            $leaveInfo = "Leave request for {$leave->employee->full_name} from {$leave->start_date} to {$leave->end_date}";
            $leave->delete();

            Log::info('Leave request deleted successfully: ' . $leaveInfo);
            return redirect()->route('leave.index')
                ->with('success', 'Leave request deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting leave request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting leave request: ' . $e->getMessage());
        }
    }

    /**
     * Approve a leave request
     */
    public function approve(LeaveRequest $leave)
    {
        try {
            if ($leave->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'Only pending leave requests can be approved.');
            }

            $leave->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            Log::info('Leave request approved: ' . $leave->id);
            return redirect()->back()
                ->with('success', 'Leave request approved successfully!');

        } catch (\Exception $e) {
            Log::error('Error approving leave request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error approving leave request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a leave request
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Rejection reason is required.');
        }

        try {
            if ($leave->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'Only pending leave requests can be rejected.');
            }

            $leave->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason
            ]);

            Log::info('Leave request rejected: ' . $leave->id);
            return redirect()->back()
                ->with('success', 'Leave request rejected successfully!');

        } catch (\Exception $e) {
            Log::error('Error rejecting leave request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error rejecting leave request: ' . $e->getMessage());
        }
    }

    /**
     * Get leave statistics
     */
    public function getStats()
    {
        try {
            $thisMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            $thisYear = [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            
            return [
                'pending' => LeaveRequest::where('status', 'pending')->count(),
                'approved_this_month' => LeaveRequest::where('status', 'approved')
                    ->whereBetween('start_date', $thisMonth)->count(),
                'total_this_year' => LeaveRequest::whereBetween('start_date', $thisYear)->count(),
                'emergency_requests' => LeaveRequest::where('is_emergency', true)
                    ->where('status', 'pending')->count()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting leave stats: ' . $e->getMessage());
            return [
                'pending' => 0,
                'approved_this_month' => 0,
                'total_this_year' => 0,
                'emergency_requests' => 0
            ];
        }
    }
}
