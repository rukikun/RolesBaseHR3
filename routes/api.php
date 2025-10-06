<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClockifyController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\UnifiedHRController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Dashboard API routes
Route::middleware('auth')->group(function () {
    Route::post('/dashboard/clock-in', [DashboardController::class, 'clockIn']);
    Route::post('/dashboard/clock-out', [DashboardController::class, 'clockOut']);
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/recent-entries', [DashboardController::class, 'getRecentEntries']);
    Route::get('/dashboard/timesheet-data', [DashboardController::class, 'getTimesheetData']);
    Route::get('/dashboard/leave-data', [DashboardController::class, 'getLeaveData']);
    Route::get('/dashboard/claims-data', [DashboardController::class, 'getClaimsData']);
    Route::post('/dashboard/attendance-correction', [DashboardController::class, 'submitAttendanceCorrection']);
    Route::post('/dashboard/overtime-request', [DashboardController::class, 'submitOvertimeRequest']);
    Route::post('/dashboard/manual-time-entry', [DashboardController::class, 'manualTimeEntry']);
    
    // Time and Attendance API routes
    Route::get('/dashboard/attendance-status', [DashboardController::class, 'getAttendanceStatus']);
    Route::get('/dashboard/attendance-stats', [DashboardController::class, 'getAttendanceStats']);
    
    // Leave Management API routes
    Route::post('/leave-types', [LeaveController::class, 'createLeaveType']);
    Route::get('/leave-types', [LeaveController::class, 'getLeaveTypes']);
    Route::put('/leave-types/{id}', [LeaveController::class, 'updateLeaveType']);
    Route::delete('/leave-types/{id}', [LeaveController::class, 'deleteLeaveType']);
    Route::get('/dashboard/attendance-log', [DashboardController::class, 'getAttendanceLog']);
    Route::get('/dashboard/attendance-detail/{id}', [DashboardController::class, 'getAttendanceDetail']);
    Route::get('/dashboard/attendance-export', [DashboardController::class, 'exportAttendance']);
    
    // Attendance Management API routes
    Route::get('/attendance', [AttendanceController::class, 'getAttendances']);
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak']);
    Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak']);
    Route::get('/attendance/status/{employeeId}', [AttendanceController::class, 'getStatus']);
    Route::get('/attendance/stats', [AttendanceController::class, 'getStats']);
    
    // Shift Management API routes
    Route::get('/shifts/stats', [ShiftController::class, 'getStats']);
    Route::get('/shifts/types', [ShiftController::class, 'getShiftTypes']);
    Route::post('/shifts/types', [ShiftController::class, 'storeShiftType']);
    Route::get('/shifts/types/{id}', [ShiftController::class, 'show']);
    Route::put('/shifts/types/{id}', [ShiftController::class, 'updateShiftType']);
    Route::delete('/shifts/types/{id}', [ShiftController::class, 'destroyShiftType']);
    Route::get('/shifts/schedule', [ShiftController::class, 'getSchedule']);
    Route::post('/shifts/create', [ShiftController::class, 'createShift']);
    Route::put('/shifts/{id}', [ShiftController::class, 'updateShift']);
    Route::delete('/shifts/{id}', [ShiftController::class, 'deleteShift']);
    Route::post('/shifts/bulk-assign', [ShiftController::class, 'bulkAssignShifts']);
    Route::get('/shifts/requests', [ShiftController::class, 'getShiftRequests']);
    Route::post('/shifts/requests', [ShiftController::class, 'createShiftRequest']);
    Route::put('/shifts/requests/{id}/approve', [ShiftController::class, 'approveShiftRequest']);
    Route::put('/shifts/requests/{id}/reject', [ShiftController::class, 'rejectShiftRequest']);
    Route::post('/shifts/configure-hours', [ShiftController::class, 'configureHours']);
    Route::post('/shifts/save-templates', [ShiftController::class, 'saveTemplates']);
    Route::post('/shifts/publish-schedule', [ShiftController::class, 'publishSchedule']);
    Route::apiResource('shifts', ShiftController::class);
    Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy']);
    
    // Leave Management API routes
    Route::get('/leave/requests', [LeaveController::class, 'getLeaveRequests']);
    Route::get('/leave/balances', [LeaveController::class, 'getLeaveBalances']);
    Route::get('/leave/stats', [LeaveController::class, 'getLeaveStats']);
    Route::get('/leave/balance/{leaveTypeId}', [LeaveController::class, 'getLeaveBalance']);
    Route::post('/leave/requests', [LeaveController::class, 'store']);
    Route::get('/leave/requests/{leaveRequest}', [LeaveController::class, 'show']);
    Route::put('/leave/requests/{leaveRequest}', [LeaveController::class, 'update']);
    Route::delete('/leave/requests/{id}', [LeaveController::class, 'destroy']);
    Route::post('/leave/requests/{leaveRequest}/approve', [LeaveController::class, 'approve']);
    Route::post('/leave/requests/{leaveRequest}/reject', [LeaveController::class, 'reject']);
    Route::post('/leave/requests/bulk-approve', [LeaveController::class, 'bulkApprove']);
    
    // Leave Types Management API routes  
    Route::get('/leave/types', [LeaveController::class, 'getLeaveTypes']);
    Route::post('/leave/types', [LeaveController::class, 'createLeaveType']);
    Route::put('/leave/types/{id}', [LeaveController::class, 'updateLeaveType']);
    Route::delete('/leave/types/{id}', [LeaveController::class, 'deleteLeaveType']);
    
    // Claims Management API routes
    Route::get('/claims', [ClaimController::class, 'getClaims']);
    // Removed conflicting POST /claims route - use web route instead
    Route::get('/claims/stats', [ClaimController::class, 'getClaimsStats']);
    Route::post('/claims/validate-attachments', [ClaimController::class, 'validateAttachments']);
    Route::post('/claims/forward-payroll', [ClaimController::class, 'forwardToPayroll']);
    Route::get('/claims/{id}', [ClaimController::class, 'show']);
    Route::put('/claims/{id}', [ClaimController::class, 'update']);
    Route::delete('/claims/{id}', [ClaimController::class, 'destroy']);
    Route::post('/claims/{id}/approve', [ClaimController::class, 'approve']);
    Route::post('/claims/{id}/reject', [ClaimController::class, 'reject']);
    Route::post('/claims/{id}/pay', [ClaimController::class, 'markPaid']);
    Route::post('/claims/bulk-approve', [ClaimController::class, 'bulkApprove']);
    
    // Claim Types Management API routes
    Route::get('/claims/types', [ClaimController::class, 'getClaimTypes']);
    Route::post('/claims/types', [ClaimController::class, 'storeClaimType']);
    Route::get('/claims/types/{id}', [ClaimController::class, 'showClaimType']);
    Route::put('/claims/types/{id}', [ClaimController::class, 'updateClaimType']);
    Route::delete('/claims/types/{id}', [ClaimController::class, 'destroyClaimType']);
    
    // Employee API routes
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::get('/employees/{id}', [EmployeeController::class, 'show']);
    Route::put('/employees/{id}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
    Route::get('/employees/stats/summary', [EmployeeController::class, 'getStats']);
    Route::get('/employees/departments/list', [EmployeeController::class, 'getDepartments']);
    
    // Users API routes (for employee dropdown)
    Route::get('/users', [EmployeeController::class, 'getUsers']);
    
    // Legacy employee endpoint for timesheets
    Route::get('/employees/dropdown/list', [TimesheetController::class, 'getEmployees']);
    
    // Timesheet CRUD API routes
    Route::get('/timesheets/stats', [TimesheetController::class, 'stats']);
    Route::get('/timesheets', [TimesheetController::class, 'getTimesheets']);
    Route::post('/timesheets', [TimesheetController::class, 'store']);
    Route::get('/timesheets/{id}', [TimesheetController::class, 'show']);
    Route::put('/timesheets/{id}', [TimesheetController::class, 'update']);
    Route::delete('/timesheets/{id}', [TimesheetController::class, 'destroy']);
    Route::post('/timesheets/{id}/approve', [TimesheetController::class, 'approve']);
    Route::post('/timesheets/{id}/reject', [TimesheetController::class, 'reject']);
    Route::post('/timesheets/bulk-approve', [TimesheetController::class, 'bulkApprove']);
    Route::get('/timesheets/export', [TimesheetController::class, 'export']);
    
    // ESS Clock-in/out integration routes
    Route::post('/timesheets/clock-data', [TimesheetController::class, 'createFromClockData']);
    Route::get('/timesheets/employee/{employeeId}', [TimesheetController::class, 'getTimesheetForEmployee']);
    
    // Attendance to Timesheet sync route
    Route::post('/sync-attendance-to-timesheets', [TimesheetController::class, 'syncAttendanceToTimesheets']);
    
    // Fix negative attendance hours route
    Route::post('/fix-negative-attendance-hours', [TimesheetController::class, 'fixNegativeAttendanceHours']);
    
    // Weekly timesheet view route
    Route::get('/timesheets/{id}/weekly', [TimesheetController::class, 'getWeeklyTimesheetForModal']);

    // Claims API routes
    Route::prefix('claims')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ClaimsController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\ClaimsController::class, 'store']);
        Route::get('/statistics', [App\Http\Controllers\Api\ClaimsController::class, 'statistics']);
        Route::get('/{id}', [App\Http\Controllers\Api\ClaimsController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\ClaimsController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\ClaimsController::class, 'destroy']);
        Route::post('/{id}/approve', [App\Http\Controllers\Api\ClaimsController::class, 'approve']);
        Route::post('/{id}/reject', [App\Http\Controllers\Api\ClaimsController::class, 'reject']);
    });

    // Leave Requests API routes
    Route::prefix('leave-requests')->group(function () {
        // Single endpoint for external systems to send data and create leave requests
        Route::post('/receive', [App\Http\Controllers\Api\LeaveRequestController::class, 'receiveData']);
        
        // Approval endpoints
        Route::post('/{id}/approve', [App\Http\Controllers\Api\LeaveRequestController::class, 'approve']);
        Route::post('/{id}/reject', [App\Http\Controllers\Api\LeaveRequestController::class, 'reject']);
    });

    // Attendance API routes
    Route::prefix('attendances')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\AttendanceController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\AttendanceController::class, 'store']);
        Route::get('/statistics', [App\Http\Controllers\Api\AttendanceController::class, 'statistics']);
        Route::get('/status/{employeeId}', [App\Http\Controllers\Api\AttendanceController::class, 'currentStatus']);
        Route::get('/{id}', [App\Http\Controllers\Api\AttendanceController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\AttendanceController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\AttendanceController::class, 'destroy']);
        Route::post('/{id}/clock-out', [App\Http\Controllers\Api\AttendanceController::class, 'clockOut']);
        Route::post('/{id}/start-break', [App\Http\Controllers\Api\AttendanceController::class, 'startBreak']);
        Route::post('/{id}/end-break', [App\Http\Controllers\Api\AttendanceController::class, 'endBreak']);
    });

    // Unified HR Module API routes
    Route::prefix('unified-hr')->group(function () {
        Route::get('/stats', [UnifiedHRController::class, 'getUnifiedStats']);
        Route::get('/navigation-cards', [UnifiedHRController::class, 'getNavigationCards']);
        Route::get('/employee/{id}/module-data', [UnifiedHRController::class, 'getEmployeeModuleData']);
        Route::get('/dashboard-overview', [UnifiedHRController::class, 'getDashboardOverview']);
    });
    
    // AI Timesheet Generation API routes (moved outside auth middleware)
    // These will be accessed from authenticated web pages, so we'll handle auth in controller
    
    // Database Testing API routes
    Route::prefix('database')->group(function () {
        Route::get('/test-connection', [App\Http\Controllers\DatabaseTestController::class, 'testConnection']);
        Route::get('/test-crud', [App\Http\Controllers\DatabaseTestController::class, 'testCrudOperations']);
        Route::get('/stats', [App\Http\Controllers\DatabaseTestController::class, 'getStats']);
    });
});

// AI Timesheet Generation routes moved to web.php for proper session authentication
