<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request)
    {
        try {
            $query = Attendance::with(['employee']);

            // Filter by employee if specified
            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            // Filter by status if specified
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date if specified
            if ($request->has('date')) {
                $query->whereDate('date', $request->date);
            }

            // Filter by date range if specified
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            // Filter by location if specified
            if ($request->has('location')) {
                $query->where('location', 'like', '%' . $request->location . '%');
            }

            $attendances = $query->orderBy('date', 'desc')
                                ->orderBy('clock_in_time', 'desc')
                                ->paginate(15);

            return response()->json([
                'status' => 'success',
                'data' => $attendances,
                'message' => 'Attendance records retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Attendance API index error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve attendance records'
            ], 500);
        }
    }

    /**
     * Store a newly created attendance record (Clock In).
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|integer|exists:employees,id',
                'date' => 'required|date',
                'clock_in_time' => 'required|date_format:H:i:s',
                'location' => 'nullable|string|max:255',
                'ip_address' => 'nullable|ip',
                'notes' => 'nullable|string|max:500'
            ]);

            // Check if employee already has attendance record for this date
            $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
                ->whereDate('date', $validated['date'])
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee already has attendance record for this date'
                ], 422);
            }

            // Calculate total hours as 0 initially (will be updated on clock out)
            $attendance = Attendance::create([
                'employee_id' => $validated['employee_id'],
                'date' => $validated['date'],
                'clock_in_time' => $validated['clock_in_time'],
                'clock_out_time' => null,
                'break_start_time' => null,
                'break_end_time' => null,
                'total_hours' => 0,
                'overtime_hours' => 0,
                'status' => 'clocked_in',
                'location' => $validated['location'] ?? 'Office',
                'ip_address' => $validated['ip_address'] ?? $request->ip(),
                'notes' => $validated['notes'] ?? null
            ]);

            // Load relationships for response
            $attendance->load(['employee']);

            return response()->json([
                'status' => 'success',
                'data' => $attendance,
                'message' => 'Clock in recorded successfully'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Attendance API store error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record clock in'
            ], 500);
        }
    }

    /**
     * Display the specified attendance record.
     */
    public function show($id)
    {
        try {
            $attendance = Attendance::with(['employee'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $attendance,
                'message' => 'Attendance record retrieved successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Attendance API show error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve attendance record'
            ], 500);
        }
    }

    /**
     * Update the specified attendance record.
     */
    public function update(Request $request, $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);

            $validated = $request->validate([
                'clock_out_time' => 'nullable|date_format:H:i:s|after:clock_in_time',
                'break_start_time' => 'nullable|date_format:H:i:s',
                'break_end_time' => 'nullable|date_format:H:i:s|after:break_start_time',
                'location' => 'sometimes|string|max:255',
                'notes' => 'nullable|string|max:500',
                'status' => 'sometimes|in:clocked_in,on_break,clocked_out,present,late'
            ]);

            // Calculate total hours if clock_out_time is provided
            if (isset($validated['clock_out_time'])) {
                $clockIn = Carbon::createFromFormat('H:i:s', $attendance->clock_in_time);
                $clockOut = Carbon::createFromFormat('H:i:s', $validated['clock_out_time']);
                
                $totalMinutes = $clockOut->diffInMinutes($clockIn);
                
                // Subtract break time if provided
                if ($attendance->break_start_time && $attendance->break_end_time) {
                    $breakStart = Carbon::createFromFormat('H:i:s', $attendance->break_start_time);
                    $breakEnd = Carbon::createFromFormat('H:i:s', $attendance->break_end_time);
                    $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                    $totalMinutes -= $breakMinutes;
                }
                
                $totalHours = $totalMinutes / 60;
                $validated['total_hours'] = round($totalHours, 2);
                
                // Calculate overtime (anything over 8 hours)
                $validated['overtime_hours'] = max(0, $totalHours - 8);
                
                // Update status to clocked_out
                $validated['status'] = 'clocked_out';
            }

            $attendance->update($validated);
            $attendance->load(['employee']);

            return response()->json([
                'status' => 'success',
                'data' => $attendance,
                'message' => 'Attendance record updated successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Attendance API update error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update attendance record'
            ], 500);
        }
    }

    /**
     * Clock out an employee.
     */
    public function clockOut(Request $request, $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);

            if ($attendance->status === 'clocked_out') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee is already clocked out'
                ], 400);
            }

            $validated = $request->validate([
                'clock_out_time' => 'required|date_format:H:i:s',
                'notes' => 'nullable|string|max:500'
            ]);

            // Calculate total hours
            $clockIn = Carbon::createFromFormat('H:i:s', $attendance->clock_in_time);
            $clockOut = Carbon::createFromFormat('H:i:s', $validated['clock_out_time']);
            
            $totalMinutes = $clockOut->diffInMinutes($clockIn);
            
            // Subtract break time if exists
            if ($attendance->break_start_time && $attendance->break_end_time) {
                $breakStart = Carbon::createFromFormat('H:i:s', $attendance->break_start_time);
                $breakEnd = Carbon::createFromFormat('H:i:s', $attendance->break_end_time);
                $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                $totalMinutes -= $breakMinutes;
            }
            
            $totalHours = $totalMinutes / 60;
            $overtimeHours = max(0, $totalHours - 8);

            $attendance->update([
                'clock_out_time' => $validated['clock_out_time'],
                'total_hours' => round($totalHours, 2),
                'overtime_hours' => round($overtimeHours, 2),
                'status' => 'clocked_out',
                'notes' => $validated['notes'] ?? $attendance->notes
            ]);

            $attendance->load(['employee']);

            return response()->json([
                'status' => 'success',
                'data' => $attendance,
                'message' => 'Clock out recorded successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Attendance API clock out error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record clock out'
            ], 500);
        }
    }

    /**
     * Start break for an employee.
     */
    public function startBreak(Request $request, $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);

            if ($attendance->status !== 'clocked_in') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee must be clocked in to start break'
                ], 400);
            }

            $validated = $request->validate([
                'break_start_time' => 'required|date_format:H:i:s'
            ]);

            $attendance->update([
                'break_start_time' => $validated['break_start_time'],
                'status' => 'on_break'
            ]);

            $attendance->load(['employee']);

            return response()->json([
                'status' => 'success',
                'data' => $attendance,
                'message' => 'Break started successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Attendance API start break error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start break'
            ], 500);
        }
    }

    /**
     * End break for an employee.
     */
    public function endBreak(Request $request, $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);

            if ($attendance->status !== 'on_break') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee is not currently on break'
                ], 400);
            }

            $validated = $request->validate([
                'break_end_time' => 'required|date_format:H:i:s|after:break_start_time'
            ]);

            $attendance->update([
                'break_end_time' => $validated['break_end_time'],
                'status' => 'clocked_in'
            ]);

            $attendance->load(['employee']);

            return response()->json([
                'status' => 'success',
                'data' => $attendance,
                'message' => 'Break ended successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Attendance API end break error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to end break'
            ], 500);
        }
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance record deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Attendance API destroy error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete attendance record'
            ], 500);
        }
    }

    /**
     * Get attendance statistics.
     */
    public function statistics(Request $request)
    {
        try {
            $employeeId = $request->get('employee_id');
            $date = $request->get('date', date('Y-m-d'));
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            
            $query = Attendance::query();

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            } else {
                $query->whereDate('date', $date);
            }

            $stats = [
                'total_records' => $query->count(),
                'present_today' => $query->where('status', '!=', 'absent')->count(),
                'clocked_in' => $query->where('status', 'clocked_in')->count(),
                'on_break' => $query->where('status', 'on_break')->count(),
                'clocked_out' => $query->where('status', 'clocked_out')->count(),
                'late_arrivals' => $query->where('status', 'late')->count(),
                'total_hours_worked' => $query->sum('total_hours'),
                'total_overtime_hours' => $query->sum('overtime_hours'),
                'average_hours_per_employee' => $query->avg('total_hours'),
                'date_range' => $startDate && $endDate ? "$startDate to $endDate" : $date
            ];

            // Get location breakdown
            $locationStats = $query->selectRaw('location, COUNT(*) as count')
                ->groupBy('location')
                ->get()
                ->map(function($item) {
                    return [
                        'location' => $item->location,
                        'count' => $item->count
                    ];
                });

            $stats['location_breakdown'] = $locationStats;

            return response()->json([
                'status' => 'success',
                'data' => $stats,
                'message' => 'Statistics retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Attendance API statistics error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }

    /**
     * Get current attendance status for an employee.
     */
    public function currentStatus($employeeId)
    {
        try {
            $today = date('Y-m-d');
            $attendance = Attendance::where('employee_id', $employeeId)
                ->whereDate('date', $today)
                ->with(['employee'])
                ->first();

            if (!$attendance) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'employee_id' => $employeeId,
                        'status' => 'not_clocked_in',
                        'message' => 'Employee has not clocked in today'
                    ],
                    'message' => 'Current status retrieved successfully'
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => $attendance,
                'message' => 'Current status retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Attendance API current status error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve current status'
            ], 500);
        }
    }
}
