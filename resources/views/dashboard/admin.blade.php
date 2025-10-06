@extends('layouts.hr')

@section('title', 'Admin Dashboard - Jetlouge Travels')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Admin Dashboard</h2>
        <p class="text-muted mb-0">Welcome back, {{ Auth::check() ? Auth::user()->name : 'Admin' }}! Here's what's happening with your HR system today.</p>
      </div>
    </div>
    <div class="d-flex align-items-center">
      <!-- Profile Dropdown -->
      <div class="dropdown me-3">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown">
          @if(Auth::user()->profile_picture)
            <img src="{{ Storage::url(Auth::user()->profile_picture) }}" alt="Profile" class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
          @else
            <i class="fas fa-user-circle me-2"></i>
          @endif
          {{ Auth::user()->name }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="{{ route('admin.profile.index') }}">
            <i class="fas fa-user me-2"></i>My Profile
          </a></li>
          <li><a class="dropdown-item" href="{{ route('admin.profile.change-password') }}">
            <i class="fas fa-key me-2"></i>Change Password
          </a></li>
          @if(Auth::user()->isSuperAdmin())
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ route('admin.profile.manage-admins') }}">
              <i class="fas fa-users-cog me-2"></i>Manage Admins
            </a></li>
          @endif
          <li><hr class="dropdown-divider"></li>
          <li>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="dropdown-item text-danger">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
              </button>
            </form>
          </li>
        </ul>
      </div>
      
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

<!-- System Overview Section -->
<div class="dashboard-section mb-4">
  <div class="system-overview text-center p-4" style="background-color: var(--jetlouge-light); border-radius: 8px;">
    <div class="current-time mb-3" id="live-clock" style="font-size: 36px; font-weight: bold; color: var(--jetlouge-primary);">--:--:--</div>
    <p class="text-muted mb-0">HR Management System - Administrative Dashboard</p>
    
  
  </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-users text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="total-employees">{{ $stats['total_employees'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Employees</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-success">
          <i class="fas fa-user-check text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="online-employees">{{ $stats['online_employees'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Present Today</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-warning">
          <i class="fas fa-clock text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="employees-with-timesheets">{{ $stats['employees_with_timesheets'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Pending Timesheets</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-building text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="leave-requests">{{ $leaveRequests ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Leave Requests</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- HR Modules Quick Access -->
<div class="dashboard-section mb-4">
  <div class="section-header mb-3">
    <h3 style="color: var(--jetlouge-primary);">HR System Modules</h3>
  </div>
  <div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('time-attendance') }}" class="text-decoration-none">
        <div class="card module-card h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="module-icon-circle bg-gradient-primary mb-3 mx-auto">
              <i class="fas fa-clock text-white"></i>
            </div>
            <h5 class="card-title mb-2">Time & Attendance</h5>
            <p class="card-text text-muted small">Track employee clock-in and attendance</p>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('timesheet-management') }}" class="text-decoration-none">
        <div class="card module-card h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="module-icon-circle bg-gradient-success mb-3 mx-auto">
              <i class="fas fa-file-alt text-white"></i>
            </div>
            <h5 class="card-title mb-2">Timesheet Management</h5>
            <p class="card-text text-muted small">Manage and approve employee timesheets</p>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('shift-schedule-management') }}" class="text-decoration-none">
        <div class="card module-card h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="module-icon-circle bg-gradient-warning mb-3 mx-auto">
              <i class="fas fa-calendar-alt text-white"></i>
            </div>
            <h5 class="card-title mb-2">Shift & Schedule</h5>
            <p class="card-text text-muted small">Schedule shifts and manage work hours</p>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('leave-management') }}" class="text-decoration-none">
        <div class="card module-card h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="module-icon-circle bg-gradient-info mb-3 mx-auto">
              <i class="fas fa-user-clock text-white"></i>
            </div>
            <h5 class="card-title mb-2">Leave Management</h5>
            <p class="card-text text-muted small">Handle leave requests and approvals</p>
          </div>
        </div>
      </a>
    </div>
  </div>
  <div class="row g-4">
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('claims-reimbursement') }}" class="text-decoration-none">
        <div class="card module-card h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="module-icon-circle bg-gradient-danger mb-3 mx-auto">
              <i class="fas fa-receipt text-white"></i>
            </div>
            <h5 class="card-title mb-2">Claims & Reimbursement</h5>
            <p class="card-text text-muted small">Process expense claims and reimbursements</p>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('employees.index') }}" class="text-decoration-none">
        <div class="card module-card h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="module-icon-circle bg-gradient-secondary mb-3 mx-auto">
              <i class="fas fa-users text-white"></i>
            </div>
            <h5 class="card-title mb-2">Employee Management</h5>
            <p class="card-text text-muted small">Manage employee profiles and data</p>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('dashboard') }}" class="text-decoration-none">
        <div class="card module-card h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="module-icon-circle bg-gradient-dark mb-3 mx-auto">
              <i class="fas fa-cog text-white"></i>
            </div>
            <h5 class="card-title mb-2">System Settings</h5>
            <p class="card-text text-muted small">Configure system preferences and settings</p>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('dashboard') }}" class="text-decoration-none">
        <div class="card module-card h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="module-icon-circle bg-gradient-purple mb-3 mx-auto">
              <i class="fas fa-chart-bar text-white"></i>
            </div>
            <h5 class="card-title mb-2">Reports & Analytics</h5>
            <p class="card-text text-muted small">View reports and business analytics</p>
          </div>
        </div>
      </a>
    </div>
  </div>
