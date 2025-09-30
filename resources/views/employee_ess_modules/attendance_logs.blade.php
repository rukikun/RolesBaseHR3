@extends('employee_ess_modules.partials.employee_layout')

@section('title', 'Attendance & Time Logs')

@section('content')
<!-- Page Header -->
<div class="page-header-container fade-in">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img" style="height: 40px;">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Attendance & Time Logs</h2>
        <p class="text-muted mb-0">Track your time and manage attendance records</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Attendance & Time Logs</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Quick Actions -->
<div class="ess-card mb-4 slide-up">
  <div class="ess-card-header">
    <h5 class="ess-card-title">
      <i class="bi bi-lightning-charge"></i>
      Quick Actions
    </h5>
    <p class="text-muted mb-0">Clock in/out or submit time entries</p>
  </div>
  <div class="ess-card-body">
    <div class="d-flex gap-3 flex-wrap">
      <button class="btn btn-jetlouge" onclick="openWorkingModal('create-time-entry-modal')">
        <i class="bi bi-clock me-2"></i>Add Time Entry
      </button>
      <button class="btn btn-jetlouge-outline" onclick="refreshTimeData()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
      </button>
    </div>
  </div>
</div>

<!-- Time Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stats-card slide-up">
      <div class="stats-icon primary">
        <i class="bi bi-calendar-check"></i>
      </div>
      <div class="stats-value">24</div>
      <p class="stats-label">Total Entries</p>
      <p class="stats-sublabel">This Month</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.1s;">
      <div class="stats-icon warning">
        <i class="bi bi-clock-history"></i>
      </div>
      <div class="stats-value">2</div>
      <p class="stats-label">Pending Entries</p>
      <p class="stats-sublabel">Awaiting Approval</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.2s;">
      <div class="stats-icon success">
        <i class="bi bi-check-circle"></i>
      </div>
      <div class="stats-value">22</div>
      <p class="stats-label">Approved Entries</p>
      <p class="stats-sublabel">Ready for Payroll</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.3s;">
      <div class="stats-icon info">
        <i class="bi bi-stopwatch"></i>
      </div>
      <div class="stats-value">176h</div>
      <p class="stats-label">Total Hours</p>
      <p class="stats-sublabel">This Month</p>
    </div>
  </div>
</div>

