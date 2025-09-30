@extends('layouts.hr')

@section('title', 'Schedule Management - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Schedule Management</h2>
        <p class="text-muted mb-0">Assign shifts to employees</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ url('/shifts') }}" class="text-decoration-none">Shifts</a></li>
        <li class="breadcrumb-item active" aria-current="page">Schedule</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Schedule Filters -->
<div class="row mb-4">
  <div class="col-md-3">
    <label for="schedule-employee" class="form-label">Employee</label>
    <select id="schedule-employee" class="form-select" name="employee_id">
      <option value="">All Employees</option>
      @foreach($employees ?? [] as $employee)
        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label for="schedule-start-date" class="form-label">Start Date</label>
    <input type="date" id="schedule-start-date" class="form-control" value="{{ date('Y-m-d', strtotime('monday this week')) }}">
  </div>
  <div class="col-md-3">
    <label for="schedule-end-date" class="form-label">End Date</label>
    <input type="date" id="schedule-end-date" class="form-control" value="{{ date('Y-m-d', strtotime('sunday this week')) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">&nbsp;</label>
    <div class="d-flex gap-2">
      <button class="btn btn-secondary" id="filter-schedule-btn">Filter</button>
      <button class="btn btn-primary" id="assign-shift-btn">
        <i class="fas fa-plus me-2"></i>Assign Shift
      </button>
    </div>
  </div>
</div>

<!-- Quick Week Navigation -->
<div class="row mb-4">
  <div class="col-12">
    <div class="d-flex justify-content-center gap-2">
      <button class="btn btn-outline-secondary btn-sm" id="prev-week-btn">
        <i class="fas fa-chevron-left me-1"></i>Previous Week
      </button>
      <button class="btn btn-outline-primary btn-sm" id="current-week-btn">Current Week</button>
      <button class="btn btn-outline-secondary btn-sm" id="next-week-btn">
        Next Week<i class="fas fa-chevron-right ms-1"></i>
      </button>
    </div>
  </div>
</div>

<!-- Schedule Calendar -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-calendar-alt me-2"></i>Weekly Schedule
    </h5>
    <div>
      <button class="btn btn-success btn-sm" id="bulk-assign-btn">
        <i class="fas fa-calendar-plus me-2"></i>Bulk Assign
      </button>
      <button class="btn btn-info btn-sm" id="refresh-schedule-btn">
        <i class="fas fa-sync-alt me-2"></i>Refresh
      </button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="schedule-table">
        <thead class="table-light">
          <tr>
            <th width="200px">Employee</th>
            <th class="text-center">Monday</th>
            <th class="text-center">Tuesday</th>
            <th class="text-center">Wednesday</th>
            <th class="text-center">Thursday</th>
            <th class="text-center">Friday</th>
            <th class="text-center">Saturday</th>
            <th class="text-center">Sunday</th>
          </tr>
        </thead>
        <tbody id="schedule-tbody">
          <!-- Schedule will be loaded here -->
        </tbody>
      </table>
    </div>
    
    <!-- Loading indicator -->
    <div class="text-center py-4" id="schedule-loading" style="display: none;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading schedule...</p>
    </div>
  </div>
</div>

