<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShiftRequest;
use App\Models\Employee;
use App\Models\ShiftType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeShiftController extends Controller
{
    /**
     * Display the shift schedule management page for employees
     */
    public function index()
    {
        try {
            // Get employees for dropdown
            $employees = DB::table('employees')
                ->select('id', 'first_name', 'last_name', 'employee_number')
                ->where('is_active', 1)
                ->orderBy('first_name')
                ->get();

            // Get shift types for dropdown
            $shiftTypes = DB::table('shift_types')
                ->select('id', 'name', 'type', 'default_start_time', 'default_end_time', 'description')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get();

            // Get shift requests for current user (if authenticated)
            $shiftRequests = collect([]);
            if (auth()->check()) {
                $shiftRequests = DB::table('shift_requests as sr')
                    ->join('employees as e', 'sr.employee_id', '=', 'e.id')
                    ->leftJoin('shift_types as st', 'sr.shift_type_id', '=', 'st.id')
                    ->select(
                        'sr.*',
                        'e.first_name',
                        'e.last_name',
                        'st.name as shift_type_name'
                    )
                    ->where('sr.employee_id', auth()->id())
                    ->orderBy('sr.created_at', 'desc')
                    ->get();
            }

            return view('employee_ess_modules.shift_schedule_management', compact(
                'employees',
                'shiftTypes', 
                'shiftRequests'
            ));

        } catch (\Exception $e) {
            Log::error('Employee shift index failed: ' . $e->getMessage());
            
            return view('employee_ess_modules.shift_schedule_management', [
                'employees' => collect([]),
                'shiftTypes' => collect([]),
                'shiftRequests' => collect([])
            ]);
        }
    }

    /**
     * Store a new shift request
     */
    public function store(Request $request)
    {
        $request->validate([
            'request_type' => 'required|in:shift_change,time_off,overtime,swap',
            'requested_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:1000',
            'shift_type_id' => 'nullable|exists:shift_types,id',
            'requested_start_time' => 'nullable|date_format:H:i',
            'requested_end_time' => 'nullable|date_format:H:i',
        ]);

        try {
            $shiftRequest = new ShiftRequest();
            $shiftRequest->employee_id = auth()->id() ?? $request->employee_id;
            $shiftRequest->request_type = $request->request_type;
            $shiftRequest->shift_type_id = $request->shift_type_id;
            $shiftRequest->requested_date = $request->requested_date;
            $shiftRequest->requested_start_time = $request->requested_start_time;
            $shiftRequest->requested_end_time = $request->requested_end_time;
            $shiftRequest->reason = $request->reason;
            $shiftRequest->status = 'pending';
            $shiftRequest->save();

            return redirect()->back()->with('success', 'Shift request submitted successfully!');

        } catch (\Exception $e) {
            Log::error('Shift request creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to submit shift request. Please try again.');
        }
    }

    /**
     * Update shift request status (for managers)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        try {
            $shiftRequest = ShiftRequest::findOrFail($id);
            $shiftRequest->status = $request->status;
            $shiftRequest->approved_by = auth()->id();
            $shiftRequest->approved_at = now();
            $shiftRequest->save();

            return redirect()->back()->with('success', 'Shift request status updated successfully!');

        } catch (\Exception $e) {
            Log::error('Shift request status update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update shift request status.');
        }
    }

    /**
     * Delete a shift request
     */
    public function destroy($id)
    {
        try {
            $shiftRequest = ShiftRequest::findOrFail($id);
            
            // Only allow deletion by the request owner or managers
            if ($shiftRequest->employee_id !== auth()->id() && !auth()->user()->is_manager) {
                return redirect()->back()->with('error', 'You are not authorized to delete this request.');
            }

            $shiftRequest->delete();
            return redirect()->back()->with('success', 'Shift request deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Shift request deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete shift request.');
        }
    }
}