</div>





<!-- HR Module Sections (Initially Hidden) -->


<!-- Timesheet Content -->
<div id="timesheet-content" class="dashboard-section" style="display: none;">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h2 style="color: var(--jetlouge-primary);">Timesheet Management</h2>
    <button class="btn btn-primary" id="generate-timesheet-btn">Generate Timesheet</button>
  </div>

  <div class="row mb-4">
    <div class="col-md-3">
      <label for="timesheet-employee" class="form-label">Employee</label>
      <select id="timesheet-employee" class="form-select">
        <option value="">All Employees</option>
      </select>
    </div>
    <div class="col-md-3">
      <label for="timesheet-period" class="form-label">Period</label>
      <select id="timesheet-period" class="form-select">
        <option value="current-week">Current Week</option>
        <option value="last-week">Last Week</option>
        <option value="current-month">Current Month</option>
        <option value="last-month">Last Month</option>
        <option value="custom">Custom Range</option>
      </select>
    </div>
    <div class="col-md-4" id="custom-date-range" style="display: none;">
      <label class="form-label">Date Range</label>
      <div class="d-flex gap-2">
        <input type="date" id="timesheet-start-date" class="form-control">
        <span class="align-self-center">to</span>
        <input type="date" id="timesheet-end-date" class="form-control">
      </div>
    </div>
    <div class="col-md-2">
      <label class="form-label">&nbsp;</label>
      <button class="btn btn-secondary d-block" id="filter-timesheet-btn">Filter</button>
    </div>
  </div>

  <table class="table table-hover" id="timesheet-table">
    <thead class="table-light">
      <tr>
        <th>Date</th>
        <th>Employee</th>
        <th>Clock In</th>
        <th>Clock Out</th>
        <th>Total Hours</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <!-- Timesheet data will be loaded here -->
    </tbody>
  </table>
</div>

