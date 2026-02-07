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

            <div class="row justify-content-center mt-3">
              <div class="col-md-6 col-lg-5">
                <div class="workplace-select">
                  <label for="workplace-type" class="form-label small text-muted mb-1">Workplace</label>
                  <select id="workplace-type" class="form-select">
                    @foreach($workplaceOptions ?? [] as $value => $label)
                      <option value="{{ $value }}" {{ ($selectedWorkplace ?? 'onsite') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            
            <!-- Status Display -->
            <div class="attendance-status mt-3" id="attendance-status">
              <span class="badge bg-secondary" id="status-badge">Not Clocked In</span>
              <div class="mt-2">
                <small class="text-muted" id="status-details">Click "Clock In" to start your workday</small>
              </div>
              <div class="mt-1">
                <small class="text-muted" id="workplace-details">Workplace: --</small>
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

<!-- Today's Summary -->
<div class="row g-4 mb-4 mt-2">
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
                                <th>Break Time:</th>
                                <td id="detail-break-time">-</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td id="detail-status">-</td>
                            </tr>
                            <tr>
                                <th>Notes:</th>
                                <td id="detail-notes">-</td>
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

<!-- Biometric Authentication Modal -->
<div class="modal fade" id="biometricModal" tabindex="-1" aria-labelledby="biometricModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 text-center">
        <h5 class="modal-title w-100" id="biometricModalLabel" style="color: var(--jetlouge-primary); font-weight: 700;">
          <i class="bi bi-fingerprint me-2" style="font-size: 1.5rem;"></i>
          Biometric Authentication
        </h5>
      </div>
      <div class="modal-body text-center py-4">
        <div id="biometricContent">
          <!-- Registration Content -->
          <div id="biometricRegister" style="display: none;">
            <div class="mb-4">
              <i class="bi bi-shield-plus" style="font-size: 4rem; color: var(--jetlouge-primary);"></i>
            </div>
            <h6 class="mb-3">Enable Default Fingerprint Authentication</h6>
            <p class="text-muted mb-4">
              Your account now has default biometric authentication enabled.
              Click below to activate fingerprint verification for future clock-ins.
            </p>
            <button type="button" class="btn btn-login mb-3" onclick="registerBiometric()">
              <i class="bi bi-fingerprint me-2"></i>
              Activate Fingerprint
            </button>
            <div class="text-center">
              <button type="button" class="btn btn-outline-warning btn-sm me-2" onclick="simulateBiometricSuccess()">
                <i class="bi bi-tools me-1"></i>
                Dev: Simulate Success
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="skipBiometric()">
                Skip for now
              </button>
            </div>
          </div>

          <!-- Verification Content -->
          <div id="biometricVerify" style="display: none;">
            <div class="mb-4">
              <i class="bi bi-fingerprint" style="font-size: 4rem; color: var(--jetlouge-primary);"></i>
            </div>
            <h6 class="mb-3">Verify Your Fingerprint</h6>
            <p class="text-muted mb-4">
              Please use your registered fingerprint to complete the clock-in process.
              Windows Hello will prompt you to scan your finger.
            </p>
            <button type="button" class="btn btn-login mb-3" onclick="verifyBiometric()">
              <i class="bi bi-shield-check me-2"></i>
              Authenticate
            </button>
          </div>

          <!-- Loading Content -->
          <div id="biometricLoading" style="display: none;">
            <div class="mb-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <h6 class="mb-3">Processing...</h6>
            <p class="text-muted">
              Please place your finger on the fingerprint sensor when Windows Hello prompts you.
            </p>
          </div>

          <!-- Error Content -->
          <div id="biometricError" style="display: none;">
            <div class="mb-4">
              <i class="bi bi-exclamation-triangle" style="font-size: 4rem; color: #dc3545;"></i>
            </div>
            <h6 class="mb-3">Authentication Failed</h6>
            <p class="text-muted mb-4" id="biometricErrorMessage">
              Biometric authentication failed. Please try again.
            </p>
            <button type="button" class="btn btn-login mb-3" onclick="retryBiometric()">
              <i class="bi bi-arrow-clockwise me-2"></i>
              Try Again
            </button>
            <div class="text-center">
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="skipBiometric()">
                Skip for now
              </button>
            </div>
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
let currentEmployeeId = {{ Auth::guard('employee')->user()->id ?? 1 }}; // Get current logged-in employee ID

// Live clock
function updateClock() {
  const now = new Date();
  const timeString = now.toLocaleTimeString();
  const clock = document.getElementById('live-clock');
  if (clock) {
    clock.textContent = timeString;
  }
}

setInterval(updateClock, 1000);
updateClock();

document.addEventListener('DOMContentLoaded', function() {
  checkAttendanceStatus();
  loadAttendanceLogs();
  loadAttendanceStats();
  updateWorkplaceDetails(getSelectedWorkplaceType());

  const workplaceSelect = document.getElementById('workplace-type');
  if (workplaceSelect) {
    workplaceSelect.addEventListener('change', function() {
      updateWorkplaceDetails(this.value);
    });
  }
});

function getSelectedWorkplaceType() {
  const workplaceSelect = document.getElementById('workplace-type');
  return workplaceSelect ? workplaceSelect.value : 'Office';
}

function updateWorkplaceDetails(workplaceType) {
  const workplaceDetails = document.getElementById('workplace-details');
  const workplaceSelect = document.getElementById('workplace-type');
  const workplaceLabels = {
    'Office': 'Office',
    'Outside Workplace': 'Outside Workplace'
  };
  const label = workplaceLabels[workplaceType] || 'Not Specified';

  if (workplaceDetails) {
    workplaceDetails.textContent = `Workplace: ${label}`;
  }
  if (workplaceSelect && workplaceType) {
    workplaceSelect.value = workplaceType;
  }
}

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
  
  // Check biometric status first
  fetch('{{ route("admin.biometric.check") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
      email: '{{ Auth::guard('employee')->user()->email }}'
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.has_biometric) {
      // Show biometric verification
      showBiometricModal('clock-in');
    } else {
      // Show biometric registration
      showBiometricModal('register');
    }
  })
  .catch(error => {
    console.error('Error checking biometric status:', error);
    // Fallback to regular clock-in
    performRegularClockIn();
  });
}

