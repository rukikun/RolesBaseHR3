@extends('layouts.hr')

@section('title', 'Time and Attendance - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Time and Attendance</h2>
        <p class="text-muted mb-0">Clock in/out and track attendance</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Time & Attendance</li>
      </ol>
    </nav>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Attendance Management Overview -->
<div class="dashboard-section mb-4">
  <div class="attendance-overview text-center p-5 bg-jetlouge-light rounded-3 border border-jetlouge-accent">
    <div class="current-time mb-3 text-jetlouge-primary" id="live-clock" style="font-size: 3.5rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">--:--:--</div>
    <div class="current-date mb-4 text-jetlouge-secondary" id="live-date" style="font-size: 1.25rem; font-weight: 500;">--</div>
    
    <div class="alert alert-info mb-4 border-0 shadow-sm">
      <i class="fas fa-info-circle me-2 text-jetlouge-info"></i>
      <strong class="text-jetlouge-primary">Time & Attendance Management</strong><br>
      Integrated with Clockify for professional time tracking.
    </div>
    
    <!-- Clockify Integration Status -->
    <div class="row mb-3">
      <div class="col-md-6">
        <div class="card border-primary">
          <div class="card-body text-center">
            <div id="clockify-status" class="mb-2">
              <span class="badge bg-secondary" id="connection-status">Checking Connection...</span>
            </div>
            <button class="btn btn-primary btn-sm" id="test-clockify-btn">
              <i class="fas fa-plug me-1"></i>Test Clockify Connection
            </button>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border-success">
          <div class="card-body text-center">
            <div class="d-flex justify-content-center gap-2">
              <button class="btn btn-success" id="clockify-clock-in-btn">
                <i class="fas fa-play me-2"></i>Clock In (Clockify)
              </button>
              <button class="btn btn-danger" id="clockify-clock-out-btn" disabled>
                <i class="fas fa-stop me-2"></i>Clock Out (Clockify)
              </button>
            </div>
            <div class="mt-2">
              <small class="text-muted" id="clockify-timer-status">Ready to start timer</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Today's Summary -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-clock text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="today-hours">0.0</h3>
          <p class="text-muted mb-0 small stat-label">Hours Today</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-success">
          <i class="fas fa-calendar-check text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="week-hours">0.0</h3>
          <p class="text-muted mb-0 small stat-label">Hours This Week</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-calendar-alt text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="month-hours">0.0</h3>
          <p class="text-muted mb-0 small stat-label">Hours This Month</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-warning">
          <i class="fas fa-user-clock text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="attendance-rate">100%</h3>
          <p class="text-muted mb-0 small stat-label">Attendance Rate</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Attendance Log Filters -->
<div class="row mb-4">
  <div class="col-md-3">
    <label for="attendance-employee" class="form-label">Employee</label>
    <select id="attendance-employee" class="form-select" name="employee_id">
      <option value="">All Employees</option>
      @foreach($employees ?? [] as $employee)
        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label for="attendance-period" class="form-label">Period</label>
    <select id="attendance-period" class="form-select">
      <option value="today">Today</option>
      <option value="yesterday">Yesterday</option>
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
      <input type="date" id="attendance-start-date" class="form-control">
      <span class="align-self-center">to</span>
      <input type="date" id="attendance-end-date" class="form-control">
    </div>
  </div>
  <div class="col-md-2">
    <label class="form-label">&nbsp;</label>
    <div class="d-flex gap-2">
      <button class="btn btn-secondary" id="filter-attendance-btn">Filter</button>
      <button class="btn btn-primary" id="refresh-attendance-btn">
        <i class="fas fa-sync-alt"></i>
      </button>
    </div>
  </div>
</div>

<!-- Attendance Log -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-list me-2"></i>Attendance Log
    </h5>
    <div>
      <button class="btn btn-success btn-sm" id="export-attendance-btn">
        <i class="fas fa-file-excel me-2"></i>Export Log
      </button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="attendance-table">
        <thead class="table-light">
          <tr>
            <th>Employee</th>
            <th>Date</th>
            <th>Clock In</th>
            <th>Clock Out</th>
            <th>Total Hours</th>
            <th>Break Time</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="attendance-tbody">
          <!-- Attendance records will be loaded here -->
        </tbody>
      </table>
    </div>
    
    <!-- Loading indicator -->
    <div class="text-center py-4" id="attendance-loading" style="display: none;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading attendance records...</p>
    </div>
    
    <!-- No data message -->
    <div class="text-center py-4" id="no-attendance-data" style="display: none;">
      <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
      <p class="text-muted">No attendance records found for the selected criteria.</p>
    </div>
  </div>
</div>

<!-- Attendance Detail Modal -->
<div class="working-modal" id="attendance-detail-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('attendance-detail-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Attendance Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('attendance-detail-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Employee:</th>
                                <td id="detail-employee">-</td>
                            </tr>
                            <tr>
                                <th>Date:</th>
                                <td id="detail-date">-</td>
                            </tr>
                            <tr>
                                <th>Clock In:</th>
                                <td id="detail-clock-in">-</td>
                            </tr>
                            <tr>
                                <th>Clock Out:</th>
                                <td id="detail-clock-out">-</td>
                            </tr>
                            <tr>
                                <th>Total Hours:</th>
                                <td id="detail-total-hours">-</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td id="detail-status">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Timeline</h6>
                        <div id="detail-timeline" class="timeline">
                            <!-- Timeline will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('attendance-detail-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Current attendance state
let currentAttendanceState = {
  clockedIn: false,
  lastClockIn: null,
  lastClockOut: null
};

// Live clock and date
function updateClock() {
  const now = new Date();
  const timeString = now.toLocaleTimeString('en-US', {
    hour12: false,
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
  const dateString = now.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
  
  document.getElementById('live-clock').textContent = timeString;
  document.getElementById('live-date').textContent = dateString;
}

setInterval(updateClock, 1000);
updateClock();

// Initialize attendance system
function initializeAttendance() {
  loadAttendanceStatus();
  loadAttendanceStats();
  loadAttendanceLog();
  testClockifyConnection();
}

// Load current attendance status
function loadAttendanceStatus() {
  fetch('/api/dashboard/attendance-status', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      updateAttendanceUI(data.data);
    }
  })
  .catch(error => {
    console.error('Error loading attendance status:', error);
  });
}

// Update attendance UI based on current status
function updateAttendanceUI(status) {
  const statusBadge = document.getElementById('status-badge');
  const lastActionTime = document.getElementById('last-action-time');
  const clockInBtn = document.getElementById('clock-in-btn');
  const clockOutBtn = document.getElementById('clock-out-btn');
  
  currentAttendanceState.clockedIn = status.clocked_in;
  currentAttendanceState.lastClockIn = status.last_clock_in;
  currentAttendanceState.lastClockOut = status.last_clock_out;
  
  if (status.clocked_in) {
    statusBadge.className = 'badge bg-success fs-6 p-2';
    statusBadge.textContent = 'Clocked In';
    lastActionTime.textContent = `Clocked in at ${status.last_clock_in}`;
    clockInBtn.disabled = true;
    clockOutBtn.disabled = false;
  } else {
    statusBadge.className = 'badge bg-secondary fs-6 p-2';
    statusBadge.textContent = 'Ready to Clock In';
    lastActionTime.textContent = status.last_clock_out ? `Last clocked out at ${status.last_clock_out}` : '';
    clockInBtn.disabled = false;
    clockOutBtn.disabled = true;
  }
}

// Load attendance statistics
function loadAttendanceStats() {
  fetch('/api/dashboard/attendance-stats', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('today-hours').textContent = data.data.today_hours.toFixed(1);
      document.getElementById('week-hours').textContent = data.data.week_hours.toFixed(1);
      document.getElementById('month-hours').textContent = data.data.month_hours.toFixed(1);
      document.getElementById('attendance-rate').textContent = data.data.attendance_rate + '%';
    }
  })
  .catch(error => {
    console.error('Error loading attendance stats:', error);
  });
}