<!-- My Time Logs List -->
<div class="ess-card slide-up" style="animation-delay: 0.4s;">
  <div class="ess-card-header">
    <h5 class="ess-card-title">
      <i class="bi bi-list-ul"></i>
      My Time Logs
    </h5>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" id="statusFilter">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
      <button class="btn btn-jetlouge btn-sm" onclick="openWorkingModal('create-time-entry-modal')">
        <i class="bi bi-plus-lg"></i>
      </button>
    </div>
  </div>
  <div class="ess-card-body p-0">
    <div class="table-responsive">
      <table class="table table-clean mb-0">
        <thead>
          <tr>
            <th><i class="bi bi-calendar3 me-2"></i>Date</th>
            <th><i class="bi bi-clock me-2"></i>Clock In</th>
            <th><i class="bi bi-clock-history me-2"></i>Clock Out</th>
            <th><i class="bi bi-stopwatch me-2"></i>Total Hours</th>
            <th><i class="bi bi-briefcase me-2"></i>Project</th>
            <th><i class="bi bi-flag me-2"></i>Status</th>
            <th><i class="bi bi-gear me-2"></i>Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            // Direct database connection for time entries (like leave_management.blade.php)
            try {
                $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Auto-create time_entries table if not exists
                $pdo->exec("CREATE TABLE IF NOT EXISTS time_entries (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id INT NOT NULL,
                    date DATE NOT NULL,
                    clock_in TIME,
                    clock_out TIME,
                    total_hours DECIMAL(4,2),
                    break_duration DECIMAL(4,2) DEFAULT 1.00,
                    overtime DECIMAL(4,2) DEFAULT 0.00,
                    status ENUM('present', 'late', 'absent', 'half_day') DEFAULT 'present',
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                
                // Get current employee's time entries
                $employeeId = Auth::guard('employee')->user()->id ?? 1;
                $stmt = $pdo->prepare("SELECT * FROM time_entries 
                    WHERE employee_id = ? 
                    ORDER BY date DESC");
                $stmt->execute([$employeeId]);
                $directTimeEntries = $stmt->fetchAll(PDO::FETCH_OBJ);
                
                echo "<!-- Direct DB Query: Found " . count($directTimeEntries) . " time entries -->"; 
            } catch (Exception $e) {
                $directTimeEntries = [];
                echo "<!-- Direct DB Query Error: " . $e->getMessage() . " -->";
            }
          @endphp
          
          @if(count($directTimeEntries) > 0)
            @foreach($directTimeEntries as $entry)
            <tr>
              <td>{{ isset($entry->date) ? date('M d, Y', strtotime($entry->date)) : 'N/A' }}</td>
              <td>{{ isset($entry->clock_in) ? date('g:i A', strtotime($entry->clock_in)) : 'N/A' }}</td>
              <td>{{ isset($entry->clock_out) ? date('g:i A', strtotime($entry->clock_out)) : 'N/A' }}</td>
              <td>{{ $entry->total_hours ?? '8.0' }} hrs</td>
              <td>{{ $entry->break_duration ?? '1.0' }} hrs</td>
              <td>{{ $entry->overtime ?? '0.0' }} hrs</td>
              <td>
                @php
                  $status = $entry->status ?? 'present';
                  $badgeClass = match($status) {
                    'present' => 'success',
                    'late' => 'warning',
                    'absent' => 'danger',
                    'half_day' => 'info',
                    default => 'secondary'
                  };
                @endphp
                <span class="badge bg-{{ $badgeClass }}">
                  {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
              </td>
              <td>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-primary" 
                          onclick="viewTimeEntryDetails({{ $entry->id ?? 0 }})" title="View">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" 
                          onclick="editTimeEntry({{ $entry->id ?? 0 }}, '{{ $entry->date ?? '' }}', '{{ $entry->clock_in ?? '' }}', '{{ $entry->clock_out ?? '' }}', '{{ addslashes($entry->description ?? '') }}')" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                </div>
              </td>
            </tr>
            @endforeach
          @else
          <tr>
            <td colspan="8" class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
              No time entries found. <a href="#" onclick="openWorkingModal('create-time-entry-modal')" class="text-primary">Log your first entry</a>
              <br><small class="text-muted">Direct DB found: {{ count($directTimeEntries) }} time entries</small>
            </td>
          </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create Time Entry Modal -->
<div class="working-modal" id="create-time-entry-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('create-time-entry-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Log Time Entry</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-time-entry-modal')">&times;</button>
            </div>
            <form id="create-time-entry-form" method="POST" action="{{ route('employee.attendance.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="time-date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="time-date" name="date" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="present">Present</option>
                                    <option value="late">Late</option>
                                    <option value="half_day">Half Day</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="time-start" class="form-label">Clock In Time</label>
                                <input type="time" class="form-control" id="time-start" name="clock_in" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="time-end" class="form-label">Clock Out Time</label>
                                <input type="time" class="form-control" id="time-end" name="clock_out" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="break-duration" class="form-label">Break Duration (hours)</label>
                                <input type="number" class="form-control" id="break-duration" name="break_duration" step="0.25" min="0" max="8" value="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hours-worked" class="form-label">Total Hours</label>
                                <input type="number" class="form-control" id="hours-worked" name="total_hours" step="0.25" min="0" max="24" readonly>
                                <div class="form-text">
                                    <i class="fas fa-clock me-1"></i>
                                    <span id="total-hours">Hours will be calculated automatically</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="time-description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="time-description" name="description" rows="3" placeholder="Any notes about your work day..."></textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-time-entry-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// AGGRESSIVE CLEANUP - Remove all blocking elements
document.addEventListener('DOMContentLoaded', function() {
  // Force cleanup function
  function aggressiveCleanup() {
    // Remove ALL modal backdrops
    document.querySelectorAll('.modal-backdrop, .backdrop, [class*="backdrop"]').forEach(backdrop => {
      backdrop.remove();
    });
    
    // Remove any overlay elements except mobile sidebar
    document.querySelectorAll('.overlay, [class*="overlay"]').forEach(overlay => {
      if (overlay.id !== 'overlay') {
        overlay.remove();
      }
    });
    
    // Reset body classes and styles AGGRESSIVELY
    document.body.classList.remove('modal-open');
    document.body.style.overflow = 'auto';
    document.body.style.paddingRight = '0';
    document.body.style.position = 'static';
    
    // Hide all modals
    document.querySelectorAll('.modal').forEach(modal => {
      modal.style.display = 'none';
      modal.classList.remove('show');
    });
    
    // Force all elements to be interactive
    document.querySelectorAll('*').forEach(element => {
      element.style.pointerEvents = 'auto';
    });
    
    console.log('Aggressive cleanup completed');
  }
  
  // Run cleanup immediately
  aggressiveCleanup();
  
  // Run cleanup again after short delay
  setTimeout(aggressiveCleanup, 100);
  setTimeout(aggressiveCleanup, 500);
  
  // Make aggressiveCleanup global
  window.aggressiveCleanup = aggressiveCleanup;
});

// Show/Hide time entry form functions - GLOBAL SCOPE
function showTimeEntryForm() {
  document.getElementById('timeEntryContainer').style.display = 'block';
  document.getElementById('timeEntryContainer').scrollIntoView({ behavior: 'smooth' });
}

function hideTimeEntryForm() {
  document.getElementById('timeEntryContainer').style.display = 'none';
  document.getElementById('timeEntryForm').reset();
}

function submitTimeEntry() {
  // Basic form validation
  const form = document.getElementById('timeEntryForm');
  const inputs = form.querySelectorAll('input[required], select[required]');
  let isValid = true;
  
  inputs.forEach(input => {
    if (!input.value.trim()) {
      input.classList.add('is-invalid');
      isValid = false;
    } else {
      input.classList.remove('is-invalid');
    }
  });
  
  if (!isValid) {
    showAlert('Please fill in all required fields.', 'warning');
    return;
  }
  
  // Simulate form submission
  showAlert('Time entry submitted successfully! Your hours have been logged.', 'success');
  
  // Hide form and reset
  hideTimeEntryForm();
}

// Additional DOMContentLoaded for form handling
document.addEventListener('DOMContentLoaded', function() {
  // Form submission with validation
  const timeForm = document.getElementById('timeEntryForm');
  if (timeForm) {
    timeForm.addEventListener('submit', function(e) {
      e.preventDefault();
      submitTimeEntry();
    });
  }
  
  // Set maximum date to today for time entries
  const dateInput = document.getElementById('entry_date');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.max = today;
  }
  
  // Auto-calculate total hours
  const clockInInput = document.getElementById('clock_in');
  const clockOutInput = document.getElementById('clock_out');
  const breakInput = document.getElementById('break_duration');
  
  if (clockInInput && clockOutInput && breakInput) {
    clockInInput.addEventListener('change', calculateHours);
    clockOutInput.addEventListener('change', calculateHours);
    breakInput.addEventListener('change', calculateHours);
  }
  
  function calculateHours() {
    const clockIn = clockInInput.value;
    const clockOut = clockOutInput.value;
    const breakMinutes = parseInt(breakInput.value) || 0;
    
    if (clockIn && clockOut) {
      const start = new Date(`2000-01-01T${clockIn}:00`);
      const end = new Date(`2000-01-01T${clockOut}:00`);
      
      if (end > start) {
        const diffMs = end - start;
        const diffHours = diffMs / (1000 * 60 * 60);
        const totalHours = diffHours - (breakMinutes / 60);
        
        // Display calculated hours (you could add a display element)
        console.log(`Total hours: ${totalHours.toFixed(2)}`);
      }
    }
  }
});

function refreshAttendanceData() {
  // Show loading state
  const btn = event.target;
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Refreshing...';
  btn.disabled = true;
  
  // Simulate refresh
  setTimeout(() => {
    btn.innerHTML = originalText;
    btn.disabled = false;
    showAlert('Attendance data refreshed successfully!', 'success');
  }, 1500);
}

function submitTimeEntry() {
  // Basic form validation
  const form = document.getElementById('timeEntryForm');
  const inputs = form.querySelectorAll('input[required], select[required]');
  let isValid = true;
  
  inputs.forEach(input => {
    if (!input.value.trim()) {
      input.classList.add('is-invalid');
      isValid = false;
    } else {
      input.classList.remove('is-invalid');
    }
  });
  
  if (isValid) {
    showAlert('Time entry submitted successfully!', 'success');
    // Close modal using Bootstrap method
    const modal = bootstrap.Modal.getInstance(document.getElementById('timeEntryModal'));
    modal.hide();
    form.reset();
  } else {
    showAlert('Please fill in all required fields', 'error');
  }
}

function showAlert(message, type = 'info') {
  const alertClass = type === 'success' ? 'alert-success' : 
                    type === 'error' ? 'alert-danger' : 'alert-info';
  
  const alertHtml = `
    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
      <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  
  // Insert at top of page
  const container = document.querySelector('.page-header-container');
  container.insertAdjacentHTML('afterend', alertHtml);
  
  // Auto dismiss after 3 seconds
  setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      alert.remove();
    }
  }, 3000);
}

// Multiple cleanup triggers
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    forceModalCleanup();
  }
  // Emergency cleanup with Ctrl+Shift+C
  if (e.ctrlKey && e.shiftKey && e.key === 'C') {
    e.preventDefault();
    window.aggressiveCleanup();
    alert('Emergency cleanup activated!');
  }
});

// Cleanup on any click
document.addEventListener('click', function() {
  // Quick cleanup on every click
  document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
  document.body.style.overflow = 'auto';
  document.body.style.pointerEvents = 'auto';
});

// Cleanup on window focus
window.addEventListener('focus', function() {
  if (window.aggressiveCleanup) {
    window.aggressiveCleanup();
  }
});

// Enhanced filter functionality
document.addEventListener('DOMContentLoaded', function() {
  const statusFilter = document.getElementById('statusFilter');
  
  statusFilter.addEventListener('change', function() {
    const filterValue = this.value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
      if (filterValue === '') {
        row.style.display = '';
      } else {
        const statusBadge = row.querySelector('.badge-clean');
        const status = statusBadge.textContent.toLowerCase();
        row.style.display = status.includes(filterValue) ? '' : 'none';
      }
    });
    
    // Show filter feedback
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none').length;
    if (filterValue) {
      showAlert(`Showing ${visibleRows} entries with status: ${filterValue}`, 'info');
    }
  });
  
  // Set minimum date to today for time entries
  const dateInput = document.querySelector('input[type="date"]');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.max = today; // Can't log future time
  }
});
</script>
@endsection
