<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TimeAttendanceController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ClaimControllerFixed;
use App\Http\Controllers\ClaimControllerSimple;
use App\Http\Controllers\ClaimsReimbursementController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeesController;
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
use App\Http\Controllers\SystemDebugController;
use App\Http\Controllers\DataSeederController;
use App\Http\Controllers\SystemTestController;
use App\Http\Controllers\SystemMaintenanceController;
use App\Http\Controllers\SystemViewController;

// New MVC Management Controllers
use App\Http\Controllers\EmployeeManagementController;
use App\Http\Controllers\ShiftManagementController;
use App\Http\Controllers\AttendanceManagementController;
use App\Http\Controllers\LeaveManagementController;
use App\Http\Controllers\ClaimManagementController;
use App\Http\Controllers\TimesheetManagementController;

// Registration routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.store');

// Authentication routes for admin portal (uses 'users' table)
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
Route::get('/api/admin/current-user', [AuthController::class, 'getCurrentUser'])->name('admin.current.user');

// Authentication routes for employee portal/ESS (uses 'employees' table)
Route::get('/employee/login', [EmployeeAuthController::class, 'showLoginForm'])->name('employee.login');
Route::post('/employee/login', [EmployeeAuthController::class, 'login'])->name('employee.login.submit');
Route::post('/employee/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');
Route::get('/employee/register', [EmployeeAuthController::class, 'showRegistrationForm'])->name('employee.register');
Route::post('/employee/register', [EmployeeAuthController::class, 'register'])->name('employee.register.submit');
Route::get('/api/employee/current-user', [EmployeeAuthController::class, 'getCurrentEmployee'])->name('employee.current.user');

// Direct access to admin_dashboard view (for /admin_dashboard URL) - uses 'web' guard (users table)
Route::get('/admin_dashboard', [DashboardController::class, 'index'])->middleware('auth:web')->name('admin.dashboard');

// Employee dashboard/ESS portal - uses 'employee' guard (employees table)
Route::get('/employee/dashboard', [App\Http\Controllers\EmployeeESSController::class, 'dashboard'])->middleware('auth:employee')->name('employee.dashboard');

// Landing page route
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Portal selection route
Route::get('/portal-selection', [SystemViewController::class, 'portalSelection'])->name('portal.selection');

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
Route::get('/test-modal', [SystemViewController::class, 'testModal'])->name('test.modal');

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

    Route::get('/claims-reimbursement', [ClaimsReimbursementController::class, 'index'])->name('claims-reimbursement');
    
    // Claims CRUD Routes - Using Proper MVC Controller
    Route::post('/claims/store', [ClaimsReimbursementController::class, 'store'])->name('claims.store.simple');
    Route::patch('/claims/{id}/approve', [ClaimsReimbursementController::class, 'approve'])->name('claims.approve');
    Route::patch('/claims/{id}/reject', [ClaimsReimbursementController::class, 'reject'])->name('claims.reject');
    Route::patch('/claims/{id}/pay', [ClaimsReimbursementController::class, 'markAsPaid'])->name('claims.pay');
    
    // Claim Types CRUD Routes - Using Proper MVC Controller
    Route::post('/claim-types/store', [ClaimsReimbursementController::class, 'storeClaimType'])->name('claim-types.store');
    Route::delete('/claim-types/{id}', [ClaimsReimbursementController::class, 'deleteClaimType'])->name('claim-types.delete');
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
    Route::get('/test-employees', [SystemDebugController::class, 'debugEmployees']);
    
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
    
    // Fix negative attendance hours route
    Route::post('/fix-negative-attendance-hours', [TimesheetController::class, 'fixNegativeAttendanceHours'])->name('fix.negative.attendance.hours');
    Route::post('/sync-attendance-to-timesheets', [TimesheetController::class, 'syncAttendanceToTimesheets'])->name('sync.attendance.timesheets');
    
    // Debug route to check attendance table structure
    Route::get('/debug-attendance-structure', [SystemDebugController::class, 'debugAttendanceStructure']);
    
    // Quick fix route to convert negative hours to positive
    Route::get('/quick-fix-negative-hours', [SystemMaintenanceController::class, 'quickFixNegativeHours']);
    
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

    // OLD Employee Management Routes - REMOVED (replaced by new MVC structure below)

    Route::get('/database-test', [SystemViewController::class, 'databaseTest'])->name('database-test');
    
    // Profile routes
    Route::get('/profile', [SystemViewController::class, 'profile'])->name('profile.edit');
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
Route::get('/test-claims', [SystemTestController::class, 'testClaims']);

