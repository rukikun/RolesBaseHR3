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

            // Get employees
            $employees = collect();
            try {
                $employees = Employee::select('id', 'first_name', 'last_name', 'employee_id')->get();
            } catch (\Exception $e) {
                Log::info('No employees table or data');
            }

            // Get shifts for current month
            $shifts = collect();
            $currentDate = Carbon::now();
            $calendarData = $this->getCalendarShifts($currentDate);

            // Get shift requests
            $shiftRequests = collect();
            try {
                $shiftRequests = $this->getShiftRequestsFallback();
            } catch (\Exception $e) {
                Log::info('No shift requests data');
            }

            // Calculate statistics
            $stats = [
                'total_shift_types' => $shiftTypes->count(),
                'active_shifts' => $shifts->count(),
                'pending_requests' => $shiftRequests->where('status', 'pending')->count(),
                'total_employees' => $employees->count()
            ];

            return view('shift_schedule_management', compact(
                'shiftTypes', 
                'employees', 
                'shifts', 
                'shiftRequests', 
                'calendarData', 
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
                    (name, code, type, start_time, end_time, duration_hours, break_duration_minutes, color_code, hourly_rate, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $type['name'], $type['code'], $type['type'], $type['start_time'], 
                    $type['end_time'], $type['duration_hours'], $type['break_duration_minutes'],
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
                INSERT INTO shift_types (name, code, start_time, end_time, duration_hours, break_duration_minutes, color_code, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");
            
            $stmt->execute([
                $request->name,
                strtoupper($request->code),
                $request->start_time,
                $request->end_time,
                $request->duration_hours,
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
                SET name = ?, code = ?, start_time = ?, end_time = ?, duration_hours = ?, break_duration_minutes = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            
            $stmt->execute([
                $request->name,
                strtoupper($request->code),
                $request->start_time,
                $request->end_time,
                $request->duration_hours,
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
}
