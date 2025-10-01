<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TimeAttendanceController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ClaimControllerFixed;
use App\Http\Controllers\ClaimControllerSimple;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ShiftTypesController;
use App\Http\Controllers\ShiftRequestController;
use App\Http\Controllers\EmployeeShiftController;
use App\Http\Controllers\EmployeeESSController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AdminProfileController;

// Registration routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.store');

// Direct access to admin_dashboard view (for /admin_dashboard URL)
Route::get('/admin_dashboard', [DashboardController::class, 'index'])->middleware('auth');

// Landing page route
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Portal selection route
Route::get('/portal-selection', function () {
    return view('portal_selection');
})->name('portal.selection');

// Landing page API routes
Route::post('/newsletter/subscribe', [LandingController::class, 'subscribeNewsletter'])->name('newsletter.subscribe');
Route::post('/booking/submit', [LandingController::class, 'submitBooking'])->name('booking.submit');

// Admin Auth routes
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// HR dashboard (protected)
Route::get('/dashboard', [App\Http\Controllers\HRDashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// Test modal route
Route::get('/test-modal', function () {
    return view('test-modal');
})->name('test.modal');

// Debug employee authentication routes
Route::get('/debug-employee-auth', [App\Http\Controllers\DebugAuthController::class, 'debugEmployeeAuth'])->name('debug.employee.auth');
Route::get('/fix-employee-passwords', [App\Http\Controllers\DebugAuthController::class, 'fixEmployeePasswords'])->name('fix.employee.passwords');
Route::get('/create-test-employee', [App\Http\Controllers\DebugAuthController::class, 'createTestEmployee'])->name('create.test.employee');

Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('auth')
    ->name('bookings.store');

Route::post('/api/clock-in', [TimeAttendanceController::class, 'clockIn']);

// Time and Attendance System Routes
Route::get('/time-attendance', function () {
    return view('TimeAndAttendance');
})->name('time-attendance');

// HR Module Routes with proper controllers
Route::middleware(['auth'])->group(function () {

    Route::get('/timesheet-management', [TimesheetController::class, 'index'])->name('timesheet-management');
    
    Route::get('/attendance-management', [AttendanceController::class, 'index'])->name('attendance-management');
    
    Route::get('/leave-management', [LeaveController::class, 'index'])->name('leave-management');

    Route::get('/shift-schedule-management', [ShiftController::class, 'index'])->name('shift-schedule-management');

    Route::get('/claims-reimbursement', [ClaimControllerSimple::class, 'index'])->name('claims-reimbursement');
    
    // Test route to bypass cache
    Route::get('/claims-test', [ClaimControllerSimple::class, 'index'])->name('claims-test');
    
    // Claims CRUD Routes - Using Simple Controller for Testing
    Route::get('/claims/test', [ClaimControllerSimple::class, 'test'])->name('claims.test');
    Route::post('/claims/store', [ClaimControllerSimple::class, 'store'])->name('claims.store.simple');
    Route::get('/claims/{id}/view', [ClaimController::class, 'viewClaim'])->name('claims.view');
    Route::get('/claims/{id}/edit', [ClaimController::class, 'editWeb'])->name('claims.edit');
    Route::put('/claims/{id}', [ClaimController::class, 'updateWeb'])->name('claims.update');
    Route::delete('/claims/{id}', [ClaimController::class, 'destroyWeb'])->name('claims.delete');
    Route::patch('/claims/{id}/approve', [ClaimController::class, 'approveWeb'])->name('claims.approve');
    Route::patch('/claims/{id}/reject', [ClaimController::class, 'rejectWeb'])->name('claims.reject');
    Route::patch('/claims/{id}/pay', [ClaimController::class, 'payWeb'])->name('claims.pay');
    
    // Claim Types CRUD Routes - Using Simple Controller for Testing
    Route::post('/claim-types/store', [ClaimControllerSimple::class, 'storeClaimTypeWeb'])->name('claim-types.store');
    Route::put('/claim-types/{id}', [ClaimController::class, 'updateClaimTypeWeb'])->name('claim-types.update');
    Route::get('/claim-types/{id}/edit', [ClaimController::class, 'editClaimTypeWeb'])->name('claim-types.edit');
    Route::get('/claim-types/{id}/view', [ClaimController::class, 'viewClaimTypeWeb'])->name('claim-types.view');
    Route::delete('/claim-types/{id}', [ClaimController::class, 'destroyClaimTypeWeb'])->name('claim-types.delete');

    // Leave Types CRUD Routes
    Route::post('/leave/types/store', [LeaveController::class, 'storeLeaveTypeWeb'])->name('leave.types.store');
    Route::put('/leave/types/{id}', [LeaveController::class, 'updateLeaveTypeWeb'])->name('leave.types.update');
    Route::get('/leave/types/{id}/edit', [LeaveController::class, 'editLeaveType'])->name('leave.types.edit');
    Route::get('/leave/types/{id}/view', [LeaveController::class, 'viewLeaveType'])->name('leave.types.view');
    Route::delete('/leave/types/{id}', [LeaveController::class, 'deleteLeaveType'])->name('leave.types.destroy');
    
    // Leave Requests CRUD Routes
    Route::post('/leave/requests/store', [LeaveController::class, 'storeLeaveRequestWeb'])->name('leave.requests.store');
    Route::put('/leave/requests/{id}', [LeaveController::class, 'updateLeaveRequestWeb'])->name('leave.requests.update');
    Route::get('/leave/requests/{id}/edit', [LeaveController::class, 'editLeaveRequestWeb'])->name('leave.requests.edit');
    Route::get('/leave/requests/{id}/view', [LeaveController::class, 'viewLeaveRequest'])->name('leave.requests.view');
    Route::post('/leave/requests/{id}/approve', [LeaveController::class, 'approveLeaveRequest'])->name('leave.requests.approve');
    Route::post('/leave/requests/{id}/reject', [LeaveController::class, 'rejectLeaveRequest'])->name('leave.requests.reject');
    
    // Shift Requests CRUD Routes
    Route::post('/shift/requests/store', [ShiftRequestController::class, 'store'])->name('shift.requests.store');
    Route::put('/shift/requests/{id}/status', [ShiftRequestController::class, 'updateStatus'])->name('shift.requests.status');
    Route::delete('/shift/requests/{id}', [ShiftRequestController::class, 'destroy'])->name('shift.requests.destroy');
    
    // Employee Shift Routes (ESS Module)
    Route::get('/employee/shift-schedule', [EmployeeShiftController::class, 'index'])->name('employee.shift.index');
    Route::post('/employee/shift/store', [EmployeeShiftController::class, 'store'])->name('employee.shift.store');
    Route::delete('/employee/shift/{id}', [EmployeeShiftController::class, 'destroy'])->name('employee.shift.destroy');
    Route::delete('/leave/requests/{id}', [LeaveController::class, 'deleteLeaveRequest'])->name('leave.requests.delete');

    Route::get('/shift-schedule-management', [ShiftController::class, 'index'])->name('shift-schedule-management');
    
    // Debug route to test employee data
    Route::get('/test-employees', function() {
        try {
            $employees = \App\Models\Employee::where('status', 'active')->get();
            return response()->json([
                'count' => $employees->count(),
                'employees' => $employees->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'fallback' => 'Using PDO...'
            ]);
        }
    });
    
    // Shift Type CRUD Routes - Using dedicated ShiftTypesController
    Route::get('/api/shift-types', [ShiftTypesController::class, 'index'])->name('shift.types.api');
    Route::post('/shift-types/store', [ShiftTypesController::class, 'store'])->name('shift.types.store');
    Route::put('/shift-types/{id}', [ShiftTypesController::class, 'update'])->name('shift.types.update');
    Route::delete('/shift-types/{id}', [ShiftTypesController::class, 'destroy'])->name('shift.types.delete');

    // Shift assignment routes
    Route::post('/shifts/store', [ShiftController::class, 'storeShiftWeb'])->name('shifts.store');
    Route::put('/shifts/{id}', [ShiftController::class, 'updateShiftWeb'])->name('shifts.update');
    Route::get('/shifts/{id}/edit', [ShiftController::class, 'editShiftWeb'])->name('shifts.edit');
    Route::get('/shifts/{id}/view', [ShiftController::class, 'viewShiftWeb'])->name('shifts.view');
    Route::delete('/shifts/{id}', [ShiftController::class, 'destroyShiftWeb'])->name('shifts.destroy');
    Route::post('/shifts/{id}/delete', [ShiftController::class, 'destroyShiftWeb'])->name('shifts.delete'); // Alternative for browsers that don't support DELETE
    Route::post('/shifts/{id}/complete', [ShiftController::class, 'completeShiftWeb'])->name('shifts.complete');
    
    // Shift Request Routes
    Route::post('/shift/requests/store', [ShiftController::class, 'storeShiftRequestWeb'])->name('shift.requests.store');
    Route::post('/shift/requests/{id}/approve', [ShiftController::class, 'approveShiftRequestWeb'])->name('shift.requests.approve');
    Route::post('/shift/requests/{id}/reject', [ShiftController::class, 'rejectShiftRequestWeb'])->name('shift.requests.reject');
    Route::post('/shift/requests/bulk-approve', [ShiftController::class, 'bulkApproveShiftRequestsWeb'])->name('shift.requests.bulk.approve');
    Route::post('/shift/requests/bulk-reject', [ShiftController::class, 'bulkRejectShiftRequestsWeb'])->name('shift.requests.bulk.reject');

    // Timesheet routes
    Route::post('/timesheets/store', [TimesheetController::class, 'storeWeb'])->name('timesheets.store');
    Route::put('/timesheets/{id}', [TimesheetController::class, 'updateWeb'])->name('timesheets.update');
    Route::get('/timesheets/{id}/edit', [TimesheetController::class, 'editWeb'])->name('timesheets.edit');
    Route::get('/timesheets/{id}/view', [TimesheetController::class, 'viewWeb'])->name('timesheets.view');
    Route::delete('/timesheets/{id}', [TimesheetController::class, 'destroyWeb'])->name('timesheets.destroy');
    Route::post('/timesheets/{id}/approve', [TimesheetController::class, 'approveWeb'])->name('timesheets.approve');
    Route::post('/timesheets/{id}/reject', [TimesheetController::class, 'rejectWeb'])->name('timesheets.reject');

    // Attendance routes
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak'])->name('attendance.start-break');
    Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak'])->name('attendance.end-break');
    
    // Admin Attendance Management Routes
    Route::get('/admin/attendance/{id}', [AttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::get('/admin/attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('admin.attendance.edit');
    Route::put('/admin/attendance/{id}', [AttendanceController::class, 'update'])->name('admin.attendance.update');
    
    // Admin Shift Management Routes
    Route::get('/admin/shifts/{id}', [ShiftController::class, 'show'])->name('admin.shifts.show');
    Route::get('/admin/shifts/{id}/edit', [ShiftController::class, 'edit'])->name('admin.shifts.edit');
    Route::put('/admin/shifts/{id}', [ShiftController::class, 'update'])->name('admin.shifts.update');
    Route::post('/shifts/store', [ShiftController::class, 'store'])->name('shifts.store');
    
    // Admin Leave Request Management Routes
    Route::get('/admin/leave-requests/{id}', [LeaveController::class, 'showAdmin'])->name('admin.leave-requests.show');
    Route::post('/admin/leave-requests/{id}/approve', [LeaveController::class, 'approveAdmin'])->name('admin.leave-requests.approve');
    Route::post('/admin/leave-requests/{id}/reject', [LeaveController::class, 'rejectAdmin'])->name('admin.leave-requests.reject');
    Route::post('/leave-requests/store', [LeaveController::class, 'store'])->name('leave-requests.store');
    
    // Admin Claims Management Routes
    Route::get('/admin/claims/{id}', [ClaimControllerSimple::class, 'show'])->name('admin.claims.show');
    Route::post('/admin/claims/{id}/approve', [ClaimControllerSimple::class, 'approve'])->name('admin.claims.approve');
    Route::post('/admin/claims/{id}/reject', [ClaimControllerSimple::class, 'reject'])->name('admin.claims.reject');

    // Employee Management Routes
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees');
    Route::get('/employees/{id}/view', [EmployeeController::class, 'viewEmployeeAPI'])->name('employees.view');
    Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::post('/employees/store', [EmployeeController::class, 'storeWeb'])->name('employees.store');
    Route::put('/employees/{id}', [EmployeeController::class, 'updateWeb'])->name('employees.update');
    Route::delete('/employees/{id}/delete', [EmployeeController::class, 'destroyWeb'])->name('employees.delete');

    Route::get('/database-test', function () {
        return view('database_test');
    })->name('database-test');
    
    // Profile routes
    Route::get('/profile', function () {
        return view('profile.edit');
    })->name('profile.edit');
});

// Additional HR System Routes (protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    
    // Resource routes for HR modules (for comprehensive menu links)
    Route::resource('timesheet', TimesheetController::class);
    Route::resource('claims', ClaimController::class);
    Route::resource('leave', LeaveController::class);
    
    // Shift routes (separate to avoid conflicts)
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    
});

// Dummy login route to prevent Laravel framework errors
Route::get('/login', function() {
    return view('employee_ess_modules.employee_login');
})->name('login');

// Employee routes moved to separate file - see routes/employee.php
require __DIR__.'/employee.php';

// CSRF token refresh endpoint
Route::get('/csrf-token', [EmployeeESSController::class, 'getCsrfToken'])->name('csrf.token');

// Leave Management Routes
Route::post('/leave-requests/store', [LeaveController::class, 'storeWeb'])->name('leave-requests.store');
Route::patch('/leave-requests/{id}/approve', [LeaveController::class, 'approveWeb'])->name('leave-requests.approve');
Route::patch('/leave-requests/{id}/reject', [LeaveController::class, 'rejectWeb'])->name('leave-requests.reject');
Route::delete('/leave-requests/{id}', [LeaveController::class, 'destroyWeb'])->name('leave-requests.destroy');

Route::post('/leave-types/store', [LeaveController::class, 'storeLeaveTypeWeb'])->name('leave-types.store');
Route::delete('/leave-types/{id}', [LeaveController::class, 'destroyLeaveTypeWeb'])->name('leave-types.destroy');

// Shift Management Routes
Route::post('/shift-types/store', [ShiftController::class, 'storeShiftTypeWeb'])->name('shift-types.store');
Route::put('/shift-types/{id}', [ShiftController::class, 'updateShiftTypeWeb'])->name('shift-types.update');
Route::get('/shift-types/{id}/edit', [ShiftController::class, 'editShiftTypeWeb'])->name('shift-types.edit');
Route::get('/shift-types/{id}/view', [ShiftController::class, 'viewShiftTypeWeb'])->name('shift-types.view');
Route::delete('/shift-types/{id}', [ShiftController::class, 'destroyShiftTypeWeb'])->name('shift-types.destroy');

Route::post('/shifts/store', [ShiftController::class, 'storeShiftWeb'])->name('shifts.store');
Route::put('/shifts/{id}', [ShiftController::class, 'updateShiftWeb'])->name('shifts.update');
Route::get('/shifts/{id}/edit', [ShiftController::class, 'editShiftWeb'])->name('shifts.edit');
Route::get('/shifts/{id}/view', [ShiftController::class, 'viewShiftWeb'])->name('shifts.view');
Route::delete('/shifts/{id}', [ShiftController::class, 'destroyShiftWeb'])->name('shifts.destroy');
Route::post('/shifts/{id}/complete', [ShiftController::class, 'completeShiftWeb'])->name('shifts.complete');

// Shift Request Routes - Updated to use ShiftRequestController
Route::post('/shift-requests/store', [ShiftRequestController::class, 'store'])->name('shift-requests.store');
Route::post('/shift-requests/{id}/approve', [ShiftRequestController::class, 'approve'])->name('shift-requests.approve');
Route::post('/shift-requests/{id}/reject', [ShiftRequestController::class, 'reject'])->name('shift-requests.reject');
Route::delete('/shift-requests/{id}', [ShiftRequestController::class, 'destroy'])->name('shift-requests.destroy');

// Test route for claims debugging
Route::get('/test-claims', function() {
    try {
        $controller = new \App\Http\Controllers\ClaimController();
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
});

// Direct database test route
Route::get('/test-db', function() {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
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
});

// Dashboard data population route (for testing)
Route::get('/populate-dashboard', function() {
    try {
        $seeder = new \Database\Seeders\DashboardDataSeeder();
        $seeder->run();
        return response()->json([
            'success' => true,
            'message' => 'Dashboard data populated successfully!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error populating data: ' . $e->getMessage()
        ]);
    }
})->name('populate.dashboard');

// Debug route to check shift data
Route::get('/debug-shifts', function() {
    $shiftTypes = DB::table('shift_types')->get();
    $shifts = DB::table('shifts')
        ->join('employees', 'shifts.employee_id', '=', 'employees.id')
        ->select('shifts.*', 'employees.first_name', 'employees.last_name', 'employees.status')
        ->orderBy('shifts.shift_date', 'desc')
        ->limit(20)
        ->get();
    $employees = DB::table('employees')->where('status', 'active')->count();
    
    return response()->json([
        'shift_types_count' => $shiftTypes->count(),
        'shift_types' => $shiftTypes,
        'recent_shifts_count' => $shifts->count(),
        'recent_shifts' => $shifts,
        'active_employees_count' => $employees,
        'today_date' => today()->format('Y-m-d')
    ]);
})->name('debug.shifts');

// Debug route to check attendance data
Route::get('/debug-attendance', function() {
    $attendances = DB::table('attendances')
        ->join('employees', 'attendances.employee_id', '=', 'employees.id')
        ->select('attendances.*', 'employees.first_name', 'employees.last_name')
        ->orderBy('attendances.attendance_date', 'desc')
        ->limit(10)
        ->get();
    
    $timeEntries = DB::table('time_entries')
        ->join('employees', 'time_entries.employee_id', '=', 'employees.id')
        ->select('time_entries.*', 'employees.first_name', 'employees.last_name')
        ->orderBy('time_entries.created_at', 'desc')
        ->limit(10)
        ->get();
    
    return response()->json([
        'attendances_count' => $attendances->count(),
        'attendances' => $attendances,
        'time_entries_count' => $timeEntries->count(),
        'time_entries' => $timeEntries,
        'today_date' => today()->format('Y-m-d')
    ]);
})->name('debug.attendance');

// Simple test route for HR Dashboard data
Route::get('/test-hr-data', function() {
    try {
        // Test attendances table
        $attendanceCount = DB::table('attendances')->count();
        $recentAttendances = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->select('attendances.*', 'employees.first_name', 'employees.last_name')
            ->limit(5)
            ->get();
        
        // Test time_entries table
        $timeEntriesCount = DB::table('time_entries')->count();
        
        // Test employees table
        $employeesCount = DB::table('employees')->count();
        
        return [
            'attendances_count' => $attendanceCount,
            'time_entries_count' => $timeEntriesCount,
            'employees_count' => $employeesCount,
            'sample_attendances' => $recentAttendances->take(3),
            'tables_exist' => [
                'attendances' => Schema::hasTable('attendances'),
                'time_entries' => Schema::hasTable('time_entries'),
                'employees' => Schema::hasTable('employees')
            ]
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
})->name('test.hr.data');

// Test HR Dashboard controller method directly
Route::get('/test-recent-entries', function() {
    $controller = new \App\Http\Controllers\HRDashboardController();
    $method = new \ReflectionMethod($controller, 'getRecentTimeEntries');
    $method->setAccessible(true);
    $result = $method->invoke($controller);
    
    return [
        'count' => $result->count(),
        'data' => $result->toArray()
    ];
})->name('test.recent.entries');

// Temporary route to populate timesheet data
Route::get('/populate-timesheets', function() {
    try {
        $count = DB::table('time_entries')->count();
        if ($count > 0) {
            return "Timesheet data already exists. Count: {$count}";
        }
        
        $timesheetData = [
            [
                'employee_id' => 1,
                'work_date' => '2024-10-01',
                'clock_in_time' => '09:00:00',
                'clock_out_time' => '17:30:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.5,
                'status' => 'pending'
            ],
            [
                'employee_id' => 1,
                'work_date' => '2024-09-30',
                'clock_in_time' => '08:45:00',
                'clock_out_time' => '17:15:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.5,
                'status' => 'approved'
            ],
            [
                'employee_id' => 2,
                'work_date' => '2024-10-01',
                'clock_in_time' => '08:30:00',
                'clock_out_time' => '17:00:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.5,
                'status' => 'approved'
            ],
            [
                'employee_id' => 2,
                'work_date' => '2024-09-30',
                'clock_in_time' => '09:00:00',
                'clock_out_time' => '17:30:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.5,
                'status' => 'pending'
            ],
            [
                'employee_id' => 3,
                'work_date' => '2024-10-01',
                'clock_in_time' => '09:30:00',
                'clock_out_time' => '18:15:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.75,
                'status' => 'pending'
            ],
            [
                'employee_id' => 3,
                'work_date' => '2024-09-30',
                'clock_in_time' => '09:00:00',
                'clock_out_time' => '17:00:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.0,
                'status' => 'rejected'
            ],
            [
                'employee_id' => 4,
                'work_date' => '2024-10-01',
                'clock_in_time' => '08:45:00',
                'clock_out_time' => '17:15:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.5,
                'status' => 'approved'
            ],
            [
                'employee_id' => 4,
                'work_date' => '2024-09-30',
                'clock_in_time' => '09:15:00',
                'clock_out_time' => '18:00:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.75,
                'status' => 'pending'
            ],
            [
                'employee_id' => 5,
                'work_date' => '2024-10-01',
                'clock_in_time' => '09:00:00',
                'clock_out_time' => '17:30:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.5,
                'status' => 'pending'
            ],
            [
                'employee_id' => 5,
                'work_date' => '2024-09-30',
                'clock_in_time' => '08:30:00',
                'clock_out_time' => '17:00:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.5,
                'status' => 'approved'
            ]
        ];

        DB::table('time_entries')->insert($timesheetData);
        $newCount = DB::table('time_entries')->count();
        
        return "Successfully populated timesheet data! Total entries: {$newCount}";
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
})->name('populate.timesheets');

// Admin Profile Management Routes (Super Admin and Admin access)
Route::middleware(['auth'])->prefix('admin/profile')->name('admin.profile.')->group(function () {
    // Profile management
    Route::get('/', [AdminProfileController::class, 'index'])->name('index');
    Route::get('/edit', [AdminProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [AdminProfileController::class, 'update'])->name('update');
    
    // Password management
    Route::get('/change-password', [AdminProfileController::class, 'showChangePasswordForm'])->name('change-password');
    Route::put('/update-password', [AdminProfileController::class, 'updatePassword'])->name('update-password');
    
    // Admin management (Super Admin only)
    Route::get('/manage-admins', [AdminProfileController::class, 'manageAdmins'])->name('manage-admins');
    Route::post('/create-admin', [AdminProfileController::class, 'createAdmin'])->name('create-admin');
    Route::put('/admins/{id}', [AdminProfileController::class, 'updateAdmin'])->name('update-admin');
    Route::delete('/admins/{id}', [AdminProfileController::class, 'deleteAdmin'])->name('delete-admin');
});

// Removed duplicate routes - using the Web methods defined above