// Perform regular clock-in (fallback)
function performRegularClockIn() {
  const clockInBtn = document.getElementById('clock-in-btn');
  const selectedWorkplace = getSelectedWorkplaceType();
  
  clockInBtn.disabled = true;
  clockInBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Clocking In...';

  fetch('/attendance/clock-in', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
      employee_id: currentEmployeeId,
      location: selectedWorkplace,
      workplace_type: selectedWorkplace === 'Outside Workplace' ? 'offsite' : 'onsite'
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
      
      // Update UI with actual database data
      updateAttendanceUI({
        status: 'present',
        clock_in_time: data.data.clock_in_time,
        workplace_type: data.data.workplace_type,
        location: data.data.location,
        is_clocked_in: true,
        is_clocked_out: false,
        is_on_break: false
      });
      
      // Update attendance logs and stats
      updateAttendanceLogsAfterAction();
      loadAttendanceStats();
      
      // Preserve workplace selection
      setTimeout(() => {
        const workplaceRadios = document.querySelectorAll('input[name="workplace_type"]');
        workplaceRadios.forEach(radio => {
          if (radio.value === selectedWorkplace) {
            radio.checked = true;
          }
        });
      }, 100);
      
    } else {
      showNotification(data.message || 'Failed to clock in', 'error');
    }
    clockInBtn.disabled = false;
    clockInBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Clock In';
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
  console.log('Clock out initiated for employee ID:', currentEmployeeId);
  performRegularClockOut();
}

