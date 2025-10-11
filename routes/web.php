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
use App\Http\Controllers\ValidateAttachmentController;
use App\Http\Controllers\PayrollController;
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
use App\Http\Controllers\SettingsController;

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

// Profile picture upload page
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/profile/upload-picture', function() {
        return view('admin.profile.upload-profile-picture');
    })->name('admin.profile.upload-picture');
});

// Profile picture API routes
Route::middleware(['auth'])->group(function () {
    Route::post('/api/profile-picture/upload', [App\Http\Controllers\ProfilePictureController::class, 'upload']);
    Route::delete('/api/profile-picture/delete', [App\Http\Controllers\ProfilePictureController::class, 'delete']);
    Route::get('/api/profile-picture/info', [App\Http\Controllers\ProfilePictureController.class, 'info']);
});

// Authentication routes for admin portal (uses 'users' table)
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::get('/admin/otp-verification', [AuthController::class, 'showOtpForm'])->name('admin.otp.form');
Route::post('/admin/otp-verify', [AuthController::class, 'verifyOtp'])->name('admin.otp.verify');
Route::post('/admin/otp-resend', [AuthController::class, 'resendOtp'])->name('admin.otp.resend');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
Route::get('/api/admin/current-user', [AuthController::class, 'getCurrentUser'])->name('admin.current.user');

// Authentication routes for employee portal/ESS (uses 'employees' table)
Route::get('/employee/login', [EmployeeAuthController::class, 'showLoginForm'])->name('employee.login');
Route::post('/employee/login', [EmployeeAuthController::class, 'login'])->name('employee.login.submit');
Route::post('/employee/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');
Route::get('/employee/register', [EmployeeAuthController::class, 'showRegistrationForm'])->name('employee.register');
Route::post('/employee/register', [EmployeeAuthController::class, 'register'])->name('employee.register.submit');
Route::get('/api/employee/current-user', [EmployeeAuthController::class, 'getCurrentEmployee'])->name('employee.current.user');

// Redirect admin_dashboard to regular dashboard - uses 'employee' guard (employees table)
Route::get('/admin_dashboard', function() {
    return redirect()->route('dashboard');
})->middleware('auth:employee')->name('admin.dashboard');

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

// HR dashboard (protected) - uses employee guard
Route::get('/dashboard', [App\Http\Controllers\HRDashboardController::class, 'index'])->middleware('auth:employee')->name('dashboard');

// Test modal route
Route::get('/test-modal', [SystemViewController::class, 'testModal'])->name('test.modal');

// 2FA Testing routes
Route::get('/test-2fa', function() {
    return view('test.2fa-test');
})->name('test.2fa');


