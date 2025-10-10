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
use App\Traits\DatabaseConnectionTrait;
use Illuminate\Support\Facades\Hash;

class ShiftController extends Controller
{
    use DatabaseConnectionTrait;
    public function index()
    {
        try {
            // Get shift types with fallback
            $shiftTypes = collect();
            try {
                // Get shift types with custom ordering (Morning first, then Afternoon)
                $shiftTypes = ShiftType::where('is_active', true)
                    ->orderByRaw("CASE 
                        WHEN LOWER(name) LIKE '%morning%' THEN 1
                        WHEN LOWER(name) LIKE '%afternoon%' THEN 2
                        WHEN LOWER(name) LIKE '%evening%' THEN 3
                        WHEN LOWER(name) LIKE '%night%' THEN 4
                        ELSE 5
                    END")
                    ->orderBy('name')
                    ->get();
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
            
            // Get employees - try Eloquent first, then PDO fallback
            try {
                $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
                Log::info('Loaded ' . $employees->count() . ' employees via Eloquent');
            } catch (\Exception $e) {
                Log::info('Eloquent failed, trying PDO for employees');
                try {
                    $pdo = $this->getPDOConnection();
                    
                    $stmt = $pdo->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name");
                    $employeeData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $employees = collect($employeeData);
                    Log::info('Loaded ' . $employees->count() . ' employees via PDO');
                } catch (\Exception $e2) {
                    Log::error('Both Eloquent and PDO failed for employees: ' . $e2->getMessage());
                    $employees = collect();
                }
            }
            
            // Only create sample employees if absolutely none exist
            if ($employees->isEmpty()) {
                Log::info('No employees found, creating minimal sample data...');
                try {
                    $pdo = $this->getPDOConnection();
                    
                    $sampleEmployees = [
                        ['John', 'Doe'],
                        ['Jane', 'Smith'], 
                        ['Mike', 'Johnson']
                    ];
                    
                    foreach ($sampleEmployees as $emp) {
                        $pdo->exec("INSERT IGNORE INTO employees (first_name, last_name, status, hire_date, created_at, updated_at) VALUES ('{$emp[0]}', '{$emp[1]}', 'active', CURDATE(), NOW(), NOW())");
                    }
                    
                    // Re-query after creating samples
                    $stmt = $pdo->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name");
                    $employeeData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $employees = collect($employeeData);
                    Log::info('Created and loaded ' . $employees->count() . ' sample employees');
                } catch (\Exception $e) {
                    Log::error('Failed to create sample employees: ' . $e->getMessage());
                    $employees = collect();
                }
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
                // Get shifts with relationships for the specified month - ensure no duplicates
                $shifts = Shift::with(['employee', 'shiftType'])
                    ->whereMonth('shift_date', $currentDate->month)
                    ->whereYear('shift_date', $currentDate->year)
                    ->where('id', '>', 0) // Exclude any invalid IDs
                    ->orderBy('shift_date')
                    ->orderBy('start_time')
                    ->get();
                
                \Log::info('Raw shifts loaded: ' . $shifts->count());
                
                // Group shifts by date for calendar display - prevent duplicates
                $calendarShifts = [];
                foreach ($shifts as $shift) {
                    $dateKey = $shift->shift_date instanceof Carbon ? $shift->shift_date->format('Y-m-d') : $shift->shift_date;
                    
                    if (!isset($calendarShifts[$dateKey])) {
                        $calendarShifts[$dateKey] = [];
                    }
                    
                    // Check for duplicate shift on same date for same employee
                    $isDuplicate = false;
                    foreach ($calendarShifts[$dateKey] as $existingShift) {
                        if ($existingShift['employee_id'] == $shift->employee_id && 
                            $existingShift['shift_type_id'] == $shift->shift_type_id &&
                            $existingShift['start_time'] == $shift->start_time) {
                            $isDuplicate = true;
                            break;
                        }
                    }
                    
                    if (!$isDuplicate) {
                        $calendarShifts[$dateKey][] = [
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
                            'shift_date' => $dateKey
                        ];
                    }
                }
                
                \Log::info('Calendar shifts processed: ' . array_sum(array_map('count', $calendarShifts)) . ' total shifts across ' . count($calendarShifts) . ' days');
                
            } catch (\Exception $e) {
                \Log::warning('Failed to load shifts with Eloquent: ' . $e->getMessage());
                $calendarShifts = $this->getCalendarShiftsFallback($currentDate);
            }

            // Get shift requests with fallback
            $shiftRequests = collect();
            try {
                $shiftRequestController = new \App\Http\Controllers\ShiftRequestController();
                $shiftRequests = $shiftRequestController->getShiftRequestsWithRelations();
                \Log::info('Loaded shift requests via controller: ' . $shiftRequests->count());
            } catch (\Exception $e) {
                \Log::warning('Failed to load shift requests with Eloquent: ' . $e->getMessage());
                $shiftRequests = $this->getShiftRequestsFallback();
                \Log::info('Loaded shift requests via fallback: ' . $shiftRequests->count());
            }
            
            // No automatic sample data creation for shift requests
            // $shiftRequests will remain empty if no data exists in database

            // Calculate statistics
            $weeklyHours = 0;
            try {
                // Calculate weekly hours from current week shifts in calendar data
                $startOfWeek = Carbon::now()->startOfWeek();
                $endOfWeek = Carbon::now()->endOfWeek();
                
                foreach ($calendarShifts as $date => $dayShifts) {
                    $shiftDate = Carbon::parse($date);
                    if ($shiftDate->between($startOfWeek, $endOfWeek)) {
                        foreach ($dayShifts as $shift) {
                            if (isset($shift['start_time']) && isset($shift['end_time'])) {
                                $start = Carbon::parse($shift['start_time']);
                                $end = Carbon::parse($shift['end_time']);
                                $weeklyHours += $end->diffInHours($start);
                            } else {
                                $weeklyHours += 8; // Default 8 hours
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $weeklyHours = 40; // Default fallback
            }
            
            $stats = [
                'total_shift_types' => $shiftTypes->count(),
                'active_shifts' => is_object($shifts) ? $shifts->count() : array_sum(array_map('count', $calendarShifts)),
                'pending_requests' => $shiftRequests->where('status', 'pending')->count(),
                'total_employees' => $employees->count(),
                'weekly_hours' => $weeklyHours
            ];

            // Add display month for calendar header
            $displayMonth = $currentDate->copy();
            
            // Debug logging
            \Log::info('=== SHIFT SCHEDULE VIEW DATA ===');
            \Log::info('Shift Types count: ' . $shiftTypes->count());
            \Log::info('Employees count: ' . $employees->count());
            \Log::info('Calendar Shifts count: ' . array_sum(array_map('count', $calendarShifts)));
            \Log::info('Shift Requests count: ' . $shiftRequests->count());
            
            if ($employees->count() > 0) {
                \Log::info('First employee: ' . json_encode($employees->first()));
            }
            
            // Convert calendar shifts back to a collection for consistency
            $shiftsCollection = collect();
            foreach ($calendarShifts as $date => $dayShifts) {
                foreach ($dayShifts as $shift) {
                    $shiftsCollection->push((object)$shift);
                }
            }

            return view('shifts.schedule_management', compact(
                'shiftTypes', 
                'employees', 
                'shiftsCollection', 
                'shiftRequests', 
                'calendarShifts', 
                'displayMonth',
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Error in ShiftController index: ' . $e->getMessage());
            
            // Return view with empty data
            return view('shifts.schedule_management', [
                'shiftTypes' => collect(),
                'employees' => collect(),
                'shiftsCollection' => collect(),
                'shiftRequests' => collect(),
                'calendarShifts' => [],
                'displayMonth' => Carbon::now(),
                'stats' => [
                    'total_shift_types' => 0,
                    'active_shifts' => 0,
                    'pending_requests' => 0,
                    'total_employees' => 0,
                    'weekly_hours' => 0
                ]
            ]);
        }
    }

    private function getShiftTypesFallback()
    {
        try {
            $pdo = $this->getPDOConnection();
            
            $stmt = $pdo->query("
                SELECT * FROM shift_types 
                WHERE is_active = 1 
                ORDER BY CASE 
                    WHEN LOWER(name) LIKE '%morning%' THEN 1
                    WHEN LOWER(name) LIKE '%afternoon%' THEN 2
                    WHEN LOWER(name) LIKE '%evening%' THEN 3
                    WHEN LOWER(name) LIKE '%night%' THEN 4
                    ELSE 5
                END, name
            ");
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return collect($results);
        } catch (\Exception $e) {
            return collect();
        }
    }

    private function getShiftRequestsFallback()
    {
        try {
            $pdo = $this->getPDOConnection();
            
            $stmt = $pdo->query("
                SELECT sr.*, 
                       e.first_name, e.last_name,
                       st.name as shift_type_name, st.code as shift_type_code,
                       approver.first_name as approver_first_name, approver.last_name as approver_last_name
                FROM shift_requests sr 
                LEFT JOIN employees e ON sr.employee_id = e.id 
                LEFT JOIN shift_types st ON sr.shift_type_id = st.id
                LEFT JOIN employees approver ON sr.approved_by = approver.id
                ORDER BY sr.created_at DESC
            ");
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Convert to objects with relationships for Blade compatibility
            return collect($results)->map(function ($row) {
                $request = (object) $row;
                
                // Create employee object
                $request->employee = (object) [
                    'id' => $row['employee_id'],
                    'first_name' => $row['first_name'] ?? 'Unknown',
                    'last_name' => $row['last_name'] ?? 'Employee'
                ];
                
                // Create shift type object
                $request->shiftType = (object) [
                    'id' => $row['shift_type_id'],
                    'name' => $row['shift_type_name'] ?? 'Unknown Shift',
                    'code' => $row['shift_type_code'] ?? 'UNK'
                ];
                
                // Create approver object if exists
                if ($row['approved_by']) {
                    $request->approver = (object) [
                        'id' => $row['approved_by'],
                        'first_name' => $row['approver_first_name'] ?? 'Unknown',
                        'last_name' => $row['approver_last_name'] ?? 'Approver'
                    ];
                } else {
                    $request->approver = null;
                }
                
                // Convert dates to Carbon instances for Blade compatibility
                $request->shift_date = $row['shift_date'] ? \Carbon\Carbon::parse($row['shift_date']) : null;
                $request->start_time = $row['start_time'] ? \Carbon\Carbon::parse($row['start_time']) : null;
                $request->end_time = $row['end_time'] ? \Carbon\Carbon::parse($row['end_time']) : null;
                $request->approved_at = $row['approved_at'] ? \Carbon\Carbon::parse($row['approved_at']) : null;
                
                return $request;
            });
        } catch (\Exception $e) {
            Log::error('Shift requests fallback failed: ' . $e->getMessage());
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
            $pdo = $this->getPDOConnection();
            
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
    
    private function createSampleShiftRequests()
    {
        // REMOVED: No longer automatically creates sample shift request data
        // This method is kept for backward compatibility but does not insert sample data
        try {
            $pdo = $this->getPDOConnection();
            
            // Only create the table structure if it doesn't exist, but no sample data
            $stmt = $pdo->query("SHOW TABLES LIKE 'shift_requests'");
            if ($stmt->rowCount() == 0) {
                // Create the table structure only
                $pdo->exec("
                    CREATE TABLE shift_requests (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        employee_id INT NOT NULL,
                        shift_type_id INT NOT NULL,
                        shift_date DATE NOT NULL,
                        start_time TIME NOT NULL,
                        end_time TIME NOT NULL,
                        hours DECIMAL(4,2) DEFAULT 8.00,
                        location VARCHAR(255) DEFAULT 'Main Office',
                        notes TEXT,
                        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                        approved_by INT NULL,
                        approved_at TIMESTAMP NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_employee_id (employee_id),
                        INDEX idx_shift_type_id (shift_type_id),
                        INDEX idx_status (status),
                        INDEX idx_shift_date (shift_date)
                    )
                ");
                Log::info('Created shift_requests table structure (no sample data)');
            }
            
            // REMOVED: No longer inserting sample data automatically
            Log::info('Shift requests table ready (no sample data created)');
        } catch (\Exception $e) {
            Log::error('Error setting up shift requests table: ' . $e->getMessage());
        }
    }

    private function getCalendarShiftsFallback($date)
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
                    'shift_date' => $shift->shift_date,
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
            $pdo = $this->getPDOConnection();
            
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
            $pdo = $this->getPDOConnection();
            
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
            $pdo = $this->getPDOConnection();
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
        \Log::info('=== SHIFT ASSIGNMENT SUBMISSION (storeShiftWeb method called) ===');
        \Log::info('Request data: ' . json_encode($request->all()));
        \Log::info('Start time received: ' . $request->start_time);
        \Log::info('End time received: ' . $request->end_time);
        \Log::info('Request URL: ' . $request->url());
        \Log::info('Request method: ' . $request->method());
        
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
        
        \Log::info('Converted start time: ' . $startTime);
        \Log::info('Converted end time: ' . $endTime);
        
        // Update request with converted times
        $request->merge([
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'shift_type_id' => 'required|integer',
            'shift_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ], [
            'shift_date.date' => 'Please provide a valid date.',
            'start_time.required' => 'The start time is required.',
            'end_time.required' => 'The end time is required.',
        ]);
        
        // Additional validation after time conversion - simplified
        if (!$validator->fails()) {
            // Just ensure we have some time value
            if (empty($startTime) || $startTime === '00:00') {
                $validator->errors()->add('start_time', 'Please provide a valid start time.');
            }
            if (empty($endTime) || $endTime === '00:00') {
                $validator->errors()->add('end_time', 'Please provide a valid end time.');
            }
        }

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
                $pdo = $this->getPDOConnection();
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
                $pdo = $this->getPDOConnection();
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
        \Log::info('=== SHIFT REQUEST SUBMISSION ===');
        \Log::info('Request data: ' . json_encode($request->all()));
        
        // Convert time format if needed
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        
        // Handle potential 12-hour format conversion
        if (strpos($startTime, 'AM') !== false || strpos($startTime, 'PM') !== false) {
            $startTime = date('H:i', strtotime($startTime));
        }
        if (strpos($endTime, 'AM') !== false || strpos($endTime, 'PM') !== false) {
            $endTime = date('H:i', strtotime($endTime));
        }
        
        // Update request with converted times
        $request->merge([
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'shift_type_id' => 'required|integer',
            'shift_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'hours' => 'required|numeric|min:0.5|max:24',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ], [
            'shift_date.date' => 'Please provide a valid date.',
            'start_time.date_format' => 'The start time must be in HH:MM format (e.g., 08:00).',
            'end_time.date_format' => 'The end time must be in HH:MM format (e.g., 16:00).',
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
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'status' => 'required|in:scheduled,in_progress,completed,cancelled',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ], [
                'shift_date.date' => 'Please provide a valid date.',
                'start_time.date_format' => 'The start time must be in HH:MM format (e.g., 08:00).',
                'end_time.date_format' => 'The end time must be in HH:MM format (e.g., 16:00).',
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

    /**
     * Handle HR authentication for protected shift actions
     */
    public function hrAuthentication(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
                'action' => 'required|string|in:edit,delete,create',
                'type' => 'required|string|in:shift',
                'item_id' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data'
                ], 422);
            }

            // Authenticate user
            $employee = Employee::where('email', $request->email)->first();
            
            if (!$employee || !Hash::check($request->password, $employee->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }

            // Check authorization
            $authorizedPositions = ['HR Manager', 'System Administrator', 'HR Scheduler', 'Admin', 'HR Administrator'];
            if (!in_array($employee->position, $authorizedPositions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only HR Manager, System Administrator, HR Scheduler, Admin, or HR Administrator can perform this action.'
                ], 403);
            }

            // Perform action
            $result = $this->performShiftAction($request->action, $request->item_id, $employee->id);
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Shift HR Authentication error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during authentication'
            ], 500);
        }
    }

    private function performShiftAction($action, $shiftId, $authorizedById)
    {
        try {
            switch ($action) {
                case 'edit':
                    // For edit, just return success - the actual edit will be handled by the edit modal
                    return ['success' => true, 'message' => 'Authorization successful. Opening edit form...'];
                    
                case 'delete':
                    DB::delete("DELETE FROM shifts WHERE id = ?", [$shiftId]);
                    return ['success' => true, 'message' => 'Shift deleted successfully'];
                    
                case 'create':
                    // For create, just return success - the actual creation will be handled by the create modal
                    return ['success' => true, 'message' => 'Authorization successful. Opening create form...'];

                default:
                    return ['success' => false, 'message' => 'Unknown action'];
            }
        } catch (\Exception $e) {
            Log::error('Shift action error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while performing the action'];
        }
    }
}