// Direct database test route
Route::get('/test-db', [SystemTestController::class, 'testDb']);

// Dashboard data population route (for testing)
Route::get('/populate-dashboard', [DataSeederController::class, 'populateDashboard'])->name('populate.dashboard');

// Debug route to check shift data
Route::get('/debug-shifts', [SystemDebugController::class, 'debugShifts'])->name('debug.shifts');

// Test route to create sample shift data
Route::get('/create-sample-shifts', [DataSeederController::class, 'createSampleShifts'])->name('create.sample.shifts');

// Test route to verify shift edit endpoint
Route::get('/test-shift-edit/{id}', [SystemTestController::class, 'testShiftEdit'])->name('test.shift.edit');

// Debug route to check calendar shifts data structure
Route::get('/debug-calendar-shifts', [SystemDebugController::class, 'debugCalendarShifts'])->name('debug.calendar.shifts');

// Debug route to check actual database shifts
Route::get('/debug-db-shifts', [SystemDebugController::class, 'debugDbShifts'])->name('debug.db.shifts');

// Route to fix invalid shift IDs in database
Route::get('/fix-invalid-shifts', [SystemMaintenanceController::class, 'fixInvalidShifts'])->name('fix.invalid.shifts');

// Verification route to check if shifts are properly fixed
Route::get('/verify-shifts-fix', [SystemMaintenanceController::class, 'verifyShiftsFix'])->name('verify.shifts.fix');

// Test route for shift request approval
Route::get('/test-shift-approval/{id}', [SystemTestController::class, 'testShiftApproval'])->name('test.shift.approval');

// Employee setup and testing routes
Route::get('/setup-employees', [SystemMaintenanceController::class, 'setupEmployees'])->name('setup.employees');

Route::get('/test-employee-add', [SystemTestController::class, 'testEmployeeAdd'])->name('test.employee.add');

// Debug route to check attendance data
Route::get('/debug-attendance', [SystemDebugController::class, 'debugAttendance'])->name('debug.attendance');

// Simple test route for HR Dashboard data
Route::get('/test-hr-data', [SystemDebugController::class, 'testHrData'])->name('test.hr.data');

// Test HR Dashboard controller method directly
Route::get('/test-recent-entries', [SystemTestController::class, 'testRecentEntries'])->name('test.recent.entries');

// Temporary route to populate timesheet data
Route::get('/populate-timesheets', [DataSeederController::class, 'populateTimesheets'])->name('populate.timesheets');

// Temporary route to populate attendance data
Route::get('/populate-attendance', [DataSeederController::class, 'populateAttendance'])->name('populate.attendance');

// Create sample attendance data for ESS testing
Route::get('/create-sample-attendance', [DataSeederController::class, 'createSampleAttendance'])->name('create.sample.attendance');

// Test route to verify ESS dashboard attendance logs
Route::get('/test-ess-attendance', function() {
    try {
        // Clear view cache
        \Artisan::call('view:clear');
        
        // Get an employee for testing
        $employee = \DB::table('employees')->first();
        if (!$employee) {
            return "No employees found. Please create employees first.";
        }
        
        // Get attendance logs
        $attendanceLogs = \App\Models\Attendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();
            
        $output = "<h3>ESS Attendance Logs Test</h3>";
        $output .= "<p>Employee: {$employee->first_name} {$employee->last_name}</p>";
        $output .= "<p>Attendance Records Found: " . $attendanceLogs->count() . "</p>";
        
        if ($attendanceLogs->count() > 0) {
            $output .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            $output .= "<tr><th>Date</th><th>Clock In</th><th>Clock Out</th><th>Hours</th><th>Status</th></tr>";
            
            foreach ($attendanceLogs as $log) {
                $clockIn = $log->clock_in_time ? $log->clock_in_time->format('h:i A') : '--';
                $clockOut = $log->clock_out_time ? $log->clock_out_time->format('h:i A') : '--';
                $hours = $log->total_hours > 0 ? number_format($log->total_hours, 2) : '0.00';
                
                $output .= "<tr>";
                $output .= "<td>" . $log->date->format('M d, Y') . "</td>";
                $output .= "<td>{$clockIn}</td>";
                $output .= "<td>{$clockOut}</td>";
                $output .= "<td>{$hours}</td>";
                $output .= "<td>{$log->status}</td>";
                $output .= "</tr>";
            }
            $output .= "</table>";
        } else {
            $output .= "<p>No attendance records found. <a href='/create-sample-attendance'>Create sample data</a></p>";
        }
        
        $output .= "<br><p><a href='/employee/login'>Test ESS Dashboard</a></p>";
        
        return $output;
        
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage() . "<br>" . $e->getTraceAsString();
    }
})->name('test.ess.attendance');