Route::post('/test-send-otp', function(Illuminate\Http\Request $request) {
    try {
        $email = $request->email;
        
        // Generate OTP
        $otpRecord = \App\Models\OtpVerification::generateOtp($email);
        
        // Send email using PHPMailer
        $phpMailer = new \App\Services\PHPMailerService();
        $result = $phpMailer->sendOtpEmail($email, $otpRecord->otp_code, 'Test User');
        
        if (!$result['success']) {
            throw new \Exception($result['message']);
        }
        
        return response()->json(['success' => true, 'message' => 'Test OTP sent successfully using PHPMailer']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
})->name('test.send.otp');

Route::post('/test-clear-otps', function() {
    try {
        $count = \App\Models\OtpVerification::count();
        \App\Models\OtpVerification::truncate();
        return response()->json(['success' => true, 'message' => "Cleared {$count} OTP records"]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
})->name('test.clear.otps');

// Email Testing Routes
Route::get('/test-email', function() {
    return view('test.email-test');
})->name('test.email');

Route::post('/test-basic-email', function(Illuminate\Http\Request $request) {
    try {
        $email = $request->email;
        
        // Send email using PHPMailer
        $phpMailer = new \App\Services\PHPMailerService();
        $result = $phpMailer->sendTestEmail($email, 'HR3 System - PHPMailer Test', 'This is a test email from HR3 System using PHPMailer. If you receive this, your PHPMailer configuration is working correctly!');
        
        if (!$result['success']) {
            throw new \Exception($result['message']);
        }
        
        return response()->json([
            'success' => true, 
            'message' => 'Test email sent successfully using PHPMailer! Check your inbox.',
            'details' => "Email sent to: {$email}\nUsing: PHPMailer\nSMTP Host: " . env('MAIL_HOST')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'message' => 'Failed to send email: ' . $e->getMessage(),
            'details' => "Error Details:\n" . $e->getTraceAsString()
        ]);
    }
})->name('test.basic.email');

Route::get('/test-check-logs', function() {
    try {
        $logPath = storage_path('logs/laravel.log');
        
        if (!file_exists($logPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Log file not found',
                'logs' => 'No log file exists at: ' . $logPath
            ]);
        }
        
        // Get last 50 lines of log file
        $lines = file($logPath);
        $lastLines = array_slice($lines, -50);
        $logContent = implode('', $lastLines);
        
        // Filter for email-related errors
        $emailErrors = [];
        foreach ($lastLines as $line) {
            if (stripos($line, 'mail') !== false || stripos($line, 'smtp') !== false || stripos($line, 'email') !== false) {
                $emailErrors[] = trim($line);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => count($emailErrors) > 0 ? 'Found email-related log entries' : 'No recent email errors found',
            'logs' => count($emailErrors) > 0 ? implode("\n", array_slice($emailErrors, -10)) : 'No email-related errors in recent logs'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error reading logs: ' . $e->getMessage(),
            'logs' => $e->getTraceAsString()
        ]);
    }
})->name('test.check.logs');

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
    return view('attendance.TimeAndAttendance');
})->name('time-attendance');

// HR Module Routes - accessible to any authenticated user
Route::group(['middleware' => function ($request, $next) {
    // Allow access if authenticated with any guard
    if (auth()->guard('web')->check() || auth()->guard('employee')->check()) {
        return $next($request);
    }
    
    // Redirect to employee login as default (more common)
    return redirect()->route('employee.login')->with('message', 'Please login to access timesheet management');
}], function () {

    Route::get('/timesheet-management', [TimesheetController::class, 'index'])->name('timesheet-management');
    
    // Payroll Management Routes
    Route::get('/payroll-management', [PayrollController::class, 'index'])->name('payroll-management');
    Route::post('/payroll/send-to-payroll', [PayrollController::class, 'sendToPayroll'])->name('payroll.send');
    Route::patch('/payroll/{id}/process', [PayrollController::class, 'process'])->name('payroll.process');
    Route::patch('/payroll/{id}/mark-paid', [PayrollController::class, 'markPaid'])->name('payroll.mark-paid');
    Route::delete('/payroll/{id}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
    Route::patch('/payroll/attachment/{id}/process', [PayrollController::class, 'processAttachment'])->name('payroll.process-attachment');
    Route::patch('/payroll/attachment/{id}/mark-paid', [PayrollController::class, 'markAttachmentPaid'])->name('payroll.mark-attachment-paid');
    Route::delete('/payroll/attachment/{id}', [PayrollController::class, 'deleteAttachment'])->name('payroll.delete-attachment');
    
    Route::get('/attendance-management', [AttendanceController::class, 'index'])->name('attendance-management');
    
    Route::get('/leave-management', [LeaveController::class, 'index'])->name('leave-management');

    Route::get('/shift-schedule-management', [ShiftController::class, 'index'])->name('shift-schedule-management');

    Route::get('/claims-reimbursement', [ClaimsReimbursementController::class, 'index'])->name('claims-reimbursement');
    
    // Validate Attachment Routes
    Route::get('/validate-attachment', [ValidateAttachmentController::class, 'index'])->name('validate-attachment');
    Route::patch('/validate-attachment/{id}/validate', [ValidateAttachmentController::class, 'validateAttachment'])->name('validate-attachment.validate');
    Route::patch('/validate-attachment/{id}/payroll', [ValidateAttachmentController::class, 'markForPayroll'])->name('validate-attachment.payroll');
    Route::delete('/validate-attachment/{id}', [ValidateAttachmentController::class, 'destroy'])->name('validate-attachment.delete');
    
    // Settings Routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
    Route::get('/settings/export', [SettingsController::class, 'export'])->name('settings.export');
    Route::post('/settings/import', [SettingsController::class, 'import'])->name('settings.import');
    Route::post('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
    
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
    
    // Test HR auth route
Route::post('/test-hr-auth', function(Illuminate\Http\Request $request) {
    return response()->json(['success' => true, 'message' => 'Route is working', 'data' => $request->all()]);
});

// Create test employee route
Route::get('/create-test-employee', function() {
    $employee = App\Models\Employee::updateOrCreate(
        ['email' => 'admin@test.com'],
        [
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Illuminate\Support\Facades\Hash::make('password123'),
            'position' => 'Admin',
            'status' => 'active'
        ]
    );
    return response()->json(['success' => true, 'message' => 'Test employee created', 'email' => 'admin@test.com', 'password' => 'password123']);
});

// Debug employee authentication route
Route::get('/debug-employee/{email}', function($email) {
    $employee = App\Models\Employee::where('email', $email)->first();
    if (!$employee) {
        return response()->json(['found' => false, 'message' => 'Employee not found']);
    }
    return response()->json([
        'found' => true,
        'email' => $employee->email,
        'position' => $employee->position,
        'role' => $employee->role ?? 'No role',
        'has_password' => !empty($employee->password),
        'authorized' => in_array($employee->position, ['HR Manager', 'System Administrator', 'HR Scheduler', 'Admin', 'HR Administrator'])
    ]);
});

// Check employee positions route
Route::get('/check-positions', function() {
    $employees = App\Models\Employee::select('email', 'position', 'role')->get();
    return response()->json($employees);
});

// Debug route for employees
Route::get('/debug-employees', function() {
    $employees = App\Models\Employee::select('email', 'role')->take(5)->get();
    return response()->json($employees);
});

// Test route for HR authentication system
    Route::get('/test-hr-auth', function() {
        return view('test.hr-auth-test');
    })->name('test.hr.auth');
    
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

    // Attendance routes (moved to employee middleware group below)
    
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

// HR Authentication Route (outside middleware for authentication purposes)
Route::post('/leave/hr-auth', [LeaveController::class, 'hrAuthentication'])->name('leave.hr.auth');

// Timesheet HR Authentication Route
Route::post('/timesheet/hr-auth', [App\Http\Controllers\TimesheetController::class, 'hrAuthentication'])->name('timesheet.hr.auth');

// Claim HR Authentication Route
Route::post('/claim/hr-auth', [App\Http\Controllers\ClaimController::class, 'hrAuthentication'])->name('claim.hr.auth');

// Shift HR Authentication Route
Route::post('/shift/hr-auth', [App\Http\Controllers\ShiftController::class, 'hrAuthentication'])->name('shift.hr.auth');

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

// Test route to create sample claims for testing
Route::get('/create-sample-claims', function() {
    try {
        // Create sample claims directly in database
        $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // First ensure we have claim types
        $stmt = $pdo->query("SELECT COUNT(*) FROM claim_types");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT IGNORE INTO claim_types (name, code, description, max_amount, requires_attachment, auto_approve, is_active) VALUES
                ('Office Supplies', 'OFFICE', 'Office equipment and supplies', 500.00, TRUE, FALSE, TRUE),
                ('Medical Expenses', 'MEDICAL', 'Medical reimbursements', 1500.00, TRUE, FALSE, TRUE)");
        }
        
        // Ensure we have employees
        $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT IGNORE INTO employees (id, first_name, last_name, email, status, created_at, updated_at) VALUES
                (1, 'Jonny', 'Duyanon', 'jonny@example.com', 'active', NOW(), NOW()),
                (2, 'JM', 'Custodio', 'jm@example.com', 'active', NOW(), NOW())");
        }
        
        // Create sample claims with attachments
        $pdo->exec("DELETE FROM claims WHERE id IN (1, 2)"); // Clear existing test data
        $pdo->exec("INSERT INTO claims (id, employee_id, claim_type_id, amount, claim_date, description, receipt_path, status, approved_at, created_at, updated_at) VALUES
            (1, 1, 1, 150.00, '2025-10-08', 'Materials', 'receipts/sample_receipt.svg', 'approved', NOW(), NOW(), NOW()),
            (2, 2, 2, 12.00, '2025-10-06', 'Meal', 'receipts/sample_receipt.svg', 'approved', NOW(), NOW(), NOW())");
            
        return "✅ Sample claims created successfully!<br>" .
               "<a href='/claims-reimbursement'>Go to Claims Management</a><br>" .
               "<a href='/validate-attachment'>Go to Validate Attachment</a>";
               
    } catch (\Exception $e) {
        return "❌ Error creating sample claims: " . $e->getMessage();
    }
})->name('create.sample.claims');

// Test route to approve a claim and check if it shows up in validate attachment
Route::get('/test-approve-claim/{id}', function($id) {
    try {
        // Approve the claim
        $claim = \App\Models\Claim::findOrFail($id);
        $claim->update([
            'status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now()
        ]);
        
        // Get approved claims count
        $approvedCount = \App\Models\Claim::where('status', 'approved')->count();
        
        return "✅ Claim #{$id} approved successfully!<br>" .
               "Total approved claims: {$approvedCount}<br><br>" .
               "<a href='/claims-reimbursement'>Go to Claims Management</a><br>" .
               "<a href='/validate-attachment'>Go to Validate Attachment</a>";
               
    } catch (\Exception $e) {
        return "❌ Error approving claim: " . $e->getMessage();
    }
})->name('test.approve.claim');

// Debug route to check validate attachment data
Route::get('/debug-validate-attachment', function() {
    try {
        $approvedClaims = \App\Models\Claim::with(['employee', 'claimType'])
            ->where('status', 'approved')
            ->get();
            
        $output = "<h3>Debug: Approved Claims for Validate Attachment</h3>";
        $output .= "<p>Total approved claims: " . $approvedClaims->count() . "</p>";
        
        if ($approvedClaims->count() > 0) {
            $output .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            $output .= "<tr><th>ID</th><th>Employee</th><th>Type</th><th>Amount</th><th>Status</th><th>Approved At</th></tr>";
            
            foreach ($approvedClaims as $claim) {
                $employeeName = ($claim->employee->first_name ?? 'Unknown') . ' ' . ($claim->employee->last_name ?? '');
                $claimType = $claim->claimType->name ?? 'Unknown Type';
                $approvedAt = $claim->approved_at ? $claim->approved_at->format('M d, Y H:i') : 'Not set';
                
                $output .= "<tr>";
                $output .= "<td>{$claim->id}</td>";
                $output .= "<td>{$employeeName}</td>";
                $output .= "<td>{$claimType}</td>";
                $output .= "<td>\${$claim->amount}</td>";
                $output .= "<td>{$claim->status}</td>";
                $output .= "<td>{$approvedAt}</td>";
                $output .= "</tr>";
            }
            $output .= "</table>";
        }
        
        $output .= "<br><a href='/validate-attachment'>Go to Validate Attachment Page</a>";
        
        return $output;
        
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage() . "<br>" . $e->getTraceAsString();
    }
})->name('debug.validate.attachment');

// Test complete flow: Claims -> Approve -> Validate Attachment
Route::get('/test-complete-flow', function() {
    try {
        $output = "<h2>🧪 Testing Complete Claims Flow</h2>";
        
        // Step 1: Check current claims
        $pendingClaims = \App\Models\Claim::where('status', 'pending')->count();
        $approvedClaims = \App\Models\Claim::where('status', 'approved')->count();
        
        $output .= "<h3>📊 Current Status:</h3>";
        $output .= "<p>Pending Claims: {$pendingClaims}</p>";
        $output .= "<p>Approved Claims: {$approvedClaims}</p>";
        
        // Step 2: Show links to test pages
        $output .= "<h3>🔗 Test Links:</h3>";
        $output .= "<p><a href='/claims-reimbursement' target='_blank' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📋 Claims & Reimbursement</a></p>";
        $output .= "<p><a href='/validate-attachment' target='_blank' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>✅ Validate Attachment</a></p>";
        
        // Step 3: Show approved claims that should appear in validate attachment
        $approvedClaimsData = \App\Models\Claim::with(['employee', 'claimType'])
            ->where('status', 'approved')
            ->get();
            
        if ($approvedClaimsData->count() > 0) {
            $output .= "<h3>✅ Approved Claims (Should appear in Validate Attachment):</h3>";
            $output .= "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            $output .= "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Employee</th><th style='padding: 8px;'>Type</th><th style='padding: 8px;'>Amount</th><th style='padding: 8px;'>Description</th></tr>";
            
            foreach ($approvedClaimsData as $claim) {
                $employeeName = ($claim->employee->first_name ?? 'Unknown') . ' ' . ($claim->employee->last_name ?? '');
                $claimType = $claim->claimType->name ?? 'Unknown Type';
                
                $output .= "<tr>";
                $output .= "<td style='padding: 8px;'>{$claim->id}</td>";
                $output .= "<td style='padding: 8px;'>{$employeeName}</td>";
                $output .= "<td style='padding: 8px;'>{$claimType}</td>";
                $output .= "<td style='padding: 8px;'>\${$claim->amount}</td>";
                $output .= "<td style='padding: 8px;'>{$claim->description}</td>";
                $output .= "</tr>";
            }
            $output .= "</table>";
        }
        
        // Step 4: Instructions
        $output .= "<h3>📝 Testing Instructions:</h3>";
        $output .= "<ol>";
        $output .= "<li>Click on <strong>Claims & Reimbursement</strong> to see the main claims page</li>";
        $output .= "<li>Look for the <strong>Claims & Reimbursement</strong> dropdown in the sidebar</li>";
        $output .= "<li>Click the dropdown to see both options: 'Claim & Reimbursement' and 'Validate Attachment'</li>";
        $output .= "<li>Click on <strong>Validate Attachment</strong> to see approved claims</li>";
        $output .= "<li>The approved claims shown above should appear in the Validate Attachment page</li>";
        $output .= "</ol>";
        
        return $output;
        
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage() . "<br>" . $e->getTraceAsString();
    }
})->name('test.complete.flow');

// Test route to verify attachment viewing
Route::get('/test-attachment-view', function() {
    try {
        $output = "<h2>🔍 Testing Attachment View Functionality</h2>";
        
        // Check claims with attachments
        $claimsWithAttachments = \App\Models\Claim::whereNotNull('receipt_path')
            ->orWhereNotNull('attachment_path')
            ->get();
            
        $output .= "<h3>📎 Claims with Attachments:</h3>";
        
        if ($claimsWithAttachments->count() > 0) {
            $output .= "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            $output .= "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Description</th><th style='padding: 8px;'>Attachment Path</th><th style='padding: 8px;'>Test View</th></tr>";
            
            foreach ($claimsWithAttachments as $claim) {
                $attachmentPath = $claim->receipt_path ?? $claim->attachment_path ?? '';
                $output .= "<tr>";
                $output .= "<td style='padding: 8px;'>{$claim->id}</td>";
                $output .= "<td style='padding: 8px;'>{$claim->description}</td>";
                $output .= "<td style='padding: 8px;'>{$attachmentPath}</td>";
                $output .= "<td style='padding: 8px;'><button onclick=\"testViewAttachment('{$attachmentPath}')\">Test View</button></td>";
                $output .= "</tr>";
            }
            $output .= "</table>";
            
            $output .= "<script>
                function testViewAttachment(path) {
                    if (!path) {
                        alert('No attachment path');
                        return;
                    }
                    const fullPath = '/storage/' + path;
                    window.open(fullPath, '_blank');
                }
            </script>";
        } else {
            $output .= "<p>No claims with attachments found.</p>";
        }
        
        $output .= "<h3>🔗 Test Links:</h3>";
        $output .= "<p><a href='/validate-attachment' target='_blank' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Go to Validate Attachment Page</a></p>";
        
        return $output;
        
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage();
    }
})->name('test.attachment.view');

// Final test route for attachment functionality
Route::get('/test-final-attachment', function() {
    try {
        $output = "<h2>🔍 Final Attachment Test</h2>";
        
        // Test direct file access
        $testPath = 'receipts/sample_receipt.svg';
        $fullUrl = asset('storage/' . $testPath);
        
        $output .= "<h3>📁 File Access Test:</h3>";
        $output .= "<p><strong>Test File:</strong> {$testPath}</p>";
        $output .= "<p><strong>Full URL:</strong> <a href='{$fullUrl}' target='_blank'>{$fullUrl}</a></p>";
        
        // Test claims with attachments
        $claims = \App\Models\Claim::where('status', 'approved')->get();
        
        $output .= "<h3>📋 Claims with Attachments:</h3>";
        if ($claims->count() > 0) {
            $output .= "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            $output .= "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Employee</th><th style='padding: 8px;'>Description</th><th style='padding: 8px;'>Attachment</th><th style='padding: 8px;'>Test Links</th></tr>";
            
            foreach ($claims as $claim) {
                $attachmentPath = $claim->receipt_path ?? $claim->attachment_path ?? '';
                $attachmentUrl = $attachmentPath ? asset('storage/' . $attachmentPath) : '';
                
                $output .= "<tr>";
                $output .= "<td style='padding: 8px;'>{$claim->id}</td>";
                $output .= "<td style='padding: 8px;'>Employee {$claim->employee_id}</td>";
                $output .= "<td style='padding: 8px;'>{$claim->description}</td>";
                $output .= "<td style='padding: 8px;'>{$attachmentPath}</td>";
                $output .= "<td style='padding: 8px;'>";
                if ($attachmentPath) {
                    $output .= "<a href='{$attachmentUrl}' target='_blank' class='btn btn-sm btn-primary'>Direct Link</a> ";
                    $output .= "<button onclick=\"testModal('{$attachmentPath}')\" class='btn btn-sm btn-secondary'>Test Modal</button>";
                } else {
                    $output .= "No attachment";
                }
                $output .= "</td>";
                $output .= "</tr>";
            }
            $output .= "</table>";
        }
        
        $output .= "<h3>🧪 Test Actions:</h3>";
        $output .= "<p><a href='/validate-attachment' target='_blank' class='btn btn-primary'>Go to Validate Attachment Page</a></p>";
        
        $output .= "<script>
            function testModal(path) {
                const fullPath = '/storage/' + path;
                const modal = window.open('', '_blank', 'width=600,height=400');
                modal.document.write(`
                    <html>
                        <head><title>Test Modal View</title></head>
                        <body style='margin: 20px; text-align: center;'>
                            <h3>Testing Attachment View</h3>
                            <p><strong>Path:</strong> ${path}</p>
                            <p><strong>Full URL:</strong> ${fullPath}</p>
                            <img src='${fullPath}' style='max-width: 100%; max-height: 300px; border: 1px solid #ccc;' 
                                 onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"block\";'>
                            <div style='display: none; color: red;'>
                                <p>❌ Failed to load image</p>
                                <a href='${fullPath}' target='_blank'>Try direct link</a>
                            </div>
                        </body>
                    </html>
                `);
            }
        </script>";
        
        return $output;
        
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage();
    }
})->name('test.final.attachment');

// Test route to create sample payroll data
Route::get('/create-sample-payroll', function() {
    try {
        // Create sample payroll entries directly in database
        $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // Ensure we have employees
        $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT IGNORE INTO employees (id, first_name, last_name, email, status, created_at, updated_at) VALUES
                (1, 'Super', 'Admin', 'admin@example.com', 'active', NOW(), NOW()),
                (2, 'Jane', 'Smith', 'jane@example.com', 'active', NOW(), NOW())");
        }
        
        // Create sample payroll entries
        $pdo->exec("DELETE FROM payroll WHERE id IN (1, 2, 3)"); // Clear existing test data
        $pdo->exec("INSERT INTO payroll (id, employee_id, department, week_period, total_hours, overtime_hours, hourly_rate, overtime_rate, regular_amount, overtime_amount, total_amount, status, created_at, updated_at) VALUES
            (1, 1, 'IT', 'Oct 06 - Oct 12, 2025', 40.00, 8.00, 25.00, 37.50, 1000.00, 300.00, 1300.00, 'pending', NOW(), NOW()),
            (2, 2, 'Human Resources', 'Oct 06 - Oct 12, 2025', 40.00, 0.00, 25.00, 37.50, 1000.00, 0.00, 1000.00, 'processed', NOW(), NOW()),
            (3, 1, 'IT', 'Sep 29 - Oct 05, 2025', 35.00, 5.00, 25.00, 37.50, 875.00, 187.50, 1062.50, 'paid', NOW(), NOW())");
            
        return "✅ Sample payroll data created successfully!<br>" .
               "<a href='/payroll-management'>Go to Payroll Management</a><br>" .
               "<a href='/timesheet-management'>Go to Timesheet Management</a>";
               
    } catch (\Exception $e) {
        return "❌ Error creating sample payroll data: " . $e->getMessage();
    }
})->name('create.sample.payroll');

// Test route to create sample validated attachments
Route::get('/create-sample-validated-attachments', function() {
    try {
        // Create sample validated attachments directly in database
        $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // Ensure we have employees
        $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT IGNORE INTO employees (id, first_name, last_name, email, status, created_at, updated_at) VALUES
                (1, 'Super', 'Admin', 'admin@example.com', 'active', NOW(), NOW()),
                (2, 'Jane', 'Smith', 'jane@example.com', 'active', NOW(), NOW())");
        }
        
        // Create sample validated attachments
        $pdo->exec("DELETE FROM validated_attachments WHERE id IN (1, 2, 3)"); // Clear existing test data
        $pdo->exec("INSERT INTO validated_attachments (id, claim_id, employee_id, employee_name, claim_type, amount, claim_date, description, attachment_path, status, validated_at, validated_by, created_at, updated_at) VALUES
            (1, 1, 1, 'Super Admin', 'Office Supplies', 150.00, '2025-10-08', 'Materials for office setup', 'receipts/sample_receipt.svg', 'validated', NOW(), 1, NOW(), NOW()),
            (2, 2, 2, 'Jane Smith', 'Meal Allowance', 12.00, '2025-10-06', 'Lunch expense during meeting', 'receipts/sample_receipt.svg', 'sent_to_payroll', NOW(), 1, NOW(), NOW()),
            (3, 3, 1, 'Super Admin', 'Travel Expense', 75.50, '2025-10-05', 'Transportation cost for client visit', 'receipts/sample_receipt.svg', 'processed', NOW(), 1, NOW(), NOW())");
            
        return "✅ Sample validated attachments created successfully!<br>" .
               "<a href='/payroll-management'>Go to Payroll Management</a><br>" .
               "<a href='/validate-attachment'>Go to Validate Attachment</a>";
               
    } catch (\Exception $e) {
        return "❌ Error creating sample validated attachments: " . $e->getMessage();
    }
})->name('create.sample.validated.attachments');

// Test complete payroll functionality
Route::get('/test-payroll-system', function() {
    try {
        $output = "<h2>🧪 Testing Complete Payroll System</h2>";
        
        // Check payroll entries
        $payrollCount = 0;
        $pendingCount = 0;
        $processedCount = 0;
        
        try {
            $payrollCount = \App\Models\Payroll::count();
            $pendingCount = \App\Models\Payroll::where('status', 'pending')->count();
            $processedCount = \App\Models\Payroll::where('status', 'processed')->count();
        } catch (\Exception $e) {
            // Fallback to raw query
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $stmt = $pdo->query("SELECT COUNT(*) FROM payroll");
            $payrollCount = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM payroll WHERE status = 'pending'");
            $pendingCount = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM payroll WHERE status = 'processed'");
            $processedCount = $stmt->fetchColumn();
        }
        
        $output .= "<h3>📊 Current Payroll Status:</h3>";
        $output .= "<p>Total Payroll Entries: {$payrollCount}</p>";
        $output .= "<p>Pending: {$pendingCount}</p>";
        $output .= "<p>Processed: {$processedCount}</p>";
        
        $output .= "<h3>🔗 Test Links:</h3>";
        $output .= "<p><a href='/payroll-management' target='_blank' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>💰 Payroll Management</a></p>";
        $output .= "<p><a href='/timesheet-management' target='_blank' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>⏰ Timesheet Management</a></p>";
        
        $output .= "<h3>📝 Testing Instructions:</h3>";
        $output .= "<ol>";
        $output .= "<li>Go to <strong>Timesheet Management</strong> and look for approved timesheets</li>";
        $output .= "<li>Click the <strong>Send to Payroll</strong> button (💰 icon) in the Actions column</li>";
        $output .= "<li>Go to <strong>Payroll Management</strong> to see the new payroll entry</li>";
        $output .= "<li>In Payroll Management, you can:</li>";
        $output .= "<ul>";
        $output .= "<li>View payroll details</li>";
        $output .= "<li>Process pending payroll items</li>";
        $output .= "<li>Mark processed items as paid</li>";
        $output .= "<li>Filter by status</li>";
        $output .= "</ul>";
        $output .= "</ol>";
        
        $output .= "<h3>✨ Features Available:</h3>";
        $output .= "<ul>";
        $output .= "<li><strong>Sidebar Navigation:</strong> Payroll Management added to sidebar</li>";
        $output .= "<li><strong>Send to Payroll:</strong> Button in timesheet actions</li>";
        $output .= "<li><strong>Payroll Dashboard:</strong> Statistics and overview</li>";
        $output .= "<li><strong>Process Workflow:</strong> Pending → Processed → Paid</li>";
        $output .= "<li><strong>Data Integration:</strong> Links timesheets to payroll</li>";
        $output .= "</ul>";
        
        return $output;
        
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage();
    }
})->name('test.payroll.system');

// Direct database test route
Route::get('/test-db', [SystemTestController::class, 'testDb']);

// Dashboard data population route (for testing)
Route::get('/populate-dashboard', [DataSeederController::class, 'populateDashboard'])->name('populate.dashboard');

// Debug route to check shift data
Route::get('/debug-shifts', [SystemDebugController::class, 'debugShifts'])->name('debug.shifts');


// Quick test route to verify send to payroll functionality
Route::get('/test-send-to-payroll', function() {
    try {
        // Get an approved timesheet
        $approvedTimesheet = \App\Models\AIGeneratedTimesheet::where('status', 'approved')->first();
        
        if (!$approvedTimesheet) {
            return "❌ No approved timesheets found. Please create and approve a timesheet first.<br>" .
                   "<a href='/timesheet-management'>Go to Timesheet Management</a>";
        }
        
        // Test the send to payroll functionality
        $controller = new \App\Http\Controllers\TimesheetController();
        $response = $controller->sendToPayroll($approvedTimesheet->id);
        $data = json_decode($response->getContent(), true);
        
        if ($data['success']) {
            return "✅ Send to Payroll Test Successful!<br>" .
                   "Timesheet ID: {$approvedTimesheet->id}<br>" .
                   "Payroll Item ID: {$data['payroll_item_id']}<br>" .
                   "Total Amount: {$data['total_amount']}<br><br>" .
                   "<a href='/payroll-management'>Check Payroll Management</a><br>" .
                   "<a href='/timesheet-management'>Back to Timesheet Management</a>";
        } else {
            return "❌ Send to Payroll Failed: " . $data['message'];
        }
        
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage();
    }
})->name('test.send.to.payroll');

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
    
    // User preferences
    Route::get('/preferences', [AdminProfileController::class, 'getPreferences'])->name('get-preferences');
    Route::post('/preferences', [AdminProfileController::class, 'updatePreferences'])->name('update-preferences');
});

// AI Timesheet Test Route
Route::get('/test-ai-timesheet', [SystemTestController::class, 'testAiTimesheet']);

// Test AI Generation Route
Route::get('/test-ai-generation/{employeeId}', [SystemTestController::class, 'testAiGeneration']);

// Test page with cache clearing button
Route::get('/test-ai-cache', function() {
    return '
    <h2>AI Timesheet Cache Test</h2>
    <button onclick="clearAllAICache()" style="padding: 10px; margin: 5px; background: #dc3545; color: white; border: none; border-radius: 5px;">Clear AI Cache</button>
    <button onclick="window.location.href=\'/timesheets/management\'" style="padding: 10px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 5px;">Go to Timesheets</button>
    <button onclick="window.location.href=\'/clear-all-ai-timesheets\'" style="padding: 10px; margin: 5px; background: #28a745; color: white; border: none; border-radius: 5px;">Clear Database Cache</button>
    <script>
    function clearAllAICache() {
        window.aiTimesheets = {};
        alert("AI timesheet cache cleared!");
    }
    </script>
    ';
});

// Clear all AI timesheets (for testing)
Route::get('/clear-all-ai-timesheets', function() {
    try {
        $deleted = DB::table('ai_generated_timesheets')->delete();
        return response()->json([
            'success' => true,
            'message' => "Cleared {$deleted} AI timesheets",
            'deleted_count' => $deleted
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
});

// Seed current week attendance data
Route::get('/seed-current-week-attendance', function() {
    try {
        Artisan::call('db:seed', ['--class' => 'CurrentWeekAttendanceSeeder']);
        return response()->json([
            'success' => true,
            'message' => 'Current week attendance data seeded successfully',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
});

// Simple attendance check
Route::get('/simple-attendance-check', function() {
    try {
        $totalAttendances = DB::table('attendances')->count();
        $recentAttendances = DB::table('attendances')->orderBy('date', 'desc')->limit(5)->get();
        $employees = DB::table('employees')->limit(5)->get();
        
        return response()->json([
            'total_attendances' => $totalAttendances,
            'recent_attendances' => $recentAttendances,
            'employees' => $employees,
            'current_week' => [
                'start' => \Carbon\Carbon::now()->startOfWeek()->format('Y-m-d'),
                'end' => \Carbon\Carbon::now()->endOfWeek()->format('Y-m-d')
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Check table structure and fix status
Route::get('/check-table-structure', function() {
    try {
        // Check if table exists
        $tableExists = Schema::hasTable('ai_generated_timesheets');
        
        if (!$tableExists) {
            return response()->json([
                'error' => 'ai_generated_timesheets table does not exist'
            ]);
        }
        
        // Get table columns
        $columns = Schema::getColumnListing('ai_generated_timesheets');
        
        // Get sample data
        $sampleData = DB::table('ai_generated_timesheets')
            ->select('id', 'employee_name', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'table_exists' => $tableExists,
            'columns' => $columns,
            'sample_data' => $sampleData,
            'total_records' => DB::table('ai_generated_timesheets')->count(),
            'status_counts' => DB::table('ai_generated_timesheets')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Add missing columns manually
Route::get('/add-approval-columns', function() {
    try {
        $columns = Schema::getColumnListing('ai_generated_timesheets');
        $added = [];
        
        // Add columns if they don't exist
        if (!in_array('approved_at', $columns)) {
            DB::statement('ALTER TABLE ai_generated_timesheets ADD COLUMN approved_at TIMESTAMP NULL');
            $added[] = 'approved_at';
        }
        
        if (!in_array('rejected_at', $columns)) {
            DB::statement('ALTER TABLE ai_generated_timesheets ADD COLUMN rejected_at TIMESTAMP NULL');
            $added[] = 'rejected_at';
        }
        
        if (!in_array('rejection_reason', $columns)) {
            DB::statement('ALTER TABLE ai_generated_timesheets ADD COLUMN rejection_reason TEXT NULL');
            $added[] = 'rejection_reason';
        }
        
        return response()->json([
            'success' => true,
            'columns_added' => $added,
            'message' => 'Added ' . count($added) . ' columns successfully'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
});

// Fix undefined status in ai_generated_timesheets
Route::get('/fix-timesheet-status', function() {
    try {
        // Update records with undefined status to pending
        $updated = DB::table('ai_generated_timesheets')
            ->where('status', 'undefined')
            ->orWhereNull('status')
            ->update(['status' => 'pending']);
        
        // Get count of records by status
        $statusCounts = DB::table('ai_generated_timesheets')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        return response()->json([
            'success' => true,
            'updated_records' => $updated,
            'status_counts' => $statusCounts,
            'message' => "Updated {$updated} records from 'undefined' to 'pending'"
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
});

// Interactive test page for timesheet actions
Route::get('/test-actions-ui/{timesheetId}', function($timesheetId) {
    try {
        $timesheet = DB::table('ai_generated_timesheets')->where('id', $timesheetId)->first();
        
        if (!$timesheet) {
            return "Timesheet not found with ID: {$timesheetId}";
        }
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Test Timesheet Actions</title>
            <meta name="csrf-token" content="' . csrf_token() . '">
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .btn { padding: 10px 15px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; }
                .btn-success { background: #28a745; color: white; }
                .btn-danger { background: #dc3545; color: white; }
                .btn-warning { background: #ffc107; color: black; }
                .info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
                .result { margin: 10px 0; padding: 10px; border-radius: 5px; }
                .success { background: #d4edda; color: #155724; }
                .error { background: #f8d7da; color: #721c24; }
            </style>
        </head>
        <body>
            <h2>Test Timesheet Actions</h2>
            
            <div class="info">
                <h3>Timesheet Info:</h3>
                <p><strong>ID:</strong> ' . $timesheet->id . '</p>
                <p><strong>Employee:</strong> ' . $timesheet->employee_name . '</p>
                <p><strong>Department:</strong> ' . $timesheet->department . '</p>
                <p><strong>Status:</strong> <span id="current-status">' . $timesheet->status . '</span></p>
                <p><strong>Week:</strong> ' . $timesheet->week_start_date . '</p>
            </div>
            
            <div>
                <h3>Actions:</h3>
                <button class="btn btn-success" onclick="approveTimesheet()">✓ Approve</button>
                <button class="btn btn-danger" onclick="rejectTimesheet()">✗ Reject</button>
                <button class="btn btn-warning" onclick="deleteTimesheet()">🗑 Delete</button>
                <button class="btn" onclick="refreshStatus()" style="background: #6c757d; color: white;">🔄 Refresh Status</button>
            </div>
            
            <div id="result"></div>
            
            <script>
                const timesheetId = ' . $timesheetId . ';
                const csrfToken = document.querySelector("meta[name=csrf-token]").getAttribute("content");
                
                function showResult(message, isSuccess) {
                    const result = document.getElementById("result");
                    result.className = "result " + (isSuccess ? "success" : "error");
                    result.innerHTML = message;
                }
                
                function approveTimesheet() {
                    fetch(`/api/ai-timesheets/approve/${timesheetId}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Accept": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showResult("✓ " + data.message, true);
                            document.getElementById("current-status").textContent = "approved";
                        } else {
                            showResult("✗ " + data.message, false);
                        }
                    })
                    .catch(error => {
                        showResult("✗ Error: " + error.message, false);
                    });
                }
                
                function rejectTimesheet() {
                    const reason = prompt("Enter rejection reason (optional):");
                    if (reason === null) return;
                    
                    fetch(`/api/ai-timesheets/reject/${timesheetId}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({ reason: reason || "No reason provided" })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showResult("✓ " + data.message + (data.reason ? " (Reason: " + data.reason + ")" : ""), true);
                            document.getElementById("current-status").textContent = "rejected";
                        } else {
                            showResult("✗ " + data.message, false);
                        }
                    })
                    .catch(error => {
                        showResult("✗ Error: " + error.message, false);
                    });
                }
                
                function deleteTimesheet() {
                    if (!confirm("Are you sure you want to delete this timesheet?")) return;
                    
                    fetch(`/api/ai-timesheets/delete/${timesheetId}`, {
                        method: "DELETE",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Accept": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showResult("✓ " + data.message, true);
                            document.getElementById("current-status").textContent = "deleted";
                        } else {
                            showResult("✗ " + data.message, false);
                        }
                    })
                    .catch(error => {
                        showResult("✗ Error: " + error.message, false);
                    });
                }
                
                function refreshStatus() {
                    fetch(`/test-timesheet-actions/${timesheetId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.timesheet_info) {
                            document.getElementById("current-status").textContent = data.timesheet_info.status;
                            showResult("Status refreshed: " + data.timesheet_info.status, true);
                        } else {
                            showResult("Timesheet may have been deleted", false);
                        }
                    })
                    .catch(error => {
                        showResult("Error refreshing status: " + error.message, false);
                    });
                }
            </script>
        </body>
        </html>';
        
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Test timesheet actions (approve, reject, delete)
Route::get('/test-timesheet-actions/{timesheetId}', function($timesheetId) {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        
        // Get the timesheet first
        $timesheet = DB::table('ai_generated_timesheets')->where('id', $timesheetId)->first();
        
        if (!$timesheet) {
            return response()->json([
                'error' => 'Timesheet not found',
                'timesheet_id' => $timesheetId
            ]);
        }
        
        return response()->json([
            'timesheet_info' => [
                'id' => $timesheet->id,
                'employee_name' => $timesheet->employee_name,
                'status' => $timesheet->status,
                'department' => $timesheet->department
            ],
            'available_actions' => [
                'approve' => "POST /api/ai-timesheets/approve/{$timesheetId}",
                'reject' => "POST /api/ai-timesheets/reject/{$timesheetId} (with reason in body)",
                'delete' => "DELETE /api/ai-timesheets/delete/{$timesheetId}"
            ],
            'test_buttons' => [
                'approve_url' => "/api/ai-timesheets/approve/{$timesheetId}",
                'reject_url' => "/api/ai-timesheets/reject/{$timesheetId}",
                'delete_url' => "/api/ai-timesheets/delete/{$timesheetId}"
            ],
            'current_status' => $timesheet->status,
            'test_instructions' => [
                '1. Use browser dev tools or Postman to test the endpoints',
                '2. For reject, send JSON body: {"reason": "Test rejection reason"}',
                '3. Check database after each action to verify status changes'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// API endpoint to view AI timesheet details
Route::get('/api/ai-timesheets/view/{employeeId}', function($employeeId) {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        return $controller->getLatestAITimesheet($employeeId);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to get AI timesheet: ' . $e->getMessage()
        ], 500);
    }
});

// API endpoint to save AI timesheet
Route::post('/api/ai-timesheets/save', function(\Illuminate\Http\Request $request) {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        return $controller->saveTimesheet($request);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to save AI timesheet: ' . $e->getMessage()
        ], 500);
    }
});

// API endpoint to get AI timesheet statistics
Route::get('/api/ai-timesheets/statistics', function() {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        return $controller->getStatistics();
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to get statistics: ' . $e->getMessage(),
            'error_details' => $e->getTraceAsString()
        ], 500);
    }
});

// Test statistics endpoint
Route::get('/test-stats-simple', function() {
    try {
        // Simple test to check database connection
        $timesheetCount = DB::table('ai_generated_timesheets')->count();
        $employeeCount = DB::table('employees')->count();
        
        return response()->json([
            'success' => true,
            'database_connection' => 'OK',
            'timesheet_count' => $timesheetCount,
            'employee_count' => $employeeCount,
            'test_statistics' => [
                'total_timesheets' => $timesheetCount,
                'pending_timesheets' => 0,
                'approved_timesheets' => 0,
                'rejected_timesheets' => 0,
                'total_hours' => 0.0
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Alternative API endpoint without JOIN
Route::get('/api/ai-timesheets/pending-simple', function() {
    try {
        // Simple query without JOIN to avoid issues
        $timesheets = DB::table('ai_generated_timesheets')
            ->select('*')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($timesheet) {
                // Format the week period
                $weekStart = \Carbon\Carbon::parse($timesheet->week_start_date);
                $weekEnd = $weekStart->copy()->endOfWeek();
                
                return [
                    'id' => $timesheet->id,
                    'employee_id' => $timesheet->employee_id,
                    'employee_name' => $timesheet->employee_name ?: 'Unknown Employee',
                    'department' => $timesheet->department ?: 'General',
                    'week_period' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
                    'total_hours' => $timesheet->total_hours ?: 0,
                    'overtime_hours' => $timesheet->overtime_hours ?: 0,
                    'status' => $timesheet->status ?: 'pending',
                    'generated_at' => $timesheet->generated_at ? \Carbon\Carbon::parse($timesheet->generated_at)->format('Y-m-d') : \Carbon\Carbon::parse($timesheet->created_at)->format('Y-m-d'),
                    'generated_date' => $timesheet->generated_at ? \Carbon\Carbon::parse($timesheet->generated_at)->format('Y-m-d') : \Carbon\Carbon::parse($timesheet->created_at)->format('Y-m-d')
                ];
            });

        return response()->json([
            'success' => true,
            'timesheets' => $timesheets,
            'count' => $timesheets->count()
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to get timesheets: ' . $e->getMessage(),
            'error_details' => $e->getTraceAsString()
        ], 500);
    }
});

// Debug page for timesheets
Route::get('/debug-timesheets-ui', function() {
    return view('debug-timesheets');
});

// Simple debug test
Route::get('/debug-friday-calc', function() {
    try {
        // Test the exact same logic as in the controller
        $weekStart = \Carbon\Carbon::parse('2025-10-06')->startOfWeek(\Carbon\Carbon::MONDAY);
        $fridayDate = $weekStart->copy()->addDays(4); // Friday should be 2025-10-10
        
        // Get attendance for Friday
        $attendance = DB::table('attendances')
            ->where('employee_id', 6)
            ->where('date', $fridayDate->format('Y-m-d'))
            ->first();
        
        $result = [
            'week_start' => $weekStart->format('Y-m-d'),
            'friday_date' => $fridayDate->format('Y-m-d'),
            'attendance_found' => $attendance ? 'YES' : 'NO',
            'attendance_data' => $attendance
        ];
        
        if ($attendance && $attendance->clock_in_time && $attendance->clock_out_time) {
            $clockIn = \Carbon\Carbon::parse($attendance->clock_in_time);
            $clockOut = \Carbon\Carbon::parse($attendance->clock_out_time);
            $totalMinutes = $clockOut->diffInMinutes($clockIn);
            $totalHours = floor($totalMinutes / 60);
            $remainingMinutes = $totalMinutes % 60;
            
            $result['calculation'] = [
                'clock_in' => $clockIn->format('h:i A'),
                'clock_out' => $clockOut->format('h:i A'),
                'total_minutes' => $totalMinutes,
                'total_hours' => $totalHours,
                'remaining_minutes' => $remainingMinutes,
                'formatted' => $totalHours . 'h ' . $remainingMinutes . 'm'
            ];
        }
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Complete test for Jonny's AI timesheet
Route::get('/test-complete-jonny', function() {
    try {
        // Test 1: Check attendance data
        $attendanceData = DB::table('attendances')
            ->where('employee_id', 6)
            ->where('date', '2025-10-10')
            ->first();
        
        // Test 2: Check week calculation
        $weekStart = \Carbon\Carbon::parse('2025-10-06')->startOfWeek(\Carbon\Carbon::MONDAY);
        $fridayDate = $weekStart->copy()->addDays(4); // Friday
        
        // Test 3: Test AI generation
        $controller = new \App\Http\Controllers\TimesheetController();
        $response = $controller->generateAITimesheet(6);
        $aiData = json_decode($response->getContent(), true);
        
        // Test 4: Manual calculation
        $manualCalc = null;
        if ($attendanceData) {
            $clockIn = \Carbon\Carbon::parse($attendanceData->clock_in_time);
            $clockOut = \Carbon\Carbon::parse($attendanceData->clock_out_time);
            $totalMinutes = $clockOut->diffInMinutes($clockIn);
            $totalHours = floor($totalMinutes / 60);
            $remainingMinutes = $totalMinutes % 60;
            
            $manualCalc = [
                'total_minutes' => $totalMinutes,
                'total_hours' => $totalHours,
                'remaining_minutes' => $remainingMinutes,
                'formatted' => $totalHours . 'h ' . $remainingMinutes . 'm'
            ];
        }
        
        return response()->json([
            'test_name' => 'Complete Jonny AI Timesheet Test',
            'step_1_attendance_data' => [
                'found' => $attendanceData ? 'YES' : 'NO',
                'data' => $attendanceData
            ],
            'step_2_week_calculation' => [
                'week_start' => $weekStart->format('Y-m-d'),
                'friday_calculated' => $fridayDate->format('Y-m-d'),
                'should_be' => '2025-10-10',
                'matches' => $fridayDate->format('Y-m-d') === '2025-10-10' ? 'YES' : 'NO'
            ],
            'step_3_ai_generation' => [
                'success' => $aiData['success'] ?? false,
                'friday_data' => isset($aiData['weekly_data']['Friday']) ? $aiData['weekly_data']['Friday'] : 'NOT FOUND'
            ],
            'step_4_manual_calculation' => $manualCalc,
            'conclusion' => $attendanceData && $fridayDate->format('Y-m-d') === '2025-10-10' ? 'SHOULD WORK' : 'PROBLEM FOUND'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'test_name' => 'Complete Test FAILED',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test AI generation directly
Route::get('/test-ai-generation/6', function() {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        $response = $controller->generateAITimesheet(6);
        $data = json_decode($response->getContent(), true);
        
        return response()->json([
            'test_name' => 'Direct AI Generation Test for Jonny (ID: 6)',
            'generation_response' => $data,
            'success' => $data['success'] ?? false,
            'weekly_data' => $data['weekly_data'] ?? null,
            'friday_data' => isset($data['weekly_data']['Friday']) ? $data['weekly_data']['Friday'] : 'NOT FOUND'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'test_name' => 'Direct AI Generation Test FAILED',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test attendance database connection
Route::get('/test-attendance-db', function() {
    try {
        // Test basic database connection
        $allAttendance = DB::table('attendances')->get();
        
        // Get Jonny's specific data
        $jonnyAttendance = DB::table('attendances')
            ->where('employee_id', 6)
            ->get();
        
        // Get Friday's specific data
        $fridayAttendance = DB::table('attendances')
            ->where('employee_id', 6)
            ->where('date', '2025-10-10')
            ->first();
        
        return response()->json([
            'database_connection' => 'SUCCESS',
            'total_attendance_records' => $allAttendance->count(),
            'jonny_records_count' => $jonnyAttendance->count(),
            'jonny_all_records' => $jonnyAttendance->toArray(),
            'friday_record' => $fridayAttendance,
            'friday_calculation' => null
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'database_connection' => 'FAILED',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Week calculation test
Route::get('/test-week-calculation', function() {
    try {
        $now = \Carbon\Carbon::now();
        $weekStart = $now->startOfWeek(\Carbon\Carbon::MONDAY);
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $weekDates = [];
        
        foreach ($days as $index => $day) {
            $dayDate = $weekStart->copy()->addDays($index);
            $weekDates[$day] = [
                'date' => $dayDate->format('Y-m-d'),
                'formatted' => $dayDate->format('m/d/y'),
                'day_name' => $dayDate->format('l')
            ];
        }
        
        return response()->json([
            'current_time' => $now->format('Y-m-d H:i:s'),
            'week_start' => $weekStart->format('Y-m-d'),
            'week_dates' => $weekDates,
            'friday_should_be' => '2025-10-10',
            'friday_calculated' => $weekDates['Friday']['date']
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Direct calculation test for Jonny's Friday data
Route::get('/test-jonny-friday', function() {
    try {
        // Test the exact calculation that should happen
        $clockInRaw = '2025-10-10 09:19:00';
        $clockOutRaw = '2025-10-10 11:37:00';
        
        $clockIn = \Carbon\Carbon::parse($clockInRaw);
        $clockOut = \Carbon\Carbon::parse($clockOutRaw);
        $totalMinutes = $clockOut->diffInMinutes($clockIn);
        
        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;
        
        // Format time display
        $formattedTime = '';
        if ($totalHours > 0 && $remainingMinutes > 0) {
            $formattedTime = $totalHours . 'h ' . $remainingMinutes . 'm';
        } elseif ($totalHours > 0) {
            $formattedTime = $totalHours . 'h';
        } elseif ($remainingMinutes > 0) {
            $formattedTime = $remainingMinutes . 'm';
        } else {
            $formattedTime = '0h';
        }
        
        return response()->json([
            'test_name' => 'Jonny Friday Calculation Test',
            'clock_in_raw' => $clockInRaw,
            'clock_out_raw' => $clockOutRaw,
            'clock_in_parsed' => $clockIn->format('Y-m-d H:i:s'),
            'clock_out_parsed' => $clockOut->format('Y-m-d H:i:s'),
            'total_minutes' => $totalMinutes,
            'total_hours' => $totalHours,
            'remaining_minutes' => $remainingMinutes,
            'formatted_time' => $formattedTime,
            'expected_result' => '2h 18m'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Simple attendance test
Route::get('/test-attendance/{employeeId}', function($employeeId) {
    try {
        // Get all attendance for this employee
        $allAttendance = DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->orderBy('date', 'desc')
            ->get();
        
        // Get today's attendance specifically
        $todayAttendance = DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->where('date', '2025-10-10')
            ->first();
        
        $result = [
            'employee_id' => $employeeId,
            'today_date' => '2025-10-10',
            'all_attendance_count' => $allAttendance->count(),
            'all_attendance' => $allAttendance->toArray(),
            'today_attendance' => $todayAttendance,
            'calculation_test' => null
        ];
        
        if ($todayAttendance && $todayAttendance->clock_in_time && $todayAttendance->clock_out_time) {
            $clockIn = \Carbon\Carbon::parse($todayAttendance->clock_in_time);
            $clockOut = \Carbon\Carbon::parse($todayAttendance->clock_out_time);
            $totalMinutes = $clockOut->diffInMinutes($clockIn);
            
            $result['calculation_test'] = [
                'clock_in_raw' => $todayAttendance->clock_in_time,
                'clock_out_raw' => $todayAttendance->clock_out_time,
                'clock_in_parsed' => $clockIn->format('Y-m-d H:i:s'),
                'clock_out_parsed' => $clockOut->format('Y-m-d H:i:s'),
                'total_minutes' => $totalMinutes,
                'total_hours' => floor($totalMinutes / 60),
                'remaining_minutes' => $totalMinutes % 60,
                'formatted' => floor($totalMinutes / 60) . 'h ' . ($totalMinutes % 60) . 'm'
            ];
        }
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Debug AI timesheet calculation
Route::get('/debug-ai-calculation/{employeeId}', function($employeeId) {
    try {
        // Get current week start
        $weekStart = \Carbon\Carbon::now()->startOfWeek();
        
        // Get attendance data for this employee
        $recentAttendance = DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->where('date', '>=', $weekStart->copy()->subWeeks(2)->format('Y-m-d'))
            ->where('date', '<=', $weekStart->copy()->endOfWeek()->format('Y-m-d'))
            ->orderBy('date', 'desc')
            ->get();
        
        // Check specific day (Friday = index 4)
        $fridayDate = $weekStart->copy()->addDays(4); // Friday
        $fridayAttendance = $recentAttendance->where('date', $fridayDate->format('Y-m-d'))->first();
        
        $debugInfo = [
            'employee_id' => $employeeId,
            'week_start' => $weekStart->format('Y-m-d'),
            'friday_date' => $fridayDate->format('Y-m-d'),
            'total_attendance_records' => $recentAttendance->count(),
            'attendance_dates' => $recentAttendance->pluck('date')->toArray(),
            'friday_attendance' => $fridayAttendance,
            'friday_calculation' => null
        ];
        
        if ($fridayAttendance && $fridayAttendance->clock_in_time && $fridayAttendance->clock_out_time) {
            $clockIn = \Carbon\Carbon::parse($fridayAttendance->clock_in_time);
            $clockOut = \Carbon\Carbon::parse($fridayAttendance->clock_out_time);
            $totalMinutes = $clockOut->diffInMinutes($clockIn);
            $totalHours = floor($totalMinutes / 60);
            $remainingMinutes = $totalMinutes % 60;
            
            $debugInfo['friday_calculation'] = [
                'clock_in' => $clockIn->format('Y-m-d H:i:s'),
                'clock_out' => $clockOut->format('Y-m-d H:i:s'),
                'total_minutes' => $totalMinutes,
                'total_hours' => $totalHours,
                'remaining_minutes' => $remainingMinutes,
                'formatted_time' => $totalHours . 'h ' . $remainingMinutes . 'm'
            ];
        }
        
        return response()->json($debugInfo);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test save timesheet functionality
Route::get('/test-save-timesheet', function() {
    try {
        // Simulate saving a timesheet
        $controller = new \App\Http\Controllers\TimesheetController();
        
        $request = new \Illuminate\Http\Request([
            'employee_id' => 1,
            'employee_name' => 'Test Employee',
            'department' => 'IT',
            'timesheet_data' => [
                'weekly_data' => [
                    'Monday' => ['date' => '10/06/25', 'time_in' => '9:00 AM', 'time_out' => '5:00 PM'],
                    'Tuesday' => ['date' => '10/07/25', 'time_in' => '9:00 AM', 'time_out' => '5:00 PM']
                ],
                'total_hours' => 16,
                'overtime_hours' => 0,
                'ai_insights' => ['Good attendance pattern']
            ]
        ]);
        
        $response = $controller->saveTimesheet($request);
        $data = json_decode($response->getContent(), true);
        
        // Check what was actually saved
        $savedTimesheet = DB::table('ai_generated_timesheets')
            ->orderBy('created_at', 'desc')
            ->first();
        
        return response()->json([
            'save_response' => $data,
            'saved_timesheet' => $savedTimesheet,
            'generated_at_field' => $savedTimesheet->generated_at ?? 'NULL',
            'test_info' => [
                'save_endpoint' => '/api/ai-timesheets/save',
                'expected_behavior' => 'generated_at should be set to current timestamp when Save button is clicked'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test statistics functionality
Route::get('/test-statistics', function() {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        $response = $controller->getStatistics();
        $data = json_decode($response->getContent(), true);
        
        // Also test the foreach logic directly
        $timesheets = DB::table('ai_generated_timesheets')->get();
        $manualStats = [
            'total_timesheets' => 0,
            'pending_timesheets' => 0,
            'approved_timesheets' => 0,
            'rejected_timesheets' => 0,
            'total_hours' => 0
        ];
        
        foreach ($timesheets as $timesheet) {
            $manualStats['total_timesheets']++;
            
            switch ($timesheet->status) {
                case 'pending':
                    $manualStats['pending_timesheets']++;
                    break;
                case 'approved':
                    $manualStats['approved_timesheets']++;
                    break;
                case 'rejected':
                    $manualStats['rejected_timesheets']++;
                    break;
            }
            
            $manualStats['total_hours'] += (float) ($timesheet->total_hours ?? 0);
        }
        
        return response()->json([
            'controller_response' => $data,
            'manual_calculation' => $manualStats,
            'raw_timesheets_sample' => $timesheets->take(3),
            'test_info' => [
                'api_endpoint' => '/api/ai-timesheets/statistics',
                'foreach_working' => 'Yes - using foreach loops as requested',
                'database_integration' => 'Yes - pulling from ai_generated_timesheets table'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test without JOIN to isolate the issue
Route::get('/test-no-join', function() {
    try {
        // Test query without JOIN first
        $timesheetsNoJoin = DB::table('ai_generated_timesheets')
            ->select('*')
            ->orderBy('created_at', 'desc')
            ->get();

        // Test if employees table exists and has data
        $employeesExist = Schema::hasTable('employees');
        $employeeCount = $employeesExist ? DB::table('employees')->count() : 0;
        $sampleEmployee = $employeesExist ? DB::table('employees')->first() : null;

        return response()->json([
            'timesheets_without_join' => [
                'count' => $timesheetsNoJoin->count(),
                'sample' => $timesheetsNoJoin->take(2)
            ],
            'employees_info' => [
                'table_exists' => $employeesExist,
                'count' => $employeeCount,
                'sample' => $sampleEmployee
            ],
            'diagnosis' => $timesheetsNoJoin->count() > 0 ? 'Timesheets exist, issue might be with JOIN' : 'No timesheets found at all'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Simple diagnostic for timesheet display issue
Route::get('/debug-timesheets', function() {
    try {
        // Test the exact same query as the controller
        $timesheets = DB::table('ai_generated_timesheets')
            ->join('employees', 'ai_generated_timesheets.employee_id', '=', 'employees.id')
            ->select(
                'ai_generated_timesheets.id',
                'ai_generated_timesheets.employee_id',
                'ai_generated_timesheets.employee_name',
                'ai_generated_timesheets.department',
                'ai_generated_timesheets.week_start_date',
                'ai_generated_timesheets.total_hours',
                'ai_generated_timesheets.overtime_hours',
                'ai_generated_timesheets.status',
                'ai_generated_timesheets.created_at as generated_date',
                'ai_generated_timesheets.generated_at',
                DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as full_employee_name")
            )
            ->orderBy('ai_generated_timesheets.created_at', 'desc')
            ->get();

        // Test the controller method directly
        $controller = new \App\Http\Controllers\TimesheetController();
        $controllerResponse = $controller->getPendingTimesheets();
        $controllerData = json_decode($controllerResponse->getContent(), true);

        return response()->json([
            'raw_query_count' => $timesheets->count(),
            'raw_query_sample' => $timesheets->take(2),
            'controller_response' => $controllerData,
            'employees_table_exists' => Schema::hasTable('employees'),
            'ai_timesheets_table_exists' => Schema::hasTable('ai_generated_timesheets'),
            'join_test' => 'Testing if JOIN is working properly'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
    }
});

// Test pending timesheets API
Route::get('/test-pending-timesheets', function() {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        $response = $controller->getPendingTimesheets();
        $data = json_decode($response->getContent(), true);
        
        // Also check raw database data
        $rawData = DB::table('ai_generated_timesheets')
            ->select('*')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'api_response' => $data,
            'raw_database_count' => $rawData->count(),
            'raw_database_sample' => $rawData->take(3),
            'test_info' => [
                'api_endpoint' => '/api/ai-timesheets/pending',
                'expected_status' => 'pending',
                'table_name' => 'ai_generated_timesheets'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test attendance database integration
Route::get('/test-attendance-integration/{employeeId}', function($employeeId) {
    try {
        $weekStart = \Carbon\Carbon::now()->startOfWeek();
        $weekEnd = \Carbon\Carbon::now()->endOfWeek();
        
        // Check what's in the attendance table
        $attendances = DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->orderBy('date')
            ->get();
        
        // Test the controller method directly
        $controller = new \App\Http\Controllers\TimesheetController();
        $recentAttendance = DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->where('date', '>=', $weekStart->copy()->subWeeks(2)->format('Y-m-d'))
            ->where('date', '<=', $weekStart->copy()->endOfWeek()->format('Y-m-d'))
            ->orderBy('date', 'desc')
            ->get();
        
        // Get employee data
        $employee = DB::table('employees')->where('id', $employeeId)->first();
        
        // Use reflection to call private method for testing
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('generateWeeklySchedule');
        $method->setAccessible(true);
        $weeklyData = $method->invoke($controller, $employee, $recentAttendance);
        
        return response()->json([
            'employee_info' => $employee,
            'week_range' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
            'raw_attendance_data' => $attendances,
            'recent_attendance_count' => $recentAttendance->count(),
            'generated_weekly_data' => $weeklyData,
            'test_summary' => [
                'attendance_records_found' => $attendances->count(),
                'days_with_data' => $attendances->pluck('date')->toArray(),
                'expected_format' => 'Date should be m/d/y (10/07/25), break only when attendance exists'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Complete test with seeding and generation
Route::get('/full-ai-test/{employeeId}', function($employeeId) {
    try {
        // Step 1: Seed current week attendance data
        Artisan::call('db:seed', ['--class' => 'CurrentWeekAttendanceSeeder']);
        
        // Step 2: Clear existing AI timesheets
        DB::table('ai_generated_timesheets')->where('employee_id', $employeeId)->delete();
        
        // Step 3: Check what attendance data we have
        $weekStart = \Carbon\Carbon::now()->startOfWeek();
        $weekEnd = \Carbon\Carbon::now()->endOfWeek();
        
        $currentWeekAttendances = DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->get();
        
        // Step 4: Generate new timesheet
        $controller = new \App\Http\Controllers\TimesheetController();
        $response = $controller->generateAITimesheet($employeeId);
        $data = json_decode($response->getContent(), true);
        
        // Step 5: Get the generated timesheet for viewing
        $viewResponse = $controller->getLatestAITimesheet($employeeId);
        $viewData = json_decode($viewResponse->getContent(), true);
        
        return response()->json([
            'step1_seeding' => 'Completed',
            'step2_clearing' => 'Completed', 
            'step3_attendance_data' => [
                'week_range' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
                'attendance_count' => $currentWeekAttendances->count(),
                'attendances' => $currentWeekAttendances
            ],
            'step4_generation' => $data,
            'step5_view_data' => $viewData,
            'test_summary' => [
                'employee_id' => $employeeId,
                'test_time' => now()->format('Y-m-d H:i:s'),
                'status' => 'completed'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Comprehensive AI Timesheet Test
Route::get('/test-complete-ai/{employeeId}', function($employeeId) {
    try {
        // Step 1: Check attendance data
        $attendances = DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->orderBy('date', 'desc')
            ->get();
            
        $weekStart = \Carbon\Carbon::now()->startOfWeek();
        $weekEnd = \Carbon\Carbon::now()->endOfWeek();
        
        $currentWeekAttendances = DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->get();
        
        // Step 2: Clear existing AI timesheet
        DB::table('ai_generated_timesheets')->where('employee_id', $employeeId)->delete();
        
        // Step 3: Generate new timesheet
        $controller = new \App\Http\Controllers\TimesheetController();
        $response = $controller->generateAITimesheet($employeeId);
        $data = json_decode($response->getContent(), true);
        
        // Step 4: Get the generated timesheet
        $viewResponse = $controller->getLatestAITimesheet($employeeId);
        $viewData = json_decode($viewResponse->getContent(), true);
        
        return response()->json([
            'step1_attendance_check' => [
                'total_attendances' => $attendances->count(),
                'current_week_attendances' => $currentWeekAttendances->count(),
                'week_range' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
                'sample_attendance' => $currentWeekAttendances->first()
            ],
            'step2_generation_result' => $data,
            'step3_view_result' => $viewData,
            'debug_info' => [
                'employee_id' => $employeeId,
                'current_time' => now()->format('Y-m-d H:i:s'),
                'test_status' => 'completed'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Check attendance data in database
Route::get('/check-attendance/{employeeId?}', function($employeeId = null) {
    try {
        $query = DB::table('attendances');
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        
        $attendances = $query->orderBy('date', 'desc')->limit(10)->get();
        
        $weekStart = \Carbon\Carbon::now()->startOfWeek();
        $weekEnd = \Carbon\Carbon::now()->endOfWeek();
        
        $currentWeekAttendances = DB::table('attendances')
            ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->get();
        
        return response()->json([
            'total_attendances' => DB::table('attendances')->count(),
            'recent_attendances' => $attendances,
            'current_week_start' => $weekStart->format('Y-m-d'),
            'current_week_end' => $weekEnd->format('Y-m-d'),
            'current_week_attendances' => $currentWeekAttendances,
            'employee_id_filter' => $employeeId
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Debug AI Timesheet Generation Route
Route::get('/debug-ai-generation/{employeeId}', function($employeeId) {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        
        // Clear any existing timesheet
        $controller->clearAITimesheet($employeeId);
        
        // Generate new timesheet
        $response = $controller->generateAITimesheet($employeeId);
        $data = json_decode($response->getContent(), true);
        
        // Return formatted debug info
        return response()->json([
            'success' => $data['success'] ?? false,
            'message' => $data['message'] ?? 'No message',
            'employee_id' => $employeeId,
            'current_time' => now()->format('Y-m-d H:i:s'),
            'week_start' => \Carbon\Carbon::now()->startOfWeek()->format('Y-m-d'),
            'sample_data' => $data['data']['weekly_data'] ?? 'No weekly data',
            'raw_response' => $data
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test AI Timesheet Data Route
Route::get('/test-ai-data/{employeeId}', function($employeeId) {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        
        // Clear existing timesheet first
        $controller->clearAITimesheet($employeeId);
        
        // Generate timesheet
        $generateResponse = $controller->generateAITimesheet($employeeId);
        $generateData = json_decode($generateResponse->getContent(), true);
        
        if (!$generateData['success']) {
            return response()->json(['error' => 'Generation failed: ' . $generateData['message']]);
        }
        
        // Get timesheet data
        $viewResponse = $controller->getLatestAITimesheet($employeeId);
        $viewData = json_decode($viewResponse->getContent(), true);
        
        return response()->json([
            'generation_result' => $generateData,
            'view_result' => $viewData,
            'test_status' => 'success',
            'debug_info' => [
                'employee_id' => $employeeId,
                'current_time' => now()->format('Y-m-d H:i:s'),
                'week_start' => \Carbon\Carbon::now()->startOfWeek()->format('Y-m-d'),
                'sample_weekly_data' => $generateData['data']['weekly_data'] ?? 'No weekly data'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// AI Timesheet Generation Web Routes (public access for AJAX calls)
Route::prefix('api/ai-timesheets')->middleware(['web'])->group(function () {
    // AI Generation Routes
    Route::get('/test/{employeeId}', [TimesheetController::class, 'testAIGeneration']);
    Route::post('/generate/{employeeId}', [TimesheetController::class, 'generateAITimesheet']);
    Route::post('/generate-all', [TimesheetController::class, 'generateAllTimesheets']);
    
    // AI Timesheet Viewing Routes
    Route::get('/view/{employeeId}', [TimesheetController::class, 'getLatestAITimesheet']);
    Route::delete('/clear/{employeeId}', [TimesheetController::class, 'clearAITimesheet']);
    
    // Timesheet Management Routes
    Route::get('/pending', [TimesheetController::class, 'getPendingTimesheets']);
    Route::post('/save', [TimesheetController::class, 'saveAITimesheet']);
    Route::get('/saved/{id}', [TimesheetController::class, 'getSavedTimesheet']);
    
    // Timesheet Action Routes
    Route::post('/approve/{id}', [TimesheetController::class, 'approveTimesheet']);
    Route::post('/reject/{id}', [TimesheetController::class, 'rejectTimesheet']);
    Route::delete('/delete/{id}', [TimesheetController::class, 'deleteTimesheet']);
    
    // Timesheet Actions
    Route::post('/{id}/approve', [TimesheetController::class, 'approveTimesheet']);
    Route::post('/{id}/reject', [TimesheetController::class, 'rejectTimesheet']);
    Route::post('/{id}/send-to-payroll', [TimesheetController::class, 'sendToPayroll']);
    Route::delete('/{id}', [TimesheetController::class, 'deleteTimesheet']);
    
    // Statistics and Testing
    Route::get('/statistics', [TimesheetController::class, 'getStatistics']);
    Route::middleware(['web'])->group(function () {
        Route::post('/{id}/approve', [TimesheetController::class, 'approveAITimesheet']);
        Route::post('/{id}/reject', [TimesheetController::class, 'rejectAITimesheet']);
        Route::delete('/{id}', [TimesheetController::class, 'deleteAITimesheet']);
        Route::post('/{id}/send-to-payroll', [TimesheetController::class, 'sendAITimesheetToPayroll']);
    });
});

// Simple test route to verify controller works
Route::get('/test-ai/{employeeId}', [SystemTestController::class, 'testAi'])->middleware('auth');

// Simple working AI timesheet routes
Route::get('/simple-ai-test/{employeeId}', function($employeeId) {
    try {
        $employee = DB::table('employees')->where('id', $employeeId)->first();
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found']);
        }
        return response()->json([
            'success' => true, 
            'message' => 'AI generation test successful',
            'employee' => $employee->first_name . ' ' . $employee->last_name
        ]);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});

// AI timesheet generation routes - using proper controller methods
Route::get('/generate-ai-timesheet/{employeeId}', [TimesheetController::class, 'generateAITimesheet'])->name('timesheet.generate.ai');
Route::get('/ai-timesheet/{timesheetId}', [TimesheetController::class, 'getAITimesheet'])->name('timesheet.ai.details');

Route::get('/generate-all-ai-timesheets', function() {
    try {
        $employees = DB::table('employees')->get();
        $generated = [];
        
        foreach ($employees as $employee) {
            $generated[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'status' => 'success'
            ];
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Generated AI timesheets for all employees',
            'generated' => $generated,
            'total' => count($employees)
        ]);
        
    } catch (Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
});

// Test save functionality
Route::post('/test-save', [SystemTestController::class, 'testSave'])->middleware('auth');

// Removed duplicate routes - using the Web methods defined above

// ===== ADMIN MANAGEMENT ROUTES (uses 'web' guard - 'users' table) =====
Route::middleware(['auth:employee'])->group(function () {
    
    // Attendance routes for employee dashboard
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak'])->name('attendance.start-break');
    Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak'])->name('attendance.end-break');
    Route::get('/attendance/status/{employeeId}', [AttendanceController::class, 'getStatus'])->name('attendance.status');
    Route::get('/attendance/logs/{employeeId}', [AttendanceController::class, 'getLogs'])->name('attendance.logs');
    // Route moved outside middleware - see line after this middleware group
    
    // Employee Management Routes - Using Proper MVC Controller
    Route::get('/employees', [EmployeeManagementController::class, 'index'])->name('employees.index');
    Route::post('/employees/export-data', [EmployeeManagementController::class, 'exportData'])->name('employees.export-data');
    Route::post('/employees/{id}/export', [EmployeeManagementController::class, 'exportSingleEmployee'])->name('employees.export-single');
    
    // Test route for export functionality
    Route::get('/test-export', function() {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get('http://hr4.jetlougetravels-ph.com/api/employees');
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'API connection successful',
                    'count' => count($data),
                    'sample' => array_slice($data, 0, 2)
                ]);
            } else {
                return response()->json(['error' => 'API request failed', 'status' => $response->status()]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Exception: ' . $e->getMessage()]);
        }
    })->name('test.export');
    
    // Debug route for shifts
    Route::get('/debug-shifts', function() {
        try {
            $today = today()->toDateString();
            
            // Get all shift types
            $shiftTypes = \DB::table('shift_types')->get();
            
            // Get all shifts
            $allShifts = \DB::table('shifts')
                ->select('*')
                ->get();
                
            // Get shifts for today
            $todayShifts = \DB::table('shifts')
                ->whereDate('shift_date', today())
                ->get();
                
            // Get employees
            $employees = \DB::table('employees')->where('status', 'active')->get();
            
            return response()->json([
                'today_date' => $today,
                'shift_types_count' => $shiftTypes->count(),
                'shift_types' => $shiftTypes,
                'all_shifts_count' => $allShifts->count(),
                'all_shifts' => $allShifts,
                'today_shifts_count' => $todayShifts->count(),
                'today_shifts' => $todayShifts,
                'active_employees_count' => $employees->count(),
                'active_employees' => $employees->take(3)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('debug.shifts');
    Route::post('/employees', [EmployeesController::class, 'store'])->name('employees.store');
    Route::get('/employees/{id}/view', [EmployeesController::class, 'view'])->name('employees.view');
    Route::put('/employees/{id}', [EmployeesController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{id}', [EmployeesController::class, 'destroy'])->name('employees.destroy');
    
    // Employee List Routes - Using Employee List Controller
    Route::get('/employees/list', [App\Http\Controllers\EmployeeListController::class, 'index'])->name('employees.list');
    Route::post('/employees/list', [App\Http\Controllers\EmployeeListController::class, 'store'])->name('employees.list.store');
    Route::get('/employees/list/{id}/view', [App\Http\Controllers\EmployeeListController::class, 'show'])->name('employees.list.view');
    Route::put('/employees/list/{id}', [App\Http\Controllers\EmployeeListController::class, 'update'])->name('employees.list.update');
    Route::delete('/employees/list/{id}', [App\Http\Controllers\EmployeeListController::class, 'destroy'])->name('employees.list.destroy');
    
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
    
    // Attendance Management Routes (using AttendanceController for clock-in/out)
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

// Recent time entries route (outside middleware for easier access)
Route::get('/recent-time-entries', function() {
    try {
        \Log::info('=== Direct Recent Time Entries Route ===');
        
        // Simple direct query to avoid any controller issues
        $attendances = \DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->select(
                'attendances.id',
                'attendances.date',
                'attendances.clock_in_time',
                'attendances.clock_out_time',
                'attendances.total_hours',
                'employees.first_name',
                'employees.last_name',
                'employees.profile_picture'
            )
            ->orderBy('attendances.date', 'desc')
            ->orderBy('attendances.id', 'desc')
            ->limit(3)
            ->get();
        
        \Log::info('Retrieved ' . $attendances->count() . ' attendance records directly');
        
        $processedEntries = $attendances->map(function($attendance) {
            // Handle total_hours safely and format as time
            $totalHours = null;
            $formattedTotalTime = '--';
            if ($attendance->total_hours !== null) {
                $hours = abs(floatval($attendance->total_hours)); // Ensure positive number
                $totalHours = $hours;
                
                // Format as "Xh Ym" instead of "X.XX hrs"
                $wholeHours = floor($hours);
                $minutes = round(($hours - $wholeHours) * 60);
                
                if ($wholeHours > 0 && $minutes > 0) {
                    $formattedTotalTime = $wholeHours . 'h ' . $minutes . 'm';
                } elseif ($wholeHours > 0) {
                    $formattedTotalTime = $wholeHours . 'h';
                } elseif ($minutes > 0) {
                    $formattedTotalTime = $minutes . 'm';
                } else {
                    $formattedTotalTime = '0m';
                }
            }
            
            // Format date
            $workDate = '--';
            if ($attendance->date) {
                try {
                    $workDate = \Carbon\Carbon::parse($attendance->date)->format('M d, Y');
                } catch (\Exception $e) {
                    $workDate = '--';
                }
            }
            
            // Format times
            $clockIn = '--';
            $clockOut = '--';
            
            if ($attendance->clock_in_time) {
                try {
                    $clockIn = \Carbon\Carbon::parse($attendance->clock_in_time)->format('g:i A');
                } catch (\Exception $e) {
                    $clockIn = '--';
                }
            }
            
            if ($attendance->clock_out_time) {
                try {
                    $clockOut = \Carbon\Carbon::parse($attendance->clock_out_time)->format('g:i A');
                } catch (\Exception $e) {
                    $clockOut = '--';
                }
            }
            
            // Employee name
            $employeeName = trim(($attendance->first_name ?? '') . ' ' . ($attendance->last_name ?? ''));
            if (empty($employeeName)) {
                $employeeName = 'Unknown Employee';
            }
            
            return [
                'id' => $attendance->id,
                'employee_name' => $employeeName,
                'profile_picture' => $attendance->profile_picture,
                'work_date' => $workDate,
                'formatted_clock_in' => $clockIn,
                'formatted_clock_out' => $clockOut,
                'total_hours' => $totalHours,
                'formatted_total_time' => $formattedTotalTime,
                'status' => ($totalHours !== null && $totalHours >= 8) ? 'success' : 'secondary'
            ];
        });
        
        return response()->json([
            'success' => true,
            'entries' => $processedEntries,
            'count' => $processedEntries->count(),
            'message' => 'Data retrieved successfully'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in direct recent-time-entries route: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Database error occurred',
            'error' => $e->getMessage(),
            'entries' => []
        ], 500);
    }
});

// Simple attendance data creation for testing
Route::get('/create-sample-data', function() {
    try {
        // Clear existing data for employee 1
        \DB::table('attendances')->where('employee_id', 1)->delete();
        
        // Simple insert with minimal data
        $records = [
            [
                'employee_id' => 1,
                'date' => '2025-10-09',
                'clock_in_time' => '2025-10-09 08:30:00',
                'status' => 'present',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 1,
                'date' => '2025-10-08',
                'clock_in_time' => '2025-10-08 09:15:00',
                'clock_out_time' => '2025-10-08 18:30:00',
                'total_hours' => 8.25,
                'status' => 'late',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 1,
                'date' => '2025-10-07',
                'clock_in_time' => '2025-10-07 08:00:00',
                'clock_out_time' => '2025-10-07 17:30:00',
                'total_hours' => 8.5,
                'status' => 'present',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 1,
                'date' => '2025-10-04',
                'clock_in_time' => '2025-10-04 08:45:00',
                'clock_out_time' => '2025-10-04 17:15:00',
                'total_hours' => 7.5,
                'status' => 'late',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 1,
                'date' => '2025-10-03',
                'clock_in_time' => '2025-10-03 08:15:00',
                'clock_out_time' => '2025-10-03 19:00:00',
                'total_hours' => 9.75,
                'status' => 'present',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 1,
                'date' => '2025-10-02',
                'status' => 'absent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 1,
                'date' => '2025-10-01',
                'clock_in_time' => '2025-10-01 08:00:00',
                'clock_out_time' => '2025-10-01 17:00:00',
                'total_hours' => 8.0,
                'status' => 'present',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        \DB::table('attendances')->insert($records);
        
        return '<h1>✅ Success!</h1><p>Added ' . count($records) . ' sample attendance records for employee ID 1</p><p><a href="/dashboard">Go to Dashboard</a> to see the attendance logs</p>';
        
    } catch (\Exception $e) {
        return '<h1>❌ Error</h1><p>' . $e->getMessage() . '</p>';
    }
});

// Fix negative hours in attendance records
Route::get('/fix-attendance-hours', function() {
    try {
        $attendances = \DB::table('attendances')
            ->whereNotNull('clock_in_time')
            ->whereNotNull('clock_out_time')
            ->where(function($query) {
                $query->where('total_hours', '<', 0)
                      ->orWhereNull('total_hours');
            })
            ->get();
        
        $fixed = 0;
        foreach ($attendances as $record) {
            $attendance = \App\Models\Attendance::find($record->id);
            if ($attendance) {
                $totalHours = $attendance->calculateTotalHours();
                $overtimeHours = $attendance->calculateOvertimeHours();
                
                $attendance->update([
                    'total_hours' => $totalHours,
                    'overtime_hours' => $overtimeHours
                ]);
                $fixed++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Fixed {$fixed} attendance records with negative or null hours",
            'total_checked' => count($attendances)
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fixing attendance hours: ' . $e->getMessage()
        ]);
    }
});

// Quick fix for negative hours - make all hours positive
Route::get('/fix-negative-hours', function() {
    try {
        $updated = \DB::table('attendances')
            ->where('total_hours', '<', 0)
            ->update(['total_hours' => \DB::raw('ABS(total_hours)')]);
            
        $updatedOvertime = \DB::table('attendances')
            ->where('overtime_hours', '<', 0)
            ->update(['overtime_hours' => \DB::raw('ABS(overtime_hours)')]);
        
        return response()->json([
            'success' => true,
            'message' => "Fixed negative hours: {$updated} total hours, {$updatedOvertime} overtime hours made positive",
            'total_hours_fixed' => $updated,
            'overtime_hours_fixed' => $updatedOvertime
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fixing negative hours: ' . $e->getMessage()
        ]);
    }
});

// Test AI timesheet calculation
Route::get('/test-ai-timesheet/{employeeId}', function($employeeId) {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        $request = new \Illuminate\Http\Request();
        $response = $controller->generateAITimesheet($request, $employeeId);
        return $response;
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Test failed: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test route for recent time entries
Route::get('/test-recent-entries', function() {
    try {
        $controller = new \App\Http\Controllers\HRDashboardController();
        $response = $controller->getRecentTimeEntriesJson();
        return $response;
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Test failed: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test routes for attendance functionality
Route::get('/test-attendance-functionality', function() {
    try {
        // Check if we can access the database
        $employeeCount = \DB::table('employees')->count();
        $attendanceCount = \DB::table('attendances')->count();
        
        // Get a test employee
        $testEmployee = \DB::table('employees')->first();
        
        if (!$testEmployee) {
            return response()->json([
                'error' => 'No employees found in database',
                'employees_count' => $employeeCount,
                'attendances_count' => $attendanceCount
            ]);
        }
        
        // Check if attendance table has correct structure
        $attendanceColumns = \DB::getSchemaBuilder()->getColumnListing('attendances');
        
        // Test attendance model
        $attendance = \App\Models\Attendance::getTodayAttendance($testEmployee->id);
        
        return response()->json([
            'success' => true,
            'database_connection' => 'OK',
            'employees_count' => $employeeCount,
            'attendances_count' => $attendanceCount,
            'test_employee' => [
                'id' => $testEmployee->id,
                'name' => $testEmployee->first_name . ' ' . $testEmployee->last_name,
                'email' => $testEmployee->email
            ],
            'attendance_columns' => $attendanceColumns,
            'today_attendance' => $attendance ? [
                'id' => $attendance->id,
                'status' => $attendance->status,
                'clock_in' => $attendance->clock_in_time,
                'clock_out' => $attendance->clock_out_time,
                'is_clocked_in' => $attendance->isClockedIn(),
                'is_clocked_out' => $attendance->isClockedOut()
            ] : null
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Database error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Quick add sample attendance data for testing
Route::get('/add-sample-attendance/{employeeId?}', function($employeeId = 1) {
    try {
        // First, check if employee exists
        $employee = \DB::table('employees')->where('id', $employeeId)->first();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID ' . $employeeId . ' not found. Available employees: ' . \DB::table('employees')->pluck('id')->implode(', ')
            ]);
        }
        
        // Clear existing attendance for this employee (to avoid duplicates)
        \DB::table('attendances')->where('employee_id', $employeeId)->delete();
        
        $attendanceData = [];
        
        // Add sample data for the past 10 days
        for ($i = 9; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i);
            
            // Skip weekends
            if ($date->dayOfWeek == 0 || $date->dayOfWeek == 6) {
                continue;
            }
            
            if ($i == 0) {
                // Today - only clock in
                $attendanceData[] = [
                    'employee_id' => $employeeId,
                    'date' => $date->format('Y-m-d'),
                    'clock_in_time' => $date->setTime(8, 30, 0),
                    'clock_out_time' => null,
                    'status' => 'present',
                    'location' => 'Office',
                    'ip_address' => '192.168.1.100',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } elseif ($i == 1) {
                // Yesterday - late with overtime
                $clockIn = $date->setTime(9, 15, 0);
                $clockOut = $date->setTime(18, 30, 0);
                $attendanceData[] = [
                    'employee_id' => $employeeId,
                    'date' => $date->format('Y-m-d'),
                    'clock_in_time' => $clockIn,
                    'clock_out_time' => $clockOut,
                    'break_start_time' => $date->copy()->setTime(12, 0, 0),
                    'break_end_time' => $date->copy()->setTime(13, 0, 0),
                    'total_hours' => 8.25,
                    'overtime_hours' => 0.25,
                    'status' => 'late',
                    'location' => 'Office',
                    'ip_address' => '192.168.1.101',
                    'notes' => 'Traffic delay',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } elseif ($i == 3) {
                // Absent day
                $attendanceData[] = [
                    'employee_id' => $employeeId,
                    'date' => $date->format('Y-m-d'),
                    'clock_in_time' => null,
                    'clock_out_time' => null,
                    'status' => 'absent',
                    'location' => null,
                    'ip_address' => null,
                    'notes' => 'Sick leave',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                // Regular working days
                $clockInHour = rand(8, 9);
                $clockInMinute = rand(0, 30);
                $clockIn = $date->setTime($clockInHour, $clockInMinute, 0);
                $clockOut = $date->setTime(rand(17, 18), rand(0, 59), 0);
                $totalHours = round($clockOut->diffInMinutes($clockIn) / 60 - 1, 2); // Subtract 1 hour lunch
                
                $attendanceData[] = [
                    'employee_id' => $employeeId,
                    'date' => $date->format('Y-m-d'),
                    'clock_in_time' => $clockIn,
                    'clock_out_time' => $clockOut,
                    'break_start_time' => $date->copy()->setTime(12, 0, 0),
                    'break_end_time' => $date->copy()->setTime(13, 0, 0),
                    'total_hours' => $totalHours,
                    'overtime_hours' => max(0, $totalHours - 8),
                    'status' => ($clockInHour > 8 || ($clockInHour == 8 && $clockInMinute > 30)) ? 'late' : 'present',
                    'location' => 'Office',
                    'ip_address' => '192.168.1.' . rand(100, 200),
                    'notes' => rand(1, 3) == 1 ? 'Productive day' : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Insert the data
        \DB::table('attendances')->insert($attendanceData);
        
        return response()->json([
            'success' => true,
            'message' => 'Added ' . count($attendanceData) . ' sample attendance records for employee: ' . $employee->first_name . ' ' . $employee->last_name . ' (ID: ' . $employeeId . ')',
            'employee' => $employee,
            'records_added' => count($attendanceData),
            'data' => $attendanceData
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to add sample data: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
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












// Debug route to check employee positions
Route::get('/debug-employees', function() {
    $employees = App\Models\Employee::select('email', 'position', 'role')->get();
    $output = '<h3>Employee Database Check:</h3><ul>';
    foreach($employees as $emp) {
        $output .= '<li>' . $emp->email . ' - Position: ' . ($emp->position ?? 'NULL') . ' - Role: ' . ($emp->role ?? 'NULL') . '</li>';
    }
    $output .= '</ul>';
    return $output;
});


// Debug route to test claim authentication
Route::get('/test-claim-auth', function() {
    $claims = DB::select('SELECT id, employee_id, amount, status FROM claims LIMIT 5');
    $employees = App\Models\Employee::select('email', 'position')->get();
    
    $output = '<h3>Test Claim Authentication</h3>';
    $output .= '<h4>Available Claims:</h4><ul>';
    foreach($claims as $claim) {
        $output .= '<li>Claim ID: ' . $claim->id . ' - Amount: ' . $claim->amount . ' - Status: ' . $claim->status . '</li>';
    }
    $output .= '</ul>';
    
    $output .= '<h4>Authorized Employees:</h4><ul>';
    foreach($employees as $emp) {
        $authorizedPositions = ['HR Manager', 'System Administrator', 'HR Scheduler', 'Admin', 'HR Administrator'];
        $isAuthorized = in_array($emp->position, $authorizedPositions) ? 'YES' : 'NO';
        $output .= '<li>' . $emp->email . ' - Position: ' . $emp->position . ' - Authorized: ' . $isAuthorized . '</li>';
    }
    $output .= '</ul>';
    
    return $output;
});
// Debug routes for AI timesheet testing
Route::get('/test-ai-timesheets', function() {
    try {
        $records = DB::table('ai_generated_timesheets')->get();
        
        $output = "<h2>AI Timesheet Debug</h2>";
        $output .= "<p>Total records in database: " . $records->count() . "</p>";
        
        if ($records->count() > 0) {
            $output .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            $output .= "<tr><th>ID</th><th>Employee</th><th>Department</th><th>Total Hours</th><th>Overtime</th><th>Status</th><th>Generated At</th></tr>";
            
            foreach ($records as $record) {
                $output .= "<tr>";
                $output .= "<td>{$record->id}</td>";
                $output .= "<td>{$record->employee_name}</td>";
                $output .= "<td>{$record->department}</td>";
                $output .= "<td>{$record->total_hours}</td>";
                $output .= "<td>{$record->overtime_hours}</td>";
                $output .= "<td>{$record->status}</td>";
                $output .= "<td>{$record->generated_at}</td>";
                $output .= "</tr>";
            }
            $output .= "</table>";
        }
        
        $output .= "<br><h3>Test API Endpoint</h3>";
        $output .= "<button onclick='testAPI()'>Test /api/ai-timesheets/pending</button>";
        $output .= "<div id='api-result'></div>";
        
        $output .= "<script>
        function testAPI() {
            fetch('/api/ai-timesheets/pending')
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('API Response:', data);
                    document.getElementById('api-result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(error => {
                    console.error('API Error:', error);
                    document.getElementById('api-result').innerHTML = '<p style=\"color: red;\">Error: ' + error.message + '</p>';
                });
        }
        </script>";
        
        return $output;
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Test the controller method directly
Route::get('/test-controller', function() {
    try {
        $controller = new \App\Http\Controllers\TimesheetController();
        $request = new \Illuminate\Http\Request();
        $response = $controller->getPendingTimesheets($request);
        
        return [
            'status_code' => $response->getStatusCode(),
            'content' => json_decode($response->getContent(), true)
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
});

// Debug route to test authentication and data (no auth required)
Route::get('/debug-timesheet-auth', function() {
    try {
        $webAuth = auth()->guard('web')->check();
        $employeeAuth = auth()->guard('employee')->check();
        $webUser = auth()->guard('web')->user();
        $employeeUser = auth()->guard('employee')->user();
        
        return response()->json([
            'web_auth' => $webAuth ? 'Authenticated' : 'Not authenticated',
            'employee_auth' => $employeeAuth ? 'Authenticated' : 'Not authenticated',
            'web_user' => $webUser ? ($webUser->email ?? 'No email') : 'No user',
            'employee_user' => $employeeUser ? ($employeeUser->email ?? 'No email') : 'No user',
            'employee_role' => $employeeUser ? ($employeeUser->position ?? 'No position') : 'No employee',
            'employee_count' => DB::table('employees')->count(),
            'admin_count' => DB::table('users')->count(),
            'attendance_count' => DB::table('attendances')->count(),
            'claims_count' => DB::table('claims')->count(),
            'shifts_count' => DB::table('shifts')->count(),
            'login_options' => [
                'admin_login' => url('/admin/login'),
                'employee_login' => url('/employee/login'),
                'test_without_auth' => url('/timesheet-management-test')
            ]
        ]);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Test route without authentication to verify controller works
Route::get('/test-timesheet-no-auth', [TimesheetController::class, 'index'])->name('test.timesheet.no.auth');

// TEMPORARY: Timesheet management without authentication for testing
Route::get('/timesheet-management-test', [TimesheetController::class, 'index'])->name('timesheet.management.test');
