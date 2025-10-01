<?php

use App\Http\Controllers\EmployeeESSController;
use Illuminate\Support\Facades\Route;

// Employee Self-Service (ESS) Routes
Route::prefix('employee')->group(function () {
    // Public routes (no authentication required)
    Route::get('/login', [EmployeeESSController::class, 'showLogin'])->name('employee.login.show');
    Route::post('/login', [EmployeeESSController::class, 'login'])->name('employee.login.submit');
    
    // Protected routes (require employee authentication)
    Route::middleware(['auth:employee'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [EmployeeESSController::class, 'dashboard'])->name('employee.dashboard');
        
        // Profile management
        Route::get('/profile', [EmployeeESSController::class, 'profile'])->name('employee.profile.show');
        Route::post('/profile/update', [EmployeeESSController::class, 'updateProfile'])->name('employee.profile.update');
        
        // ESS Module Routes
        Route::get('/timesheet', [EmployeeESSController::class, 'timesheet'])->name('employee.timesheet');
        Route::get('/timesheet/history', [EmployeeESSController::class, 'timesheetHistory'])->name('employee.timesheet.history');
        Route::get('/timesheet/{id}/edit', [EmployeeESSController::class, 'editTimesheet'])->name('employee.timesheet.edit');
        Route::get('/schedule', [EmployeeESSController::class, 'schedule'])->name('employee.schedule');
        Route::get('/shift-schedule', [EmployeeESSController::class, 'shiftSchedule'])->name('employee.shift.schedule');
        Route::get('/shift-request', [EmployeeESSController::class, 'shiftRequest'])->name('employee.shift.request');
        Route::get('/leave-management', [EmployeeESSController::class, 'leaveManagement'])->name('employee.leave.management');
        Route::get('/leave-balance', [EmployeeESSController::class, 'leaveBalance'])->name('employee.leave.balance');
        Route::get('/claims', [EmployeeESSController::class, 'claims'])->name('employee.claims');
        Route::get('/claims/create', [EmployeeESSController::class, 'createClaim'])->name('employee.claims.create');
        Route::get('/claims/history', [EmployeeESSController::class, 'claimsHistory'])->name('employee.claims.history');
        Route::get('/requests', [EmployeeESSController::class, 'requests'])->name('employee.requests');
        Route::post('/requests/store', [EmployeeESSController::class, 'storeRequest'])->name('employee.requests.store');
        Route::post('/claims/store', [EmployeeESSController::class, 'storeClaim'])->name('employee.claims.store');
        Route::put('/claims/{id}', [EmployeeESSController::class, 'updateClaim'])->name('employee.claims.update');
        Route::get('/claims/{id}/view', [EmployeeESSController::class, 'viewClaim'])->name('employee.claims.view');
        Route::get('/claims/{id}/edit', [EmployeeESSController::class, 'editClaim'])->name('employee.claims.edit');
        
        // Shift and Schedule Routes
        Route::get('/shift-schedule', [EmployeeESSController::class, 'shiftSchedule'])->name('employee.shift_schedule.index');
        Route::post('/shifts/store', [EmployeeESSController::class, 'storeShift'])->name('employee.shifts.store');
        Route::put('/shifts/{id}', [EmployeeESSController::class, 'updateShift'])->name('employee.shifts.update');
        
        // Shift Request Routes
        Route::post('/shift-requests/store', [EmployeeESSController::class, 'storeShiftRequest'])->name('employee.shift.request.store');
        Route::get('/shift-requests/{id}/view', [EmployeeESSController::class, 'viewShiftRequest'])->name('employee.shift.request.view');
        Route::get('/shift-requests/{id}/edit', [EmployeeESSController::class, 'editShiftRequest'])->name('employee.shift.request.edit');
        Route::put('/shift-requests/{id}/update', [EmployeeESSController::class, 'updateShiftRequest'])->name('employee.shift.request.update');
        Route::delete('/shift-requests/{id}/cancel', [EmployeeESSController::class, 'cancelShiftRequest'])->name('employee.shift.request.cancel');
        
        // Leave Application Routes
        Route::get('/leave-applications', [EmployeeESSController::class, 'leaveApplications'])->name('employee.leave_applications.index');
        Route::post('/leave/store', [EmployeeESSController::class, 'storeLeave'])->name('employee.leave.store');
        Route::put('/leave/{id}', [EmployeeESSController::class, 'updateLeave'])->name('employee.leave.update');
        Route::get('/leave-requests/{id}/view', [EmployeeESSController::class, 'viewLeaveRequest'])->name('employee.leave_requests.view');
        Route::get('/leave-requests/{id}/edit', [EmployeeESSController::class, 'editLeaveRequest'])->name('employee.leave_requests.edit');
        
        // Attendance and Time Entry Routes
        Route::get('/attendance-logs', [EmployeeESSController::class, 'attendanceLogs'])->name('employee.attendance_logs.index');
        Route::post('/attendance/store', [EmployeeESSController::class, 'storeTimesheet'])->name('employee.attendance.store');
        Route::post('/timesheet/store', [EmployeeESSController::class, 'storeTimesheet'])->name('employee.timesheet.store');
        Route::put('/timesheet/{id}', [EmployeeESSController::class, 'updateTimesheet'])->name('employee.timesheet.update');
        Route::put('/attendance/{id}', [EmployeeESSController::class, 'updateTimesheet'])->name('employee.attendance.update');
        Route::get('/timesheet/{id}/view', [EmployeeESSController::class, 'viewTimesheet'])->name('employee.timesheet.view');
        Route::get('/timesheet/{id}/edit', [EmployeeESSController::class, 'editTimesheet'])->name('employee.timesheet.edit');
        
        // ESS Clock-in/Clock-out Routes
        Route::post('/clock-in', [EmployeeESSController::class, 'clockIn'])->name('employee.clock.in');
        Route::post('/clock-out', [EmployeeESSController::class, 'clockOut'])->name('employee.clock.out');
        Route::get('/clock-status', [EmployeeESSController::class, 'getClockStatus'])->name('employee.clock.status');
        Route::get('/attendance-log', [EmployeeESSController::class, 'getAttendanceLog'])->name('employee.attendance.log');
        
        // Debug route for testing
        Route::get('/debug-attendance', function() {
            $employee = Auth::guard('employee')->user();
            $today = Carbon\Carbon::today();
            
            $attendance = DB::table('attendances')
                ->where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();
                
            return response()->json([
                'employee_id' => $employee->id,
                'today' => $today->toDateString(),
                'attendance' => $attendance,
                'can_clock_out' => $attendance && $attendance->clock_in_time && !$attendance->clock_out_time
            ]);
        });
        
        // Quick actions
        Route::post('/attendance/log', [EmployeeESSController::class, 'logAttendance'])->name('employee.attendance.log.submit');
        Route::post('/leave-application', [EmployeeESSController::class, 'submitLeaveApplication'])->name('employee.leave.application');
        Route::post('/request-form', [EmployeeESSController::class, 'submitRequestForm'])->name('employee.request.form');
        
        // Logout
        Route::post('/logout', [EmployeeESSController::class, 'logout'])->name('employee.logout');
    });
});
