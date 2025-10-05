<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Traits\DatabaseConnectionTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceManagementController extends Controller
{
    use DatabaseConnectionTrait;

    /**
     * Display a listing of attendance records
     */
    public function index(Request $request)
    {
        try {
            $query = Attendance::with(['employee']);

            // Apply filters
            if ($request->has('date') && $request->date != '') {
                $query->whereDate('attendance_date', $request->date);
            }

            if ($request->has('employee_id') && $request->employee_id != '') {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            if ($request->has('is_approved') && $request->is_approved != '') {
                $query->where('is_approved', $request->is_approved);
            }

            // Default to current month if no date filter
            if (!$request->has('date')) {
                $startOfMonth = Carbon::now()->startOfMonth();
                $endOfMonth = Carbon::now()->endOfMonth();
                $query->whereBetween('attendance_date', [$startOfMonth, $endOfMonth]);
            }

            $attendances = $query->orderBy('attendance_date', 'desc')
                               ->orderBy('clock_in_time', 'asc')
                               ->paginate(20);

            // Get data for filters
            $employees = Employee::active()->orderBy('first_name')->get();

            return view('admin.attendance.index', compact('attendances', 'employees'));
            
        } catch (\Exception $e) {
            Log::error('Error fetching attendance records: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading attendance records: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new attendance record
     */
    public function create()
    {
        $employees = Employee::active()->orderBy('first_name')->get();
        return view('admin.attendance.create', compact('employees'));
    }

    /**
     * Store a newly created attendance record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i|after:clock_in_time',
            'break_hours' => 'nullable|numeric|min:0|max:8',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:present,absent,late,half_day,on_leave'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Check for existing attendance record
            $existing = Attendance::where('employee_id', $request->employee_id)
                ->where('attendance_date', $request->attendance_date)
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Attendance record already exists for this employee on this date.');
            }

            // Calculate total hours if both clock in and out times are provided
            $totalHours = null;
            $overtimeHours = 0;

            if ($request->clock_in_time && $request->clock_out_time) {
                $clockIn = Carbon::createFromFormat('H:i', $request->clock_in_time);
                $clockOut = Carbon::createFromFormat('H:i', $request->clock_out_time);
                $breakHours = $request->break_hours ?? 1.0;
                
                $totalMinutes = $clockOut->diffInMinutes($clockIn);
                $totalHours = ($totalMinutes / 60) - $breakHours;
                
                // Calculate overtime (assuming 8 hours is standard)
                if ($totalHours > 8) {
                    $overtimeHours = $totalHours - 8;
                }
            }

            $attendance = Attendance::create([
                'employee_id' => $request->employee_id,
                'attendance_date' => $request->attendance_date,
                'clock_in_time' => $request->clock_in_time,
                'clock_out_time' => $request->clock_out_time,
                'total_hours' => $totalHours,
                'break_hours' => $request->break_hours ?? 1.0,
                'overtime_hours' => $overtimeHours,
                'location' => $request->location,
                'notes' => $request->notes,
                'status' => $request->status
            ]);

            Log::info('Attendance record created successfully: ' . $attendance->id);
            return redirect()->route('attendance.index')
                ->with('success', 'Attendance record created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating attendance record: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating attendance record: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified attendance record
     */
    public function show(Attendance $attendance)
    {
        try {
            $attendance->load(['employee']);
            return view('admin.attendance.show', compact('attendance'));
        } catch (\Exception $e) {
            Log::error('Error showing attendance record: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading attendance details.');
        }
    }

    /**
     * Show the form for editing the specified attendance record
     */
    public function edit(Attendance $attendance)
    {
        $employees = Employee::active()->orderBy('first_name')->get();
        return view('admin.attendance.edit', compact('attendance', 'employees'));
    }

    /**
     * Update the specified attendance record
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i|after:clock_in_time',
            'break_hours' => 'nullable|numeric|min:0|max:8',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:present,absent,late,half_day,on_leave',
            'is_approved' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Check for existing attendance record (excluding current one)
            $existing = Attendance::where('employee_id', $request->employee_id)
                ->where('attendance_date', $request->attendance_date)
                ->where('id', '!=', $attendance->id)
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Another attendance record already exists for this employee on this date.');
            }

            // Recalculate total hours if times are provided
            $totalHours = $attendance->total_hours;
            $overtimeHours = $attendance->overtime_hours;

            if ($request->clock_in_time && $request->clock_out_time) {
                $clockIn = Carbon::createFromFormat('H:i', $request->clock_in_time);
                $clockOut = Carbon::createFromFormat('H:i', $request->clock_out_time);
                $breakHours = $request->break_hours ?? 1.0;
                
                $totalMinutes = $clockOut->diffInMinutes($clockIn);
                $totalHours = ($totalMinutes / 60) - $breakHours;
                
                // Calculate overtime (assuming 8 hours is standard)
                $overtimeHours = $totalHours > 8 ? $totalHours - 8 : 0;
            }

            $updateData = $request->all();
            $updateData['total_hours'] = $totalHours;
            $updateData['overtime_hours'] = $overtimeHours;

            $attendance->update($updateData);

            Log::info('Attendance record updated successfully: ' . $attendance->id);
            return redirect()->route('attendance.index')
                ->with('success', 'Attendance record updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating attendance record: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating attendance record: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified attendance record
     */
    public function destroy(Attendance $attendance)
    {
        try {
            $attendanceInfo = "Attendance for {$attendance->employee->full_name} on {$attendance->attendance_date}";
            $attendance->delete();

            Log::info('Attendance record deleted successfully: ' . $attendanceInfo);
            return redirect()->route('attendance.index')
                ->with('success', 'Attendance record deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting attendance record: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting attendance record: ' . $e->getMessage());
        }
    }

    /**
     * Clock in an employee
     */
    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'location' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed'], 422);
        }

        try {
            $today = Carbon::today();
            
            // Check if already clocked in today
            $existing = Attendance::where('employee_id', $request->employee_id)
                ->where('attendance_date', $today)
                ->first();

            if ($existing && $existing->clock_in_time) {
                return response()->json(['error' => 'Already clocked in today'], 400);
            }

            $attendance = $existing ?: new Attendance();
            $attendance->employee_id = $request->employee_id;
            $attendance->attendance_date = $today;
            $attendance->clock_in_time = Carbon::now()->format('H:i:s');
            $attendance->location = $request->location;
            $attendance->status = 'present';
            $attendance->save();

            return response()->json([
                'success' => true,
                'message' => 'Clocked in successfully',
                'attendance' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Error clocking in: ' . $e->getMessage());
            return response()->json(['error' => 'Error clocking in'], 500);
        }
    }

    /**
     * Clock out an employee
     */
    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed'], 422);
        }

        try {
            $today = Carbon::today();
            
            $attendance = Attendance::where('employee_id', $request->employee_id)
                ->where('attendance_date', $today)
                ->first();

            if (!$attendance || !$attendance->clock_in_time) {
                return response()->json(['error' => 'No clock-in record found for today'], 400);
            }

            if ($attendance->clock_out_time) {
                return response()->json(['error' => 'Already clocked out today'], 400);
            }

            $attendance->clock_out_time = Carbon::now()->format('H:i:s');
            
            // Calculate total hours
            $clockIn = Carbon::createFromFormat('H:i:s', $attendance->clock_in_time);
            $clockOut = Carbon::now();
            $breakHours = $attendance->break_hours ?? 1.0;
            
            $totalMinutes = $clockOut->diffInMinutes($clockIn);
            $totalHours = ($totalMinutes / 60) - $breakHours;
            $attendance->total_hours = max(0, $totalHours);
            
            // Calculate overtime
            $attendance->overtime_hours = $totalHours > 8 ? $totalHours - 8 : 0;
            
            $attendance->save();

            return response()->json([
                'success' => true,
                'message' => 'Clocked out successfully',
                'attendance' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Error clocking out: ' . $e->getMessage());
            return response()->json(['error' => 'Error clocking out'], 500);
        }
    }

    /**
     * Get attendance statistics
     */
    public function getStats()
    {
        try {
            $today = Carbon::today();
            $thisMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            
            return [
                'present_today' => Attendance::whereDate('attendance_date', $today)
                    ->where('status', 'present')->count(),
                'absent_today' => Attendance::whereDate('attendance_date', $today)
                    ->where('status', 'absent')->count(),
                'late_today' => Attendance::whereDate('attendance_date', $today)
                    ->where('status', 'late')->count(),
                'total_this_month' => Attendance::whereBetween('attendance_date', $thisMonth)->count(),
                'pending_approval' => Attendance::where('is_approved', false)->count()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting attendance stats: ' . $e->getMessage());
            return [
                'present_today' => 0,
                'absent_today' => 0,
                'late_today' => 0,
                'total_this_month' => 0,
                'pending_approval' => 0
            ];
        }
    }
}