<!-- Leave Management Content -->
<div id="leave-content" class="dashboard-section" style="display: none;">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h2 style="color: var(--jetlouge-primary);">Leave Management</h2>
    <button class="btn btn-primary" id="request-leave-btn" onclick="openWorkingModal('leave-request-modal')">Request Leave</button>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Leave Balance</h5>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <span>Annual Leave</span>
            <strong>15 days remaining</strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Sick Leave</span>
            <strong>10 days remaining</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Unpaid Leave</span>
            <strong>Unlimited</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Pending Approval</h5>
        </div>
        <div class="card-body" id="pending-leave-list">
          <!-- Pending leave requests will be loaded here -->
        </div>
      </div>
    </div>
  </div>

  <h3 class="mb-3">Leave History</h3>
  <table class="table table-hover" id="leave-history-table">
    <thead class="table-light">
      <tr>
        <th>Leave Type</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Days</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Leave history will be loaded here -->
    </tbody>
  </table>
</div>

<!-- Claims & Reimbursement Content -->
<div id="claims-content" class="dashboard-section" style="display: none;">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h2 style="color: var(--jetlouge-primary);">Claims & Reimbursement</h2>
    <button class="btn btn-primary" id="new-claim-btn" onclick="openWorkingModal('claims-modal')">New Claim</button>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Pending Claims</h5>
        </div>
        <div class="card-body" id="pending-claims-list">
          <!-- Pending claims will be loaded here -->
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Approved Claims</h5>
        </div>
        <div class="card-body" id="approved-claims-list">
          <!-- Approved claims will be loaded here -->
        </div>
      </div>
    </div>
  </div>

  <h3 class="mb-3">Claims History</h3>
  <table class="table table-hover" id="claims-history-table">
    <thead class="table-light">
      <tr>
        <th>Claim Date</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Description</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Claims history will be loaded here -->
    </tbody>
  </table>
</div>

<!-- HR Modals -->


<!-- Time Entry Modal -->
<div class="working-modal" id="time-entry-modal" style="display: none;">
  <div class="working-modal-backdrop" onclick="closeWorkingModal('time-entry-modal')"></div>
  <div class="working-modal-dialog">
    <div class="working-modal-content">
      <div class="working-modal-header">
        <h5 class="working-modal-title">Add Time Entry</h5>
        <button type="button" class="working-modal-close" onclick="closeWorkingModal('time-entry-modal')">&times;</button>
      </div>
      <form id="time-entry-form" method="POST" action="{{ route('timesheets.store') }}">
        @csrf
        <div class="working-modal-body">
          <div class="mb-3">
            <label for="employee_id" class="form-label">Employee</label>
            <select class="form-select" id="employee_id" name="employee_id" required>
              <option value="">Select Employee</option>
              @foreach($employees ?? [] as $employee)
                <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="work_date" class="form-label">Work Date</label>
            <input type="date" class="form-control" id="work_date" name="work_date" required>
          </div>
          <div class="mb-3">
            <label for="hours_worked" class="form-label">Hours Worked</label>
            <input type="number" class="form-control" id="hours_worked" name="hours_worked" min="0" max="24" step="0.5" required>
          </div>
          <div class="mb-3">
            <label for="overtime_hours" class="form-label">Overtime Hours</label>
            <input type="number" class="form-control" id="overtime_hours" name="overtime_hours" min="0" max="12" step="0.5">
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
        </div>
        <div class="working-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('time-entry-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Entry</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Leave Request Modal -->
<div class="working-modal" id="leave-request-modal" style="display: none;">
  <div class="working-modal-backdrop" onclick="closeWorkingModal('leave-request-modal')"></div>
  <div class="working-modal-dialog">
    <div class="working-modal-content">
      <div class="working-modal-header">
        <h5 class="working-modal-title">Request Leave</h5>
        <button type="button" class="working-modal-close" onclick="closeWorkingModal('leave-request-modal')">&times;</button>
      </div>
      <form id="leave-request-form" method="POST" action="{{ route('leave.requests.store') }}">
        @csrf
        <div class="working-modal-body">
          <div class="mb-3">
            <label for="leave_type_id" class="form-label">Leave Type</label>
            <select class="form-select" id="leave_type_id" name="leave_type_id" required>
              <option value="">Select Leave Type</option>
              @foreach($leaveTypes ?? [] as $leaveType)
                <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
          </div>
          <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
          </div>
          <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
          </div>
        </div>
        <div class="working-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('leave-request-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Claims Modal -->
