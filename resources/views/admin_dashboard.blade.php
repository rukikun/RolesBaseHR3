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
    
    <!-- AI Status Indicators -->
    <div class="row mt-3">
      <div class="col-md-6">
        <div class="ai-status-card p-2 rounded" style="background-color: rgba(0, 123, 255, 0.1);">
          <div class="d-flex align-items-center">
            <i class="fas fa-robot text-primary me-2"></i>
            <span class="small">OpenAI Status: <span id="openai-status" class="badge bg-secondary">Checking...</span></span>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="ai-status-card p-2 rounded" style="background-color: rgba(40, 167, 69, 0.1);">
          <div class="d-flex align-items-center">
            <i class="fas fa-clock text-success me-2"></i>
            <span class="small">Clockify Status: <span id="clockify-status" class="badge bg-secondary">Checking...</span></span>
          </div>
        </div>
      </div>
    </div>
  
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
          <h3 class="fw-bold mb-0 stat-number" id="total-employees">{{ $stats['total_employees'] }}</h3>
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
          <h3 class="fw-bold mb-0 stat-number" id="online-employees">{{ $stats['online_employees'] }}</h3>
          <p class="text-muted mb-0 small stat-label">Online</p>
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
          <h3 class="fw-bold mb-0 stat-number" id="employees-with-timesheets">{{ $stats['employees_with_timesheets'] }}</h3>
          <p class="text-muted mb-0 small stat-label">With Timesheets</p>
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
          <h3 class="fw-bold mb-0 stat-number" id="departments">{{ $stats['departments'] }}</h3>
          <p class="text-muted mb-0 small stat-label">Departments</p>
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
  <div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('time-attendance') }}" class="text-decoration-none">
        <div class="card module-card h-100 shadow-sm border-0">
          <div class="card-body text-center">
            <div class="module-icon text-primary mb-3 mx-auto">
              <i class="fas fa-clock fa-2x"></i>
            </div>
            <h5 class="card-title">Time & Attendance</h5>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('timesheet-management') }}" class="text-decoration-none">
        <div class="card module-card h-100 shadow-sm border-0">
          <div class="card-body text-center">
            <div class="module-icon text-success mb-3 mx-auto">
              <i class="fas fa-file-alt fa-2x"></i>
            </div>
            <h5 class="card-title">Timesheet Management</h5>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('shift-schedule-management') }}" class="text-decoration-none">
        <div class="card module-card h-100 shadow-sm border-0">
          <div class="card-body text-center">
            <div class="module-icon text-warning mb-3 mx-auto">
              <i class="fas fa-calendar-alt fa-2x"></i>
            </div>
            <h5 class="card-title">Shift & Schedule</h5>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('leave-management') }}" class="text-decoration-none">
        <div class="card module-card h-100 shadow-sm border-0">
          <div class="card-body text-center">
            <div class="module-icon text-info mb-3 mx-auto">
              <i class="fas fa-user-clock fa-2x"></i>
            </div>
            <h5 class="card-title">Leave Management</h5>
          </div>
        </div>
      </a>
    </div>
  </div>
  <div class="row g-3">
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('claims-reimbursement') }}" class="text-decoration-none">
        <div class="card module-card h-100 shadow-sm border-0">
          <div class="card-body text-center">
            <div class="module-icon text-danger mb-3 mx-auto">
              <i class="fas fa-receipt fa-2x"></i>
            </div>
            <h5 class="card-title">Claims & Reimbursement</h5>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('dashboard') }}" class="text-decoration-none">
        <div class="card module-card h-100 shadow-sm border-0">
          <div class="card-body text-center">
            <div class="module-icon text-dark mb-3 mx-auto">
              <i class="fas fa-users fa-2x"></i>
            </div>
            <h5 class="card-title">Employee Management</h5>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-3">
      <a href="{{ route('dashboard') }}" class="text-decoration-none">
        <div class="card module-card h-100 shadow-sm border-0">
          <div class="card-body text-center">
            <div class="module-icon text-primary mb-3 mx-auto">
              <i class="fas fa-cog fa-2x"></i>
            </div>
            <h5 class="card-title">System Settings</h5>
          </div>
        </div>
      </a>
    </div>
  </div>
</div>

<!-- Today's Schedule Section -->
<div class="dashboard-section mb-4">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h3 style="color: var(--jetlouge-primary);">Schedule</h3>
    <a href="{{ route('shift-schedule-management') }}" class="btn btn-primary" id="view-full-schedule-btn">View Full Schedule</a>
  </div>
  <div id="todays-schedule">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <div class="text-center p-3 border rounded">
              <i class="fas fa-sun text-warning fs-2 mb-2"></i>
              <h5>Morning Shift</h5>
              <p class="text-muted mb-0">8:00 AM - 4:00 PM</p>
              <small class="text-success">25 employees</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="text-center p-3 border rounded">
              <i class="fas fa-cloud-sun text-info fs-2 mb-2"></i>
              <h5>Afternoon Shift</h5>
              <p class="text-muted mb-0">2:00 PM - 10:00 PM</p>
              <small class="text-success">18 employees</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="text-center p-3 border rounded">
              <i class="fas fa-moon text-dark fs-2 mb-2"></i>
              <h5>Night Shift</h5>
              <p class="text-muted mb-0">10:00 PM - 6:00 AM</p>
              <small class="text-success">12 employees</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- AI Insights Section -->
