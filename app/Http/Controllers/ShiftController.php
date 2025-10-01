<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\ShiftType;
use App\Models\Shift;
use App\Models\Employee;
use App\Models\ShiftRequest;

class ShiftController extends Controller
{
    public function index()
    {
        try {
            // Get shift types with fallback
            $shiftTypes = collect();
            try {
                $shiftTypes = ShiftType::where('is_active', true)->orderBy('name')->get();
            } catch (\Exception $e) {
                Log::info('Using fallback query for shift types');
                $shiftTypes = $this->getShiftTypesFallback();
            }

            // Create sample data if none exists
            if ($shiftTypes->isEmpty()) {
                $this->createSampleShiftTypes();
                $shiftTypes = $this->getShiftTypesFallback();
            }

            // Get employees with guaranteed fallback
            $employees = collect();
            
            // Always try PDO first for reliability
            try {
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name");
                $employeeData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                $employees = collect($employeeData);
                Log::info('Loaded ' . $employees->count() . ' employees via PDO');
                
                // If no employees found, create sample ones
                if ($employees->count() == 0) {
                    Log::info('No employees found, creating sample employees...');
                    
                    $sampleEmployees = [
                        ['John', 'Doe'],
                        ['Jane', 'Smith'], 
                        ['Mike', 'Johnson'],
                        ['Sarah', 'Wilson'],
                        ['David', 'Brown']
                    ];
                    
                    foreach ($sampleEmployees as $emp) {
                        $pdo->exec("INSERT IGNORE INTO employees (first_name, last_name, status, hire_date, created_at, updated_at) VALUES ('{$emp[0]}', '{$emp[1]}', 'active', CURDATE(), NOW(), NOW())");
                    }
                    
                    // Re-query after creating samples
                    $stmt = $pdo->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name");
                    $employeeData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $employees = collect($employeeData);
                    Log::info('After creating samples: ' . $employees->count() . ' employees');
                }
                
            } catch (\Exception $e) {
                Log::error('PDO employee query failed: ' . $e->getMessage());
                
                // Last resort: create static collection
                $employees = collect([
                    (object)['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                    (object)['id' => 2, 'first_name' => 'Jane', 'last_name' => 'Smith'],
                    (object)['id' => 3, 'first_name' => 'Mike', 'last_name' => 'Johnson']
                ]);
                Log::info('Using static employee collection: ' . $employees->count() . ' employees');
            }

            // Get current date and handle month parameter
            $requestedMonth = request('month');
            if ($requestedMonth && preg_match('/^\d{4}-\d{2}$/', $requestedMonth)) {
                try {
                    $currentDate = Carbon::createFromFormat('Y-m', $requestedMonth)->startOfMonth();
                } catch (\Exception $e) {
                    $currentDate = Carbon::now()->startOfMonth();
                }
            } else {
                $currentDate = Carbon::now()->startOfMonth();
            }
            
            $shifts = collect();
            $calendarShifts = [];
            
            try {
                // Get shifts with relationships for the specified month
                $shifts = Shift::with(['employee', 'shiftType'])
                    ->whereMonth('shift_date', $currentDate->month)
                    ->whereYear('shift_date', $currentDate->year)
                    ->orderBy('shift_date')
                    ->orderBy('start_time')
                    ->get();
                
                // Group shifts by date for calendar display
                $calendarShifts = $shifts->groupBy(function ($shift) {
                    return $shift->shift_date;
                })->map(function ($dayShifts) {
                    return $dayShifts->map(function ($shift) {
                        return [
                            'id' => $shift->id,
                            'employee_id' => $shift->employee_id,
                            'shift_type_id' => $shift->shift_type_id,
                            'employee_name' => $shift->employee ? $shift->employee->first_name . ' ' . $shift->employee->last_name : 'Unknown',
                            'employee_initials' => $shift->employee ? substr($shift->employee->first_name, 0, 1) . substr($shift->employee->last_name, 0, 1) : 'UN',
                            'shift_type' => $shift->shiftType ? $shift->shiftType->name : 'Unknown',
                            'start_time' => $shift->start_time,
                            'end_time' => $shift->end_time,
                            'location' => $shift->location ?? 'Main Office',
                            'notes' => $shift->notes,
                            'status' => $shift->status,
                            'shift_date' => $shift->shift_date
                        ];
                    })->toArray();
                })->toArray();
                
                \Log::info('Loaded ' . $shifts->count() . ' shifts for calendar display');
                
            } catch (\Exception $e) {
                \Log::warning('Failed to load shifts with Eloquent: ' . $e->getMessage());
                $calendarShifts = $this->getCalendarShifts($currentDate);
            }

            // Get shift requests using the controller method
            $shiftRequestController = new \App\Http\Controllers\ShiftRequestController();
            $shiftRequests = $shiftRequestController->getShiftRequestsWithRelations();

            // Calculate statistics
            $stats = [
                'total_shift_types' => $shiftTypes->count(),
                'active_shifts' => $shifts->count(),
                'pending_requests' => $shiftRequests->where('status', 'pending')->count(),
                'total_employees' => $employees->count()
            ];

            // Add display month for calendar header
            $displayMonth = $currentDate->copy();
            
            // Debug logging
            \Log::info('=== SHIFT SCHEDULE VIEW DATA ===');
            \Log::info('Shift Types count: ' . $shiftTypes->count());
            \Log::info('Employees count: ' . $employees->count());
            \Log::info('Shifts count: ' . $shifts->count());
            \Log::info('Shift Requests count: ' . $shiftRequests->count());
            
            if ($employees->count() > 0) {
                \Log::info('First employee: ' . json_encode($employees->first()));
            }
            
            return view('shift_schedule_management', compact(
                'shiftTypes', 
                'employees', 
                'shifts', 
                'shiftRequests', 
                'calendarShifts', 
                'displayMonth',
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Error in ShiftController index: ' . $e->getMessage());
            
            // Return view with empty data
            return view('shift_schedule_management', [
                'shiftTypes' => collect(),
                'employees' => collect(),
                'shifts' => collect(),
                'shiftRequests' => collect(),
                'calendarData' => [],
                'stats' => [
                    'total_shift_types' => 0,
                    'active_shifts' => 0,
                    'pending_requests' => 0,
                    'total_employees' => 0
                ]
            ]);
        }
    }

    private function getShiftTypesFallback()
    {
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->query("SELECT * FROM shift_types WHERE is_active = 1 ORDER BY name");
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return collect($results);
        } catch (\Exception $e) {
            return collect();
        }
    }

    private function getShiftRequestsFallback()
    {
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->query("
                SELECT sr.*, e.first_name, e.last_name 
                FROM shift_requests sr 
                LEFT JOIN employees e ON sr.employee_id = e.id 
                ORDER BY sr.created_at DESC
            ");
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return collect($results);
        } catch (\Exception $e) {
            return collect();
        }
    }

    private function createSampleShiftTypes()
    {
        $sampleTypes = [
            [
                'name' => 'Morning Shift',
                'code' => 'MORNING',
                'type' => 'regular',
                'start_time' => '08:00',
                'end_time' => '16:00',
                'duration_hours' => 8,
                'break_duration_minutes' => 60,
                'color_code' => '#28a745',
                'hourly_rate' => 15.00,
                'is_active' => true
            ],
            [
                'name' => 'Evening Shift',
                'code' => 'EVENING',
                'type' => 'regular',
                'start_time' => '16:00',
                'end_time' => '00:00',
                'duration_hours' => 8,
                'break_duration_minutes' => 60,
                'color_code' => '#ffc107',
                'hourly_rate' => 16.00,
                'is_active' => true
            ],
            [
                'name' => 'Night Shift',
                'code' => 'NIGHT',
                'type' => 'night',
                'start_time' => '00:00',
                'end_time' => '08:00',
                'duration_hours' => 8,
                'break_duration_minutes' => 60,
                'color_code' => '#6f42c1',
                'hourly_rate' => 18.00,
                'is_active' => true
            ]
        ];

        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Create table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS shift_types (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    code VARCHAR(10) NOT NULL UNIQUE,
                    type ENUM('regular', 'overtime', 'night', 'weekend', 'holiday') DEFAULT 'regular',
                    start_time TIME NOT NULL,
                    end_time TIME NOT NULL,
                    duration_hours INT NOT NULL,
                    break_duration_minutes INT DEFAULT 0,
                    color_code VARCHAR(7) DEFAULT '#007bff',
                    hourly_rate DECIMAL(8,2),
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");

            foreach ($sampleTypes as $type) {
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO shift_types 
                    (name, code, type, default_start_time, default_end_time, break_duration, color_code, hourly_rate, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $type['name'], $type['code'], $type['type'], $type['start_time'], 
                    $type['end_time'], $type['break_duration_minutes'],
                    $type['color_code'], $type['hourly_rate'], $type['is_active']
                ]);
            }
            
            Log::info('Created sample shift types');
        } catch (\Exception $e) {
            Log::error('Error creating sample shift types: ' . $e->getMessage());
        }
    }

    private function getCalendarShifts($date)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        try {
            $shifts = Shift::with(['employee', 'shiftType'])
                ->whereBetween('shift_date', [$startOfMonth, $endOfMonth])
                ->get();
                
            $calendarData = [];
            foreach ($shifts as $shift) {
                $dateKey = $shift->shift_date->format('Y-m-d');
                if (!isset($calendarData[$dateKey])) {
                    $calendarData[$dateKey] = [];
                }
                $calendarData[$dateKey][] = [
                    'id' => $shift->id,
                    'employee_name' => $shift->employee->first_name . ' ' . $shift->employee->last_name,
                    'shift_type' => $shift->shiftType->name,
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                    'color' => $shift->shiftType->color_code ?? '#007bff'
                ];
            }
            
            return $calendarData;
        } catch (\Exception $e) {
            return [];
        }
    }