<div class="working-modal" id="claims-modal" style="display: none;">
  <div class="working-modal-backdrop" onclick="closeWorkingModal('claims-modal')"></div>
  <div class="working-modal-dialog">
    <div class="working-modal-content">
      <div class="working-modal-header">
        <h5 class="working-modal-title">New Claim</h5>
        <button type="button" class="working-modal-close" onclick="closeWorkingModal('claims-modal')">&times;</button>
      </div>
      <form id="claims-form" method="POST" action="{{ route('claims.store') }}">
        @csrf
        <div class="working-modal-body">
          <div class="mb-3">
            <label for="claim_type_id" class="form-label">Claim Type</label>
            <select class="form-select" id="claim_type_id" name="claim_type_id" required>
              <option value="">Select Claim Type</option>
              @foreach($claimTypes ?? [] as $claimType)
                <option value="{{ $claimType->id }}">{{ $claimType->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
          </div>
          <div class="mb-3">
            <label for="claim_date" class="form-label">Expense Date</label>
            <input type="date" class="form-control" id="claim_date" name="claim_date" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="attachment" class="form-label">Receipt (optional)</label>
            <input type="file" class="form-control" id="attachment" name="attachment" accept="image/*,.pdf">
          </div>
        </div>
        <div class="working-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('claims-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Claim</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('styles')
<style>
/* Enhanced Module Cards */
.module-card {
  transition: all 0.3s ease;
  cursor: pointer;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
}

.module-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--jetlouge-primary), var(--jetlouge-secondary));
  opacity: 0;
  transition: opacity 0.3s ease;
}

.module-card:hover::before {
  opacity: 1;
}

.module-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  border-color: rgba(0, 123, 255, 0.2);
}

.module-icon-circle {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  position: relative;
  transition: all 0.3s ease;
}

.module-icon-circle::after {
  content: '';
  position: absolute;
  inset: -3px;
  border-radius: 50%;
  background: linear-gradient(45deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));
  z-index: -1;
}

