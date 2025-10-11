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
        <p class="text-muted mb-0">Welcome back, {{ Auth::guard('employee')->user()->first_name ?? 'John' }}! Here's what's happening with your HR system today.</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active" aria-current="page">Home</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Time Clock Section -->
<div class="dashboard-section mb-4">
  <div class="row h-100">
    <!-- Clock In/Out Controls - Left Side -->
    <div class="col-lg-8 d-flex">
      <div class="time-clock text-center p-3 w-100 d-flex flex-column justify-content-between" style="background-color: var(--jetlouge-light); border-radius: 8px; min-height: 280px;">
        <div class="flex-grow-1 d-flex flex-column justify-content-center">
          <div class="current-time mb-3" id="live-clock" style="font-size: 36px; font-weight: bold; color: var(--jetlouge-primary);">--:--:--</div>
          <p class="text-muted mb-3">HR Management System Dashboard</p>
          
          <!-- Clock In/Out Controls -->
          <div class="attendance-controls" id="attendance-controls">
            <div class="row justify-content-center">
              <div class="col-auto">
                <button type="button" class="btn btn-success btn-lg me-2" id="clock-in-btn" onclick="clockIn()">
                  <i class="fas fa-clock me-2"></i>Clock In
                </button>
                <button type="button" class="btn btn-danger btn-lg me-2" id="clock-out-btn" onclick="clockOut()" style="display: none;">
                  <i class="fas fa-clock me-2"></i>Clock Out
                </button>
                <button type="button" class="btn btn-warning btn-lg me-2" id="break-start-btn" onclick="startBreak()" style="display: none;">
                  <i class="fas fa-coffee me-2"></i>Start Break
                </button>
                <button type="button" class="btn btn-info btn-lg" id="break-end-btn" onclick="endBreak()" style="display: none;">
                  <i class="fas fa-play me-2"></i>End Break
                </button>
              </div>
            </div>
            
            <!-- Status Display -->
            <div class="attendance-status mt-3" id="attendance-status">
              <span class="badge bg-secondary" id="status-badge">Not Clocked In</span>
              <div class="mt-2">
                <small class="text-muted" id="status-details">Click "Clock In" to start your workday</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Attendance Logs - Right Side -->
    <div class="col-lg-4 d-flex">
      <div class="attendance-logs-panel w-100 d-flex flex-column" style="background-color: var(--jetlouge-light); border-radius: 8px; padding: 1rem; min-height: 280px;">
        <div class="mb-3">
          <h5 class="mb-0" style="color: var(--jetlouge-primary);">
            <i class="fas fa-history me-2"></i>Attendance Logs
          </h5>
        </div>
        
        <!-- Logs Container -->
        <div class="attendance-logs-wrapper flex-grow-1">
          <h6 class="text-muted mb-2 small fw-bold">
            Recent Attendance:
            <small class="text-info ms-1" id="scroll-hint" style="display: none;">(Scroll to see more)</small>
          </h6>
          <div class="attendance-logs-container" id="attendance-logs-container">
            <div class="text-center text-muted py-4" id="logs-loading">
              <i class="fas fa-spinner fa-spin mb-2"></i>
              <div>Loading attendance logs...</div>
            </div>
          </div>
        </div>
        
        <!-- View All Link -->
        <div class="text-center mt-3">
          <a href="{{ route('timesheet-management') }}#attendance-tab" class="btn btn-sm btn-primary" onclick="navigateToAttendanceLogs()">
            <i class="fas fa-external-link-alt me-1"></i>View All Logs
          </a>
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
          <h3 class="fw-bold mb-0 stat-number">{{ $employeeStats['present_today'] ?? 0 }}</h3>
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
          <h3 class="fw-bold mb-0 stat-number">{{ $stats['pending_timesheets'] ?? 0 }}</h3>
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
          <h3 class="fw-bold mb-0 stat-number">{{ $stats['leave_requests'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Leave Requests</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="dashboard-section mb-4">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 style="color: var(--jetlouge-primary);">
        Scheduled Shifts
      </h3>
    </div>
    <a href="{{ route('shift-schedule-management') }}#calendar-section" class="btn btn-primary" onclick="scrollToCalendar()">View Full Schedule</a>
  </div>
  <div class="card">
    <div class="card-body shift-cards-container p-2">
      <div class="row">
        @php
          // Reorder shifts: Morning first, then Afternoon, then others
          $reorderedShifts = collect($todayShifts)->sortBy(function($shift) {
            $name = strtolower($shift['name']);
            if (str_contains($name, 'morning')) return 1;
            if (str_contains($name, 'afternoon')) return 2;
            if (str_contains($name, 'night')) return 3;
            if (str_contains($name, 'split')) return 4;
            if (str_contains($name, 'weekend')) return 5;
            return 6;
          });
          
          // Filter out shifts with no employees (hide empty boxes)
          $shiftsWithEmployees = $reorderedShifts->filter(function($shift) {
            return !empty($shift['employees']) && count($shift['employees']) > 0;
          });
        @endphp
        
        @forelse($shiftsWithEmployees as $index => $shift)
        @php
          // Determine responsive column class
          $totalShifts = $shiftsWithEmployees->count();
          if ($totalShifts == 1) {
            $colClass = 'col-12';
          } elseif ($totalShifts == 2) {
            $colClass = 'col-lg-6 col-md-12';
          } elseif ($totalShifts == 3) {
            $colClass = 'col-lg-4 col-md-6 col-sm-12';
          } elseif ($totalShifts == 4) {
            $colClass = 'col-lg-3 col-md-6 col-sm-12';
          } elseif ($totalShifts == 5) {
            // First 3 cards: col-lg-4, last 2 cards: col-lg-6 (centered)
            $colClass = $index < 3 ? 'col-lg-4 col-md-6 col-sm-12' : 'col-lg-6 col-md-6 col-sm-12';
          } else {
            $colClass = 'col-lg-4 col-md-6 col-sm-12';
          }
        @endphp
        <div class="{{ $colClass }} mb-3">
          <div class="shift-card-hr p-3 border rounded h-100">
            <div class="text-center mb-3">
              @php
                $shiftName = strtolower($shift['name']);
              @endphp
              @if(str_contains($shiftName, 'morning'))
                <i class="fas fa-sun text-warning fs-2 mb-2"></i>
              @elseif(str_contains($shiftName, 'afternoon'))
                <i class="fas fa-cloud-sun text-info fs-2 mb-2"></i>
              @elseif(str_contains($shiftName, 'night'))
                <i class="fas fa-moon text-dark fs-2 mb-2"></i>
              @elseif(str_contains($shiftName, 'split'))
                <i class="fas fa-clock text-secondary fs-2 mb-2"></i>
              @elseif(str_contains($shiftName, 'weekend'))
                <i class="fas fa-calendar-alt text-success fs-2 mb-2"></i>
              @else
                <i class="fas fa-briefcase text-primary fs-2 mb-2"></i>
              @endif
              <h5 class="mb-1">{{ $shift['name'] }}</h5>
              <p class="text-muted mb-2 small">{{ $shift['time_range'] }}</p>
              <span class="badge bg-primary">{{ $shift['employee_count'] }} employees</span>
            </div>
            
            <div class="employee-list-hr">
              <h6 class="text-muted mb-2 small fw-bold">
                Assigned Employees:
                @if(count($shift['employees']) > 2)
                  <small class="text-info ms-1">(Scroll to see all {{ count($shift['employees']) }})</small>
                @endif
              </h6>
              <div class="employee-list-container" data-employee-count="{{ count($shift['employees']) }}">
                @forelse($shift['employees'] as $employee)
                  <div class="employee-item-hr d-flex align-items-center mb-2 p-2 bg-light rounded">
                    <div class="employee-avatar-hr me-2">
                      @if($employee['avatar'] ?? false)
                        <img src="{{ asset('storage/' . $employee['avatar']) }}" alt="{{ $employee['name'] }}" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
                      @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px; font-weight: 600;">
                          {{ substr($employee['name'], 0, 1) }}
                        </div>
                      @endif
                    </div>
                    <div class="employee-info-hr flex-grow-1">
                      <div class="employee-name-hr small fw-bold text-truncate">{{ $employee['name'] }}</div>
                      @if(!empty($employee['position']))
                        <div class="employee-position-hr text-muted" style="font-size: 10px;">{{ $employee['position'] }}</div>
                      @endif
                      @if(!empty($employee['specific_time']))
                        <div class="employee-specific-time text-info" style="font-size: 9px; font-weight: 500;">{{ $employee['specific_time'] }}</div>
                      @endif
                    </div>
                  </div>
                @empty
                  <div class="text-center text-muted py-3">
                    <i class="fas fa-user-slash mb-2"></i>
                    <div class="small">No employees assigned for today</div>
                  </div>
                @endforelse
                @if(count($shift['employees']) > 2)
                  <div class="scroll-indicator text-center text-muted small py-1">
                    <i class="fas fa-chevron-down"></i> Scroll for more
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
        @empty
        <div class="col-12">
          <div class="text-center p-4">
            <i class="fas fa-calendar-times text-muted fs-2 mb-2"></i>
            <h5 class="text-muted">No Shifts Scheduled</h5>
            <p class="text-muted mb-0">No shift schedules found for today</p>
          </div>
        </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<!-- Recent Time Entries Section -->
<div class="dashboard-section">
  <div class="section-header d-flex justify-content-between align-items-center mb-3">
    <h3 style="color: var(--jetlouge-primary);">Recent Time Entries</h3>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshRecentTimeEntries()" id="refresh-time-entries-btn">
        <i class="fas fa-sync-alt"></i>
      </button>
      <a href="{{ route('timesheet-management') }}#attendance-tab" class="btn btn-primary" onclick="navigateToAttendanceLogs()">View All Entries</a>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="recent-entries-table">
          <thead class="table-light">
            <tr>
              <th>Employee</th>
              <th>Date</th>
              <th>Clock In</th>
              <th>Clock Out</th>
              <th>Total Time</th>
              <th>Status</th>
            </tr>
          </thead>
        </table>
        <div class="recent-entries-scroll-container">
          <table class="table table-hover mb-0">
            <tbody id="recent-entries-tbody">
          @forelse($recentTimeEntries as $entry)
          <tr>
            <td>
              <div class="d-flex align-items-center">
                @if($entry->profile_picture)
                  <img src="{{ asset('storage/' . $entry->profile_picture) }}" alt="{{ $entry->employee_name }}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                @else
                  <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px; font-weight: 600;">
                    {{ substr($entry->employee_name, 0, 1) }}
                  </div>
                @endif
                <span class="fw-medium">{{ $entry->employee_name }}</span>
              </div>
            </td>
            <td>{{ $entry->work_date ? $entry->work_date->format('M d, Y') : '--' }}</td>
            <td>{{ $entry->formatted_clock_in ?? '--' }}</td>
            <td>{{ $entry->formatted_clock_out ?? '--' }}</td>
            <td>
              @if($entry->total_hours !== null)
                @if($entry->total_hours >= 8)
                  <span class="text-success fw-bold">{{ $entry->formatted_total_time ?? $entry->total_hours . ' hrs' }}</span>
                @else
                  <span class="text-muted">{{ $entry->formatted_total_time ?? $entry->total_hours . ' hrs' }}</span>
                @endif
              @else
                <span class="text-muted">--</span>
              @endif
            </td>
            <td>
              @if($entry->total_hours !== null && $entry->total_hours >= 8)
                <span class="badge bg-success">
                  Clocked Out
                </span>
              @else
                <span class="badge bg-secondary">
                  Clocked Out
                </span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">
              <i class="fas fa-clock me-2"></i>No recent time entries found
            </td>
          </tr>
          @endforelse
            </tbody>
          </table>
        </div>
      </div>
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
        <a href="{{ route('employees.index') }}" class="btn btn-success">View Employees</a>
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

// Employee list scroll functionality
document.addEventListener('DOMContentLoaded', function() {
  const employeeContainers = document.querySelectorAll('.employee-list-container');
  
  employeeContainers.forEach(container => {
    // Check if scrolling is needed
    function checkScrollNeeded() {
      const isScrollable = container.scrollHeight > container.clientHeight;
      if (!isScrollable) {
        const scrollIndicator = container.querySelector('.scroll-indicator');
        if (scrollIndicator) {
          scrollIndicator.style.display = 'none';
        }
      }
    }
    
    // Handle scroll events
    container.addEventListener('scroll', function() {
      const scrollTop = container.scrollTop;
      const scrollHeight = container.scrollHeight;
      const clientHeight = container.clientHeight;
      
      // Check if scrolled to bottom (with small tolerance)
      const isAtBottom = scrollTop + clientHeight >= scrollHeight - 5;
      
      if (isAtBottom) {
        container.classList.add('scrolled-bottom');
      } else {
        container.classList.remove('scrolled-bottom');
      }
    });
    
    // Initial check
    checkScrollNeeded();
    
    // Add smooth scroll behavior on wheel events
    container.addEventListener('wheel', function(e) {
      e.preventDefault();
      container.scrollTop += e.deltaY * 0.5; // Smoother scrolling
    });
  });
});

// Dashboard functionality

// Attendance Management Functions
let currentEmployeeId = {{ Auth::guard('employee')->user()->id ?? 1 }}; // Get current logged-in employee ID

// Check attendance status on page load
document.addEventListener('DOMContentLoaded', function() {
  checkAttendanceStatus();
  loadAttendanceLogs();
});

// Check current attendance status
function checkAttendanceStatus() {
  console.log('Checking attendance status for employee ID:', currentEmployeeId);
  fetch(`/attendance/status/${currentEmployeeId}`)
    .then(response => {
      console.log('Response status:', response.status);
      return response.json();
    })
    .then(data => {
      console.log('Attendance status response:', data);
      if (data.success && data.data.status !== 'not_clocked_in') {
        updateAttendanceUI(data.data);
      } else {
        console.log('Employee not clocked in or no attendance record found');
      }
    })
    .catch(error => {
      console.error('Error checking attendance status:', error);
    });
}

// Clock In function
function clockIn() {
  console.log('Clock in initiated for employee ID:', currentEmployeeId);
  const clockInBtn = document.getElementById('clock-in-btn');
  clockInBtn.disabled = true;
  clockInBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Clocking In...';

  fetch('/attendance/clock-in', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      employee_id: currentEmployeeId,
      location: 'Office'
    })
  })
  .then(response => {
    console.log('Clock-in response status:', response.status);
    return response.json();
  })
  .then(data => {
    console.log('Clock-in response data:', data);
    if (data.success) {
      showNotification('Successfully clocked in!', 'success');
      updateAttendanceUI({
        status: 'present',
        clock_in_time: data.data.clock_in_time,
        is_clocked_in: true,
        is_clocked_out: false,
        is_on_break: false
      });
      updateAttendanceLogsAfterAction();
      updateRecentTimeEntriesAfterAction();
    } else {
      showNotification(data.message || 'Failed to clock in', 'error');
      clockInBtn.disabled = false;
      clockInBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Clock In';
    }
  })
  .catch(error => {
    console.error('Error clocking in:', error);
    showNotification('Error occurred while clocking in', 'error');
    clockInBtn.disabled = false;
    clockInBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Clock In';
  });
}