// Load attendance log
function loadAttendanceLog() {
  const employeeId = document.getElementById('attendance-employee').value;
  const period = document.getElementById('attendance-period').value;
  const startDate = document.getElementById('attendance-start-date').value;
  const endDate = document.getElementById('attendance-end-date').value;
  
  const params = new URLSearchParams({
    employee_id: employeeId,
    period: period,
    start_date: startDate,
    end_date: endDate
  });
  
  showAttendanceLoading(true);
  
  fetch(`/api/dashboard/attendance-log?${params}`, {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    showAttendanceLoading(false);
    if (data.success) {
      populateAttendanceTable(data.data);
    } else {
      showNoAttendanceData(true);
    }
  })
  .catch(error => {
    showAttendanceLoading(false);
    console.error('Error loading attendance log:', error);
    showNotification('Error loading attendance log', 'error');
  });
}

// Show/hide loading indicator
function showAttendanceLoading(show) {
  document.getElementById('attendance-loading').style.display = show ? 'block' : 'none';
  document.getElementById('no-attendance-data').style.display = 'none';
}

// Show/hide no data message
function showNoAttendanceData(show) {
  document.getElementById('no-attendance-data').style.display = show ? 'block' : 'none';
  document.getElementById('attendance-loading').style.display = 'none';
}