.module-card:hover .module-icon-circle {
  transform: scale(1.1);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.module-card .card-title {
  color: #2c3e50;
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 8px;
  transition: color 0.3s ease;
}

.module-card:hover .card-title {
  color: var(--jetlouge-primary);
}

.module-card .card-text {
  font-size: 0.875rem;
  line-height: 1.4;
  color: #6c757d;
  transition: color 0.3s ease;
}

.module-card:hover .card-text {
  color: #495057;
}

/* Gradient Backgrounds */
.bg-gradient-primary {
  background: linear-gradient(135deg, #007bff, #0056b3);
}

.bg-gradient-success {
  background: linear-gradient(135deg, #28a745, #1e7e34);
}

.bg-gradient-warning {
  background: linear-gradient(135deg, #ffc107, #e0a800);
}

.bg-gradient-info {
  background: linear-gradient(135deg, #17a2b8, #138496);
}

.bg-gradient-danger {
  background: linear-gradient(135deg, #dc3545, #c82333);
}

.bg-gradient-secondary {
  background: linear-gradient(135deg, #6c757d, #545b62);
}

.bg-gradient-dark {
  background: linear-gradient(135deg, #343a40, #23272b);
}

.bg-gradient-purple {
  background: linear-gradient(135deg, #6f42c1, #5a32a3);
}

/* Responsive Design */
@media (max-width: 768px) {
  .module-card {
    border-radius: 12px;
  }
  
  .module-icon-circle {
    width: 60px;
    height: 60px;
    font-size: 20px;
  }
  
  .module-card .card-title {
    font-size: 1rem;
  }
  
  .module-card .card-text {
    font-size: 0.8rem;
  }
}

/* Animation for card loading */
.module-card {
  animation: fadeInUp 0.6s ease forwards;
}

.module-card:nth-child(1) { animation-delay: 0.1s; }
.module-card:nth-child(2) { animation-delay: 0.2s; }
.module-card:nth-child(3) { animation-delay: 0.3s; }
.module-card:nth-child(4) { animation-delay: 0.4s; }

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Working Modal CSS */
.working-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 2000;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.working-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.working-modal-dialog {
    position: relative;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    z-index: 2001;
    margin: 0;
}

.working-modal-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

.working-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
}

.working-modal-title {
    margin: 0;
    color: #212529;
    font-weight: 600;
}

.working-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6c757d;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.working-modal-close:hover {
    color: #000;
}

.working-modal-body {
    padding: 1.5rem;
    max-height: 60vh;
    overflow-y: auto;
}

.working-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
    text-align: right;
}

.working-modal input,
.working-modal select,
.working-modal textarea,
.working-modal button {
    pointer-events: auto !important;
    opacity: 1 !important;
    background-color: white !important;
    color: #212529 !important;
}

.working-modal .form-control,
.working-modal .form-select {
    border: 1px solid #ced4da !important;
    background-color: white !important;
    color: #212529 !important;
}
</style>
@endpush

@push('scripts')
<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Live clock
function updateClock() {
  const now = new Date();
  const timeString = now.toLocaleTimeString();
  document.getElementById('live-clock').textContent = timeString;
}

setInterval(updateClock, 1000);
updateClock();

// Update statistics
function updateStats() {
  fetch('/api/dashboard/stats', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    document.getElementById('total-employees').textContent = data.total_employees;
    document.getElementById('online-employees').textContent = data.online_employees;
    document.getElementById('employees-with-timesheets').textContent = data.employees_with_timesheets;
    document.getElementById('departments').textContent = data.departments;
  })
  .catch(error => console.error('Error updating stats:', error));
}

// Update recent entries table
function updateRecentEntries() {
  fetch('/api/dashboard/recent-entries', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    const tbody = document.getElementById('recent-entries-tbody');
    tbody.innerHTML = '';
    
    data.forEach(entry => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${entry.entry_date}</td>
        <td>${entry.clock_in}</td>
        <td>${entry.clock_out}</td>
        <td>${entry.total_hours}</td>
        <td><span class="badge ${entry.status_badge}">${entry.status.charAt(0).toUpperCase() + entry.status.slice(1)}</span></td>
      `;
      tbody.appendChild(row);
    });
  })
  .catch(error => console.error('Error updating recent entries:', error));
}

// Load timesheet data
function loadTimesheetData() {
  const employeeId = document.getElementById('timesheet-employee').value;
  const period = document.getElementById('timesheet-period').value;
  const startDate = document.getElementById('timesheet-start-date').value;
  const endDate = document.getElementById('timesheet-end-date').value;
  
  const params = new URLSearchParams({
    employee_id: employeeId,
    period: period,
    start_date: startDate,
    end_date: endDate
  });
  
  fetch(`/api/dashboard/timesheet-data?${params}`, {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    const tbody = document.querySelector('#timesheet-table tbody');
    tbody.innerHTML = '';
    
    data.forEach(entry => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${entry.entry_date}</td>
        <td>${entry.employee_name}</td>
        <td>${entry.clock_in}</td>
        <td>${entry.clock_out}</td>
        <td>${entry.total_hours}</td>
        <td><span class="badge ${entry.status_badge}">${entry.status.charAt(0).toUpperCase() + entry.status.slice(1)}</span></td>
      `;
      tbody.appendChild(row);
    });
  })
  .catch(error => console.error('Error loading timesheet data:', error));
}

// Load leave data
function loadLeaveData() {
  fetch('/api/dashboard/leave-data', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    // Update leave balances
    const balanceContainer = document.querySelector('.card-body');
    if (data.balances && data.balances.length > 0) {
      let balanceHtml = '';
      data.balances.forEach(balance => {
        balanceHtml += `
          <div class="d-flex justify-content-between mb-2">
            <span>${balance.leave_type}</span>
            <strong>${balance.remaining_days} days remaining</strong>
          </div>
        `;
      });
      balanceContainer.innerHTML = balanceHtml;
    }
    
    // Update pending requests
    const pendingContainer = document.getElementById('pending-leave-list');
    if (data.pending_requests && data.pending_requests.length > 0) {
      let pendingHtml = '';
      data.pending_requests.forEach(request => {
        pendingHtml += `
          <div class="mb-2">
            <strong>${request.leave_type}</strong><br>
            <small>${request.start_date} - ${request.end_date} (${request.days_requested} days)</small>
          </div>
        `;
      });
      pendingContainer.innerHTML = pendingHtml;
    } else {
      pendingContainer.innerHTML = '<p class="text-muted">No pending requests</p>';
    }
    
    // Update leave history
    const historyTbody = document.querySelector('#leave-history-table tbody');
    historyTbody.innerHTML = '';
    
    data.history.forEach(request => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${request.leave_type}</td>
        <td>${request.start_date}</td>
        <td>${request.end_date}</td>
        <td>${request.days_requested}</td>
        <td><span class="badge ${request.status_badge}">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span></td>
        <td><button class="btn btn-sm btn-outline-primary">View</button></td>
      `;
      historyTbody.appendChild(row);
    });
  })
  .catch(error => console.error('Error loading leave data:', error));
}

// Load claims data
function loadClaimsData() {
  fetch('/api/dashboard/claims-data', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    // Update pending claims
    const pendingContainer = document.getElementById('pending-claims-list');
    if (data.pending_claims && data.pending_claims.length > 0) {
      let pendingHtml = '';
      data.pending_claims.forEach(claim => {
        pendingHtml += `
          <div class="mb-2">
            <strong>${claim.type}</strong> - ${claim.amount}<br>
            <small>${claim.description}</small>
          </div>
        `;
      });
      pendingContainer.innerHTML = pendingHtml;
    } else {
      pendingContainer.innerHTML = '<p class="text-muted">No pending claims</p>';
    }
    
    // Update approved claims
    const approvedContainer = document.getElementById('approved-claims-list');
    if (data.approved_claims && data.approved_claims.length > 0) {
      let approvedHtml = '';
      data.approved_claims.forEach(claim => {
        approvedHtml += `
          <div class="mb-2">
            <strong>${claim.type}</strong> - ${claim.amount}<br>
            <small>${claim.description}</small>
          </div>
        `;
      });
      approvedContainer.innerHTML = approvedHtml;
    } else {
      approvedContainer.innerHTML = '<p class="text-muted">No approved claims</p>';
    }
    
    // Update claims history
    const historyTbody = document.querySelector('#claims-history-table tbody');
    historyTbody.innerHTML = '';
    
    data.history.forEach(claim => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${claim.claim_date}</td>
        <td>${claim.type}</td>
        <td>${claim.amount}</td>
        <td>${claim.description}</td>
        <td><span class="badge ${claim.status_badge}">${claim.status.charAt(0).toUpperCase() + claim.status.slice(1)}</span></td>
        <td><button class="btn btn-sm btn-outline-primary">View</button></td>
      `;
      historyTbody.appendChild(row);
    });
  })
  .catch(error => console.error('Error loading claims data:', error));
}

// Show notification
function showNotification(message, type = 'info') {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
  notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
  notification.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(notification);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 5000);
}

// Navigation functionality for different sections
document.addEventListener('DOMContentLoaded', function() {
  const navLinks = document.querySelectorAll('[data-section]');
  const sections = {
    'dashboard': document.getElementById('dashboard-content'),
    'timesheet': document.getElementById('timesheet-content'),
    'leave': document.getElementById('leave-content'),
    'claims': document.getElementById('claims-content')
  };

  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const targetSection = this.getAttribute('data-section');
      
      // Hide all sections
      Object.values(sections).forEach(section => {
        if (section) section.style.display = 'none';
      });
      
      // Show target section and load data
      if (sections[targetSection]) {
        sections[targetSection].style.display = 'block';
        
        // Load section-specific data
        switch(targetSection) {
          case 'timesheet':
            loadTimesheetData();
            break;
          case 'leave':
            loadLeaveData();
            break;
          case 'claims':
            loadClaimsData();
            break;
        }
      }
      
      // Update active nav link
      navLinks.forEach(navLink => navLink.classList.remove('active'));
      this.classList.add('active');
    });
  });
  
  // Event listeners for timesheet filters
  document.getElementById('filter-timesheet-btn')?.addEventListener('click', loadTimesheetData);
  document.getElementById('timesheet-period')?.addEventListener('change', function() {
    const customRange = document.getElementById('custom-date-range');
    if (this.value === 'custom') {
      customRange.style.display = 'block';
    } else {
      customRange.style.display = 'none';
      loadTimesheetData();
    }
  });
  
  // Auto-refresh stats every 30 seconds
  setInterval(updateStats, 30000);
  
  
  // Initialize HR Settings functionality
  initializeHRSettings();
});