<!-- Assign Shift Modal -->
<div class="modal fade" id="assign-shift-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign Shift</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="assign-shift-form">
        <div class="modal-body">
          <div class="mb-3">
            <label for="assign-employee" class="form-label">Employee *</label>
            <select class="form-select" id="assign-employee" name="employee_id" required>
              <option value="">Select Employee</option>
              @foreach($employees ?? [] as $employee)
                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
              @endforeach
            </select>
          </div>
          
          <div class="mb-3">
            <label for="assign-shift" class="form-label">Shift *</label>
            <select class="form-select" id="assign-shift" name="shift_id" required>
              <option value="">Select Shift</option>
              @foreach($shifts ?? [] as $shift)
                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})</option>
              @endforeach
            </select>
          </div>
          
          <div class="mb-3">
            <label for="assign-date" class="form-label">Date *</label>
            <input type="date" class="form-control" id="assign-date" name="shift_date" required>
          </div>
          
          <div class="mb-3">
            <label for="assign-notes" class="form-label">Notes</label>
            <textarea class="form-control" id="assign-notes" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Assign Shift</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulk-assign-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Bulk Assign Shifts</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="bulk-assign-form">
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="bulk-start-date" class="form-label">Start Date *</label>
              <input type="date" class="form-control" id="bulk-start-date" required>
            </div>
            <div class="col-md-6">
              <label for="bulk-end-date" class="form-label">End Date *</label>
              <input type="date" class="form-control" id="bulk-end-date" required>
            </div>
          </div>
          
          <div id="bulk-assignments">
            <!-- Bulk assignment rows will be added here -->
          </div>
          
          <button type="button" class="btn btn-outline-primary btn-sm" id="add-bulk-assignment">
            <i class="fas fa-plus me-2"></i>Add Assignment
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Assign All</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
  loadSchedule();
  setupWeekNavigation();
});

// Load schedule
function loadSchedule() {
  const employeeId = document.getElementById('schedule-employee').value;
  const startDate = document.getElementById('schedule-start-date').value;
  const endDate = document.getElementById('schedule-end-date').value;
  
  const params = new URLSearchParams({
    employee_id: employeeId,
    start_date: startDate,
    end_date: endDate
  });
  
  showScheduleLoading(true);
  
  fetch(`/api/shifts/schedule?${params}`, {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    showScheduleLoading(false);
    if (data.success) {
      populateScheduleTable(data.data, startDate, endDate);
    }
  })
  .catch(error => {
    showScheduleLoading(false);
    console.error('Error loading schedule:', error);
    showNotification('Error loading schedule', 'error');
  });
}

// Show/hide loading indicator
function showScheduleLoading(show) {
  document.getElementById('schedule-loading').style.display = show ? 'block' : 'none';
}

// Populate schedule table
function populateScheduleTable(scheduleData, startDate, endDate) {
  const tbody = document.getElementById('schedule-tbody');
  const employees = @json($employees ?? []);
  
  if (employees.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No employees found</td></tr>';
    return;
  }
  
  const start = new Date(startDate);
  const weekDays = [];
  for (let i = 0; i < 7; i++) {
    const date = new Date(start);
    date.setDate(start.getDate() + i);
    weekDays.push(date.toISOString().split('T')[0]);
  }
  
  tbody.innerHTML = employees.map(employee => {
    const employeeSchedule = scheduleData.filter(s => s.employee_id == employee.id);
    
    return `
      <tr>
        <td class="fw-bold">${employee.full_name}</td>
        ${weekDays.map(date => {
          const daySchedule = employeeSchedule.find(s => s.shift_date === date);
          return `
            <td class="text-center schedule-cell" data-employee="${employee.id}" data-date="${date}">
              ${daySchedule ? `
                <div class="shift-assignment">
                  <small class="badge bg-primary">${daySchedule.shift.name}</small>
                  <br>
                  <small class="text-muted">${daySchedule.shift.start_time} - ${daySchedule.shift.end_time}</small>
                  <div class="mt-1">
                    <button class="btn btn-outline-danger btn-xs" onclick="removeAssignment(${daySchedule.id})">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              ` : `
                <button class="btn btn-outline-secondary btn-sm" onclick="quickAssign(${employee.id}, '${date}')">
                  <i class="fas fa-plus"></i>
                </button>
              `}
            </td>
          `;
        }).join('')}
      </tr>
    `;
  }).join('');
}

// Setup week navigation
function setupWeekNavigation() {
  document.getElementById('prev-week-btn').addEventListener('click', function() {
    changeWeek(-7);
  });
  
  document.getElementById('next-week-btn').addEventListener('click', function() {
    changeWeek(7);
  });
  
  document.getElementById('current-week-btn').addEventListener('click', function() {
    const today = new Date();
    const monday = new Date(today.setDate(today.getDate() - today.getDay() + 1));
    const sunday = new Date(today.setDate(today.getDate() - today.getDay() + 7));
    
    document.getElementById('schedule-start-date').value = monday.toISOString().split('T')[0];
    document.getElementById('schedule-end-date').value = sunday.toISOString().split('T')[0];
    loadSchedule();
  });
}

