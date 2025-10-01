<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ShiftRequest;
use App\Models\Employee;
use App\Models\ShiftType;

class ShiftRequestController extends Controller
{
    /**
     * Store a newly created shift request in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'shift_type_id' => 'required|integer|exists:shift_types,id',
            'shift_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'hours' => 'nullable|numeric|min:0.5|max:24'
        ]);

        try {
            // Calculate hours
            $startTime = \Carbon\Carbon::parse($request->start_time);
            $endTime = \Carbon\Carbon::parse($request->end_time);
            $hours = $endTime->diffInHours($startTime);

            // Create shift request using Eloquent model
            $shiftRequest = ShiftRequest::create([
                'employee_id' => $request->employee_id,
                'shift_type_id' => $request->shift_type_id,
                'shift_date' => $request->shift_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'hours' => $hours,
                'location' => $request->location ?? 'Main Office',
                'notes' => $request->notes,
                'status' => 'pending'
            ]);

            return redirect()->back()->with('success', 'Shift request submitted successfully! Request ID: ' . $shiftRequest->id);

        } catch (\Exception $e) {
            Log::error('Shift request creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to submit shift request. Please try again.');
        }
    }

    /**
     * Approve a shift request and automatically create shift assignment.
     */
    public function approve($id)
    {
        try {
            $shiftRequest = ShiftRequest::findOrFail($id);
            
            // Update shift request status
            $shiftRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id() ?? 1, // Default to admin if no auth
                'approved_at' => now()
            ]);

            // Check if shift assignment already exists for this employee on this date
            $existingShift = DB::table('shifts')
                ->where('employee_id', $shiftRequest->employee_id)
                ->where('shift_date', $shiftRequest->shift_date)
                ->where('start_time', $shiftRequest->start_time)
                ->first();

            if (!$existingShift) {
                // Automatically create shift assignment in calendar
                $shiftAssignment = DB::table('shifts')->insert([
                    'employee_id' => $shiftRequest->employee_id,
                    'shift_type_id' => $shiftRequest->shift_type_id,
                    'shift_date' => $shiftRequest->shift_date,
                    'start_time' => $shiftRequest->start_time,
                    'end_time' => $shiftRequest->end_time,
                    'location' => $shiftRequest->location ?? 'Main Office',
                    'notes' => $shiftRequest->notes ?? 'Auto-created from approved shift request',
                    'status' => 'scheduled',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Log::info("Shift request {$id} approved and shift assignment created for employee {$shiftRequest->employee_id}");
                $message = 'Shift request approved successfully! Shift has been automatically added to the schedule calendar.';
            } else {
                Log::info("Shift request {$id} approved but shift assignment already exists for employee {$shiftRequest->employee_id}");
                $message = 'Shift request approved successfully! Note: A shift assignment for this time slot already exists.';
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Shift request approval failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to approve shift request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a shift request.
     */
    public function reject($id)
    {
        try {
            $shiftRequest = ShiftRequest::findOrFail($id);
            $shiftRequest->update([
                'status' => 'rejected',
                'approved_by' => auth()->id() ?? 1, // Default to admin if no auth
                'approved_at' => now()
            ]);

            return redirect()->back()->with('success', 'Shift request rejected successfully!');

        } catch (\Exception $e) {
            Log::error('Shift request rejection failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject shift request.');
        }
    }

    /**
     * Remove the specified shift request from storage.
     */
    public function destroy($id)
    {
        try {
            $shiftRequest = ShiftRequest::findOrFail($id);
            $shiftRequest->delete();
            
            return redirect()->back()->with('success', 'Shift request deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Shift request deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete shift request.');
        }
    }

    /**
     * Get shift requests with relationships for display.
     */
    public function getShiftRequestsWithRelations()
    {
        try {
            return ShiftRequest::with(['employee', 'shiftType', 'approver'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to get shift requests: ' . $e->getMessage());
            return collect();
        }
    }
}