// HR Settings functionality
function initializeHRSettings() {
  // System Settings
  document.getElementById('system-settings-link')?.addEventListener('click', function(e) {
    e.preventDefault();
    showSystemSettingsModal();
  });
  
  // HR Preferences
  document.getElementById('hr-preferences-link')?.addEventListener('click', function(e) {
    e.preventDefault();
    showHRPreferencesModal();
  });
  
  // Notifications Settings
  document.getElementById('notifications-settings-link')?.addEventListener('click', function(e) {
    e.preventDefault();
    showNotificationsModal();
  });
}

// Show System Settings Modal
function showSystemSettingsModal() {
  const modal = new bootstrap.Modal(document.getElementById('systemSettingsModal'));
  modal.show();
}

// Show HR Preferences Modal
function showHRPreferencesModal() {
  const modal = new bootstrap.Modal(document.getElementById('hrPreferencesModal'));
  modal.show();
}

// Show Notifications Modal
function showNotificationsModal() {
  const modal = new bootstrap.Modal(document.getElementById('notificationsModal'));
  modal.show();
}


// Working Modal Functions
function openWorkingModal(modalId) {
  document.getElementById(modalId).style.display = 'flex';
  // Reset form if it exists
  const form = document.querySelector(`#${modalId} form`);
  if (form) {
    form.reset();
  }
  // Load dropdown data if needed
  if (modalId === 'leave-request-modal') {
    loadLeaveTypes();
  } else if (modalId === 'claims-modal') {
    loadClaimTypes();
  }
}

function closeWorkingModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

// Load leave types for dropdown
function loadLeaveTypes() {
  fetch('/api/leave/types', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const select = document.getElementById('leave-type');
      select.innerHTML = '<option value="">Select Leave Type</option>' + 
        data.data.filter(type => type.is_active).map(type => 
          `<option value="${type.id}">${type.name}</option>`
        ).join('');
    }
  })
  .catch(error => {
    console.error('Error loading leave types:', error);
  });
}

// Load claim types for dropdown
function loadClaimTypes() {
  fetch('/api/claims/types', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const select = document.getElementById('claim-type');
      select.innerHTML = '<option value="">Select Claim Type</option>' + 
        data.data.map(type => 
          `<option value="${type.id}">${type.name}</option>`
        ).join('');
    }
  })
  .catch(error => {
    console.error('Error loading claim types:', error);
  });
}

// Submit time entry
function submitTimeEntry(event) {
  event.preventDefault();
  
  const formData = {
    entry_date: document.getElementById('entry-date').value,
    clock_in: document.getElementById('clock-in-time').value,
    clock_out: document.getElementById('clock-out-time').value,
    break_duration: document.getElementById('break-duration').value,
    notes: document.getElementById('entry-notes').value
  };

  fetch('/api/time-entries', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Time entry added successfully!', 'success');
      closeWorkingModal('time-entry-modal');
      updateRecentEntries();
      updateStats();
    } else {
      showNotification(data.message || 'Failed to add time entry', 'error');
    }
  })
  .catch(error => {
    console.error('Error adding time entry:', error);
    showNotification('Error adding time entry', 'error');
  });
}

