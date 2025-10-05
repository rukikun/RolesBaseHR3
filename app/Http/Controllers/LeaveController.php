<?php

namespace App\Http\Controllers;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Traits\DatabaseConnectionTrait;

class LeaveController extends Controller
{
    use DatabaseConnectionTrait;

    public function index()
    {
        try {
            // Use Eloquent models with fallback to raw queries
            $leaveTypes = collect([]);
            $employees = collect([]);
            $leaves = collect([]);
            
            try {
                // Try using Eloquent first
                $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
                $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
                $leaves = LeaveRequest::with(['employee', 'leaveType', 'approvedBy'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($leave) {
                        // Add computed properties for blade compatibility
                        $leave->first_name = $leave->employee->first_name ?? 'Employee';
                        $leave->last_name = $leave->employee->last_name ?? 'Unknown';
                        $leave->employee_name = ($leave->employee->first_name ?? 'Employee') . ' ' . ($leave->employee->last_name ?? 'Unknown');
                        $leave->leave_type_name = $leave->leaveType->name ?? 'Unknown Type';
                        $leave->leave_type_code = $leave->leaveType->code ?? 'N/A';
                        return $leave;
                    });
                
                \Log::info('Eloquent - Retrieved ' . $leaveTypes->count() . ' leave types, ' . $leaves->count() . ' leave requests');
            } catch (\Exception $e) {
                \Log::warning('Eloquent failed, falling back to raw queries: ' . $e->getMessage());
                
                // Fallback to raw PDO queries with table creation
                try {
                    $pdo = $this->getPDOConnection();
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    
                    // Auto-create leave_types table if not exists
                    $pdo->exec("CREATE TABLE IF NOT EXISTS leave_types (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        code VARCHAR(10) NOT NULL,
                        description TEXT,
                        max_days_per_year INT DEFAULT 30,
                        carry_forward BOOLEAN DEFAULT FALSE,
                        requires_approval BOOLEAN DEFAULT TRUE,
                        is_active BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_name_code (name, code)
                    )");
                    
                    // Auto-create leave_requests table if not exists
                    $pdo->exec("CREATE TABLE IF NOT EXISTS leave_requests (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        employee_id INT NOT NULL,
                        leave_type_id INT NOT NULL,
                        start_date DATE NOT NULL,
                        end_date DATE NOT NULL,
                        days_requested INT NOT NULL,
                        reason TEXT NOT NULL,
                        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                        approved_by INT NULL,
                        approved_at TIMESTAMP NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
                        FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
                        FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL
                    )");
                    
                    // Insert sample leave types if table is empty
                    $stmt = $pdo->query("SELECT COUNT(*) FROM leave_types");
                    if ($stmt->fetchColumn() == 0) {
                        $pdo->exec("INSERT IGNORE INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active) VALUES
                            ('Annual Leave', 'AL', 'Annual vacation leave', 21, TRUE, TRUE, TRUE),
                            ('Sick Leave', 'SL', 'Medical sick leave', 10, FALSE, FALSE, TRUE),
                            ('Emergency Leave', 'EL', 'Emergency family leave', 5, FALSE, TRUE, TRUE),
                            ('Maternity Leave', 'ML', 'Maternity leave', 90, FALSE, TRUE, TRUE),
                            ('Paternity Leave', 'PL', 'Paternity leave', 7, FALSE, TRUE, TRUE)");
                    }
                    
                    // Get leave types
                    $stmt = $pdo->query("SELECT * FROM leave_types WHERE is_active = 1 ORDER BY name");
                    $leaveTypesData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $leaveTypes = collect($leaveTypesData);
                    
                    // Get employees
                    $stmt = $pdo->query("SELECT * FROM employees WHERE status = 'active' ORDER BY first_name");
                    $employeesData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $employees = collect($employeesData);
                    
                    // Get ALL leave requests with joins
                    $stmt = $pdo->query("
                        SELECT lr.id, lr.employee_id, lr.leave_type_id, lr.start_date, lr.end_date, 
                               lr.days_requested, lr.reason, lr.status, lr.approved_by, lr.approved_at,
                               lr.created_at, lr.updated_at,
                               COALESCE(e.first_name, 'Employee') as first_name, 
                               COALESCE(e.last_name, CONCAT('ID:', lr.employee_id)) as last_name,
                               CONCAT(COALESCE(e.first_name, 'Employee'), ' ', COALESCE(e.last_name, CONCAT('ID:', lr.employee_id))) as employee_name,
                               COALESCE(lt.name, CONCAT('Type ID:', lr.leave_type_id)) as leave_type_name, 
                               COALESCE(lt.code, 'N/A') as leave_type_code
                        FROM leave_requests lr
                        LEFT JOIN employees e ON lr.employee_id = e.id
                        LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
                        ORDER BY lr.created_at DESC
                    ");
                    $leavesData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $leaves = collect($leavesData);
                    
                    \Log::info('Raw PDO - Retrieved ' . count($leaveTypesData) . ' leave types, ' . count($leavesData) . ' leave requests');
                } catch (\Exception $e2) {
                    \Log::error('Raw PDO also failed, trying Laravel DB: ' . $e2->getMessage());
                    // Final fallback to Laravel DB
                    try {
                        $leaveTypes = DB::table('leave_types')->where('is_active', 1)->orderBy('name')->get();
                        $employees = DB::table('employees')->where('status', 'active')->orderBy('first_name')->get();
                        $leaves = DB::table('leave_requests')
                            ->leftJoin('employees', 'leave_requests.employee_id', '=', 'employees.id')
                            ->leftJoin('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
                            ->select('leave_requests.*', 'employees.first_name', 'employees.last_name',
                                    DB::raw('CONCAT(COALESCE(employees.first_name, ""), " ", COALESCE(employees.last_name, "")) as employee_name'),
                                    'leave_types.name as leave_type_name', 'leave_types.code as leave_type_code')
                            ->orderBy('leave_requests.created_at', 'desc')->get();
                    } catch (\Exception $e3) {
                        \Log::error('All database methods failed: ' . $e3->getMessage());
                    }
                }
            }
            
            // Calculate statistics for dashboard cards
            $totalLeaveTypes = $leaveTypes->count();
            $assignedEmployees = $leaves->pluck('employee_id')->unique()->count();
            $pendingRequests = $leaves->where('status', 'pending')->count();
            $weeklyHours = $leaves->where('status', 'approved')
                ->whereBetween('start_date', [Carbon::now()->startOfWeek()->format('Y-m-d'), Carbon::now()->endOfWeek()->format('Y-m-d')])
                ->sum('days_requested') * 8; // Assuming 8 hours per day
                
            return view('leave_management', compact('leaveTypes', 'employees', 'leaves', 'totalLeaveTypes', 'assignedEmployees', 'pendingRequests', 'weeklyHours'));
            
        } catch (\Exception $e) {
            // Final fallback with empty collections
            $leaveTypes = collect([]);
            $employees = collect([]);
            $leaves = collect([]);
            
            return view('leave_management', compact('leaveTypes', 'employees', 'leaves'))
                ->with('error', 'Error loading leave data: ' . $e->getMessage());
        }
    }

    public function getLeaveRequests(Request $request)
    {
        $query = LeaveRequest::with(['employee', 'leaveType', 'approvedBy']);
        
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->leave_type_id) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $leaves = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $leaves
        ]);
    }

    public function getLeaveBalances(Request $request)
    {
        $employeeId = $request->employee_id ?? Auth::user()->employee_id;
        $year = $request->year ?? date('Y');

        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employeeId)
            ->where('year', $year)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $balances
        ]);
    }

    public function create()
    {
        return view('leave.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        // Calculate days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $days = $startDate->diffInDays($endDate) + 1;

        // Check leave balance
        $balance = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', date('Y'))
            ->first();

        if (!$balance || $balance->remaining_days < $days) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient leave balance'
            ]);
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $request->employee_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days_requested' => $days,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully',
            'data' => $leaveRequest->load(['employee', 'leaveType'])
        ]);
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['employee', 'leaveType', 'approvedBy']);
        
        return response()->json([
            'success' => true,
            'data' => $leaveRequest
        ]);
    }

    public function edit($id)
    {
        // In a real application, fetch from database
        $leave = [
            'id' => $id,
            'employee_name' => 'John Anderson',
            'type' => 'annual',
            'start_date' => '2024-02-15',
            'end_date' => '2024-02-19',
            'days' => 5,
            'reason' => 'Family vacation',
            'status' => 'pending'
        ];

        return view('leave.edit', compact('leave'));
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update approved/rejected leave request'
            ]);
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $days = $startDate->diffInDays($endDate) + 1;

        $leaveRequest->update([
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days_requested' => $days,
            'reason' => $request->reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request updated successfully',
            'data' => $leaveRequest->load(['employee', 'leaveType'])
        ]);
    }

    public function destroy($id)
    {
        try {
            $leaveRequest = DB::selectOne("SELECT * FROM leave_requests WHERE id = ?", [$id]);
            
            if (!$leaveRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found'
                ], 404);
            }

            if ($leaveRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete approved/rejected leave request'
                ], 409);
            }

            $affected = DB::delete("DELETE FROM leave_requests WHERE id = ?", [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Leave request deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    // Web-based CRUD methods for server-side form handling
    public function storeWeb(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'leave_type_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->route('leave-management')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Calculate days requested
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $daysRequested = $startDate->diffInDays($endDate) + 1;

            // Direct PDO insertion with fallback
            try {
                $pdo = $this->getPDOConnection();
                
                $stmt = $pdo->prepare("INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, days_requested, reason, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())");
                $stmt->execute([
                    $request->employee_id,
                    $request->leave_type_id,
                    $request->start_date,
                    $request->end_date,
                    $daysRequested,
                    $request->reason
                ]);
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave request submitted successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to Laravel DB
                DB::table('leave_requests')->insert([
                    'employee_id' => $request->employee_id,
                    'leave_type_id' => $request->leave_type_id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'days_requested' => $daysRequested,
                    'reason' => $request->reason,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave request submitted successfully!');
            }
            
        } catch (\Exception $e) {
            return redirect()->route('leave-management')
                ->with('error', 'Error creating leave request: ' . $e->getMessage());
        }
    }

    public function storeLeaveTypeWeb(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10',
            'description' => 'nullable|string|max:500',
            'max_days_per_year' => 'required|integer|min:0',
            'carry_forward' => 'boolean',
            'requires_approval' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('leave-management')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Direct PDO insertion with fallback
            try {
                $pdo = $this->getPDOConnection();
                
                $stmt = $pdo->prepare("INSERT INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
                $stmt->execute([
                    $request->name,
                    $request->code,
                    $request->description,
                    $request->max_days_per_year,
                    $request->has('carry_forward') ? 1 : 0,
                    $request->has('requires_approval') ? 1 : 0
                ]);
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave type created successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to Laravel DB
                DB::table('leave_types')->insert([
                    'name' => $request->name,
                    'code' => $request->code,
                    'description' => $request->description,
                    'max_days_per_year' => $request->max_days_per_year,
                    'carry_forward' => $request->has('carry_forward'),
                    'requires_approval' => $request->has('requires_approval'),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave type created successfully!');
            }
            
        } catch (\Exception $e) {
            return redirect()->route('leave-management')
                ->with('error', 'Error creating leave type: ' . $e->getMessage());
        }
    }

    public function destroyWeb($id)
    {
        try {
            // Direct PDO deletion with fallback
            try {
                $pdo = $this->getPDOConnection();
                
                $stmt = $pdo->prepare("DELETE FROM leave_requests WHERE id = ?");
                $stmt->execute([$id]);
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave request deleted successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to Laravel DB
                DB::table('leave_requests')->where('id', $id)->delete();
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave request deleted successfully!');
            }
            
        } catch (\Exception $e) {
            return redirect()->route('leave-management')
                ->with('error', 'Error deleting leave request: ' . $e->getMessage());
        }
    }

    public function approveWeb($id)
    {
        try {
            // Direct PDO update with fallback
            try {
                $pdo = $this->getPDOConnection();
                
                $stmt = $pdo->prepare("UPDATE leave_requests SET status = 'approved', approved_at = NOW(), updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave request approved successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to Laravel DB
                DB::table('leave_requests')
                    ->where('id', $id)
                    ->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'updated_at' => now()
                    ]);
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave request approved successfully!');
            }
            
        } catch (\Exception $e) {
            return redirect()->route('leave-management')
                ->with('error', 'Error approving leave request: ' . $e->getMessage());
        }
    }

    public function rejectWeb($id)
    {
        try {
            // Direct PDO update with fallback
            try {
                $pdo = $this->getPDOConnection();
                
                $stmt = $pdo->prepare("UPDATE leave_requests SET status = 'rejected', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave request rejected successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to Laravel DB
                DB::table('leave_requests')
                    ->where('id', $id)
                    ->update([
                        'status' => 'rejected',
                        'updated_at' => now()
                    ]);
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave request rejected successfully!');
            }
            
        } catch (\Exception $e) {
            return redirect()->route('leave-management')
                ->with('error', 'Error rejecting leave request: ' . $e->getMessage());
        }
    }

    public function destroyLeaveTypeWeb($id)
    {
        try {
            // Direct PDO deletion with fallback
            try {
                $pdo = $this->getPDOConnection();
                
                $stmt = $pdo->prepare("DELETE FROM leave_types WHERE id = ?");
                $stmt->execute([$id]);
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave type deleted successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to Laravel DB
                DB::table('leave_types')->where('id', $id)->delete();
                
                return redirect()->route('leave-management')
                    ->with('success', 'Leave type deleted successfully!');
            }
            
        } catch (\Exception $e) {
            return redirect()->route('leave-management')
                ->with('error', 'Error deleting leave type: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        // Update leave balance
        $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', date('Y'))
            ->first();

        if ($balance) {
            $balance->update([
                'used_days' => $balance->used_days + $leaveRequest->days_requested,
                'remaining_days' => $balance->remaining_days - $leaveRequest->days_requested
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved successfully'
        ]);
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected'
        ]);
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'leave_request_ids' => 'required|array',
            'leave_request_ids.*' => 'exists:leave_requests,id'
        ]);

        $approved = 0;
        foreach ($request->leave_request_ids as $id) {
            $leaveRequest = LeaveRequest::find($id);
            if ($leaveRequest && $leaveRequest->status === 'pending') {
                $leaveRequest->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);

                // Update leave balance
                $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', date('Y'))
                    ->first();

                if ($balance) {
                    $balance->update([
                        'used_days' => $balance->used_days + $leaveRequest->days_requested,
                        'remaining_days' => $balance->remaining_days - $leaveRequest->days_requested
                    ]);
                }
                $approved++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "$approved leave requests approved successfully"
        ]);
    }

    // Leave Type Management Methods
    public function createLeaveType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:leave_types,name',
            'code' => 'required|string|max:10|unique:leave_types,code',
            'description' => 'nullable|string|max:500',
            'max_days_per_year' => 'required|integer|min:1|max:365',
            'carry_forward' => 'boolean',
            'requires_approval' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $leaveType = LeaveType::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'max_days_per_year' => $request->max_days_per_year,
                'carry_forward' => $request->boolean('carry_forward', false),
                'requires_approval' => $request->boolean('requires_approval', true),
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave type created successfully',
                'data' => $leaveType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating leave type: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLeaveTypes()
    {
        try {
            $leaveTypes = LeaveType::where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $leaveTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching leave types: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateLeaveType(Request $request, $id)
    {
        $leaveType = LeaveType::find($id);
        
        if (!$leaveType) {
            return response()->json([
                'success' => false,
                'message' => 'Leave type not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:leave_types,name,' . $id,
            'code' => 'required|string|max:10|unique:leave_types,code,' . $id,
            'description' => 'nullable|string|max:500',
            'max_days_per_year' => 'required|integer|min:1|max:365',
            'carry_forward' => 'boolean',
            'requires_approval' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $leaveType->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'max_days_per_year' => $request->max_days_per_year,
                'carry_forward' => $request->boolean('carry_forward', false),
                'requires_approval' => $request->boolean('requires_approval', true)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave type updated successfully',
                'data' => $leaveType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating leave type: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteLeaveType($id)
    {
        try {
            $leaveType = LeaveType::find($id);
            
            if (!$leaveType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave type not found'
                ], 404);
            }

            // Check if leave type is being used
            $hasRequests = LeaveRequest::where('leave_type_id', $id)->exists();
            $hasBalances = LeaveBalance::where('leave_type_id', $id)->exists();

            if ($hasRequests || $hasBalances) {
                // Soft delete by marking as inactive
                $leaveType->update(['is_active' => false]);
                $message = 'Leave type deactivated (cannot delete due to existing records)';
            } else {
                // Hard delete if no dependencies
                $leaveType->delete();
                $message = 'Leave type deleted successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting leave type: ' . $e->getMessage()
            ], 500);
        }
    }

    // Additional missing endpoints
    public function getLeaveStats()
    {
        try {
            $totalRequests = LeaveRequest::count();
            $pendingRequests = LeaveRequest::where('status', 'pending')->count();
            $approvedRequests = LeaveRequest::where('status', 'approved')->count();
            $rejectedRequests = LeaveRequest::where('status', 'rejected')->count();
            
            // Get remaining balance for current user (if available)
            $remainingBalance = 0;
            if (Auth::check() && Auth::user()->employee_id) {
                $balances = LeaveBalance::where('employee_id', Auth::user()->employee_id)
                    ->where('year', date('Y'))
                    ->sum('remaining_days');
                $remainingBalance = $balances;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_requests' => $totalRequests,
                    'pending_requests' => $pendingRequests,
                    'approved_requests' => $approvedRequests,
                    'rejected_requests' => $rejectedRequests,
                    'remaining_balance' => $remainingBalance
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching leave statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLeaveBalance($leaveTypeId)
    {
        try {
            $employeeId = Auth::user()->employee_id ?? 1; // Default to employee 1 if not set
            $year = date('Y');
            
            $balance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', $year)
                ->first();

            if (!$balance) {
                // Create default balance if doesn't exist
                $leaveType = LeaveType::find($leaveTypeId);
                if ($leaveType) {
                    $balance = LeaveBalance::create([
                        'employee_id' => $employeeId,
                        'leave_type_id' => $leaveTypeId,
                        'year' => $year,
                        'allocated_days' => $leaveType->max_days_per_year,
                        'used_days' => 0,
                        'remaining_days' => $leaveType->max_days_per_year
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $balance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching leave balance: ' . $e->getMessage()
            ], 500);
        }
    }

    // Web-based action methods for server-side handling
    public function viewLeaveType($id)
    {
        try {
            $leaveType = LeaveType::find($id);
            
            if (!$leaveType) {
                return redirect()->back()->with('error', 'Leave type not found');
            }

            return redirect()->back()->with('success', 
                "Leave Type Details:\n\n" .
                "Name: {$leaveType->name}\n" .
                "Code: {$leaveType->code}\n" .
                "Description: " . ($leaveType->description ?: 'N/A') . "\n" .
                "Max Days: {$leaveType->max_days_per_year}\n" .
                "Carry Forward: " . ($leaveType->carry_forward ? 'Yes' : 'No') . "\n" .
                "Requires Approval: " . ($leaveType->requires_approval ? 'Yes' : 'No') . "\n" .
                "Status: " . ($leaveType->is_active ? 'Active' : 'Inactive')
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading leave type details');
        }
    }

    public function editLeaveType($id)
    {
        try {
            $leaveType = DB::selectOne("SELECT * FROM leave_types WHERE id = ?", [$id]);
            
            if (!$leaveType) {
                return redirect()->back()->with('error', 'Leave type not found');
            }

            // Store leave type data in session for editing and flag to show modal
            session([
                'edit_leave_type' => (array)$leaveType,
                'show_edit_modal' => true
            ]);
            return redirect()->back()->with('info', 'Leave type loaded for editing');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading leave type for editing');
        }
    }

    public function viewLeaveRequest($id)
    {
        try {
            $leaveRequest = DB::selectOne("
                SELECT lr.*, 
                       CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                       lt.name as leave_type_name
                FROM leave_requests lr 
                LEFT JOIN employees e ON lr.employee_id = e.id 
                LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id 
                WHERE lr.id = ?
            ", [$id]);
            
            if (!$leaveRequest) {
                return redirect()->back()->with('error', 'Leave request not found');
            }

            $startDate = date('M d, Y', strtotime($leaveRequest->start_date));
            $endDate = date('M d, Y', strtotime($leaveRequest->end_date));
            $submitted = date('M d, Y', strtotime($leaveRequest->created_at));

            return redirect()->back()->with('success', 
                "Leave Request Details:\n\n" .
                "Employee: {$leaveRequest->employee_name}\n" .
                "Leave Type: {$leaveRequest->leave_type_name}\n" .
                "Start Date: {$startDate}\n" .
                "End Date: {$endDate}\n" .
                "Days Requested: {$leaveRequest->days_requested}\n" .
                "Reason: {$leaveRequest->reason}\n" .
                "Status: " . strtoupper($leaveRequest->status) . "\n" .
                "Submitted: {$submitted}"
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading leave request details');
        }
    }

    public function approveLeaveRequest($id)
    {
        try {
            $leaveRequest = DB::selectOne("SELECT * FROM leave_requests WHERE id = ?", [$id]);
            
            if (!$leaveRequest) {
                return redirect()->back()->with('error', 'Leave request not found');
            }

            if ($leaveRequest->status !== 'pending') {
                return redirect()->back()->with('error', 'Leave request is not pending');
            }

            // Update leave request status
            DB::update("UPDATE leave_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?", [Auth::id(), $id]);

            // Update leave balance if exists
            $balance = DB::selectOne("SELECT * FROM leave_balances WHERE employee_id = ? AND leave_type_id = ? AND year = ?", 
                [$leaveRequest->employee_id, $leaveRequest->leave_type_id, date('Y')]);

            if ($balance) {
                DB::update("UPDATE leave_balances SET used_days = used_days + ?, remaining_days = remaining_days - ? WHERE id = ?", 
                    [$leaveRequest->days_requested, $leaveRequest->days_requested, $balance->id]);
            }

            return redirect()->back()->with('success', 'Leave request approved successfully. Leave balance updated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error approving leave request');
        }
    }

    public function rejectLeaveRequest($id)
    {
        try {
            $leaveRequest = DB::selectOne("SELECT * FROM leave_requests WHERE id = ?", [$id]);
            
            if (!$leaveRequest) {
                return redirect()->back()->with('error', 'Leave request not found');
            }

            if ($leaveRequest->status !== 'pending') {
                return redirect()->back()->with('error', 'Leave request is not pending');
            }

            // Update leave request status
            DB::update("UPDATE leave_requests SET status = 'rejected', approved_by = ?, approved_at = NOW() WHERE id = ?", [Auth::id(), $id]);

            return redirect()->back()->with('success', 'Leave request rejected successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error rejecting leave request');
        }
    }

    public function deleteLeaveRequest($id)
    {
        try {
            $leaveRequest = DB::selectOne("SELECT * FROM leave_requests WHERE id = ?", [$id]);
            
            if (!$leaveRequest) {
                return redirect()->back()->with('error', 'Leave request not found');
            }

            DB::delete("DELETE FROM leave_requests WHERE id = ?", [$id]);

            return redirect()->back()->with('success', 'Leave request deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting leave request');
        }
    }

    // Duplicate method removed to fix redeclaration error

    // Removed duplicate methods to fix redeclaration errors

    // Additional methods removed to prevent redeclaration errors

    /**
     * Show leave request details for admin API
     */
    public function showAdmin($id)
    {
        try {
            $request = DB::table('leave_requests as lr')
                ->leftJoin('employees as e', 'lr.employee_id', '=', 'e.id')
                ->leftJoin('leave_types as lt', 'lr.leave_type_id', '=', 'lt.id')
                ->leftJoin('employees as approver', 'lr.approved_by', '=', 'approver.id')
                ->select(
                    'lr.*',
                    DB::raw("CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name"),
                    'lt.name as leave_type_name',
                    DB::raw("CONCAT(COALESCE(approver.first_name, ''), ' ', COALESCE(approver.last_name, '')) as approved_by_name")
                )
                ->where('lr.id', $id)
                ->first();

            if (!$request) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'request' => $request
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in LeaveController@showAdmin: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve leave request via API
     */
    public function approveAdmin($id)
    {
        try {
            $request = DB::table('leave_requests')->where('id', $id)->first();
            
            if (!$request) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found'
                ], 404);
            }

            if ($request->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request has already been processed'
                ], 400);
            }

            DB::table('leave_requests')->where('id', $id)->update([
                'status' => 'approved',
                'approved_by' => 1, // Default admin ID - you might want to get this from Auth
                'approved_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave request approved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in LeaveController@approveAdmin: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject leave request via API
     */
    public function rejectAdmin($id)
    {
        try {
            $request = DB::table('leave_requests')->where('id', $id)->first();
            
            if (!$request) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found'
                ], 404);
            }

            if ($request->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request has already been processed'
                ], 400);
            }

            DB::table('leave_requests')->where('id', $id)->update([
                'status' => 'rejected',
                'approved_by' => 1, // Default admin ID - you might want to get this from Auth
                'approved_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave request rejected successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in LeaveController@rejectAdmin: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject leave request: ' . $e->getMessage()
            ], 500);
        }
    }
}
