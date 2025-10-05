@extends('layouts.hr')

@section('title', 'Attendance Management - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Attendance Management</h2>
        <p class="text-muted mb-0">Track employee clock-in/clock-out and attendance records</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Attendance Management</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Alert Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="fas fa-exclamation-triangle me-2"></i>
  <strong>Validation Errors:</strong>
  <ul class="mb-0 mt-2">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Attendance Statistics -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-users text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $attendanceStats['total_records'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Records</p>
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
          <h3 class="fw-bold mb-0 stat-number">{{ $attendanceStats['present_today'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Present Today</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-warning">
          <i class="fas fa-coffee text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $attendanceStats['on_break'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">On Break</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-clock text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ number_format($attendanceStats['total_hours_today'] ?? 0, 1) }}h</h3>
          <p class="text-muted mb-0 small stat-label">Hours Today</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="card shadow-sm border-0">
  <div class="card-header bg-white border-bottom-0 py-3">
    <ul class="nav nav-tabs card-header-tabs" id="modulesTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">
          <i class="fas fa-clock me-2"></i>Attendance Records
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="clock-system-tab" data-bs-toggle="tab" data-bs-target="#clock-system" type="button" role="tab">
          <i class="fas fa-stopwatch me-2"></i>Clock In/Out System
        </button>
      </li>
    </ul>
  </div>

  <div class="card-body p-0">
    <div class="tab-content" id="modulesTabContent">
      
      <!-- Attendance Records Tab -->
      <div class="tab-pane fade show active" id="attendance" role="tabpanel">
        <div class="p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Attendance Records</h5>
            <button type="button" class="btn btn-primary btn-sm" onclick="openWorkingModal('attendance-modal')">
              <i class="fas fa-plus me-1"></i>Add Manual Entry
            </button>
          </div>

          <!-- Attendance Table -->
          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr>
                  <th>ID</th>
                  <th>Employee</th>
                  <th>Date</th>
                  <th>Clock In</th>
                  <th>Clock Out</th>
                  <th>Total Hours</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($attendances ?? [] as $attendance)
                  <tr>
                    <td>#{{ str_pad($attendance->id ?? 0, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $attendance->employee_name ?? 'Unknown Employee' }}</td>
                    <td>{{ isset($attendance->date) ? \Carbon\Carbon::parse($attendance->date)->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $attendance->formatted_clock_in ?? 'N/A' }}</td>
                    <td>{{ $attendance->formatted_clock_out ?? 'N/A' }}</td>
                    <td>{{ number_format($attendance->total_hours ?? 0, 2) }}h</td>
                    <td>
                      @php
                        $statusClass = match($attendance->status ?? 'unknown') {
                          'present' => 'success',
                          'absent' => 'danger',
                          'late' => 'warning',
                          'on_break' => 'info',
                          'clocked_out' => 'secondary',
                          default => 'light text-dark'
                        };
                      @endphp
                      <span class="badge bg-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $attendance->status ?? 'Unknown')) }}</span>
                    </td>
                    <td>
                      <button type="button" class="btn btn-outline-info btn-sm" onclick="viewAttendanceDetails({{ $attendance->id ?? 0 }})" title="View Details">
                        <i class="fas fa-eye"></i>
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                      <i class="fas fa-clock fa-3x mb-3 text-muted"></i><br>
                      No attendance records found. <a href="#" onclick="openWorkingModal('attendance-modal')" class="text-primary">Add your first record</a>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Clock In/Out System Tab -->
      <div class="tab-pane fade" id="clock-system" role="tabpanel">
        <div class="p-4">
          <div class="row">
            <!-- Current Time Display -->
            <div class="col-md-6">
              <div class="card bg-primary text-white text-center">
                <div class="card-body">
                  <h2 class="display-4 mb-0" id="current-time">{{ now()->format('H:i:s') }}</h2>
                  <p class="mb-0">{{ now()->format('l, F j, Y') }}</p>
                </div>
              </div>
            </div>

            <!-- Clock Actions -->
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Quick Clock Actions</h5>
                  
                  <div class="mb-3">
                    <label for="employee-select" class="form-label">Select Employee</label>
                    <select class="form-select" id="employee-select">
                      <option value="">Choose employee...</option>
                      @forelse($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                      @empty
                      @endforelse
                    </select>
                  </div>

                  <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" onclick="clockInEmployee()">
                      <i class="fas fa-sign-in-alt me-2"></i>Clock In
                    </button>
                    <button type="button" class="btn btn-danger" onclick="clockOutEmployee()">
                      <i class="fas fa-sign-out-alt me-2"></i>Clock Out
                    </button>
                    <button type="button" class="btn btn-warning" onclick="startBreak()">
                      <i class="fas fa-coffee me-2"></i>Start Break
                    </button>
                    <button type="button" class="btn btn-info" onclick="endBreak()">
                      <i class="fas fa-play me-2"></i>End Break
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Manual Attendance Entry Modal -->
<div class="working-modal" id="attendance-modal" style="display: none;">
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Add Manual Attendance Entry</h5>
                <button type="button" class="btn-close" onclick="closeWorkingModal('attendance-modal')"></button>
            </div>
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee *</label>
                                <select class="form-select" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @forelse($employees ?? [] as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="clock_in_time" class="form-label">Clock In Time *</label>
                                <input type="time" class="form-control" id="clock_in_time" name="clock_in_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="clock_out_time" class="form-label">Clock Out Time</label>
                                <input type="time" class="form-control" id="clock_out_time" name="clock_out_time">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="Office" placeholder="Work location">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="present">Present</option>
                                    <option value="late">Late</option>
                                    <option value="absent">Absent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Additional notes"></textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('attendance-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.working-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    overflow-y: auto;
}

.working-modal-dialog {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 1rem;
}

.working-modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
}

.working-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.working-modal-body {
    padding: 1.5rem;
}

.working-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.working-modal input,
.working-modal select,
.working-modal textarea,
.working-modal button {
    pointer-events: auto !important;
}

.stat-card-modern {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
    transition: transform 0.2s ease;
}

.stat-card-modern:hover {
    transform: translateY(-2px);
}

.stat-icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-number {
    font-size: 2rem;
    color: #2c3e50;
}

.display-4 {
    font-family: 'Courier New', monospace;
    font-weight: bold;
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
});

function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { 
        hour12: false, 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    });
    
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

function clockInEmployee() {
    const employeeId = document.getElementById('employee-select').value;
    
    if (!employeeId) {
        alert('Please select an employee first.');
        return;
    }
    
    const data = {
        employee_id: employeeId,
        location: 'Office'
    };
    
    fetch('/api/attendance/clock-in', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Employee clocked in successfully!');
            window.location.reload();
        } else {
            alert(result.message || 'Failed to clock in employee');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while clocking in');
    });
}