<div class="dashboard-section mb-4">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h3 style="color: var(--jetlouge-primary);">AI Insights & Recommendations</h3>
    <button class="btn btn-outline-primary" id="refresh-ai-insights-btn">
      <i class="fas fa-sync-alt"></i> Refresh Insights
    </button>
  </div>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0"><i class="fas fa-brain text-primary me-2"></i>Productivity Analysis</h5>
    </div>
    <div class="card-body" id="ai-insights-content">
      <div class="text-center text-muted">
        <i class="fas fa-spinner fa-spin"></i> Loading AI insights...
      </div>
    </div>
  </div>
</div>

<!-- AI-Enhanced Time Tracking Section -->
<div class="dashboard-section mb-4">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h3 style="color: var(--jetlouge-primary);">AI-Enhanced Time Tracking</h3>
    <div class="btn-group">
      <button class="btn btn-success" id="ai-start-timer-btn">
        <i class="fas fa-play"></i> Start AI Timer
      </button>
      <button class="btn btn-danger" id="ai-stop-timer-btn" disabled>
        <i class="fas fa-stop"></i> Stop Timer
      </button>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">Task Description</h6>
          <textarea class="form-control mb-3" id="ai-task-description" placeholder="Describe your task for AI analysis..."></textarea>
          <div id="ai-task-analysis" class="alert alert-info" style="display: none;">
            <strong>AI Analysis:</strong>
            <div id="ai-analysis-content"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">Current Session</h6>
          <div class="d-flex justify-content-between mb-2">
            <span>Status:</span>
            <span id="timer-status" class="badge bg-secondary">Stopped</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Duration:</span>
            <span id="timer-duration">00:00:00</span>
          </div>
          <div class="d-flex justify-content-between">
            <span>Today's Total:</span>
            <span id="today-total-hours">0.0 hrs</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Time Entries Section -->
<div class="dashboard-section">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h3 style="color: var(--jetlouge-primary);">Recent Time Entries</h3>
    <button class="btn btn-primary" id="add-time-entry-btn" onclick="openWorkingModal('time-entry-modal')">Add Entry</button>
  </div>
  <table class="table table-hover" id="recent-entries-table">
    <thead class="table-light">
      <tr>
        <th>Date</th>
        <th>Clock In</th>
        <th>Clock Out</th>
        <th>Total Hours</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="recent-entries-tbody">
      @foreach($recentEntries as $entry)
      <tr>
        <td>{{ $entry->entry_date->format('M d, Y') }}</td>
        <td>{{ $entry->formatted_clock_in ?? '--' }}</td>
        <td>{{ $entry->formatted_clock_out ?? '--' }}</td>
        <td>{{ $entry->total_hours ? $entry->total_hours . ' hrs' : '--' }}</td>
        <td><span class="badge {{ $entry->status == 'approved' ? 'bg-success' : ($entry->status == 'pending' ? 'bg-warning' : 'bg-danger') }}">{{ ucfirst($entry->status) }}</span></td>
      </tr>
      @endforeach
    </tbody>
  </table>
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
.module-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
}

.module-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.module-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.module-card .card-title {
  color: var(--jetlouge-primary);
  font-weight: 600;
}

.module-card:hover .card-title {
  color: var(--jetlouge-secondary);
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
  
  // Initialize AI features
  initializeAIFeatures();
  
  // Initialize HR Settings functionality
  initializeHRSettings();
});

// AI Features Initialization
function initializeAIFeatures() {
  // Check AI connections status
  checkAIConnections();
  
  // Load AI dashboard data
  loadAIDashboardData();
  
  // Set up AI timer functionality
  setupAITimer();
  
  // Set up task analysis
  setupTaskAnalysis();
  
  // Auto-refresh AI data every 60 seconds
  setInterval(loadAIDashboardData, 60000);
}

// Check AI and Clockify connections
function checkAIConnections() {
  fetch('/api/ai/test-connections', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    // Update OpenAI status
    const openaiStatus = document.getElementById('openai-status');
    if (data.openai && data.openai.success) {
      openaiStatus.textContent = 'Connected';
      openaiStatus.className = 'badge bg-success';
    } else {
      openaiStatus.textContent = 'Disconnected';
      openaiStatus.className = 'badge bg-danger';
    }
    
    // Update Clockify status
    const clockifyStatus = document.getElementById('clockify-status');
    if (data.clockify && data.clockify.success) {
      clockifyStatus.textContent = 'Connected';
      clockifyStatus.className = 'badge bg-success';
    } else {
      clockifyStatus.textContent = 'Disconnected';
      clockifyStatus.className = 'badge bg-danger';
    }
  })
  .catch(error => {
    console.error('Error checking AI connections:', error);
    document.getElementById('openai-status').textContent = 'Error';
    document.getElementById('openai-status').className = 'badge bg-danger';
    document.getElementById('clockify-status').textContent = 'Error';
    document.getElementById('clockify-status').className = 'badge bg-danger';
  });
}