// Simple test route to check view compilation
Route::get('/test-view-compilation', function() {
    try {
        // Clear view cache first
        \Artisan::call('view:clear');
        
        // Test basic view compilation with minimal data
        $employee = (object) [
            'id' => 1,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com'
        ];
        
        $attendanceLogs = collect([]);
        $notifications = collect([]);
        $upcomingTrainingsList = [];
        $recentRequests = [];
        
        // Basic stats
        $stats = [
            'pendingLeaveRequests' => 0,
            'attendanceRate' => 0,
            'latestPayslip' => 0,
            'payslipMonth' => 'N/A',
            'upcomingTrainings' => 0,
            'competencyGoalsAchieved' => 0,
            'totalCompetencies' => 1,
            'competencyProgress' => 0,
            'completedTrainings' => 0,
            'totalTrainings' => 1,
            'trainingCompletionRate' => 0
        ];
        
        // Try to render the view
        $view = view('employee_ess_modules.employee_dashboard', compact(
            'employee',
            'notifications',
            'upcomingTrainingsList',
            'recentRequests',
            'attendanceLogs'
        ) + $stats);
        
        return "✅ View compilation successful! The ESS dashboard should work now.<br><br>" .
               "<a href='/employee/login'>Test Employee Login</a><br>" .
               "<a href='/create-sample-attendance'>Create Sample Attendance Data</a>";
        
    } catch (\Exception $e) {
        return "❌ View compilation failed: " . $e->getMessage() . "<br><br>" .
               "Stack trace:<br>" . nl2br($e->getTraceAsString());
    }
})->name('test.view.compilation');

// Simple debug route to check Blade compilation
Route::get('/debug-blade-syntax', function() {
    try {
        // Clear view cache
        \Artisan::call('view:clear');
        
        // Create minimal test data
        $employee = new \stdClass();
        $employee->id = 1;
        $employee->first_name = 'Test';
        $employee->last_name = 'User';
        $employee->email = 'test@example.com';
        $employee->profile_picture = null;
        $employee->employee_id = 'EMP001';
        
        $attendanceLogs = collect([]);
        $notifications = collect([]);
        $upcomingTrainingsList = [];
        $recentRequests = [];
        
        // Basic stats
        $pendingLeaveRequests = 0;
        $attendanceRate = 95;
        $pendingTimesheets = 0;
        $pendingClaims = 0;
        $latestPayslip = 0;
        $payslipMonth = 'N/A';
        $upcomingTrainings = 0;
        $competencyGoalsAchieved = 0;
        $totalCompetencies = 1;
        $competencyProgress = 0;
        $completedTrainings = 0;
        $totalTrainings = 1;
        $trainingCompletionRate = 0;
        
        // Test view compilation without Auth
        return view('employee_ess_modules.employee_dashboard', [
            'employee' => $employee,
            'attendanceLogs' => $attendanceLogs,
            'notifications' => $notifications,
            'upcomingTrainingsList' => $upcomingTrainingsList,
            'recentRequests' => $recentRequests,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'attendanceRate' => $attendanceRate,
            'pendingTimesheets' => $pendingTimesheets,
            'pendingClaims' => $pendingClaims,
            'latestPayslip' => $latestPayslip,
            'payslipMonth' => $payslipMonth,
            'upcomingTrainings' => $upcomingTrainings,
            'competencyGoalsAchieved' => $competencyGoalsAchieved,
            'totalCompetencies' => $totalCompetencies,
            'competencyProgress' => $competencyProgress,
            'completedTrainings' => $completedTrainings,
            'totalTrainings' => $totalTrainings,
            'trainingCompletionRate' => $trainingCompletionRate
        ]);
        
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage() . "<br><br>" .
               "File: " . $e->getFile() . "<br>" .
               "Line: " . $e->getLine() . "<br><br>" .
               "Stack trace:<br>" . nl2br($e->getTraceAsString());
    }
})->name('debug.blade.syntax');