    // Store shift type via web form
    public function storeShiftTypeWeb(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'duration_hours' => 'required|integer|min:1|max:24',
            'break_duration_minutes' => 'required|integer|min:0|max:480'
        ]);

        if ($validator->fails()) {
            return redirect()->route('shift-schedule-management')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("
                INSERT INTO shift_types (name, code, default_start_time, default_end_time, break_duration, color_code, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            
            $stmt->execute([
                $request->name,
                strtoupper($request->code),
                $request->start_time,
                $request->end_time,
                $request->break_duration_minutes,
                $request->color_code ?? '#007bff'
            ]);
            
            return redirect()->route('shift-schedule-management')
                ->with('success', 'Shift type created successfully!');
                
        } catch (\Exception $e) {
            return redirect()->route('shift-schedule-management')
                ->with('error', 'Error creating shift type: ' . $e->getMessage());
        }
    }

    // Update shift type via web form
    public function updateShiftTypeWeb(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'duration_hours' => 'required|integer|min:1|max:24',
            'break_duration_minutes' => 'required|integer|min:0|max:480'
        ]);

        if ($validator->fails()) {
            return redirect()->route('shift-schedule-management')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("
                UPDATE shift_types 
                SET name = ?, code = ?, default_start_time = ?, default_end_time = ?, break_duration = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            
            $stmt->execute([
                $request->name,
                strtoupper($request->code),
                $request->start_time,
                $request->end_time,
                $request->break_duration_minutes,
                $id
            ]);
            
            return redirect()->route('shift-schedule-management')
                ->with('success', 'Shift type updated successfully!');
                
        } catch (\Exception $e) {
            return redirect()->route('shift-schedule-management')
                ->with('error', 'Error updating shift type: ' . $e->getMessage());
        }
    }

    // Delete shift type via web form
    public function deleteShiftTypeWeb($id)
    {
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("UPDATE shift_types SET is_active = 0, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            
            return redirect()->route('shift-schedule-management')
                ->with('success', 'Shift type deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('shift-schedule-management')
                ->with('error', 'Error deleting shift type: ' . $e->getMessage());
        }
    }

    // Add the missing destroyShiftTypeWeb method
    public function destroyShiftTypeWeb($id)
    {
        return $this->deleteShiftTypeWeb($id);
    }

    // Store shift assignment via web form
    public function storeShiftWeb(Request $request)
    {
        \Log::info('=== SHIFT ASSIGNMENT SUBMISSION ===');
        \Log::info('Request data: ' . json_encode($request->all()));
        
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'shift_type_id' => 'required|integer',
            'shift_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            \Log::error('Shift assignment validation failed: ' . json_encode($validator->errors()));
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            \Log::info('Attempting to create shift with data: ' . json_encode([
                'employee_id' => $request->employee_id,
                'shift_type_id' => $request->shift_type_id,
                'shift_date' => $request->shift_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location ?? 'Main Office',
                'notes' => $request->notes
            ]));

            // Try Eloquent first
            try {
                $shift = Shift::create([
                    'employee_id' => $request->employee_id,
                    'shift_type_id' => $request->shift_type_id,
                    'shift_date' => $request->shift_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'location' => $request->location ?? 'Main Office',
                    'notes' => $request->notes,
                    'status' => 'scheduled'
                ]);

                \Log::info('Shift created successfully with Eloquent, ID: ' . $shift->id);
                
            } catch (\Exception $eloquentError) {
                \Log::warning('Eloquent shift creation failed: ' . $eloquentError->getMessage());
                
                // Fallback to direct PDO insertion
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    INSERT INTO shifts (employee_id, shift_type_id, shift_date, start_time, end_time, location, notes, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW(), NOW())
                ");
                
                $stmt->execute([
                    $request->employee_id,
                    $request->shift_type_id,
                    $request->shift_date,
                    $request->start_time,
                    $request->end_time,
                    $request->location ?? 'Main Office',
                    $request->notes
                ]);
                
                $shiftId = $pdo->lastInsertId();
                \Log::info('Shift created successfully with PDO, ID: ' . $shiftId);
            }

            return redirect()->route('shift-schedule-management')
                ->with('success', 'Employee assigned to shift successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to create shift: ' . $e->getMessage());
            \Log::error('Full error details: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to assign shift: ' . $e->getMessage());
        }
    }

    // Delete shift via web form
    public function destroyShiftWeb($id)
    {
        \Log::info('=== SHIFT DELETION REQUEST ===');
        \Log::info('Shift ID to delete: ' . $id);
        
        // Validate shift ID
        if (!$id || !is_numeric($id) || $id <= 0) {
            \Log::error('Invalid shift ID provided: ' . $id);
            return redirect()->route('shift-schedule-management')
                ->with('error', 'Invalid shift ID provided.');
        }
        
        try {
            // Try Eloquent first
            try {
                $shift = Shift::findOrFail($id);
                $employeeName = $shift->employee ? $shift->employee->first_name . ' ' . $shift->employee->last_name : 'Unknown';
                $shiftDate = $shift->shift_date;
                
                $shift->delete();
                \Log::info('Shift deleted successfully via Eloquent');
                
                return redirect()->route('shift-schedule-management')
                    ->with('success', "Shift for $employeeName on $shiftDate has been deleted successfully!");
                    
            } catch (\Exception $eloquentError) {
                \Log::warning('Eloquent shift deletion failed: ' . $eloquentError->getMessage());
                
                // Fallback to direct PDO deletion
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                // Get shift info before deletion
                $stmt = $pdo->prepare("
                    SELECT s.shift_date, CONCAT(e.first_name, ' ', e.last_name) as employee_name
                    FROM shifts s
                    LEFT JOIN employees e ON s.employee_id = e.id
                    WHERE s.id = ?
                ");
                $stmt->execute([$id]);
                $shiftInfo = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                // Delete the shift
                $stmt = $pdo->prepare("DELETE FROM shifts WHERE id = ?");
                $stmt->execute([$id]);
                
                if ($stmt->rowCount() > 0) {
                    \Log::info('Shift deleted successfully via PDO');
                    $employeeName = $shiftInfo['employee_name'] ?? 'Unknown';
                    $shiftDate = $shiftInfo['shift_date'] ?? 'Unknown date';
                    
                    return redirect()->route('shift-schedule-management')
                        ->with('success', "Shift for $employeeName on $shiftDate has been deleted successfully!");
                } else {
                    return redirect()->route('shift-schedule-management')
                        ->with('error', 'Shift not found or already deleted.');
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to delete shift: ' . $e->getMessage());
            return redirect()->route('shift-schedule-management')
                ->with('error', 'Failed to delete shift: ' . $e->getMessage());
        }
    }

    // Store shift request via web form
    public function storeShiftRequestWeb(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'shift_type_id' => 'required|integer',
            'shift_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'hours' => 'required|numeric|min:0.5|max:24',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->route('shift-schedule-management')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("INSERT INTO shift_requests (employee_id, shift_type_id, shift_date, start_time, end_time, hours, location, notes, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())");
            
            $stmt->execute([
                $request->employee_id,
                $request->shift_type_id,
                $request->shift_date,
                $request->start_time,
                $request->end_time,
                $request->hours,
                $request->location,
                $request->notes
            ]);

            return redirect()->route('shift-schedule-management')
                ->with('success', 'Shift request submitted successfully!');
                
        } catch (\Exception $e) {
            return redirect()->route('shift-schedule-management')
                ->with('error', 'Error submitting shift request: ' . $e->getMessage());
        }
    }

    // Approve shift request
    public function approveShiftRequest($id)
    {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("UPDATE shift_requests SET status = 'approved', approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?");
            $stmt->execute([1, $id]); // Default approver ID

            return redirect()->route('shift-schedule-management')
                ->with('success', 'Shift request approved successfully!');
        } catch (\Exception $e) {
            return redirect()->route('shift-schedule-management')
                ->with('error', 'Error approving shift request: ' . $e->getMessage());
        }
    }

    // Reject shift request
    public function rejectShiftRequest($id)
    {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("UPDATE shift_requests SET status = 'rejected', approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?");
            $stmt->execute([1, $id]); // Default approver ID

            return redirect()->route('shift-schedule-management')
                ->with('success', 'Shift request rejected successfully!');
        } catch (\Exception $e) {
            return redirect()->route('shift-schedule-management')
                ->with('error', 'Error rejecting shift request: ' . $e->getMessage());
        }
    }

    /**
     * Show shift details for admin
     */
    public function show($id)
    {
        try {
            $shift = DB::table('shifts as s')
                ->leftJoin('employees as e', 's.employee_id', '=', 'e.id')
                ->leftJoin('shift_types as st', 's.shift_type_id', '=', 'st.id')
                ->select(
                    's.*',
                    DB::raw("CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name"),
                    'st.name as shift_type_name'
                )
                ->where('s.id', $id)
                ->first();

            if (!$shift) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'shift' => $shift
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ShiftController@show: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shift: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shift for editing
     */
    public function edit($id)
    {
        try {
            $shift = DB::table('shifts as s')
                ->leftJoin('employees as e', 's.employee_id', '=', 'e.id')
                ->leftJoin('shift_types as st', 's.shift_type_id', '=', 'st.id')
                ->select(
                    's.*',
                    DB::raw("CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name"),
                    'st.name as shift_type_name'
                )
                ->where('s.id', $id)
                ->first();

            if (!$shift) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'shift' => $shift
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ShiftController@edit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shift: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update shift
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'shift_date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'status' => 'required|in:scheduled,in_progress,completed,cancelled',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $shift = DB::table('shifts')->where('id', $id)->first();
            
            if (!$shift) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift not found'
                ], 404);
            }

            $updateData = [
                'shift_date' => $request->shift_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => $request->status,
                'location' => $request->location,
                'notes' => $request->notes,
                'updated_at' => now()
            ];

            DB::table('shifts')->where('id', $id)->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Shift updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ShiftController@update: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shift: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new shift assignment
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|integer',
                'shift_date' => 'required|date',
                'shift_type_id' => 'required|integer',
                'start_time' => 'required',
                'end_time' => 'required',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if employee already has a shift on this date
            $existingShift = DB::table('shifts')
                ->where('employee_id', $request->employee_id)
                ->where('shift_date', $request->shift_date)
                ->first();

            if ($existingShift) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee already has a shift assigned for this date'
                ], 400);
            }

            $shiftId = DB::table('shifts')->insertGetId([
                'employee_id' => $request->employee_id,
                'shift_type_id' => $request->shift_type_id,
                'shift_date' => $request->shift_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location ?? 'Main Office',
                'notes' => $request->notes,
                'status' => 'scheduled',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shift assigned successfully',
                'shift_id' => $shiftId
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ShiftController@store: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign shift: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Edit shift for web interface
     */
    public function editShiftWeb($id)
    {
        try {
            $shift = DB::table('shifts as s')
                ->leftJoin('employees as e', 's.employee_id', '=', 'e.id')
                ->leftJoin('shift_types as st', 's.shift_type_id', '=', 'st.id')
                ->select(
                    's.*',
                    DB::raw("CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name"),
                    'st.name as shift_type_name'
                )
                ->where('s.id', $id)
                ->first();

            if (!$shift) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'shift' => $shift
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ShiftController@editShiftWeb: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shift: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update shift for web interface
     */
    public function updateShiftWeb(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'shift_date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'status' => 'required|in:scheduled,in_progress,completed,cancelled',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = DB::table('shifts')
                ->where('id', $id)
                ->update([
                    'shift_date' => $request->shift_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'status' => $request->status,
                    'location' => $request->location,
                    'notes' => $request->notes,
                    'updated_at' => now()
                ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shift updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update shift'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Error in ShiftController@updateShiftWeb: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shift: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View shift for web interface
     */
    public function viewShiftWeb($id)
    {
        try {
            $shift = DB::table('shifts as s')
                ->leftJoin('employees as e', 's.employee_id', '=', 'e.id')
                ->leftJoin('shift_types as st', 's.shift_type_id', '=', 'st.id')
                ->select(
                    's.*',
                    DB::raw("CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name"),
                    'st.name as shift_type_name'
                )
                ->where('s.id', $id)
                ->first();

            if (!$shift) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'shift' => $shift
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ShiftController@viewShiftWeb: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shift: ' . $e->getMessage()
            ], 500);
        }
    }


}