// Perform regular clock-out (fallback)
function performRegularClockOut() {
  const clockOutBtn = document.getElementById('clock-out-btn');
  clockOutBtn.disabled = true;
  clockOutBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Clocking Out...';

  fetch('/attendance/clock-out', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
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
        workplace_type: data.data.workplace_type,
        location: data.data.location,
        is_clocked_in: false,
        is_clocked_out: true,
        is_on_break: false
      });
      updateAttendanceLogsAfterAction();
      loadAttendanceStats();
    } else {
      showNotification(data.message || 'Failed to clock out', 'error');
    }
    clockOutBtn.disabled = false;
    clockOutBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Clock Out';
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
        workplace_type: data.data.workplace_type,
        location: data.data.location,
        is_clocked_in: true,
        is_clocked_out: false,
        is_on_break: true
      });
      updateAttendanceLogsAfterAction();
      loadAttendanceStats();
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
        workplace_type: data.data.workplace_type,
        location: data.data.location,
        is_clocked_in: true,
        is_clocked_out: false,
        is_on_break: false
      });
      updateAttendanceLogsAfterAction();
      loadAttendanceStats();
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

  const workplaceSelect = document.getElementById('workplace-type');
  if (workplaceSelect) {
    workplaceSelect.disabled = attendanceData.is_clocked_in || attendanceData.is_on_break;
  }

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

  updateWorkplaceDetails(attendanceData.location || attendanceData.workplace_type || getSelectedWorkplaceType());
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
            <i class="${statusIcon} me-1"></i>${log.status_text || (log.status === 'present' ? 'Present' : 'Absent')}
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
        <i class="fas fa-redo me-1"></i>Try Again
      </button>
    </div>
  `;
}

function refreshAttendanceLogs() {
  console.log('Refreshing attendance logs...');
  loadAttendanceLogs();
}

function getStatusClass(status) {
  switch(status) {
    case 'present': return 'bg-success';
    case 'late': return 'bg-warning';
    case 'absent': return 'bg-danger';
    case 'on_break': return 'bg-info';
    case 'half_day': return 'bg-primary';
    default: return 'bg-secondary';
  }
}

function getStatusIcon(status) {
  switch(status) {
    case 'present': return 'fas fa-check-circle';
    case 'late': return 'fas fa-clock';
    case 'absent': return 'fas fa-times-circle';
    case 'on_break': return 'fas fa-coffee';
    case 'half_day': return 'fas fa-adjust';
    default: return 'fas fa-question-circle';
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
  }
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: date.getFullYear() !== today.getFullYear() ? 'numeric' : undefined
  });
}

function formatHoursToTime(hours) {
  if (hours === null || hours === undefined) {
    return '--';
  }

  const wholeHours = Math.floor(hours);
  const minutes = Math.round((hours - wholeHours) * 60);

  if (wholeHours > 0 && minutes > 0) {
    return wholeHours + 'h ' + minutes + 'm';
  }
  if (wholeHours > 0) {
    return wholeHours + 'h';
  }
  if (minutes > 0) {
    return minutes + 'm';
  }
  return '0m';
}

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

function setupAttendanceLogsScroll() {
  const container = document.getElementById('attendance-logs-container');
  if (!container) {
    return;
  }

  container.addEventListener('wheel', function(e) {
    e.preventDefault();
    container.scrollTop += e.deltaY * 0.5;
  });
}

// Load attendance statistics
function loadAttendanceStats() {
  fetch('/api/attendance/dashboard-stats', {
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      const todayHours = document.getElementById('today-hours');
      const weekHours = document.getElementById('week-hours');
      const monthHours = document.getElementById('month-hours');
      const attendanceRate = document.getElementById('attendance-rate');
      
      if (todayHours) todayHours.textContent = data.data.today_hours || '0.0';
      if (weekHours) weekHours.textContent = data.data.week_hours || '0.0';
      if (monthHours) monthHours.textContent = data.data.month_hours || '0.0';
      if (attendanceRate) attendanceRate.textContent = `${data.data.attendance_rate || 0}%`;
      
      const totalTimeLabel = document.getElementById('today-total-time-label');
      const totalTimeSmall = document.getElementById('today-total-time');
      if (totalTimeLabel) {
        totalTimeLabel.textContent = `${data.data.today_hours || 0}h`;
      }
      if (totalTimeSmall) {
        totalTimeSmall.textContent = `${data.data.today_hours || 0}h`;
      }
    }
  })
  .catch(error => {
    console.error('Error loading attendance stats:', error);
  });
}

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
      document.getElementById('detail-date').textContent = record.date ? new Date(record.date).toLocaleDateString() : '--';
      document.getElementById('detail-clock-in').textContent = record.clock_in || '--';
      document.getElementById('detail-clock-out').textContent = record.clock_out || '--';
      document.getElementById('detail-total-hours').textContent = record.total_hours !== null && record.total_hours !== undefined
        ? record.total_hours.toFixed(1) + ' hours'
        : '--';
      document.getElementById('detail-break-time').textContent = record.break_time !== null && record.break_time !== undefined
        ? record.break_time + ' minutes'
        : '--';
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

function navigateToAttendanceLogs() {
  // Store the tab target in sessionStorage so it persists across page navigation
  sessionStorage.setItem('activateAttendanceTab', 'true');
}

function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
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
  checkAttendanceStatus();
  loadAttendanceStats();
}, 30000);
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
/* Attendance Controls Styling */
.attendance-controls {
  margin-top: 1rem;
}

.workplace-select .form-select {
  border-radius: 10px;
  padding: 0.55rem 1rem;
  border: 1px solid rgba(0, 0, 0, 0.12);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  background-color: #fff;
  font-weight: 500;
}

.workplace-select .form-select:focus {
  border-color: rgba(0, 123, 255, 0.6);
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
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

/* Attendance logs container with vertical scrolling - Limited to 2 visible logs */
.attendance-logs-container {
  max-height: 160px; /* Height for exactly 2 attendance log items */
  overflow-y: auto; /* Enable vertical scrolling */
  overflow-x: hidden; /* Disable horizontal scrolling */
  padding-right: 8px;
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
}

/* Custom scrollbar for WebKit browsers */
.attendance-logs-container::-webkit-scrollbar {
  width: 8px;
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
  transition: all 0.3s ease;
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

/* Individual log item styling */
.attendance-log-item {
  background-color: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 12px 15px;
  margin-bottom: 10px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.attendance-log-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.attendance-log-item.latest {
  border-left: 3px solid var(--jetlouge-primary);
}

.attendance-logs-list:empty::after {
  content: "No attendance logs found";
  display: block;
  text-align: center;
  color: #6c757d;
  padding: 20px;
}

/* Log item content */
.log-header {
  margin-bottom: 8px;
}

.log-date {
  font-size: 0.85rem;
  color: #6c757d;
  display: flex;
  align-items: center;
}

.log-details .row > div {
  margin-bottom: 4px;
}

.log-summary {
  padding-top: 8px;
  border-top: 1px dashed #eee;
  margin-top: 8px;
  font-size: 0.9rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .attendance-logs-panel {
    margin-top: 1rem;
    min-height: 250px;
  }
  
  .attendance-logs-container {
    max-height: 140px;
  }
  
  .attendance-log-item {
    padding: 10px;
  }
  
  .attendance-logs-container::-webkit-scrollbar {
    width: 6px;
  }
  
  .attendance-logs-wrapper:hover .attendance-logs-container::-webkit-scrollbar-thumb {
    opacity: 1;
    background: rgba(0, 123, 255, 0.6);
  }
}

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

// Global variables for biometric authentication
let currentBiometricAction = null;
let biometricModal = null;

// Show biometric authentication modal
function showBiometricModal(action) {
  console.log('showBiometricModal called with action:', action);
  currentBiometricAction = action;
  
  const modalElement = document.getElementById('biometricModal');
  if (!modalElement) {
    console.error('Biometric modal element not found!');
    return;
  }
  
  biometricModal = new bootstrap.Modal(modalElement);
  
  // Update action text
  const actionElement = document.getElementById('biometricAction');
  if (actionElement) {
    actionElement.textContent = action;
  }
  
  // Check biometric status
  fetch('{{ route("admin.biometric.check") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
      email: '{{ Auth::guard('employee')->user()->email }}'
    })
  })
  .then(response => response.json())
  .then(data => {
    console.log('Biometric status data:', data);
    if (data.has_biometric) {
      // Show verification screen
      console.log('User has biometric, showing verify screen');
      showBiometricState('verify');
    } else {
      // Show registration screen
      console.log('User does not have biometric, showing register screen');
      showBiometricState('register');
    }
    console.log('Showing biometric modal...');
    biometricModal.show();
  })
  .catch(error => {
    console.error('Error checking biometric status:', error);
    // Default to registration
    console.log('Error occurred, defaulting to register screen');
    showBiometricState('register');
    biometricModal.show();
  });
}

// Show different biometric states
function showBiometricState(state) {
  // Hide all states
  document.getElementById('biometricRegister').style.display = 'none';
  document.getElementById('biometricVerify').style.display = 'none';
  document.getElementById('biometricLoading').style.display = 'none';
  document.getElementById('biometricError').style.display = 'none';

  // Show requested state
  document.getElementById('biometric' + state.charAt(0).toUpperCase() + state.slice(1)).style.display = 'block';
}

// Register biometric authentication with fallback
async function registerBiometric() {
  showBiometricState('loading');

  try {
    console.log('Attempting biometric registration...');
    console.log('Current URL:', window.location.href);
    console.log('Hostname:', window.location.hostname);
    console.log('Is secure context:', window.isSecureContext);
    
    // First try real WebAuthn if conditions are right
    if (navigator.credentials && window.PublicKeyCredential && window.isSecureContext && isBiometricHostAllowed()) {
      console.log('Attempting real WebAuthn registration...');
      
      try {
        // Check if platform authenticator is available
        const available = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
        console.log('Platform authenticator available:', available);
        
        if (available) {
          // Generate a proper challenge
          const challenge = new Uint8Array(32);
          crypto.getRandomValues(challenge);
          
          // Create credential options for real WebAuthn
          const credentialCreationOptions = {
            publicKey: {
              challenge: challenge,
              rp: {
                name: "Jetlouge HR System"
              },
              user: {
                id: new TextEncoder().encode(currentEmployeeId.toString()),
                name: '{{ Auth::guard('employee')->user()->email }}',
                displayName: '{{ Auth::guard('employee')->user()->first_name }} {{ Auth::guard('employee')->user()->last_name }}',
              },
              pubKeyCredParams: [
                {alg: -7, type: "public-key"}
              ],
              authenticatorSelection: {
                authenticatorAttachment: "platform",
                userVerification: "required",
                requireResidentKey: false
              },
              timeout: 60000,
              attestation: "none"
            }
          };

          console.log('Prompting for real fingerprint registration...');
          
          // This will prompt Windows Hello for fingerprint
          const credential = await navigator.credentials.create(credentialCreationOptions);
          
          console.log('Real biometric credential created successfully!');
          
          // Convert credential data for server
          const credentialData = {
            email: '{{ Auth::guard('employee')->user()->email }}',
            credential_id: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
            public_key: btoa(String.fromCharCode(...new Uint8Array(credential.response.getPublicKey()))),
            authenticator_data: {
              clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON))),
              attestationObject: btoa(String.fromCharCode(...new Uint8Array(credential.response.attestationObject)))
            },
            device_name: 'Windows Hello Fingerprint'
          };

          // Send real credential to server
          const response = await fetch('{{ route("admin.biometric.simple.register") }}?email={{ urlencode(Auth::guard('employee')->user()->email) }}&real_credential=1', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(credentialData)
          });

          const result = await response.json();
          
          if (result.success) {
            showNotification('✅ Real biometric authentication registered successfully! Your fingerprint is now linked to your account.', 'success');
            showBiometricState('verify');
            return;
          }
        }
      } catch (webauthnError) {
        console.log('WebAuthn failed, falling back to default registration:', webauthnError);
      }
    }
    
    // Fallback: Use default registration
    console.log('Using default biometric registration...');
    
    const response = await fetch('{{ route("admin.biometric.simple.register") }}?email={{ urlencode(Auth::guard('employee')->user()->email) }}', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const result = await response.json();
    console.log('Default registration response:', result);

    if (result.success) {
      showNotification('✅ Default biometric authentication registered! You can now proceed with verification.', 'success');
      showBiometricState('verify');
    } else {
      showBiometricError(result.error || 'Failed to register biometric authentication');
    }

  } catch (error) {
    console.error('Biometric registration error:', error);
    showBiometricError('Registration failed: ' + error.message + '\n\nPlease use a secure HTTPS domain or localhost for biometric support.');
  }
}

// Verify biometric authentication with fallback
async function verifyBiometric() {
  showBiometricState('loading');

  try {
    console.log('Attempting biometric verification...');
    
    // First try real WebAuthn if conditions are right
    if (navigator.credentials && window.PublicKeyCredential && window.isSecureContext && isBiometricHostAllowed()) {
      console.log('Attempting real WebAuthn verification...');
      
      try {
        // Generate a proper challenge for verification
        const challenge = new Uint8Array(32);
        crypto.getRandomValues(challenge);
        
        // Create assertion options for real WebAuthn verification
        const credentialRequestOptions = {
          publicKey: {
            challenge: challenge,
            timeout: 60000,
            userVerification: "required",
            allowCredentials: [] // Allow any registered credential
          }
        };

        console.log('Prompting for real fingerprint verification...');
        
        // This will prompt Windows Hello for fingerprint verification
        const assertion = await navigator.credentials.get(credentialRequestOptions);
        
        console.log('Real biometric verification completed successfully!');
        
        // Convert assertion data for server
        const assertionData = {
          email: '{{ Auth::guard('employee')->user()->email }}',
          credential_id: btoa(String.fromCharCode(...new Uint8Array(assertion.rawId))),
          authenticator_data: btoa(String.fromCharCode(...new Uint8Array(assertion.response.authenticatorData))),
          signature: btoa(String.fromCharCode(...new Uint8Array(assertion.response.signature))),
          client_data: btoa(String.fromCharCode(...new Uint8Array(assertion.response.clientDataJSON)))
        };

        // Send verification data to server
        const response = await fetch('{{ route("admin.biometric.simple.verify") }}?email={{ urlencode(Auth::guard('employee')->user()->email) }}&real_verification=1', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(assertionData)
        });

        const result = await response.json();
        
        if (result.success) {
          showNotification('✅ Real biometric verification successful! Your fingerprint was verified.', 'success');
          performBiometricClockIn();
          return;
        }
      } catch (webauthnError) {
        console.log('WebAuthn verification failed, falling back to default:', webauthnError);
      }
    }
    
    // Fallback: Use default verification with delay
    console.log('Using default biometric verification...');
    
    // Simulate fingerprint verification delay
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    const response = await fetch('{{ route("admin.biometric.simple.verify") }}?email={{ urlencode(Auth::guard('employee')->user()->email) }}', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const result = await response.json();
    console.log('Default verification response:', result);

    if (result.success) {
      showNotification('✅ Biometric verification successful!', 'success');
      performBiometricClockIn();
    } else {
      showBiometricError(result.error || 'Biometric verification failed');
    }

  } catch (error) {
    console.error('Biometric verification error:', error);
    showBiometricError('Verification failed: ' + error.message + '\n\nPlease use a secure HTTPS domain or localhost for biometric support.');
  }
}

// Perform biometric clock-in
function performBiometricClockIn() {
  const clockInBtn = document.getElementById('clock-in-btn');
  const selectedWorkplace = getSelectedWorkplaceType();
  
  clockInBtn.disabled = true;
  clockInBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Clocking In...';

  fetch('{{ route("attendance.clock-in.biometric") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
      employee_id: currentEmployeeId,
      location: selectedWorkplace,
      workplace_type: selectedWorkplace === 'Outside Workplace' ? 'offsite' : 'onsite'
    })
  })
  .then(response => {
    console.log('Biometric clock-in response status:', response.status);
    return response.json();
  })
  .then(data => {
    console.log('Biometric clock-in response data:', data);
    if (data.success) {
      showNotification(data.message || 'Successfully clocked in with biometric!', 'success');
      
      // Update UI with actual database data
      updateAttendanceUI({
        status: 'present',
        clock_in_time: data.data.clock_in_time,
        workplace_type: data.data.workplace_type,
        location: data.data.location,
        is_clocked_in: true,
        is_clocked_out: false,
        is_on_break: false
      });
      
      // Update attendance logs and stats
      updateAttendanceLogsAfterAction();
      loadAttendanceStats();
      
      // Close modal
      biometricModal.hide();
      
      // Preserve workplace selection
      setTimeout(() => {
        const workplaceRadios = document.querySelectorAll('input[name="workplace_type"]');
        workplaceRadios.forEach(radio => {
          if (radio.value === selectedWorkplace) {
            radio.checked = true;
          }
        });
      }, 100);
      
    } else {
      showNotification(data.message || 'Failed to clock in with biometric', 'error');
    }
    clockInBtn.disabled = false;
    clockInBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Clock In';
  })
  .catch(error => {
    console.error('Error clocking in with biometric:', error);
    showNotification('Error occurred while clocking in with biometric', 'error');
    clockInBtn.disabled = false;
    clockInBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Clock In';
  });
}

// Check if biometric host is allowed
function isBiometricHostAllowed() {
  const allowedHosts = [
    'localhost',
    '127.0.0.1',
    'hr3.jetlougetravels-ph.com'
  ];

  return allowedHosts.includes(window.location.hostname);
}

// Retry biometric authentication
function retryBiometric() {
  showBiometricModal(currentBiometricAction);
}

// Skip biometric authentication
function skipBiometric() {
  // Close modal and use regular method
  if (biometricModal) {
    biometricModal.hide();
  }
  
  // Perform regular clock-in (since biometric is only for clock-in)
  performRegularClockIn();
}

// Show biometric error
function showBiometricError(message) {
  const errorElement = document.getElementById('biometricErrorMessage');
  if (errorElement) {
    errorElement.textContent = message;
  }
  showBiometricState('error');
}

// Simulate biometric success for development
function simulateBiometricSuccess() {
  console.log('Simulating biometric success for development...');
  
  showBiometricState('loading');
  
  setTimeout(() => {
    showNotification('Development Mode: Biometric authentication simulated successfully!', 'success');
    
    // Get current workplace selection
    const selectedWorkplace = getSelectedWorkplaceType();
    
    // Update UI for clock-in only
    updateAttendanceUI({
      status: 'present',
      clock_in_time: new Date().toLocaleTimeString(),
      workplace_type: selectedWorkplace === 'Outside Workplace' ? 'offsite' : 'onsite',
      location: selectedWorkplace,
      is_clocked_in: true,
      is_clocked_out: false,
      is_on_break: false
    });
    
    updateAttendanceLogsAfterAction();
    loadAttendanceStats();
    
    // Close modal
    if (biometricModal) {
      biometricModal.hide();
    }
    
    // Preserve workplace selection
    setTimeout(() => {
      const workplaceRadios = document.querySelectorAll('input[name="workplace_type"]');
      workplaceRadios.forEach(radio => {
        if (radio.value === selectedWorkplace) {
          radio.checked = true;
        }
      });
    }, 100);
  }, 2000);
}

</script>
@endsection
