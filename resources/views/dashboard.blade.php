@extends('layouts.hr')

@section('title', 'HR Dashboard - Jetlouge Travels')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">HR Dashboard</h2>
        <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name ?? 'John' }}! Here's what's happening with your HR system today.</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/admin_dashboard" class="text-decoration-none">Admin Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Home</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Time Clock Section -->
<div class="dashboard-section mb-4">
  <div class="time-clock text-center p-4" style="background-color: var(--jetlouge-light); border-radius: 8px;">
    <div class="current-time mb-3" id="live-clock" style="font-size: 36px; font-weight: bold; color: var(--jetlouge-primary);">--:--:--</div>
    <p class="text-muted mb-0">HR Management System Dashboard</p>
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
          <h3 class="fw-bold mb-0 stat-number">{{ $stats['total_employees'] ?? 0 }}</h3>
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
          <h3 class="fw-bold mb-0 stat-number">{{ $employeeStats['active_employees'] ?? 0 }}</h3>
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
          <h3 class="fw-bold mb-0 stat-number">15</h3>
          <p class="text-muted mb-0 small stat-label">Pending Timesheets</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-umbrella-beach text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">8</h3>
          <p class="text-muted mb-0 small stat-label">Leave Requests</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Today's Schedule Section -->
<div class="dashboard-section mb-4">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h3 style="color: var(--jetlouge-primary);">Today's Schedule</h3>
    <a href="{{ route('timesheet-management') }}" class="btn btn-primary">View Full Schedule</a>
  </div>
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

<!-- Recent Time Entries Section -->
<div class="dashboard-section">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h3 style="color: var(--jetlouge-primary);">Recent Time Entries</h3>
    <a href="{{ route('timesheet-management') }}" class="btn btn-primary">View All Entries</a>
  </div>
  <div class="card">
    <div class="card-body">
      <table class="table table-hover" id="recent-entries-table">
        <thead class="table-light">
          <tr>
            <th>Employee</th>
            <th>Date</th>
            <th>Clock In</th>
            <th>Clock Out</th>
            <th>Total Hours</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>John Anderson</td>
            <td>{{ now()->format('M d, Y') }}</td>
            <td>9:00 AM</td>
            <td>5:30 PM</td>
            <td>8.5 hrs</td>
            <td><span class="badge bg-success">Approved</span></td>
          </tr>
          <tr>
            <td>Jane Smith</td>
            <td>{{ now()->format('M d, Y') }}</td>
            <td>8:30 AM</td>
            <td>5:00 PM</td>
            <td>8.5 hrs</td>
            <td><span class="badge bg-warning">Pending</span></td>
          </tr>
          <tr>
            <td>Mike Johnson</td>
            <td>{{ now()->subDay()->format('M d, Y') }}</td>
            <td>9:15 AM</td>
            <td>6:00 PM</td>
            <td>8.75 hrs</td>
            <td><span class="badge bg-success">Approved</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <i class="fas fa-file-invoice-dollar text-primary fs-1 mb-3"></i>
        <h5>Claims</h5>
        <p class="text-muted">Manage expense claims</p>
        <a href="{{ route('claims-reimbursement') }}" class="btn btn-primary">View Claims</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <i class="fas fa-users text-success fs-1 mb-3"></i>
        <h5>Employees</h5>
        <p class="text-muted">Manage employee records</p>
        <a href="{{ route('employees') }}" class="btn btn-success">View Employees</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <i class="fas fa-umbrella-beach text-info fs-1 mb-3"></i>
        <h5>Leave</h5>
        <p class="text-muted">Manage leave requests</p>
        <a href="{{ route('leave-management') }}" class="btn btn-info">View Leave</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <i class="fas fa-clock text-warning fs-1 mb-3"></i>
        <h5>Timesheet</h5>
        <p class="text-muted">Track working hours</p>
        <a href="{{ route('timesheet-management') }}" class="btn btn-warning">View Timesheet</a>
      </div>
    </div>
  </div>
</div>

<script>
// Live clock
function updateClock() {
  const now = new Date();
  const timeString = now.toLocaleTimeString();
  document.getElementById('live-clock').textContent = timeString;
}

setInterval(updateClock, 1000);
updateClock();

// Dashboard functionality
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
</style>
@endsection