// Combined route to populate both timesheet and attendance data
Route::get('/populate-all-data', [DataSeederController::class, 'populateAllData'])->name('populate.all.data');

// Debug route to test attendance creation
Route::get('/test-attendance', [SystemTestController::class, 'testAttendance'])->name('test.attendance');

// Debug route to check table structure
Route::get('/check-table-structure', [SystemDebugController::class, 'checkTableStructure'])->name('check.table.structure');

// Simple fix route to populate data after fixing ID columns
Route::get('/fix-and-populate', [DataSeederController::class, 'fixAndPopulate'])->name('fix.and.populate');

// Fix shift calendar data issues
Route::get('/fix-shift-calendar', [SystemMaintenanceController::class, 'fixShiftCalendarData'])->name('fix.shift.calendar');

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
Route::get('/test-ai-timesheet', [SystemTestController::class, 'testAiTimesheet']);

// Test AI Generation Route
Route::get('/test-ai-generation/{employeeId}', [SystemTestController::class, 'testAiGeneration']);

// AI Timesheet Generation Web Routes (for AJAX calls from authenticated pages)
Route::middleware(['auth'])->group(function () {
    Route::prefix('api/ai-timesheets')->group(function () {
        Route::get('/test/{employeeId}', [TimesheetController::class, 'testAIGeneration']);
        Route::post('/generate/{employeeId}', [TimesheetController::class, 'generateAITimesheet']);
        Route::post('/generate-all', [TimesheetController::class, 'generateAllAITimesheets']);
        Route::get('/view/{employeeId}', [TimesheetController::class, 'getAITimesheet']);
        Route::post('/save', [TimesheetController::class, 'saveAITimesheet']);
        Route::get('/pending', [TimesheetController::class, 'getPendingTimesheets']);
        Route::get('/saved/{id}', [TimesheetController::class, 'getSavedTimesheet']);
        Route::post('/approve/{id}', [TimesheetController::class, 'approveAITimesheet']);
        Route::post('/reject/{id}', [TimesheetController::class, 'rejectAITimesheet']);
        Route::post('/send-to-payroll/{id}', [TimesheetController::class, 'sendToPayroll']);
        Route::get('/statistics', [TimesheetController::class, 'getTimesheetStatistics']);
    });
});

// Simple test route to verify controller works
Route::get('/test-ai/{employeeId}', [SystemTestController::class, 'testAi'])->middleware('auth');

// Test save functionality
Route::post('/test-save', [SystemTestController::class, 'testSave'])->middleware('auth');

// Removed duplicate routes - using the Web methods defined above

