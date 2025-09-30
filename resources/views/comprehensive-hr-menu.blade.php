@extends('layouts.hr')

@section('title', 'HR Management System - Jetlouge Travels')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">HR Management System</h2>
        <p class="text-muted mb-0">Complete Time & Attendance Management</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">HR System</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Quick Stats -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
            <i class="fas fa-users"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0" id="total-employees">0</h3>
            <p class="text-muted mb-0 small">Active Employees</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
            <i class="fas fa-clock"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0" id="clocked-in">0</h3>
            <p class="text-muted mb-0 small">Currently Clocked In</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
            <i class="fas fa-calendar-times"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0" id="pending-requests">0</h3>
            <p class="text-muted mb-0 small">Pending Requests</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
            <i class="fas fa-receipt"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0" id="pending-claims">0</h3>
            <p class="text-muted mb-0 small">Pending Claims</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Menu Grid -->
<div class="row g-4">
  
  <!-- Time and Attendance System -->
  <div class="col-lg-6">
    <div class="card h-100 module-card">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
          <i class="fas fa-clock me-2"></i>Time and Attendance System
        </h5>
      </div>
      <div class="card-body">
        <p class="card-text text-muted mb-4">Comprehensive time tracking and attendance management</p>
        
        <div class="row g-3">
          <div class="col-md-6">
            <a href="{{ route('time-attendance') }}" class="btn btn-outline-primary w-100 text-start">
              <i class="fas fa-sign-in-alt me-2"></i>Clock In/Out & Attendance Log
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('timesheet.index') }}" class="btn btn-outline-primary w-100 text-start">
              <i class="fas fa-file-alt me-2"></i>Timesheet Management
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('shifts.index') }}" class="btn btn-outline-primary w-100 text-start">
              <i class="fas fa-calendar-week me-2"></i>Shift Management
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('shifts.schedule') }}" class="btn btn-outline-primary w-100 text-start">
              <i class="fas fa-calendar-alt me-2"></i>Employee Scheduling
            </a>
          </div>
        </div>
        
        <div class="mt-3">
          <small class="text-muted">
            <strong>Features:</strong> Overtime calculation, Break tracking, Shift swapping
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Leave Management -->
  <div class="col-lg-6">
    <div class="card h-100 module-card">
      <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0">
          <i class="fas fa-calendar-times me-2"></i>Leave Management
        </h5>
      </div>
      <div class="card-body">
        <p class="card-text text-muted mb-4">Complete leave request and approval workflow system</p>
        
        <div class="row g-3">
          <div class="col-md-6">
            <a href="{{ route('leave-management') }}" class="btn btn-outline-success w-100 text-start">
              <i class="fas fa-plus-circle me-2"></i>Leave Requests
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('leave-management') }}?tab=balances" class="btn btn-outline-success w-100 text-start">
              <i class="fas fa-chart-pie me-2"></i>Leave Balances
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('leave-management') }}?tab=types" class="btn btn-outline-success w-100 text-start">
              <i class="fas fa-cogs me-2"></i>Leave Types Config
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('leave-management') }}?tab=approval" class="btn btn-outline-success w-100 text-start">
              <i class="fas fa-check-circle me-2"></i>Approval Workflow
            </a>
          </div>
        </div>
        
        <div class="mt-3">
          <small class="text-muted">
            <strong>Features:</strong> Multiple leave types, Balance tracking, Approval workflow, Carry forward
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Claims and Reimbursement -->
  <div class="col-lg-6">
    <div class="card h-100 module-card">
      <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0">
          <i class="fas fa-receipt me-2"></i>Claims and Reimbursement
        </h5>
      </div>
      <div class="card-body">
        <p class="card-text text-muted mb-4">Expense claims and reimbursement management system</p>
        
        <div class="row g-3">
          <div class="col-md-6">
            <a href="{{ route('claims.index') }}" class="btn btn-outline-warning w-100 text-start">
              <i class="fas fa-plus-circle me-2"></i>Submit Claims
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('claims.index') }}?tab=review" class="btn btn-outline-warning w-100 text-start">
              <i class="fas fa-search me-2"></i>Review & Approval
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('claims.index') }}?tab=types" class="btn btn-outline-warning w-100 text-start">
              <i class="fas fa-cogs me-2"></i>Claim Types Config
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('claims.index') }}?tab=reports" class="btn btn-outline-warning w-100 text-start">
              <i class="fas fa-chart-bar me-2"></i>Claims Reports
            </a>
          </div>
        </div>
        
        <div class="mt-3">
          <small class="text-muted">
            <strong>Features:</strong> Receipt upload, Approval limits, Payment tracking, Expense categories
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Employee Management -->
  <div class="col-lg-6">
    <div class="card h-100 module-card">
      <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0">
          <i class="fas fa-users me-2"></i>Employee Management
        </h5>
      </div>
      <div class="card-body">
        <p class="card-text text-muted mb-4">Complete employee information and management system</p>
        
        <div class="row g-3">
          <div class="col-md-6">
            <a href="{{ route('employees') }}" class="btn btn-outline-info w-100 text-start">
              <i class="fas fa-list me-2"></i>Employee Directory
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('employees') }}" class="btn btn-outline-info w-100 text-start">
              <i class="fas fa-user-plus me-2"></i>Add Employee
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('employees') }}?tab=profiles" class="btn btn-outline-info w-100 text-start">
              <i class="fas fa-id-card me-2"></i>Employee Profiles
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('employees') }}?tab=reports" class="btn btn-outline-info w-100 text-start">
              <i class="fas fa-chart-line me-2"></i>Employee Reports
            </a>
          </div>
        </div>
        
        <div class="mt-3">
          <small class="text-muted">
            <strong>Features:</strong> Personal info, Department management, Role assignments, Performance tracking
          </small>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Quick Actions -->