function clockOutEmployee() {
    const employeeId = document.getElementById('employee-select').value;
    
    if (!employeeId) {
        alert('Please select an employee first.');
        return;
    }
    
    const data = {
        employee_id: employeeId
    };
    
    fetch('/api/attendance/clock-out', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Employee clocked out successfully!');
            window.location.reload();
        } else {
            alert(result.message || 'Failed to clock out employee');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while clocking out');
    });
}

function startBreak() {
    const employeeId = document.getElementById('employee-select').value;
    
    if (!employeeId) {
        alert('Please select an employee first.');
        return;
    }
    
    const data = {
        employee_id: employeeId
    };
    
    fetch('/api/attendance/start-break', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Break started successfully!');
            window.location.reload();
        } else {
            alert(result.message || 'Failed to start break');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while starting break');
    });
}

function endBreak() {
    const employeeId = document.getElementById('employee-select').value;
    
    if (!employeeId) {
        alert('Please select an employee first.');
        return;
    }
    
    const data = {
        employee_id: employeeId
    };
    
    fetch('/api/attendance/end-break', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Break ended successfully!');
            window.location.reload();
        } else {
            alert(result.message || 'Failed to end break');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while ending break');
    });
}

function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function viewAttendanceDetails(attendanceId) {
    // Placeholder for viewing attendance details
    alert('View attendance details for ID: ' + attendanceId);
}
</script>
@endsection