// Clock Out function
function clockOut() {
  const clockOutBtn = document.getElementById('clock-out-btn');
  clockOutBtn.disabled = true;
  clockOutBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Clocking Out...';

  fetch('/attendance/clock-out', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      employee_id: currentEmployeeId
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Successfully clocked out!', 'success');
      updateAttendanceUI({
        status: 'clocked_out',
        clock_out_time: data.data.clock_out_time,
        total_hours: data.data.total_hours,
        is_clocked_in: false,
        is_clocked_out: true,
        is_on_break: false
      });
      updateAttendanceLogsAfterAction();
      updateRecentTimeEntriesAfterAction();
    } else {
      showNotification(data.message || 'Failed to clock out', 'error');
      clockOutBtn.disabled = false;
      clockOutBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Clock Out';
    }
  })
  .catch(error => {
    console.error('Error clocking out:', error);
    showNotification('Error occurred while clocking out', 'error');
    clockOutBtn.disabled = false;
    clockOutBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Clock Out';
  });
}

// Start Break function
function startBreak() {
  const breakStartBtn = document.getElementById('break-start-btn');
  breakStartBtn.disabled = true;
  breakStartBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Starting Break...';

  fetch('/attendance/start-break', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      employee_id: currentEmployeeId
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Break started!', 'success');
      updateAttendanceUI({
        status: 'on_break',
        is_clocked_in: true,
        is_clocked_out: false,
        is_on_break: true
      });
      updateAttendanceLogsAfterAction();
      updateRecentTimeEntriesAfterAction();
    } else {
      showNotification(data.message || 'Failed to start break', 'error');
      breakStartBtn.disabled = false;
      breakStartBtn.innerHTML = '<i class="fas fa-coffee me-2"></i>Start Break';
    }
  })
  .catch(error => {
    console.error('Error starting break:', error);
    showNotification('Error occurred while starting break', 'error');
    breakStartBtn.disabled = false;
    breakStartBtn.innerHTML = '<i class="fas fa-coffee me-2"></i>Start Break';
  });
}