// ===== ADMIN MANAGEMENT ROUTES (uses 'web' guard - 'users' table) =====
Route::middleware(['auth:web'])->group(function () {
    
    // Employee Management Routes - Using Proper MVC Controller
    Route::get('/employees', [EmployeesController::class, 'index'])->name('employees.index');
    Route::post('/employees', [EmployeesController::class, 'store'])->name('employees.store');
    Route::get('/employees/{id}/view', [EmployeesController::class, 'view'])->name('employees.view');
    Route::put('/employees/{id}', [EmployeesController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{id}', [EmployeesController::class, 'destroy'])->name('employees.destroy');
    
    // Shift Management Routes (specific routes BEFORE resource routes)
    Route::get('admin/shifts/calendar/data', [ShiftManagementController::class, 'getCalendarShifts'])->name('admin.shifts.calendar.data');
    Route::get('admin/shifts/stats', [ShiftManagementController::class, 'getStats'])->name('admin.shifts.stats');
    Route::resource('admin/shifts', ShiftManagementController::class)->names([
        'index' => 'admin.shifts.index',
        'create' => 'admin.shifts.create', 
        'store' => 'admin.shifts.store',
        'show' => 'admin.shifts.show',
        'edit' => 'admin.shifts.edit',
        'update' => 'admin.shifts.update',
        'destroy' => 'admin.shifts.destroy'
    ]);
    
    // Attendance Management Routes (specific routes BEFORE resource routes)
    Route::post('attendance/clock-in', [AttendanceManagementController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('attendance/clock-out', [AttendanceManagementController::class, 'clockOut'])->name('attendance.clock-out');
    Route::get('attendance/stats', [AttendanceManagementController::class, 'getStats'])->name('attendance.stats');
    Route::resource('attendance', AttendanceManagementController::class);
    
    // Leave Management Routes (specific routes BEFORE resource routes)
    Route::post('leave/{leave}/approve', [LeaveManagementController::class, 'approve'])->name('leave.approve');
    Route::post('leave/{leave}/reject', [LeaveManagementController::class, 'reject'])->name('leave.reject');
    Route::get('leave/stats', [LeaveManagementController::class, 'getStats'])->name('leave.stats');
    Route::resource('leave', LeaveManagementController::class);
    
    // Claims Management Routes (specific routes BEFORE resource routes)
    Route::post('claims/{claim}/approve', [ClaimManagementController::class, 'approve'])->name('claims.approve');
    Route::post('claims/{claim}/reject', [ClaimManagementController::class, 'reject'])->name('claims.reject');
    Route::post('claims/{claim}/mark-paid', [ClaimManagementController::class, 'markAsPaid'])->name('claims.mark-paid');
    Route::get('claims/stats', [ClaimManagementController::class, 'getStats'])->name('claims.stats');
    Route::resource('claims', ClaimManagementController::class);
    
    // Timesheet Management Routes (specific routes BEFORE resource routes)
    Route::get('timesheets/stats', [TimesheetManagementController::class, 'getStats'])->name('timesheets.stats');
    Route::resource('timesheets', TimesheetManagementController::class);
    
});

// ===== EMPLOYEE ESS ROUTES (uses 'employee' guard - 'employees' table) =====
Route::middleware(['auth:employee'])->prefix('employee')->group(function () {
    
    // Employee Self-Service Dashboard (defined above as /employee/dashboard)
    
    // Employee Clock-in/Clock-out (ESS)
    Route::post('/clock-in', [EmployeeESSController::class, 'clockIn'])->name('employee.clock.in');
    Route::post('/clock-out', [EmployeeESSController::class, 'clockOut'])->name('employee.clock.out');
    Route::get('/clock-status', [EmployeeESSController::class, 'getClockStatus'])->name('employee.clock.status');
    Route::get('/attendance-log', [EmployeeESSController::class, 'getAttendanceLog'])->name('employee.attendance.log');
    
    // Employee Profile Management
    Route::get('/profile', [EmployeeESSController::class, 'showProfile'])->name('employee.profile');
    Route::put('/profile', [EmployeeESSController::class, 'updateProfile'])->name('employee.profile.update');
    
    // Employee Leave Requests
    Route::get('/leave-requests', [EmployeeESSController::class, 'showLeaveRequests'])->name('employee.leave.requests');
    Route::post('/leave-requests', [EmployeeESSController::class, 'submitLeaveRequest'])->name('employee.leave.submit');
    
    // Employee Shift Requests
    Route::get('/shift-requests', [EmployeeESSController::class, 'showShiftRequests'])->name('employee.shift.requests');
    Route::post('/shift-requests', [EmployeeESSController::class, 'submitShiftRequest'])->name('employee.shift.submit');
    
    // Employee Claims/Reimbursements
    Route::get('/claims', [EmployeeESSController::class, 'showClaims'])->name('employee.claims');
    Route::post('/claims', [EmployeeESSController::class, 'submitClaim'])->name('employee.claims.submit');
    
    // Employee Timesheets View
    Route::get('/timesheets', [EmployeeESSController::class, 'showTimesheets'])->name('employee.timesheets');
    
});
