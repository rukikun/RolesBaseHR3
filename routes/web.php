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
use Illuminate\Support\Facades\DB;

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
    Route::post('/shifts/api/store', [ShiftController::class, 'store'])->name('shifts.api.store');
    
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

// Duplicate shift routes removed - already defined in middleware group above

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

// Test route to create sample shift data
Route::get('/create-sample-shifts', function() {
    try {
        // Check if we have employees and shift types
        $employees = DB::table('employees')->where('status', 'active')->limit(3)->get();
        $shiftTypes = DB::table('shift_types')->where('is_active', 1)->limit(3)->get();
        
        if ($employees->isEmpty() || $shiftTypes->isEmpty()) {
            return response()->json([
                'error' => 'Need employees and shift types first',
                'employees_count' => $employees->count(),
                'shift_types_count' => $shiftTypes->count()
            ]);
        }
        
        // Create sample shifts for the next few days
        $dates = [
            Carbon\Carbon::today()->format('Y-m-d'),
            Carbon\Carbon::today()->addDay()->format('Y-m-d'),
            Carbon\Carbon::today()->addDays(2)->format('Y-m-d'),
        ];
        
        $created = 0;
        foreach ($employees as $employee) {
            foreach ($dates as $date) {
                $shiftType = $shiftTypes->random();
                
                // Check if shift already exists
                $exists = DB::table('shifts')
                    ->where('employee_id', $employee->id)
                    ->where('shift_date', $date)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('shifts')->insert([
                        'employee_id' => $employee->id,
                        'shift_type_id' => $shiftType->id,
                        'shift_date' => $date,
                        'start_time' => $shiftType->default_start_time ?? '09:00:00',
                        'end_time' => $shiftType->default_end_time ?? '17:00:00',
                        'location' => 'Main Office',
                        'notes' => 'Sample shift for testing',
                        'status' => 'scheduled',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $created++;
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Created {$created} sample shifts",
            'employees_used' => $employees->count(),
            'shift_types_available' => $shiftTypes->count()
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
})->name('create.sample.shifts');

// Test route to verify shift edit endpoint
Route::get('/test-shift-edit/{id}', function($id) {
    try {
        $controller = new \App\Http\Controllers\ShiftController();
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
})->name('test.shift.edit');

// Debug route to check calendar shifts data structure
Route::get('/debug-calendar-shifts', function() {
    try {
        $controller = new \App\Http\Controllers\ShiftController();
        $request = request();
        $request->merge(['month' => now()->format('Y-m')]);
        
        // Call the controller method to get the data
        $response = $controller->index();
        $data = $response->getData();
        
        return response()->json([
            'calendar_shifts_structure' => $data['calendarShifts'] ?? 'Not found',
            'sample_shift' => isset($data['calendarShifts']) && !empty($data['calendarShifts']) ? 
                array_values($data['calendarShifts'])[0][0] ?? 'No shifts in first date' : 'No calendar shifts',
            'shifts_count' => isset($data['calendarShifts']) ? count($data['calendarShifts']) : 0
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('debug.calendar.shifts');

// Debug route to check actual database shifts
Route::get('/debug-db-shifts', function() {
    try {
        $shifts = DB::table('shifts')
            ->leftJoin('employees', 'shifts.employee_id', '=', 'employees.id')
            ->leftJoin('shift_types', 'shifts.shift_type_id', '=', 'shift_types.id')
            ->select(
                'shifts.*',
                'employees.first_name',
                'employees.last_name',
                'shift_types.name as shift_type_name'
            )
            ->orderBy('shifts.shift_date', 'desc')
            ->limit(10)
            ->get();
            
        return response()->json([
            'total_shifts' => DB::table('shifts')->count(),
            'shifts_with_zero_id' => DB::table('shifts')->where('id', 0)->count(),
            'shifts_with_null_id' => DB::table('shifts')->whereNull('id')->count(),
            'recent_shifts' => $shifts,
            'id_range' => [
                'min' => DB::table('shifts')->min('id'),
                'max' => DB::table('shifts')->max('id')
            ]
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
})->name('debug.db.shifts');

// Route to fix invalid shift IDs in database
Route::get('/fix-invalid-shifts', function() {
    try {
        $results = [];
        
        // Find shifts with invalid IDs
        $invalidShifts = DB::table('shifts')->where('id', '<=', 0)->get();
        $results['invalid_shifts_found'] = $invalidShifts->count();
        
        if ($invalidShifts->count() > 0) {
            $results['invalid_shifts'] = $invalidShifts->toArray();
            
            // Delete shifts with invalid IDs
            $deleted = DB::table('shifts')->where('id', '<=', 0)->delete();
            $results['deleted_invalid_shifts'] = $deleted;
        }
        
        // Check for any shifts that might have been created incorrectly
        $duplicateShifts = DB::table('shifts')
            ->select('employee_id', 'shift_date', DB::raw('COUNT(*) as count'))
            ->groupBy('employee_id', 'shift_date')
            ->having('count', '>', 1)
            ->get();
            
        $results['duplicate_shifts'] = $duplicateShifts->count();
        
        // Get current shift statistics
        $results['current_stats'] = [
            'total_shifts' => DB::table('shifts')->count(),
            'valid_shifts' => DB::table('shifts')->where('id', '>', 0)->count(),
            'min_id' => DB::table('shifts')->min('id'),
            'max_id' => DB::table('shifts')->max('id')
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Invalid shifts cleanup completed',
            'results' => $results
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
})->name('fix.invalid.shifts');

// Verification route to check if shifts are properly fixed
Route::get('/verify-shifts-fix', function() {
    try {
        $stats = [
            'total_shifts' => DB::table('shifts')->count(),
            'valid_shifts' => DB::table('shifts')->where('id', '>', 0)->count(),
            'invalid_shifts' => DB::table('shifts')->where('id', '<=', 0)->count(),
            'id_range' => [
                'min' => DB::table('shifts')->min('id'),
                'max' => DB::table('shifts')->max('id')
            ],
            'auto_increment_info' => DB::select("SHOW TABLE STATUS LIKE 'shifts'")[0] ?? null
        ];
        
        $message = $stats['invalid_shifts'] > 0 ? 
            'WARNING: Still have invalid shifts!' : 
            'SUCCESS: All shifts have valid IDs!';
            
        return response()->json([
            'status' => $stats['invalid_shifts'] > 0 ? 'warning' : 'success',
            'message' => $message,
            'statistics' => $stats
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
})->name('verify.shifts.fix');

// Test route for shift request approval
Route::get('/test-shift-approval/{id}', function($id) {
    try {
        $shiftRequest = DB::table('shift_requests')->where('id', $id)->first();
        
        if (!$shiftRequest) {
            return response()->json([
                'error' => 'Shift request not found'
            ]);
        }
        
        // Simulate approval process
        $controller = new \App\Http\Controllers\ShiftRequestController();
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
})->name('test.shift.approval');

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

// Temporary route to populate attendance data
Route::get('/populate-attendance', function() {
    try {
        $count = DB::table('attendances')->count();
        if ($count > 0) {
            return "Attendance data already exists. Count: {$count}";
        }
        
        // Get employees
        $employees = DB::table('employees')->limit(5)->get();
        
        if ($employees->isEmpty()) {
            return "No employees found. Please ensure employees exist first.";
        }
        
        $attendanceData = [];
        $dates = [
            '2024-10-01',
            '2024-09-30', 
            '2024-09-29',
            '2024-09-28',
            '2024-09-27'
        ];
        
        foreach ($employees as $employee) {
            foreach ($dates as $date) {
                $clockIn = Carbon::parse($date . ' ' . sprintf('%02d:%02d:00', rand(8, 9), rand(0, 59)));
                $clockOut = $clockIn->copy()->addHours(8)->addMinutes(rand(0, 60));
                $totalHours = $clockOut->diffInHours($clockIn, true);
                $overtimeHours = max(0, $totalHours - 8);
                
                $attendanceData[] = [
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'clock_in_time' => $clockIn,
                    'clock_out_time' => $clockOut,
                    'total_hours' => round($totalHours, 2),
                    'overtime_hours' => round($overtimeHours, 2),
                    'status' => 'clocked_out',
                    'location' => 'ESS Portal',
                    'ip_address' => '127.0.0.1',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        DB::table('attendances')->insert($attendanceData);
        $newCount = DB::table('attendances')->count();
        
        return "Successfully populated attendance data! Total entries: {$newCount}";
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
})->name('populate.attendance');

// Combined route to populate both timesheet and attendance data
Route::get('/populate-all-data', function() {
    try {
        $results = [];
        
        // Check existing data
        $timesheetCount = DB::table('time_entries')->count();
        $attendanceCount = DB::table('attendances')->count();
        
        $results[] = "Current data - Timesheets: {$timesheetCount}, Attendances: {$attendanceCount}";
        
        // Get employees
        $employees = DB::table('employees')->limit(5)->get();
        
        if ($employees->isEmpty()) {
            return "No employees found. Please ensure employees exist first.";
        }
        
        $dates = [
            '2024-10-01',
            '2024-09-30', 
            '2024-09-29',
            '2024-09-28',
            '2024-09-27'
        ];
        
        $timesheetData = [];
        $attendanceData = [];
        
        foreach ($employees as $employee) {
            foreach ($dates as $date) {
                // Generate realistic times
                $clockInHour = rand(8, 9);
                $clockInMinute = rand(0, 59);
                $clockInTime = sprintf('%02d:%02d:00', $clockInHour, $clockInMinute);
                
                $clockInDateTime = Carbon::parse($date . ' ' . $clockInTime);
                $clockOutDateTime = $clockInDateTime->copy()->addHours(8)->addMinutes(rand(0, 60));
                
                $totalHours = $clockOutDateTime->diffInHours($clockInDateTime, true);
                $regularHours = min(8, $totalHours);
                $overtimeHours = max(0, $totalHours - 8);
                
                // Create timesheet entry
                if ($timesheetCount == 0) {
                    $timesheetData[] = [
                        'employee_id' => $employee->id,
                        'work_date' => $date,
                        'clock_in_time' => $clockInTime,
                        'clock_out_time' => $clockOutDateTime->format('H:i:s'),
                        'hours_worked' => round($regularHours, 2),
                        'overtime_hours' => round($overtimeHours, 2),
                        'break_duration' => 1.0,
                        'description' => 'Regular work day',
                        'status' => rand(0, 1) ? 'approved' : 'pending',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                
                // Create attendance entry
                if ($attendanceCount == 0) {
                    $attendanceData[] = [
                        'employee_id' => $employee->id,
                        'date' => $date,
                        'clock_in_time' => $clockInDateTime,
                        'clock_out_time' => $clockOutDateTime,
                        'total_hours' => round($totalHours, 2),
                        'overtime_hours' => round($overtimeHours, 2),
                        'status' => 'clocked_out',
                        'location' => 'ESS Portal',
                        'ip_address' => '127.0.0.1',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
        }
        
        // Insert data
        if (!empty($timesheetData)) {
            DB::table('time_entries')->insert($timesheetData);
            $results[] = "Inserted " . count($timesheetData) . " timesheet entries";
        }
        
        if (!empty($attendanceData)) {
            DB::table('attendances')->insert($attendanceData);
            $results[] = "Inserted " . count($attendanceData) . " attendance entries";
        }
        
        // Final counts
        $finalTimesheetCount = DB::table('time_entries')->count();
        $finalAttendanceCount = DB::table('attendances')->count();
        
        $results[] = "Final data - Timesheets: {$finalTimesheetCount}, Attendances: {$finalAttendanceCount}";
        
        return implode('<br>', $results);
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
})->name('populate.all.data');

// Debug route to test attendance creation
Route::get('/test-attendance', function() {
    try {
        // Test creating attendance record directly
        $employee = DB::table('employees')->first();
        
        if (!$employee) {
            return "No employees found";
        }
        
        $today = Carbon::today();
        $clockInTime = Carbon::now('Asia/Manila');
        
        // Try using Attendance model
        $attendance = new App\Models\Attendance();
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
})->name('test.attendance');

// Debug route to check table structure
Route::get('/check-table-structure', function() {
    try {
        $results = [];
        
        // Check if tables exist
        $tables = ['employees', 'attendances', 'time_entries'];
        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                $results[] = "Table '{$table}': {$count} records";
            } catch (Exception $e) {
                $results[] = "Table '{$table}': ERROR - " . $e->getMessage();
            }
        }
        
        // Check attendances table structure
        try {
            $columns = DB::select("DESCRIBE attendances");
            $results[] = "<br><strong>Attendances table structure:</strong>";
            foreach ($columns as $column) {
                $results[] = "- {$column->Field}: {$column->Type} " . 
                           ($column->Null === 'NO' ? 'NOT NULL' : 'NULL') . 
                           ($column->Key ? " ({$column->Key})" : '') .
                           ($column->Default ? " DEFAULT {$column->Default}" : '') .
                           ($column->Extra ? " {$column->Extra}" : '');
            }
        } catch (Exception $e) {
            $results[] = "Error checking table structure: " . $e->getMessage();
        }
        
        return implode('<br>', $results);
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
})->name('check.table.structure');

// Simple fix route to populate data after fixing ID columns
Route::get('/fix-and-populate', function() {
    try {
        $results = [];
        
        // Fix both table ID columns
        try {
            DB::statement('ALTER TABLE attendances MODIFY id BIGINT UNSIGNED AUTO_INCREMENT');
            $results[] = "✅ Fixed attendances ID column";
        } catch (Exception $e) {
            $results[] = "⚠️ Attendances ID: " . $e->getMessage();
        }
        
        try {
            DB::statement('ALTER TABLE time_entries MODIFY id BIGINT UNSIGNED AUTO_INCREMENT');
            $results[] = "✅ Fixed time_entries ID column";
        } catch (Exception $e) {
            $results[] = "⚠️ Time entries ID: " . $e->getMessage();
        }
        
        // Create sample data
        $employees = DB::table('employees')->limit(3)->get();
        
        if ($employees->isEmpty()) {
            $results[] = "❌ No employees found";
            return implode('<br>', $results);
        }
        
        $attendanceCount = 0;
        $timesheetCount = 0;
        
        foreach ($employees as $employee) {
            // Create attendance for today
            try {
                DB::table('attendances')->insert([
                    'employee_id' => $employee->id,
                    'date' => Carbon::today(),
                    'clock_in_time' => Carbon::now()->subHours(2),
                    'clock_out_time' => Carbon::now(),
                    'total_hours' => 8.0,
                    'overtime_hours' => 0.0,
                    'status' => 'clocked_out',
                    'location' => 'ESS Portal',
                    'ip_address' => '127.0.0.1',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $attendanceCount++;
            } catch (Exception $e) {
                $results[] = "⚠️ Attendance for employee {$employee->id}: " . $e->getMessage();
            }
            
            // Create timesheet entry
            try {
                DB::table('time_entries')->insert([
                    'employee_id' => $employee->id,
                    'work_date' => Carbon::today(),
                    'clock_in_time' => '09:00:00',
                    'clock_out_time' => '17:00:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.0,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $timesheetCount++;
            } catch (Exception $e) {
                $results[] = "⚠️ Timesheet for employee {$employee->id}: " . $e->getMessage();
            }
        }
        
        $results[] = "✅ Created {$attendanceCount} attendance records";
        $results[] = "✅ Created {$timesheetCount} timesheet records";
        $results[] = "<br><strong>🎉 System should now work! Try clock-in/out functionality.</strong>";
        
        return implode('<br>', $results);
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
})->name('fix.and.populate');

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

// AI Timesheet Test Route
Route::get('/test-ai-timesheet', function() {
    return '<h1>🤖 AI Timesheet System Ready!</h1><p><a href="/timesheet-management">Go to Timesheet Management</a></p>';
});

// Test AI Generation Route
Route::get('/test-ai-generation/{employeeId}', function($employeeId) {
    try {
        $employee = \App\Models\Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        
        $aiTimesheet = \App\Models\AIGeneratedTimesheet::generateForEmployee($employeeId);
        
        return response()->json([
            'success' => true,
            'employee' => $employee->first_name . ' ' . $employee->last_name,
            'timesheet_id' => $aiTimesheet->id,
            'weekly_data' => $aiTimesheet->weekly_data,
            'total_hours' => $aiTimesheet->total_hours,
            'overtime_hours' => $aiTimesheet->overtime_hours
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// AI Timesheet Generation Web Routes (for AJAX calls from authenticated pages)
Route::middleware(['auth'])->group(function () {
    Route::prefix('api/ai-timesheets')->group(function () {
        Route::post('/generate/{employeeId}', [TimesheetController::class, 'generateAITimesheet']);
        Route::post('/generate-all', [TimesheetController::class, 'generateAllAITimesheets']);
        Route::get('/view/{employeeId}', [TimesheetController::class, 'getAITimesheet']);
        Route::post('/approve/{id}', [TimesheetController::class, 'approveAITimesheet']);
    });
});

// Removed duplicate routes - using the Web methods defined above