// End Break function
function endBreak() {
  const breakEndBtn = document.getElementById('break-end-btn');
  breakEndBtn.disabled = true;
  breakEndBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Ending Break...';

  fetch('/attendance/end-break', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      employee_id: currentEmployeeId
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Break ended!', 'success');
      updateAttendanceUI({
        status: 'present',
        is_clocked_in: true,
        is_clocked_out: false,
        is_on_break: false
      });
      updateAttendanceLogsAfterAction();
      updateRecentTimeEntriesAfterAction();
    } else {
      showNotification(data.message || 'Failed to end break', 'error');
      breakEndBtn.disabled = false;
      breakEndBtn.innerHTML = '<i class="fas fa-play me-2"></i>End Break';
    }
  })
  .catch(error => {
    console.error('Error ending break:', error);
    showNotification('Error occurred while ending break', 'error');
    breakEndBtn.disabled = false;
    breakEndBtn.innerHTML = '<i class="fas fa-play me-2"></i>End Break';
  });
}

// Update UI based on attendance status
function updateAttendanceUI(attendanceData) {
  const clockInBtn = document.getElementById('clock-in-btn');
  const clockOutBtn = document.getElementById('clock-out-btn');
  const breakStartBtn = document.getElementById('break-start-btn');
  const breakEndBtn = document.getElementById('break-end-btn');
  const statusBadge = document.getElementById('status-badge');
  const statusDetails = document.getElementById('status-details');

  // Reset all buttons
  clockInBtn.style.display = 'none';
  clockOutBtn.style.display = 'none';
  breakStartBtn.style.display = 'none';
  breakEndBtn.style.display = 'none';

  // Reset button states
  clockInBtn.disabled = false;
  clockOutBtn.disabled = false;
  breakStartBtn.disabled = false;
  breakEndBtn.disabled = false;

  // Reset button text
  clockInBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Clock In';
  clockOutBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Clock Out';
  breakStartBtn.innerHTML = '<i class="fas fa-coffee me-2"></i>Start Break';
  breakEndBtn.innerHTML = '<i class="fas fa-play me-2"></i>End Break';

  if (attendanceData.is_clocked_out) {
    // Employee has clocked out
    statusBadge.className = 'badge bg-secondary';
    statusBadge.textContent = 'Clocked Out';
    statusDetails.textContent = `Total time worked: ${formatHoursToTime(parseFloat(attendanceData.total_hours || 0))}`;
    clockInBtn.style.display = 'inline-block';
  } else if (attendanceData.is_on_break) {
    // Employee is on break
    statusBadge.className = 'badge bg-warning';
    statusBadge.textContent = 'On Break';
    statusDetails.textContent = 'You are currently on break';
    clockOutBtn.style.display = 'inline-block';
    breakEndBtn.style.display = 'inline-block';
  } else if (attendanceData.is_clocked_in) {
    // Employee is clocked in and working
    statusBadge.className = 'badge bg-success';
    statusBadge.textContent = 'Clocked In';
    statusDetails.textContent = `Clocked in at: ${attendanceData.clock_in_time || 'Unknown'}`;
    clockOutBtn.style.display = 'inline-block';
    breakStartBtn.style.display = 'inline-block';
  } else {
    // Employee not clocked in
    statusBadge.className = 'badge bg-secondary';
    statusBadge.textContent = 'Not Clocked In';
    statusDetails.textContent = 'Click "Clock In" to start your workday';
    clockInBtn.style.display = 'inline-block';
  }
}

