<?php

use App\Http\Controllers\EmployeeESSController;
use Illuminate\Support\Facades\Route;

// Employee Portal Routes - Fixed routing
Route::get('/employee/login', [EmployeeESSController::class, 'showLogin'])->name('employee.login');
Route::post('/employee/login', [EmployeeESSController::class, 'login'])->name('employee.login.submit');

// Employee dashboard and protected routes - using employee guard
Route::middleware(['auth:employee'])->group(function () {
    Route::get('/employee-dashboard', [EmployeeESSController::class, 'dashboard'])->name('employee.dashboard');
    Route::get('/employee-profile', [EmployeeESSController::class, 'profile'])->name('employee.profile');
    Route::post('/employee-profile/update', [EmployeeESSController::class, 'updateProfile'])->name('employee.profile.update');
    Route::post('/employee-logout', [EmployeeESSController::class, 'logout'])->name('employee.logout');
    
    // Employee ESS Module Routes
    Route::get('/employee/leave-applications', [EmployeeESSController::class, 'leaveApplications'])->name('employee.leave_applications.index');
    
    Route::get('/employee/timesheet', [EmployeeESSController::class, 'timesheet'])->name('employee.timesheet');
    Route::post('/employee/timesheet', [EmployeeESSController::class, 'storeTimesheet'])->name('employee.timesheet.store');
    Route::get('/employee/schedule', [EmployeeESSController::class, 'shiftSchedule'])->name('employee.schedule');
    Route::get('/employee/attendance-logs', [EmployeeESSController::class, 'attendanceLogs'])->name('employee.attendance_logs.index');
    
    Route::get('/employee/payslips', function() {
        return view('employee_ess_modules.payslips');
    })->name('payslips.index');
    
    Route::get('/employee/claim-reimbursements', [EmployeeESSController::class, 'claims'])->name('employee.claim_reimbursements.index');
    Route::post('/employee/claims', [EmployeeESSController::class, 'storeClaim'])->name('employee.claims.store');
    Route::put('/employee/claims/{id}', [EmployeeESSController::class, 'updateClaim'])->name('employee.claims.update');
    
    Route::get('/employee/requests', [EmployeeESSController::class, 'requests'])->name('employee.requests.index');
    Route::post('/employee/requests', [EmployeeESSController::class, 'storeRequest'])->name('employee.requests.store');
    Route::put('/employee/requests/{id}', [EmployeeESSController::class, 'updateRequest'])->name('employee.requests.update');
    
    Route::get('/employee/my-trainings', function() {
        return view('employee_ess_modules.my_trainings');
    })->name('employee.my_trainings.index');
    
    Route::get('/employee/competency-profile', function() {
        return view('employee_ess_modules.competency_profile');
    })->name('employee.competency_profile.index');
});