// Change week
function changeWeek(days) {
  const startDate = new Date(document.getElementById('schedule-start-date').value);
  const endDate = new Date(document.getElementById('schedule-end-date').value);
  
  startDate.setDate(startDate.getDate() + days);
  endDate.setDate(endDate.getDate() + days);
  
  document.getElementById('schedule-start-date').value = startDate.toISOString().split('T')[0];
  document.getElementById('schedule-end-date').value = endDate.toISOString().split('T')[0];
  loadSchedule();
}

// Quick assign shift
function quickAssign(employeeId, date) {
  document.getElementById('assign-employee').value = employeeId;
  document.getElementById('assign-date').value = date;
  new bootstrap.Modal(document.getElementById('assign-shift-modal')).show();
}

// Assign shift form
document.getElementById('assign-shift-form').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const data = {};
  formData.forEach((value, key) => data[key] = value);
  
  fetch('/api/shifts/assign', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message, 'success');
      bootstrap.Modal.getInstance(document.getElementById('assign-shift-modal')).hide();
      loadSchedule();
    } else {
      showNotification(data.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error assigning shift:', error);
    showNotification('Error assigning shift', 'error');
  });
});

// Remove assignment
function removeAssignment(assignmentId) {
  if (confirm('Are you sure you want to remove this shift assignment?')) {
    fetch(`/api/shifts/assignment/${assignmentId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification(data.message, 'success');
        loadSchedule();
      } else {
        showNotification(data.message, 'error');
      }
    })
    .catch(error => {
      console.error('Error removing assignment:', error);
      showNotification('Error removing assignment', 'error');
    });
  }
}

// Event listeners
document.getElementById('filter-schedule-btn').addEventListener('click', loadSchedule);
document.getElementById('refresh-schedule-btn').addEventListener('click', loadSchedule);
document.getElementById('schedule-employee').addEventListener('change', loadSchedule);
document.getElementById('assign-shift-btn').addEventListener('click', function() {
  document.getElementById('assign-shift-form').reset();
  new bootstrap.Modal(document.getElementById('assign-shift-modal')).show();
});

// Bulk assign functionality
document.getElementById('bulk-assign-btn').addEventListener('click', function() {
  document.getElementById('bulk-assign-form').reset();
  document.getElementById('bulk-assignments').innerHTML = '';
  addBulkAssignmentRow();
  new bootstrap.Modal(document.getElementById('bulk-assign-modal')).show();
});

document.getElementById('add-bulk-assignment').addEventListener('click', addBulkAssignmentRow);

function addBulkAssignmentRow() {
  const container = document.getElementById('bulk-assignments');
  const rowCount = container.children.length;
  
  const row = document.createElement('div');
  row.className = 'row mb-3 bulk-assignment-row';
  row.innerHTML = `
    <div class="col-md-4">
      <select class="form-select" name="assignments[${rowCount}][employee_id]" required>
        <option value="">Select Employee</option>
        @foreach($employees ?? [] as $employee)
          <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <select class="form-select" name="assignments[${rowCount}][shift_id]" required>
        <option value="">Select Shift</option>
        @foreach($shifts ?? [] as $shift)
          <option value="{{ $shift->id }}">{{ $shift->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select" name="assignments[${rowCount}][days]" required>
        <option value="">Select Days</option>
        <option value="weekdays">Weekdays Only</option>
        <option value="weekends">Weekends Only</option>
        <option value="all">All Days</option>
      </select>
    </div>
    <div class="col-md-1">
      <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeBulkAssignmentRow(this)">
        <i class="fas fa-times"></i>
      </button>
    </div>
  `;
  
  container.appendChild(row);
}

function removeBulkAssignmentRow(button) {
  button.closest('.bulk-assignment-row').remove();
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
</script>
@endpush
@endsection