// Show notification function
function showNotification(message, type = 'info') {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
  notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
  notification.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;

  // Add to page
  document.body.appendChild(notification);

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 5000);
}

// Test function for debugging attendance (available in browser console)
window.testAttendance = function() {
  console.log('Testing attendance functionality...');
  console.log('Current Employee ID:', currentEmployeeId);
  
  // Test database connection
  fetch('/test-attendance-functionality')
    .then(response => response.json())
    .then(data => {
      console.log('Database test result:', data);
      
      if (data.success) {
        console.log('✅ Database connection OK');
        console.log('📊 Employees:', data.employees_count);
        console.log('📊 Attendance records:', data.attendances_count);
        console.log('👤 Test employee:', data.test_employee);
        console.log('📋 Attendance columns:', data.attendance_columns);
        console.log('🕐 Today attendance:', data.today_attendance);
      } else {
        console.error('❌ Database test failed:', data.error);
      }
    })
    .catch(error => {
      console.error('❌ Test failed:', error);
    });
}

// Test clock-in function (available in browser console)
window.testClockIn = function() {
  console.log('Testing clock-in for employee:', currentEmployeeId);
  clockIn();
}

// Add sample attendance data for testing (available in browser console)
window.addSampleAttendanceData = function() {
  console.log('Adding sample attendance data for employee:', currentEmployeeId);
  
  fetch(`/add-sample-attendance/${currentEmployeeId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        console.log('✅ Sample data added successfully:', data.message);
        showNotification('Sample attendance data added successfully!', 'success');
        // Refresh the attendance logs to show new data
        setTimeout(() => {
          loadAttendanceLogs();
        }, 1000);
      } else {
        console.error('❌ Failed to add sample data:', data.message);
        showNotification('Failed to add sample data: ' + data.message, 'error');
      }
    })
    .catch(error => {
      console.error('❌ Error adding sample data:', error);
      showNotification('Error occurred while adding sample data', 'error');
    });
}

// Attendance Logs Functions
function loadAttendanceLogs() {
  const logsContainer = document.getElementById('attendance-logs-container');
  const loadingElement = document.getElementById('logs-loading');
  
  // Show loading state
  if (loadingElement) {
    loadingElement.style.display = 'block';
  }
  
  console.log('📡 Loading attendance logs for employee:', currentEmployeeId);
  
  fetch(`/attendance/logs/${currentEmployeeId}`)
    .then(response => response.json())
    .then(data => {
      if (loadingElement) {
        loadingElement.style.display = 'none';
      }
      
      console.log('📊 Attendance logs response:', data);
      
      if (data.success && data.logs && data.logs.length > 0) {
        console.log('✅ Found', data.logs.length, 'attendance logs');
        displayAttendanceLogs(data.logs);
      } else {
        console.log('ℹ️ No attendance logs found');
        displayNoLogsMessage();
      }
    })
    .catch(error => {
      console.error('❌ Error loading attendance logs:', error);
      if (loadingElement) {
        loadingElement.style.display = 'none';
      }
      displayErrorMessage();
    });
}

function displayAttendanceLogs(logs) {
  const logsContainer = document.getElementById('attendance-logs-container');
  const scrollHint = document.getElementById('scroll-hint');
  
  // Show scroll hint if more than 2 logs
  if (logs.length > 2) {
    scrollHint.style.display = 'inline';
  } else {
    scrollHint.style.display = 'none';
  }
  
  let logsHtml = '<div class="attendance-logs-list">';
  
  logs.forEach((log, index) => {
    const statusClass = getStatusClass(log.status);
    const statusIcon = getStatusIcon(log.status);
    
    logsHtml += `
      <div class="attendance-log-item ${index === 0 ? 'latest' : ''}" data-log-id="${log.id}">
        <div class="log-header d-flex justify-content-between align-items-center">
          <div class="log-date">
            <i class="fas fa-calendar-alt me-1"></i>
            ${formatLogDate(log.date)}
          </div>
          <span class="badge ${statusClass}">
            <i class="${statusIcon} me-1"></i>${log.status_text}
          </span>
        </div>
        <div class="log-details mt-2">
          <div class="row">
            <div class="col-6">
              <small class="text-muted">Clock In:</small>
              <div class="fw-bold">${log.clock_in_time || '--'}</div>
            </div>
            <div class="col-6">
              <small class="text-muted">Clock Out:</small>
              <div class="fw-bold">${log.clock_out_time || '--'}</div>
            </div>
          </div>
          ${log.total_hours ? `
            <div class="log-summary mt-2">
              <small class="text-muted">Total Time:</small>
              <span class="fw-bold text-primary">${formatHoursToTime(parseFloat(log.total_hours))}</span>
              ${log.overtime_hours > 0 ? `<span class="text-warning ms-2">+${parseFloat(log.overtime_hours).toFixed(2)} OT</span>` : ''}
            </div>
          ` : ''}
        </div>
      </div>
    `;
  });
  
  logsHtml += '</div>';
  logsContainer.innerHTML = logsHtml;
  
  // Add a subtle fade-in animation for new logs
  logsContainer.style.opacity = '0.7';
  setTimeout(() => {
    logsContainer.style.opacity = '1';
  }, 200);
  
  // Add scroll functionality similar to employee list
  setupAttendanceLogsScroll();
}

function displayNoLogsMessage() {
  const logsContainer = document.getElementById('attendance-logs-container');
  logsContainer.innerHTML = `
    <div class="text-center text-muted py-4">
      <i class="fas fa-clock-o fs-2 mb-2"></i>
      <div>No attendance logs found</div>
      <small>Your attendance history will appear here</small>
    </div>
  `;
}

function displayErrorMessage() {
  const logsContainer = document.getElementById('attendance-logs-container');
  logsContainer.innerHTML = `
    <div class="text-center text-danger py-4">
      <i class="fas fa-exclamation-triangle fs-2 mb-2"></i>
      <div>Failed to load attendance logs</div>
      <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadAttendanceLogs()">
        <i class="fas fa-retry me-1"></i>Try Again
      </button>
    </div>
  `;
}

function refreshAttendanceLogs() {
  console.log('Refreshing attendance logs...');
  loadAttendanceLogs();
}

// Helper functions for attendance logs
function getStatusClass(status) {
  switch(status) {
    case 'present': return 'bg-success';
    case 'late': return 'bg-warning';
    case 'absent': return 'bg-danger';
    case 'on_break': return 'bg-info';
    case 'clocked_out': return 'bg-secondary';
    default: return 'bg-light text-dark';
  }
}

function getStatusIcon(status) {
  switch(status) {
    case 'present': return 'fas fa-check';
    case 'late': return 'fas fa-clock';
    case 'absent': return 'fas fa-times';
    case 'on_break': return 'fas fa-coffee';
    case 'clocked_out': return 'fas fa-sign-out-alt';
    default: return 'fas fa-question';
  }
}

function formatLogDate(dateString) {
  const date = new Date(dateString);
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);
  
  if (date.toDateString() === today.toDateString()) {
    return 'Today';
  } else if (date.toDateString() === yesterday.toDateString()) {
    return 'Yesterday';
  } else {
    return date.toLocaleDateString('en-US', { 
      month: 'short', 
      day: 'numeric',
      year: date.getFullYear() !== today.getFullYear() ? 'numeric' : undefined
    });
  }
}

function formatHoursToTime(hours) {
  if (hours === null || hours === undefined) {
    return '--';
  }
  
  const wholeHours = Math.floor(hours);
  const minutes = Math.round((hours - wholeHours) * 60);
  
  if (wholeHours > 0 && minutes > 0) {
    return wholeHours + 'h ' + minutes + 'm';
  } else if (wholeHours > 0) {
    return wholeHours + 'h';
  } else if (minutes > 0) {
    return minutes + 'm';
  } else {
    return '0m';
  }
}

// Update attendance logs when clock actions are performed
function updateAttendanceLogsAfterAction() {
  console.log('🔄 Refreshing attendance logs after action...');
  
  // Show a subtle loading indicator
  const refreshBtn = document.querySelector('#attendance-logs-container .btn-outline-primary');
  if (refreshBtn) {
    const originalIcon = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    refreshBtn.disabled = true;
    
    // Restore button after refresh
    setTimeout(() => {
      refreshBtn.innerHTML = originalIcon;
      refreshBtn.disabled = false;
    }, 2000);
  }
  
  // Multiple refresh attempts to ensure data is updated
  setTimeout(() => {
    console.log('🔄 First refresh attempt...');
    loadAttendanceLogs();
  }, 500); // Quick first attempt
  
  setTimeout(() => {
    console.log('🔄 Second refresh attempt...');
    loadAttendanceLogs();
  }, 1500); // Second attempt to ensure database is updated
  
  // Show success message
  setTimeout(() => {
    console.log('✅ Attendance logs refreshed successfully');
  }, 2000);
}

// Setup scroll functionality for attendance logs (similar to employee list)
function setupAttendanceLogsScroll() {
  const container = document.getElementById('attendance-logs-container');
  if (!container) return;
  
  // Add smooth scroll behavior on wheel events
  container.addEventListener('wheel', function(e) {
    e.preventDefault();
    container.scrollTop += e.deltaY * 0.5; // Smoother scrolling
  });
}

// Recent Time Entries Functions
function refreshRecentTimeEntries() {
  console.log('🔄 Refreshing Recent Time Entries...');
  
  const refreshBtn = document.getElementById('refresh-time-entries-btn');
  const tableBody = document.querySelector('#recent-entries-table tbody');
  
  if (refreshBtn) {
    const originalIcon = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    refreshBtn.disabled = true;
    
    // Restore button after refresh
    setTimeout(() => {
      refreshBtn.innerHTML = originalIcon;
      refreshBtn.disabled = false;
    }, 2000);
  }
  
  fetch('/recent-time-entries')
    .then(response => {
      console.log('📡 Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('📊 Recent Time Entries response:', data);
      
      if (data.success && data.entries) {
        updateRecentTimeEntriesTable(data.entries);
        console.log('✅ Recent Time Entries updated successfully');
        showNotification('Time entries refreshed successfully!', 'success');
      } else {
        console.error('❌ Failed to get recent time entries:', data.message);
        showNotification(`Failed to refresh time entries: ${data.message || 'Unknown error'}`, 'error');
      }
    })
    .catch(error => {
      console.error('❌ Error refreshing recent time entries:', error);
      showNotification(`Error occurred while refreshing time entries: ${error.message}`, 'error');
    });
}

function updateRecentTimeEntriesTable(entries) {
  const tableBody = document.querySelector('#recent-entries-tbody');
  if (!tableBody) return;
  
  // Add fade effect
  tableBody.style.opacity = '0.7';
  
  let tableHtml = '';
  
  if (entries.length === 0) {
    tableHtml = `
      <tr>
        <td colspan="6" class="text-center text-muted py-4">
          <i class="fas fa-clock me-2"></i>No recent time entries found
        </td>
      </tr>
    `;
  } else {
    entries.forEach(entry => {
      const totalHoursDisplay = entry.total_hours !== null ? 
        (entry.total_hours >= 8 ? 
          `<span class="text-success fw-bold">${entry.formatted_total_time || parseFloat(entry.total_hours).toFixed(2) + ' hrs'}</span>` : 
          `<span class="text-muted">${entry.formatted_total_time || parseFloat(entry.total_hours).toFixed(2) + ' hrs'}</span>`) : 
        '<span class="text-muted">--</span>';
      
      const statusBadge = entry.status === 'success' ? 
        '<span class="badge bg-success">Clocked Out</span>' : 
        '<span class="badge bg-secondary">Clocked Out</span>';
      
      const profileImage = entry.profile_picture ? 
        `<img src="/storage/${entry.profile_picture}" alt="${entry.employee_name}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">` :
        `<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px; font-weight: 600;">
          ${entry.employee_name.charAt(0)}
        </div>`;
      
      tableHtml += `
        <tr>
          <td>
            <div class="d-flex align-items-center">
              ${profileImage}
              <span class="fw-medium">${entry.employee_name}</span>
            </div>
          </td>
          <td>${entry.work_date}</td>
          <td>${entry.formatted_clock_in}</td>
          <td>${entry.formatted_clock_out}</td>
          <td>${totalHoursDisplay}</td>
          <td>${statusBadge}</td>
        </tr>
      `;
    });
  }
  
  tableBody.innerHTML = tableHtml;
  
  // Restore opacity with animation
  setTimeout(() => {
    tableBody.style.opacity = '1';
  }, 200);
}

// Auto-refresh Recent Time Entries after clock actions
function updateRecentTimeEntriesAfterAction() {
  setTimeout(() => {
    refreshRecentTimeEntries();
  }, 1000); // Delay to ensure database is updated
}
</script>

<style>
/* Dashboard Layout Fix - Remove Vertical Scroll */
.dashboard-section {
  margin-bottom: 1.5rem;
}

.dashboard-section:last-child {
  margin-bottom: 1rem; /* Add bottom margin to prevent cutoff */
}

/* Ensure content doesn't get cut off by scrollbar */
.row {
  margin-right: 0;
  margin-left: 0;
}

.card, .stat-card-modern {
  margin-right: 0;
}

/* Table responsive container */
.table-responsive {
  margin-right: 0;
  overflow-x: auto;
}

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

/* HR Dashboard Shift Card Styling */
.shift-card-hr {
  transition: all 0.3s ease;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border: 1px solid #e9ecef !important;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  position: relative;
  overflow: visible;
  padding: 20px !important;
  min-height: 240px;
}

.shift-card-hr:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
  border-color: #007bff !important;
}

/* Removed the blue top border from individual shift cards */

.employee-item-hr {
  transition: all 0.2s ease;
  border: 1px solid #dee2e6;
  background: #ffffff !important;
  margin-bottom: 8px;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  padding: 8px 12px !important;
  width: 100%;
  box-sizing: border-box;
}

.employee-item-hr:hover {
  background-color: #e3f2fd !important;
  border-color: #2196f3;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.2);
}

.employee-avatar-hr .rounded-circle {
  background: linear-gradient(135deg, #007bff, #0056b3) !important;
  font-weight: 600;
  text-transform: uppercase;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Employee list container with vertical scrolling - Limited to 2 visible employees */
.employee-list-container {
  max-height: 150px; /* Height for exactly 2 employee items (60px each) */
  overflow-y: auto; /* Enable vertical scrolling */
  overflow-x: hidden; /* Disable horizontal scrolling */
  padding: 4px;
  padding-right: 12px; /* More space for scrollbar */
  margin-right: -4px; /* Compensate for padding */
  
  /* Hide scrollbar by default */
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* Internet Explorer 10+ */
  
  /* Smooth scrolling */
  scroll-behavior: smooth;
}

/* Hide scrollbar for WebKit browsers (Chrome, Safari, Edge) */
.employee-list-container::-webkit-scrollbar {
  width: 8px; /* Slightly wider for better visibility */
  background: transparent;
}

.employee-list-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
  margin: 2px 0;
}

.employee-list-container::-webkit-scrollbar-thumb {
  background: rgba(0, 123, 255, 0.2);
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
  opacity: 0; /* Hidden by default */
}

/* Show scrollbar when hovering over the entire employee list area */
.employee-list-hr:hover .employee-list-container::-webkit-scrollbar-thumb {
  opacity: 1;
  background: rgba(0, 123, 255, 0.5);
}

/* Enhanced scrollbar on direct hover */
.employee-list-container:hover::-webkit-scrollbar-thumb {
  opacity: 1;
  background: rgba(0, 123, 255, 0.7);
  border: 1px solid rgba(0, 123, 255, 0.3);
}

.employee-list-container:hover::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 123, 255, 0.9);
}

/* Firefox scrollbar styling */
.employee-list-hr:hover .employee-list-container {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 123, 255, 0.5) rgba(0, 0, 0, 0.05);
}

.employee-list-container:hover {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 123, 255, 0.7) rgba(0, 0, 0, 0.1);
}

/* Employee list container with proper spacing */
.employee-list-hr {
  position: relative;
  padding-bottom: 5px;
}

/* Scroll indicator styling */
.scroll-indicator {
  position: sticky;
  bottom: 0;
  background: linear-gradient(to top, rgba(248, 249, 250, 0.9), transparent);
  margin: 0 -4px -2px -4px;
  padding: 4px 0 2px 0;
  font-size: 10px;
  opacity: 0.7;
  transition: opacity 0.3s ease;
}

.employee-list-container:hover .scroll-indicator {
  opacity: 1;
}

/* Hide scroll indicator when scrolled to bottom */
.employee-list-container.scrolled-bottom .scroll-indicator {
  display: none;
}

.shift-card-hr .badge {
  font-size: 0.7rem;
  padding: 0.35em 0.7em;
  background: linear-gradient(135deg, #007bff, #0056b3) !important;
  box-shadow: 0 2px 4px rgba(0,123,255,0.3);
}

.employee-name-hr {
  color: #2c3e50;
  font-weight: 600;
}

.employee-position-hr {
  color: #6c757d;
  font-weight: 500;
}

/* Today's Schedule section styling */
.dashboard-section .card {
  position: relative;
  overflow: hidden;
}

.dashboard-section .card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #007bff, #0056b3);
  z-index: 1;
}

/* Enhanced table styling */
.table-hover tbody tr:hover {
  background-color: rgba(0,123,255,0.05);
}

.table thead th {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  color: #495057;
}

/* Improved shift card alignment - Fill available space */
.shift-cards-container .row {
  display: flex;
  flex-wrap: wrap;
  justify-content: stretch;
  align-items: stretch;
  margin: 0;
}

.shift-cards-container .row > div {
  display: flex;
  margin-bottom: 1rem;
  padding: 0 0.5rem;
}

/* Remove extra margins and ensure full width coverage */
.shift-cards-container {
  padding: 0;
}

.shift-card-hr {
  width: 100%;
  min-height: 280px;
  margin: 0.25rem;
  flex: 1;
}

/* Responsive card layout */
@media (min-width: 992px) {
  .shift-cards-container .row {
    justify-content: center;
  }
}

@media (min-width: 768px) and (max-width: 991px) {
  .shift-cards-container .row {
    justify-content: flex-start;
  }
  
  .shift-card-hr {
    min-height: 260px;
  }
}

@media (max-width: 767px) {
  .shift-cards-container .row {
    justify-content: center;
  }
  
  .shift-card-hr {
    min-height: 240px;
    margin: 0.125rem;
  }
}

/* Attendance Controls Styling */
.attendance-controls {
  margin-top: 1rem;
}

.attendance-controls .btn {
  font-weight: 600;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  min-width: 120px;
}

.attendance-controls .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.attendance-controls .btn:active {
  transform: translateY(0);
}

.attendance-controls .btn-success {
  background: linear-gradient(135deg, #28a745, #20c997);
  border: none;
}

.attendance-controls .btn-danger {
  background: linear-gradient(135deg, #dc3545, #e74c3c);
  border: none;
}

.attendance-controls .btn-warning {
  background: linear-gradient(135deg, #ffc107, #f39c12);
  border: none;
  color: #fff;
}

.attendance-controls .btn-info {
  background: linear-gradient(135deg, #17a2b8, #3498db);
  border: none;
}

.attendance-status {
  padding: 1rem;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.attendance-status .badge {
  font-size: 0.9rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
}

/* Notification styling */
.alert.position-fixed {
  animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Attendance Logs Panel Styling */
.attendance-logs-panel {
  min-height: 280px;
}

/* Time Clock Panel Styling */
.time-clock {
  min-height: 280px;
}

/* Attendance logs container with vertical scrolling - Limited to 2 visible logs */
.attendance-logs-container {
  max-height: 160px; /* Height for exactly 2 attendance log items */
  overflow-y: auto; /* Enable vertical scrolling */
  overflow-x: hidden; /* Disable horizontal scrolling */
  padding-right: 8px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease, opacity 0.3s ease;
}

/* Hide scrollbar for WebKit browsers (Chrome, Safari, Edge) */
.attendance-logs-container::-webkit-scrollbar {
  width: 8px; /* Slightly wider for better visibility */
  background: transparent;
}

.attendance-logs-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
  margin: 2px 0;
}

.attendance-logs-container::-webkit-scrollbar-thumb {
  background: rgba(0, 123, 255, 0.2);
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  opacity: 0;
  transition: opacity 0.3s ease, background 0.3s ease;
}

/* Show scrollbar when hovering over the entire attendance logs wrapper */
.attendance-logs-wrapper:hover .attendance-logs-container::-webkit-scrollbar-thumb {
  opacity: 1;
  background: rgba(0, 123, 255, 0.5);
}

/* Enhanced scrollbar on direct hover */
.attendance-logs-container:hover::-webkit-scrollbar-thumb {
  opacity: 1;
  background: rgba(0, 123, 255, 0.7);
  border: 1px solid rgba(0, 123, 255, 0.3);
}

.attendance-logs-container:hover::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 123, 255, 0.9);
}

/* Firefox scrollbar styling */
.attendance-logs-wrapper:hover .attendance-logs-container {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 123, 255, 0.5) rgba(0, 0, 0, 0.05);
}

.attendance-logs-container:hover {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 123, 255, 0.7) rgba(0, 0, 0, 0.1);
}

.attendance-log-item {
  background: rgba(255, 255, 255, 0.8);
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 0.75rem;
  border: 1px solid rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.attendance-log-item:hover {
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

.attendance-log-item.latest {
  border-left: 4px solid var(--jetlouge-primary);
  background: rgba(255, 255, 255, 1);
}

.attendance-log-item .log-header {
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  padding-bottom: 0.5rem;
  margin-bottom: 0.5rem;
}

.attendance-log-item .log-date {
  font-weight: 600;
  color: var(--jetlouge-primary);
  font-size: 0.9rem;
}

.attendance-log-item .log-details {
  font-size: 0.85rem;
}

.attendance-log-item .log-summary {
  background: rgba(0, 123, 255, 0.05);
  border-radius: 4px;
  padding: 0.5rem;
  border-left: 3px solid var(--jetlouge-primary);
}

.attendance-logs-list:empty::after {
  content: "No attendance logs found";
  display: block;
  text-align: center;
  color: #6c757d;
  padding: 2rem;
  font-style: italic;
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
  
  .shift-card-hr {
    margin-bottom: 1rem;
    min-height: 220px;
    padding: 15px !important;
  }
  
  .employee-list-hr {
    position: relative;
    padding-bottom: 5px;
  }
  
  .employee-list-container {
    max-height: 110px; /* Height for 2 employee items on mobile */
    overflow-y: auto;
    overflow-x: hidden;
    padding: 3px;
    padding-right: 10px;
    margin-right: -3px;
    scroll-behavior: smooth;
  }
  
  /* Mobile scrollbar adjustments */
  .employee-list-container::-webkit-scrollbar {
    width: 6px;
  }
  
  .employee-list-hr:hover .employee-list-container::-webkit-scrollbar-thumb {
    opacity: 1;
    background: rgba(0, 123, 255, 0.6);
  }
  
  .employee-item-hr {
    margin-bottom: 6px;
    padding: 6px 10px !important;
  }
  
  /* Mobile attendance controls */
  .attendance-controls .btn {
    min-width: 100px;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    margin-bottom: 0.5rem;
  }
  
  .attendance-controls .row {
    flex-direction: column;
    align-items: center;
  }
  
  .attendance-controls .col-auto {
    width: 100%;
    text-align: center;
  }
  
  .attendance-status {
    margin-top: 1rem;
    padding: 0.75rem;
  }
  
  /* Mobile attendance logs */
  .attendance-logs-panel {
    margin-top: 1rem;
    min-height: 250px;
  }
  
  .attendance-logs-container {
    max-height: 140px; /* Height for 2 attendance items on mobile */
  }
  
  .attendance-log-item {
    padding: 0.75rem;
    margin-bottom: 0.5rem;
  }
  
  .attendance-log-item .log-details {
    font-size: 0.8rem;
  }
  
  /* Mobile scrollbar adjustments for attendance logs */
  .attendance-logs-container::-webkit-scrollbar {
    width: 6px;
  }
  
  .attendance-logs-wrapper:hover .attendance-logs-container::-webkit-scrollbar-thumb {
    opacity: 1;
    background: rgba(0, 123, 255, 0.6);
  }
}

/* Recent Time Entries Table Animation */
#recent-entries-tbody {
  transition: opacity 0.3s ease;
}

/* Recent Time Entries Scroll Container - Show only 3 entries */
.recent-entries-scroll-container {
  max-height: 156px; /* Height for exactly 3 table rows (52px each) */
  overflow-y: auto;
  overflow-x: hidden;
  border: 1px solid #dee2e6;
  border-top: none; /* Remove top border since header table has bottom border */
  border-radius: 0 0 0.375rem 0.375rem; /* Round bottom corners */
}

/* Scrollbar styling for Recent Time Entries */
.recent-entries-scroll-container::-webkit-scrollbar {
  width: 8px;
  background: transparent;
}

.recent-entries-scroll-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
  margin: 2px 0;
}

.recent-entries-scroll-container::-webkit-scrollbar-thumb {
  background: rgba(0, 123, 255, 0.2);
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  opacity: 0.6;
}

.recent-entries-scroll-container:hover::-webkit-scrollbar-thumb {
  opacity: 1;
  background: rgba(0, 123, 255, 0.7);
  border: 1px solid rgba(0, 123, 255, 0.3);
}

.recent-entries-scroll-container:hover::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 123, 255, 0.9);
}

/* Firefox scrollbar styling */
.recent-entries-scroll-container:hover {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 123, 255, 0.7) rgba(0, 0, 0, 0.1);
}

/* Ensure table columns align with header */
.recent-entries-scroll-container table {
  table-layout: fixed;
  width: 100%;
}

.recent-entries-scroll-container table th,
.recent-entries-scroll-container table td {
  border-top: none; /* Remove top borders since we have container border */
}
</style>

<script>
function scrollToCalendar() {
  // Store the scroll target in sessionStorage so it persists across page navigation
  sessionStorage.setItem('scrollToCalendar', 'true');
}

function navigateToAttendanceLogs() {
  // Store the tab target in sessionStorage so it persists across page navigation
  sessionStorage.setItem('activateAttendanceTab', 'true');
}

// Check if we need to scroll when the shift-schedule page loads
if (sessionStorage.getItem('scrollToCalendar') === 'true') {
  sessionStorage.removeItem('scrollToCalendar');
  // Wait for page to load then scroll
  window.addEventListener('load', function() {
    setTimeout(function() {
      const calendarSection = document.getElementById('calendar-section') || 
                             document.querySelector('.calendar') || 
                             document.querySelector('[id*="calendar"]') ||
                             document.querySelector('.tab-pane');
      if (calendarSection) {
        calendarSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        // Fallback: scroll to bottom of page where calendar usually is
        window.scrollTo({ top: document.body.scrollHeight * 0.7, behavior: 'smooth' });
      }
    }, 500);
  });
}
</script>
@endsection
