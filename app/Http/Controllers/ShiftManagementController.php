<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\ShiftType;
use App\Models\Employee;
use App\Traits\DatabaseConnectionTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShiftManagementController extends Controller
{
    use DatabaseConnectionTrait;

    /**
     * Display a listing of shifts
     */
    public function index(Request $request)
    {
        try {
            $query = Shift::with(['employee', 'shiftType']);

            // Apply filters
            if ($request->has('date') && $request->date != '') {
                $query->whereDate('shift_date', $request->date);
            }

            if ($request->has('employee_id') && $request->employee_id != '') {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->has('shift_type_id') && $request->shift_type_id != '') {
                $query->where('shift_type_id', $request->shift_type_id);
            }

            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            // Default to current week if no date filter
            if (!$request->has('date')) {
                $startOfWeek = Carbon::now()->startOfWeek();
                $endOfWeek = Carbon::now()->endOfWeek();
                $query->whereBetween('shift_date', [$startOfWeek, $endOfWeek]);
            }

            $shifts = $query->orderBy('shift_date', 'asc')
                          ->orderBy('start_time', 'asc')
                          ->paginate(20);

            // Get data for filters
            $employees = Employee::active()->orderBy('first_name')->get();
            $shiftTypes = ShiftType::active()->orderBy('name')->get();

            // Redirect to shift schedule management page instead of non-existent view
            return redirect()->route('shift-schedule-management');
            
        } catch (\Exception $e) {
            Log::error('Error fetching shifts: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading shifts: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new shift
     */
    public function create()
    {
        $employees = Employee::active()->orderBy('first_name')->get();
        $shiftTypes = ShiftType::active()->orderBy('name')->get();
        
        // Redirect to shift schedule management page instead of non-existent view
        return redirect()->route('shift-schedule-management');
    }

    /**
     * Store a newly created shift
     */
    public function store(Request $request)
    {
        Log::info('=== ShiftManagementController::store called ===');
        Log::info('Request data: ' . json_encode($request->all()));
        
        // Convert time format if needed - simplified approach
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        
        // Simple time format conversion
        if (!empty($startTime)) {
            if (strpos($startTime, 'AM') !== false || strpos($startTime, 'PM') !== false) {
                $startTime = date('H:i', strtotime($startTime));
            } elseif (preg_match('/^\d{1,2}:\d{2}$/', $startTime)) {
                // Already in correct format, just ensure 2-digit hours
                $parts = explode(':', $startTime);
                $startTime = str_pad($parts[0], 2, '0', STR_PAD_LEFT) . ':' . $parts[1];
            }
        }
        
        if (!empty($endTime)) {
            if (strpos($endTime, 'AM') !== false || strpos($endTime, 'PM') !== false) {
                $endTime = date('H:i', strtotime($endTime));
            } elseif (preg_match('/^\d{1,2}:\d{2}$/', $endTime)) {
                // Already in correct format, just ensure 2-digit hours
                $parts = explode(':', $endTime);
                $endTime = str_pad($parts[0], 2, '0', STR_PAD_LEFT) . ':' . $parts[1];
            }
        }
        
        // Update request with converted times
        $request->merge([
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'shift_type_id' => 'nullable|exists:shift_types,id',
            'shift_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'nullable|string|max:255',
            'break_duration' => 'nullable|integer|min:0|max:480',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:scheduled,confirmed,completed,cancelled,no_show'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Check for conflicts
            $conflict = Shift::where('employee_id', $request->employee_id)
                ->where('shift_date', $request->shift_date)
                ->where('start_time', $request->start_time)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($conflict) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Employee already has a shift scheduled at this time.');
            }

            $shift = Shift::create([
                'employee_id' => $request->employee_id,
                'shift_type_id' => $request->shift_type_id,
                'shift_date' => $request->shift_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location,
                'break_duration' => $request->break_duration ?? 60,
                'notes' => $request->notes,
                'status' => $request->status ?? 'scheduled'
            ]);

            Log::info('Shift created successfully: ' . $shift->id);
            return redirect()->route('shift-schedule-management')
                ->with('success', 'Shift assigned successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating shift: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating shift: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified shift
     */
    public function show(Shift $shift)
    {
        try {
            $shift->load(['employee', 'shiftType']);
            // Redirect to shift schedule management page instead of non-existent view
            return redirect()->route('shift-schedule-management');
        } catch (\Exception $e) {
            Log::error('Error showing shift: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading shift details.');
        }
    }

    /**
     * Show the form for editing the specified shift
     */
    public function edit(Shift $shift)
    {
        $employees = Employee::active()->orderBy('first_name')->get();
        $shiftTypes = ShiftType::active()->orderBy('name')->get();
        
        // Redirect to shift schedule management page instead of non-existent view
        return redirect()->route('shift-schedule-management');
    }

    /**
     * Update the specified shift
     */
    public function update(Request $request, Shift $shift)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'shift_type_id' => 'nullable|exists:shift_types,id',
            'shift_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:255',
            'break_duration' => 'nullable|integer|min:0|max:480',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:scheduled,confirmed,completed,cancelled,no_show'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Check for conflicts (excluding current shift)
            $conflict = Shift::where('employee_id', $request->employee_id)
                ->where('shift_date', $request->shift_date)
                ->where('start_time', $request->start_time)
                ->where('status', '!=', 'cancelled')
                ->where('id', '!=', $shift->id)
                ->first();

            if ($conflict) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Employee already has a shift scheduled at this time.');
            }

            $shift->update($request->all());

            Log::info('Shift updated successfully: ' . $shift->id);
            return redirect()->route('shift-schedule-management')
                ->with('success', 'Shift updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating shift: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating shift: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified shift
     */
    public function destroy(Shift $shift)
    {
        try {
            $shiftInfo = "Shift for {$shift->employee->full_name} on {$shift->shift_date}";
            $shift->delete();

            Log::info('Shift deleted successfully: ' . $shiftInfo);
            return redirect()->route('shift-schedule-management')
                ->with('success', 'Shift deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting shift: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting shift: ' . $e->getMessage());
        }
    }

    /**
     * Get shifts for calendar view
     */
    public function getCalendarShifts(Request $request)
    {
        try {
            $start = $request->get('start', Carbon::now()->startOfMonth());
            $end = $request->get('end', Carbon::now()->endOfMonth());

            $shifts = Shift::with(['employee', 'shiftType'])
                ->whereBetween('shift_date', [$start, $end])
                ->where('status', '!=', 'cancelled')
                ->get()
                ->map(function ($shift) {
                    return [
                        'id' => $shift->id,
                        'title' => $shift->employee->full_name . ' - ' . ($shift->shiftType->name ?? 'Shift'),
                        'start' => $shift->shift_date . 'T' . $shift->start_time,
                        'end' => $shift->shift_date . 'T' . $shift->end_time,
                        'backgroundColor' => $shift->shiftType->color_code ?? '#007bff',
                        'borderColor' => $shift->shiftType->color_code ?? '#007bff',
                        'extendedProps' => [
                            'employee' => $shift->employee->full_name,
                            'shift_type' => $shift->shiftType->name ?? 'N/A',
                            'location' => $shift->location,
                            'status' => $shift->status,
                            'notes' => $shift->notes
                        ]
                    ];
                });

            return response()->json($shifts);

        } catch (\Exception $e) {
            Log::error('Error getting calendar shifts: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading calendar data'], 500);
        }
    }

    /**
     * Get shift statistics
     */
    public function getStats()
    {
        try {
            $today = Carbon::today();
            $thisWeek = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            
            return [
                'today' => Shift::whereDate('shift_date', $today)->count(),
                'this_week' => Shift::whereBetween('shift_date', $thisWeek)->count(),
                'scheduled' => Shift::where('status', 'scheduled')->count(),
                'completed' => Shift::where('status', 'completed')->count(),
                'cancelled' => Shift::where('status', 'cancelled')->count()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting shift stats: ' . $e->getMessage());
            return [
                'today' => 0,
                'this_week' => 0,
                'scheduled' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
        }
    }
}
