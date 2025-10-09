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

// HR Module Routes with proper controllers
Route::middleware(['auth'])->group(function () {

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