// Load AI dashboard data
function loadAIDashboardData() {
  fetch('/api/ai/dashboard-data', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    // Update productivity metrics
    if (data.productivity_metrics) {
      document.getElementById('today-total-hours').textContent = data.productivity_metrics.today_hours + ' hrs';
    }
    
    // Update AI insights
    if (data.ai_insights && data.ai_insights.success) {
      document.getElementById('ai-insights-content').innerHTML = `
        <div class="ai-insight-item">
          <pre style="white-space: pre-wrap; font-family: inherit;">${data.ai_insights.content}</pre>
        </div>
      `;
    }
    
    // Add recommendations to insights content
    if (data.recommendations && data.recommendations.length > 0) {
      let recommendationHtml = '<div class="mt-3"><h6 class="text-primary">Smart Recommendations:</h6>';
      data.recommendations.forEach(rec => {
        recommendationHtml += `<div class="alert alert-info alert-sm mb-2"><i class="fas fa-lightbulb me-2"></i>${rec}</div>`;
      });
      recommendationHtml += '</div>';
      
      // Append to insights content
      const currentContent = document.getElementById('ai-insights-content').innerHTML;
      if (!currentContent.includes('Loading AI insights')) {
        document.getElementById('ai-insights-content').innerHTML += recommendationHtml;
      }
    }
    
  })
  .catch(error => {
    console.error('Error loading AI dashboard data:', error);
    document.getElementById('ai-insights-content').innerHTML = '<p class="text-muted">Unable to load AI insights</p>';
  });
}

// Setup AI timer functionality
function setupAITimer() {
  const startBtn = document.getElementById('ai-start-timer-btn');
  const stopBtn = document.getElementById('ai-stop-timer-btn');
  const taskDescription = document.getElementById('ai-task-description');
  
  startBtn.addEventListener('click', function() {
    const description = taskDescription.value.trim();
    if (!description) {
      showNotification('Please enter a task description', 'warning');
      return;
    }
    
    // Start AI-enhanced timer
    fetch('/api/ai/timer/start-ai', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        description: description
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data && data.id) {
        startBtn.disabled = true;
        stopBtn.disabled = false;
        document.getElementById('timer-status').textContent = 'Running';
        document.getElementById('timer-status').className = 'badge bg-success';
        showNotification('AI timer started successfully', 'success');
        startTimerDisplay();
      } else {
        showNotification('Failed to start timer', 'error');
      }
    })
    .catch(error => {
      console.error('Error starting AI timer:', error);
      showNotification('Error starting timer', 'error');
    });
  });
  
  stopBtn.addEventListener('click', function() {
    fetch('/api/ai/timer/stop', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    })
    .then(response => response.json())
    .then(data => {
      startBtn.disabled = false;
      stopBtn.disabled = true;
      document.getElementById('timer-status').textContent = 'Stopped';
      document.getElementById('timer-status').className = 'badge bg-secondary';
      showNotification('Timer stopped successfully', 'success');
      stopTimerDisplay();
      loadAIDashboardData(); // Refresh data
    })
    .catch(error => {
      console.error('Error stopping timer:', error);
      showNotification('Error stopping timer', 'error');
    });
  });
}

// Setup task analysis
function setupTaskAnalysis() {
  const taskDescription = document.getElementById('ai-task-description');
  let analysisTimeout;
  
  taskDescription.addEventListener('input', function() {
    clearTimeout(analysisTimeout);
    const description = this.value.trim();
    
    if (description.length > 10) {
      analysisTimeout = setTimeout(() => {
        analyzeTask(description);
      }, 1000); // Debounce for 1 second
    } else {
      document.getElementById('ai-task-analysis').style.display = 'none';
    }
  });
}

// Analyze task with AI
function analyzeTask(description) {
  fetch('/api/ai/analyze-time-entry', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
      description: description
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success && data.content) {
      document.getElementById('ai-analysis-content').innerHTML = data.content;
      document.getElementById('ai-task-analysis').style.display = 'block';
    }
  })
  .catch(error => {
    console.error('Error analyzing task:', error);
  });
}

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

// Timer display functions
let timerInterval;
let startTime;

function startTimerDisplay() {
  startTime = new Date();
  timerInterval = setInterval(updateTimerDisplay, 1000);
}

function stopTimerDisplay() {
  clearInterval(timerInterval);
  document.getElementById('timer-duration').textContent = '00:00:00';
}

function updateTimerDisplay() {
  const now = new Date();
  const elapsed = Math.floor((now - startTime) / 1000);
  
  const hours = Math.floor(elapsed / 3600);
  const minutes = Math.floor((elapsed % 3600) / 60);
  const seconds = elapsed % 60;
  
  const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
  document.getElementById('timer-duration').textContent = timeString;
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

// Refresh AI insights
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('refresh-ai-insights-btn')?.addEventListener('click', function() {
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    loadAIDashboardData();
    setTimeout(() => {
      this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Insights';
    }, 2000);
  });
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