<div class="row mt-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-bolt me-2"></i>Quick Actions
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <button class="btn btn-success w-100" id="quick-leave-request">
              <i class="fas fa-calendar-plus me-2"></i>Request Leave
            </button>
          </div>
          <div class="col-md-3">
            <button class="btn btn-warning w-100" id="quick-submit-claim">
              <i class="fas fa-receipt me-2"></i>Submit Claim
            </button>
          </div>
          <div class="col-md-3">
            <button class="btn btn-info w-100" id="view-schedule">
              <i class="fas fa-calendar-week me-2"></i>View Schedule
            </button>
          </div>
          <div class="col-md-3">
            <button class="btn btn-secondary w-100" id="generate-report">
              <i class="fas fa-file-download me-2"></i>Reports
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
  loadQuickStats();
  setupQuickActions();
});

// Load quick statistics
function loadQuickStats() {
  fetch('/api/dashboard/stats', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('total-employees').textContent = data.data.total_employees || 0;
      document.getElementById('clocked-in').textContent = data.data.clocked_in || 0;
      document.getElementById('pending-requests').textContent = data.data.pending_leave_requests || 0;
      document.getElementById('pending-claims').textContent = data.data.pending_claims || 0;
    }
  })
  .catch(error => {
    console.error('Error loading stats:', error);
  });
}

// Setup quick actions
function setupQuickActions() {
  // Quick actions
  document.getElementById('quick-leave-request').addEventListener('click', function() {
    window.location.href = '/leave-management';
  });

  document.getElementById('quick-submit-claim').addEventListener('click', function() {
    window.location.href = '/claims';
  });

  document.getElementById('view-schedule').addEventListener('click', function() {
    window.location.href = '/shifts/schedule';
  });

  document.getElementById('generate-report').addEventListener('click', function() {
    window.location.href = '/timesheet?action=report';
  });
}

// Notification function
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
  notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
  notification.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 5000);
}

// Auto-refresh stats every 30 seconds
setInterval(loadQuickStats, 30000);
</script>

<style>
.module-card {
  transition: transform 0.2s ease-in-out;
}

.module-card:hover {
  transform: translateY(-5px);
}

.stat-card {
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.stat-icon {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 1.5rem;
}
</style>
@endpush
@endsection