// Populate attendance table
function populateAttendanceTable(attendanceRecords) {
  const tbody = document.getElementById('attendance-tbody');
  
  if (attendanceRecords.length === 0) {
    showNoAttendanceData(true);
    tbody.innerHTML = '';
    return;
  }
  
  tbody.innerHTML = attendanceRecords.map(record => `
    <tr>
      <td>${record.employee_name}</td>
      <td>${new Date(record.date).toLocaleDateString()}</td>
      <td>${record.clock_in || '--'}</td>
      <td>${record.clock_out || '--'}</td>
      <td>${record.total_hours ? record.total_hours.toFixed(1) + ' hrs' : '--'}</td>
      <td>${record.break_time ? record.break_time + ' min' : '--'}</td>
      <td>
        <span class="badge bg-${getStatusColor(record.status)}">${record.status}</span>
      </td>
      <td>
        <button class="btn btn-sm btn-outline-primary" onclick="viewAttendanceDetail(${record.id})">
          <i class="fas fa-eye"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

// Get status color for badges
function getStatusColor(status) {
  switch (status) {
    case 'present': return 'success';
    case 'late': return 'warning';
    case 'absent': return 'danger';
    case 'partial': return 'info';
    default: return 'secondary';
  }
}

// Clock in functionality
document.getElementById('clock-in-btn').addEventListener('click', function() {
  const button = this;
  button.disabled = true;
  
  fetch('/api/dashboard/clock-in', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message, 'success');
      loadAttendanceStatus();
      loadAttendanceStats();
      loadAttendanceLog();
    } else {
      button.disabled = false;
      showNotification(data.message, 'error');
    }
  })
  .catch(error => {
    button.disabled = false;
    showNotification('Error clocking in. Please try again.', 'error');
    console.error('Clock in error:', error);
  });
});

// Clock out functionality
document.getElementById('clock-out-btn').addEventListener('click', function() {
  const button = this;
  button.disabled = true;
  
  fetch('/api/dashboard/clock-out', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message, 'success');
      loadAttendanceStatus();
      loadAttendanceStats();
      loadAttendanceLog();
    } else {
      button.disabled = false;
      showNotification(data.message, 'error');
    }
  })
  .catch(error => {
    button.disabled = false;
    showNotification('Error clocking out. Please try again.', 'error');
    console.error('Clock out error:', error);
  });
});

// Show custom date range
document.getElementById('attendance-period').addEventListener('change', function() {
  const customRange = document.getElementById('custom-date-range');
  if (this.value === 'custom') {
    customRange.style.display = 'block';
  } else {
    customRange.style.display = 'none';
    loadAttendanceLog();
  }
});

// Filter attendance log
document.getElementById('filter-attendance-btn').addEventListener('click', loadAttendanceLog);
document.getElementById('refresh-attendance-btn').addEventListener('click', function() {
  loadAttendanceStatus();
  loadAttendanceStats();
  loadAttendanceLog();
});

// Employee filter change
document.getElementById('attendance-employee').addEventListener('change', loadAttendanceLog);

// View attendance detail
function viewAttendanceDetail(id) {
  fetch(`/api/dashboard/attendance-detail/${id}`, {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const record = data.data;
      document.getElementById('detail-employee').textContent = record.employee_name;
      document.getElementById('detail-date').textContent = new Date(record.date).toLocaleDateString();
      document.getElementById('detail-clock-in').textContent = record.clock_in || '--';
      document.getElementById('detail-clock-out').textContent = record.clock_out || '--';
      document.getElementById('detail-total-hours').textContent = record.total_hours ? record.total_hours.toFixed(1) + ' hours' : '--';
      document.getElementById('detail-break-time').textContent = record.break_time ? record.break_time + ' minutes' : '--';
      document.getElementById('detail-status').innerHTML = `<span class="badge bg-${getStatusColor(record.status)}">${record.status}</span>`;
      document.getElementById('detail-notes').textContent = record.notes || 'No additional notes';
      
      // Populate timeline if available
      const timeline = document.getElementById('detail-timeline');
      if (record.timeline && record.timeline.length > 0) {
        timeline.innerHTML = record.timeline.map(event => `
          <div class="d-flex align-items-center mb-2">
            <div class="badge bg-primary me-2">${event.time}</div>
            <small>${event.action}</small>
          </div>
        `).join('');
      } else {
        timeline.innerHTML = '<small class="text-muted">No timeline data available</small>';
      }
      
      openWorkingModal('attendance-detail-modal');
    }
  })
  .catch(error => {
    console.error('Error loading attendance detail:', error);
    showNotification('Error loading attendance details', 'error');
  });
}

// Export attendance log
document.getElementById('export-attendance-btn').addEventListener('click', function() {
  const employeeId = document.getElementById('attendance-employee').value;
  const period = document.getElementById('attendance-period').value;
  const startDate = document.getElementById('attendance-start-date').value;
  const endDate = document.getElementById('attendance-end-date').value;
  
  const params = new URLSearchParams({
    employee_id: employeeId,
    period: period,
    start_date: startDate,
    end_date: endDate,
    export: 'csv'
  });
  
  fetch(`/api/dashboard/attendance-export?${params}`, {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const csv = convertAttendanceToCSV(data.data);
      downloadCSV(csv, 'attendance-log.csv');
      showNotification('Attendance log exported successfully!', 'success');
    } else {
      showNotification('Error exporting attendance log', 'error');
    }
  })
  .catch(error => {
    console.error('Error exporting attendance log:', error);
    showNotification('Error exporting attendance log', 'error');
  });
});

// Helper functions
function convertAttendanceToCSV(data) {
  const headers = ['Employee Name', 'Date', 'Clock In', 'Clock Out', 'Total Hours', 'Break Time', 'Status'];
  const csvContent = [
    headers.join(','),
    ...data.map(row => [
      row.employee_name,
      row.date,
      row.clock_in || '',
      row.clock_out || '',
      row.total_hours || '',
      row.break_time || '',
      row.status
    ].join(','))
  ].join('\n');
  
  return csvContent;
}

function downloadCSV(csv, filename) {
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.setAttribute('hidden', '');
  a.setAttribute('href', url);
  a.setAttribute('download', filename);
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}

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

// Auto-refresh every 30 seconds
setInterval(() => {
  loadAttendanceStatus();
  loadAttendanceStats();
}, 30000);

// Clockify Integration Functions
function testClockifyConnection() {
  fetch('/api/clockify/test-connection', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    const statusBadge = document.getElementById('connection-status');
    if (data.success) {
      statusBadge.className = 'badge bg-success';
      statusBadge.textContent = 'Clockify Connected';
      document.getElementById('clockify-clock-in-btn').disabled = false;
    } else {
      statusBadge.className = 'badge bg-danger';
      statusBadge.textContent = 'Clockify Disconnected';
      document.getElementById('clockify-clock-in-btn').disabled = true;
    }
  })
  .catch(error => {
    console.error('Error testing Clockify connection:', error);
    const statusBadge = document.getElementById('connection-status');
    statusBadge.className = 'badge bg-danger';
    statusBadge.textContent = 'Connection Error';
  });
}

// Test Clockify connection button
document.getElementById('test-clockify-btn').addEventListener('click', testClockifyConnection);

// Clockify Clock In
document.getElementById('clockify-clock-in-btn').addEventListener('click', function() {
  const button = this;
  button.disabled = true;
  
  fetch('/api/clockify/timer/start', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      description: 'Work session - HR3 System'
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Clockify timer started successfully!', 'success');
      button.disabled = true;
      document.getElementById('clockify-clock-out-btn').disabled = false;
      document.getElementById('clockify-timer-status').textContent = 'Timer running...';
      
      // Also trigger local clock in
      fetch('/api/dashboard/clock-in', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(localData => {
        if (localData.success) {
          loadAttendanceStatus();
          loadAttendanceStats();
          loadAttendanceLog();
        }
      });
    } else {
      button.disabled = false;
      showNotification(data.message || 'Failed to start Clockify timer', 'error');
    }
  })
  .catch(error => {
    button.disabled = false;
    showNotification('Error starting Clockify timer', 'error');
    console.error('Clockify clock in error:', error);
  });
});

// Clockify Clock Out
document.getElementById('clockify-clock-out-btn').addEventListener('click', function() {
  const button = this;
  button.disabled = true;
  
  fetch('/api/clockify/timer/stop', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Clockify timer stopped successfully!', 'success');
      button.disabled = true;
      document.getElementById('clockify-clock-in-btn').disabled = false;
      document.getElementById('clockify-timer-status').textContent = 'Timer stopped';
      
      // Also trigger local clock out
      fetch('/api/dashboard/clock-out', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(localData => {
        if (localData.success) {
          loadAttendanceStatus();
          loadAttendanceStats();
          loadAttendanceLog();
        }
      });
    } else {
      button.disabled = false;
      showNotification(data.message || 'Failed to stop Clockify timer', 'error');
    }
  })
  .catch(error => {
    button.disabled = false;
    showNotification('Error stopping Clockify timer', 'error');
    console.error('Clockify clock out error:', error);
  });
});

// Load Clockify time entries and sync with local data
function loadClockifyTimeEntries() {
  const today = new Date().toISOString().split('T')[0];
  
  fetch(`/api/clockify/time-entries?start_date=${today}&end_date=${today}`, {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success && data.data.length > 0) {
      // Update today's hours from Clockify data
      let totalHours = 0;
      data.data.forEach(entry => {
        if (entry.timeInterval && entry.timeInterval.duration) {
          // Parse duration (PT format) to hours
          const duration = entry.timeInterval.duration;
          const hours = parseDuration(duration);
          totalHours += hours;
        }
      });
      
      document.getElementById('today-hours').textContent = totalHours.toFixed(1);
    }
  })
  .catch(error => {
    console.error('Error loading Clockify time entries:', error);
  });
}

// Parse ISO 8601 duration to hours
function parseDuration(duration) {
  if (!duration) return 0;
  
  const match = duration.match(/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/);
  if (!match) return 0;
  
  const hours = parseInt(match[1] || 0);
  const minutes = parseInt(match[2] || 0);
  const seconds = parseInt(match[3] || 0);
  
  return hours + (minutes / 60) + (seconds / 3600);
}

// Auto-refresh Clockify data every 30 seconds
setInterval(() => {
  testClockifyConnection();
  loadClockifyTimeEntries();
}, 30000);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  initializeAttendance();
  loadClockifyTimeEntries();
});
</script>
@endpush
<!-- Working Modal CSS and JavaScript -->
<style>
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

<script>
function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    }
}

function closeWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Reset form
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const visibleModals = document.querySelectorAll('.working-modal[style*="block"]');
        visibleModals.forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = '';
    }
});
</script>

@endsection
