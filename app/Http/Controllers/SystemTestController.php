<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Attendance;
use App\Traits\DatabaseConnectionTrait;
use App\Models\AIGeneratedTimesheet;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\HRDashboardController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\ShiftRequestController;
use ReflectionMethod;
use Exception;
use PDO;

class SystemTestController extends Controller
{
    use DatabaseConnectionTrait;
    /**
     * Test claims functionality
     */
    public function testClaims()
    {
        try {
            $controller = new ClaimController();
            $response = $controller->index();
            
            if ($response instanceof \Illuminate\View\View) {
                $data = $response->getData();
                
                $result = [
                    'success' => true,
                    'data_keys' => array_keys($data),
                    'claim_types_count' => $data['claimTypes']->count(),
                    'employees_count' => $data['employees']->count(),
                    'claims_count' => $data['claims']->count(),
                    'statistics' => [
                        'totalClaims' => $data['totalClaims'],
                        'pendingClaims' => $data['pendingClaims'],
                        'approvedClaims' => $data['approvedClaims'],
                        'totalAmount' => $data['totalAmount']
                    ]
                ];
                
                // Add sample data for debugging
                if ($data['claimTypes']->count() > 0) {
                    $result['first_claim_type'] = $data['claimTypes']->first();
                }
                if ($data['claims']->count() > 0) {
                    $result['first_claim'] = $data['claims']->first();
                }
                if ($data['employees']->count() > 0) {
                    $result['first_employee'] = $data['employees']->first();
                }
                
                return response()->json($result);
            }
            
            return response()->json(['error' => 'Invalid response type']);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Test database connection with PDO
     */
    public function testDb()
    {
        try {
            $pdo = $this->getPDOConnection();
            
            $result = ['success' => true];
            
            // Test claim_types
            $stmt = $pdo->query("SELECT COUNT(*) FROM claim_types");
            $result['claim_types_total'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM claim_types WHERE is_active = 1");
            $result['claim_types_active'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT * FROM claim_types LIMIT 3");
            $result['sample_claim_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Test claims
            $stmt = $pdo->query("SELECT COUNT(*) FROM claims");
            $result['claims_total'] = $stmt->fetchColumn();
            
            // Test employees
            $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'");
            $result['employees_active'] = $stmt->fetchColumn();
            
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test shift edit endpoint
     */
    public function testShiftEdit($id)
    {
        try {
            $controller = new ShiftController();
            $response = $controller->editShiftWeb($id);
            
            return [
                'endpoint_test' => '/shifts/' . $id . '/edit',
                'controller_method' => 'editShiftWeb',
                'response_status' => $response->getStatusCode(),
                'response_data' => $response->getData()
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Test shift approval
     */
    public function testShiftApproval($id)
    {
        try {
            $shiftRequest = DB::table('shift_requests')->where('id', $id)->first();
            
            if (!$shiftRequest) {
                return response()->json([
                    'error' => 'Shift request not found'
                ]);
            }
            
            // Simulate approval process
            $controller = new ShiftRequestController();
            $response = $controller->approve($id);
            
            // Check if shift was created
            $createdShift = DB::table('shifts')
                ->where('employee_id', $shiftRequest->employee_id)
                ->where('shift_date', $shiftRequest->shift_date)
                ->where('notes', 'LIKE', '%Auto-created from approved shift request%')
                ->first();
                
            return response()->json([
                'shift_request' => $shiftRequest,
                'created_shift' => $createdShift,
                'approval_successful' => $createdShift ? true : false
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test recent entries from HR Dashboard
     */
    public function testRecentEntries()
    {
        $controller = new HRDashboardController();
        $method = new ReflectionMethod($controller, 'getRecentTimeEntries');
        $method->setAccessible(true);
        $result = $method->invoke($controller);
        
        return [
            'count' => $result->count(),
            'data' => $result->toArray()
        ];
    }

    /**
     * Test employee add functionality
     */
    public function testEmployeeAdd()
    {
        try {
            // Test data for employee creation
            $testData = [
                'first_name' => 'Test',
                'last_name' => 'Employee',
                'email' => 'test.employee' . time() . '@jetlouge.com',
                'phone' => '+63 912 345 6799',
                'position' => 'Test Position',
                'department' => 'Information Technology',
                'hire_date' => date('Y-m-d'),
                'salary' => 50000.00,
                'status' => 'active'
            ];
            
            $employeeId = DB::table('employees')->insertGetId(array_merge($testData, [
                'online_status' => 'offline',
                'created_at' => now(),
                'updated_at' => now()
            ]));
            
            return response()->json([
                'success' => true,
                'message' => 'Test employee created successfully',
                'employee_id' => $employeeId,
                'test_data' => $testData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test attendance creation
     */
    public function testAttendance()
    {
        try {
            // Test creating attendance record directly
            $employee = DB::table('employees')->first();
            
            if (!$employee) {
                return "No employees found";
            }
            
            $today = Carbon::today();
            $clockInTime = Carbon::now('Asia/Manila');
            
            // Try using Attendance model
            $attendance = new Attendance();
            $attendance->employee_id = $employee->id;
            $attendance->date = $today;
            $attendance->clock_in_time = $clockInTime;
            $attendance->status = 'present';
            $attendance->location = 'ESS Portal';
            $attendance->ip_address = '127.0.0.1';
            
            $attendance->save();
            
            return "Attendance created successfully! ID: " . $attendance->id;
            
        } catch (Exception $e) {
            return "Error: " . $e->getMessage() . "<br>Line: " . $e->getLine() . "<br>File: " . $e->getFile();
        }
    }

    /**
     * Test AI timesheet functionality
     */
    public function testAiTimesheet()
    {
        return '<h1>🤖 AI Timesheet System Ready!</h1><p><a href="/timesheet-management">Go to Timesheet Management</a></p>';
    }

    /**
     * Test AI generation for specific employee
     */
    public function testAiGeneration($employeeId)
    {
        try {
            $employee = Employee::find($employeeId);
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }
            
            $aiTimesheet = AIGeneratedTimesheet::generateForEmployee($employeeId);
            
            return response()->json([
                'success' => true,
                'employee' => $employee->first_name . ' ' . $employee->last_name,
                'timesheet_id' => $aiTimesheet->id,
                'weekly_data' => $aiTimesheet->weekly_data,
                'total_hours' => $aiTimesheet->total_hours,
                'overtime_hours' => $aiTimesheet->overtime_hours
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Test AI controller method
     */
    public function testAi($employeeId)
    {
        try {
            $controller = new TimesheetController();
            $request = new Request();
            $response = $controller->generateAITimesheet($request, $employeeId);
            return $response;
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Test save functionality
     */
    public function testSave(Request $request)
    {
        try {
            \Log::info('Test Save Route - Request received', $request->all());
            
            $controller = new TimesheetController();
            $response = $controller->saveAITimesheet($request);
            
            \Log::info('Test Save Route - Response', ['response' => $response->getContent()]);
            
            return $response;
        } catch (Exception $e) {
            \Log::error('Test Save Route - Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }
}