// Submit leave request
function submitLeaveRequest(event) {
  event.preventDefault();
  
  const formData = {
    leave_type_id: document.getElementById('leave-type').value,
    start_date: document.getElementById('leave-start-date').value,
    end_date: document.getElementById('leave-end-date').value,
    reason: document.getElementById('leave-reason').value
  };

  fetch('/api/leave/requests', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Leave request submitted successfully!', 'success');
      closeWorkingModal('leave-request-modal');
      loadLeaveData();
      updateStats();
    } else {
      showNotification(data.message || 'Failed to submit leave request', 'error');
    }
  })
  .catch(error => {
    console.error('Error submitting leave request:', error);
    showNotification('Error submitting leave request', 'error');
  });
}

// Submit claim
function submitClaim(event) {
  event.preventDefault();
  
  const formData = new FormData();
  formData.append('claim_type_id', document.getElementById('claim-type').value);
  formData.append('amount', document.getElementById('claim-amount').value);
  formData.append('claim_date', document.getElementById('claim-date').value);
  formData.append('description', document.getElementById('claim-description').value);
  
  const receiptFile = document.getElementById('claim-receipt').files[0];
  if (receiptFile) {
    formData.append('receipt', receiptFile);
  }

  fetch('/api/claims', {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Claim submitted successfully!', 'success');
      closeWorkingModal('claims-modal');
      loadClaimsData();
      updateStats();
    } else {
      showNotification(data.message || 'Failed to submit claim', 'error');
    }
  })
  .catch(error => {
    console.error('Error submitting claim:', error);
    showNotification('Error submitting claim', 'error');
  });
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const visibleModals = document.querySelectorAll('.working-modal[style*="flex"]');
    visibleModals.forEach(modal => {
      modal.style.display = 'none';
    });
  }
});

</script>

<style>
/* Modern Statistics Cards */
.stat-card-modern {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-card-modern::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--jetlouge-primary), var(--jetlouge-secondary));
}

.stat-icon-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  position: relative;
}

.stat-icon-circle::after {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 50%;
  background: linear-gradient(45deg, rgba(255,255,255,0.2), rgba(255,255,255,0.05));
  z-index: -1;
}

.stat-number {
  font-size: 2.2rem;
  font-weight: 700;
  color: #2c3e50;
  line-height: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #6c757d;
  font-weight: 500;
  margin-top: 4px;
}

/* Color variations for icons */
.bg-primary {
  background: linear-gradient(135deg, #007bff, #0056b3);
}

.bg-success {
  background: linear-gradient(135deg, #28a745, #1e7e34);
}

.bg-warning {
  background: linear-gradient(135deg, #ffc107, #e0a800);
}

.bg-info {
  background: linear-gradient(135deg, #17a2b8, #138496);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .stat-card-modern {
    padding: 20px;
    border-radius: 12px;
  }
  
  .stat-icon-circle {
    width: 48px;
    height: 48px;
    font-size: 18px;
  }
  
  .stat-number {
    font-size: 1.8rem;
  }
}

/* Animation for number changes */
.stat-number {
  transition: all 0.3s ease;
}

.stat-number.updating {
  transform: scale(1.1);
  color: var(--jetlouge-primary);
}


</style>
@endpush
@endsection
