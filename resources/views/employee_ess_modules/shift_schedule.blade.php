@extends('employee_ess_modules.partials.employee_layout')

@section('title', 'Shift & Schedule')

@section('content')
<!-- Page Header -->
<div class="page-header-container fade-in">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img" style="height: 40px;">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Shift & Schedule Management</h2>
        <p class="text-muted mb-0">Manage your shifts and view schedule</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Shift & Schedule</li>
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
    <p class="text-muted mb-0">Request shifts or view your schedule</p>
  </div>
  <div class="ess-card-body">
    <div class="d-flex gap-3 flex-wrap">
      <button class="btn btn-jetlouge" onclick="openWorkingModal('create-shift-modal')">
        <i class="bi bi-calendar-plus me-2"></i>Request Shift
      </button>
      <button class="btn btn-jetlouge-outline" onclick="refreshShiftData()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
      </button>
    </div>
  </div>
</div>

<!-- Shift Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stats-card slide-up">
      <div class="stats-icon primary">
        <i class="bi bi-calendar-week"></i>
      </div>
      <div class="stats-value">12</div>
      <p class="stats-label">Total Shifts</p>
      <p class="stats-sublabel">This Month</p>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.1s;">
      <div class="stats-icon warning">
        <i class="bi bi-clock-history"></i>
      </div>
      <div class="stats-value">3</div>
      <p class="stats-label">Pending Shifts</p>
      <p class="stats-sublabel">Awaiting Approval</p>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.2s;">
      <div class="stats-icon success">
        <i class="bi bi-check-circle"></i>
      </div>
      <div class="stats-value">9</div>
      <p class="stats-label">Approved Shifts</p>
      <p class="stats-sublabel">Ready to Work</p>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.3s;">
      <div class="stats-icon info">
        <i class="bi bi-currency-dollar"></i>
      </div>
      <div class="stats-value">$2,340</div>
      <p class="stats-label">Total Amount</p>
      <p class="stats-sublabel">Expected Earnings</p>
    </div>
  </div>
</div>

<!-- Schedule List -->
<div class="row">
  <div class="col-12">
    <div class="ess-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bi bi-calendar3 me-2"></i>My Schedule
        </h5>
        <div class="d-flex gap-2">
          <select class="form-select form-select-sm" id="periodFilter" onchange="filterSchedule()">
            <option value="current_week">Current Week</option>
            <option value="next_week">Next Week</option>
            <option value="current_month">Current Month</option>
            <option value="next_month">Next Month</option>
          </select>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-clean">
            <thead>
              <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Shift Time</th>
                <th>Duration</th>
                <th>Location</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @php
                // Direct database connection for shifts using config
                try {
                    $host = config('database.connections.mysql.host', '127.0.0.1');
                    $port = config('database.connections.mysql.port', '3306');
                    $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
                    $username = config('database.connections.mysql.username', 'root');
                    $password = config('database.connections.mysql.password', '');
                    
                    $dsn = "mysql:host={$host};port={$port};dbname={$database}";
                    $pdo = new PDO($dsn, $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Auto-create shifts table if not exists
                    $pdo->exec("CREATE TABLE IF NOT EXISTS shifts (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        employee_id INT NOT NULL,
                        shift_type_id INT,
                        shift_date DATE NOT NULL,
                        start_time TIME,
                        end_time TIME,
                        hours DECIMAL(4,2),
                        status ENUM('scheduled', 'completed', 'cancelled', 'pending') DEFAULT 'scheduled',
                        location VARCHAR(255) DEFAULT 'Main Office',
                        notes TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )");
                    
                    // Auto-create shift_types table if not exists
                    $pdo->exec("CREATE TABLE IF NOT EXISTS shift_types (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        code VARCHAR(10) NOT NULL UNIQUE,
                        start_time TIME,
                        end_time TIME,
                        hours DECIMAL(4,2),
                        description TEXT,
                        is_active BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )");
                    
                    // Insert sample shift types if table is empty
                    $countStmt = $pdo->query("SELECT COUNT(*) as count FROM shift_types");
                    $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    if ($count == 0) {
                        $pdo->exec("INSERT INTO shift_types (name, code, start_time, end_time, hours, description) VALUES
                            ('Morning Shift', 'MORNING', '08:00:00', '16:00:00', 8.00, 'Regular morning shift'),
                            ('Evening Shift', 'EVENING', '16:00:00', '00:00:00', 8.00, 'Evening shift'),
                            ('Night Shift', 'NIGHT', '00:00:00', '08:00:00', 8.00, 'Night shift'),
                            ('Part Time', 'PARTTIME', '09:00:00', '13:00:00', 4.00, 'Part-time shift'),
                            ('Overtime', 'OVERTIME', '17:00:00', '21:00:00', 4.00, 'Overtime shift')");
                    }
                    
                    // Get current employee's shifts
                    $employeeId = Auth::guard('employee')->user()->id ?? 1;
                    $stmt = $pdo->prepare("SELECT s.*, st.name as shift_type_name, st.start_time as type_start_time, st.end_time as type_end_time 
                        FROM shifts s 
                        LEFT JOIN shift_types st ON s.shift_type_id = st.id 
                        WHERE s.employee_id = ? 
                        ORDER BY s.shift_date DESC");
                    $stmt->execute([$employeeId]);
                    $directShifts = $stmt->fetchAll(PDO::FETCH_OBJ);
                    
                    echo "<!-- Direct DB Query: Found " . count($directShifts) . " shifts -->"; 
                } catch (Exception $e) {
                    $directShifts = [];
                    echo "<!-- Direct DB Query Error: " . $e->getMessage() . " -->";
                }
              @endphp
              
              @if(count($directShifts) > 0)
                @foreach($directShifts as $shift)
                <tr>
                  <td>{{ isset($shift->shift_date) ? date('M d, Y', strtotime($shift->shift_date)) : 'N/A' }}</td>
                  <td>{{ isset($shift->shift_date) ? date('l', strtotime($shift->shift_date)) : 'N/A' }}</td>
                  <td>
                    @if(isset($shift->start_time) && $shift->start_time)
                      {{ date('g:i A', strtotime($shift->start_time)) }}
                    @elseif(isset($shift->type_start_time) && $shift->type_start_time)
                      {{ date('g:i A', strtotime($shift->type_start_time)) }}
                    @else
                      N/A
                    @endif
                    -
                    @if(isset($shift->end_time) && $shift->end_time)
                      {{ date('g:i A', strtotime($shift->end_time)) }}
                    @elseif(isset($shift->type_end_time) && $shift->type_end_time)
                      {{ date('g:i A', strtotime($shift->type_end_time)) }}
                    @else
                      N/A
                    @endif
                  </td>
                  <td>{{ $shift->hours ?? '8' }} hours</td>
                  <td>{{ $shift->location ?? 'Main Office' }}</td>
                  <td>
                    @php
                      $status = $shift->status ?? 'scheduled';
                      $statusClass = match($status) {
                        'completed' => 'badge-success',
                        'scheduled' => 'badge-primary',
                        'cancelled' => 'badge-danger',
                        'pending' => 'badge-warning',
                        default => 'badge-secondary'
                      };
                    @endphp
                    <span class="badge-clean {{ $statusClass }}">
                      {{ ucfirst($status) }}
                    </span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-sm btn-outline-jetlouge" onclick="viewShiftDetails({{ $shift->id ?? 0 }})">
                        <i class="bi bi-eye"></i>
                      </button>
                      @if(($shift->status ?? 'scheduled') !== 'cancelled' && ($shift->status ?? 'scheduled') !== 'completed')
                        <button class="btn btn-sm btn-outline-warning" onclick="editShift({{ $shift->id ?? 0 }}, '{{ addslashes($shift->shift_type_name ?? '') }}', '{{ $shift->shift_date ?? '' }}', '{{ $shift->start_time ?? '' }}', '{{ $shift->end_time ?? '' }}')">
                          <i class="bi bi-pencil"></i>
                        </button>
                      @endif
                    </div>
                  </td>
                </tr>
                @endforeach
              @else
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">
                    <i class="bi bi-calendar-x display-4 d-block mb-3"></i>
                    No shifts scheduled. <a href="#" onclick="openWorkingModal('create-shift-modal')" class="text-primary">Request your first shift</a>
                    <br><small class="text-muted">Direct DB found: {{ count($directShifts) }} shifts</small>
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Create Shift Request Modal -->
<div class="working-modal" id="create-shift-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal(this.closest('.working-modal').id)"></div>
    <div class="working-modal-backdrop" onclick="closeWorkingModal('create-shift-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Request Shift</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-shift-modal')">&times;</button>
            </div>
            <form id="create-shift-form" method="POST" action="{{ route('employee.shifts.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-date" class="form-label">Shift Date</label>
                                <input type="date" class="form-control" id="shift-date" name="shift_date" required min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-type-id" class="form-label">Shift Type</label>
                                <select class="form-select" id="shift-type-id" name="shift_type_id" required>
                                    <option value="">Select Shift Type</option>
                                    @php
                                        try {
                                            $host = config('database.connections.mysql.host', '127.0.0.1');
                                            $port = config('database.connections.mysql.port', '3306');
                                            $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
                                            $username = config('database.connections.mysql.username', 'root');
                                            $password = config('database.connections.mysql.password', '');
                                            
                                            $dsn = "mysql:host={$host};port={$port};dbname={$database}";
                                            $pdo = new PDO($dsn, $username, $password);
                                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                            
                                            // Check if shift_types table exists
                                            $tableCheck = $pdo->query("SHOW TABLES LIKE 'shift_types'")->rowCount();
                                            if ($tableCheck == 0) {
                                                // Create shift_types table if it doesn't exist
                                                $pdo->exec("
                                                    CREATE TABLE shift_types (
                                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                                        name VARCHAR(100) NOT NULL,
                                                        code VARCHAR(10) UNIQUE,
                                                        start_time TIME,
                                                        end_time TIME,
                                                        hours DECIMAL(4,2),
                                                        description TEXT,
                                                        is_active BOOLEAN DEFAULT TRUE,
                                                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                                    )
                                                ");
                                                
                                                // Insert sample shift types
                                                $pdo->exec("
                                                    INSERT INTO shift_types (name, code, start_time, end_time, hours, description, is_active) VALUES
                                                    ('Morning Shift', 'MORNING', '08:00:00', '16:00:00', 8.00, 'Standard morning shift', 1),
                                                    ('Evening Shift', 'EVENING', '16:00:00', '00:00:00', 8.00, 'Evening shift coverage', 1),
                                                    ('Night Shift', 'NIGHT', '00:00:00', '08:00:00', 8.00, 'Overnight shift', 1),
                                                    ('Weekend Day', 'WEEKEND_DAY', '09:00:00', '17:00:00', 8.00, 'Weekend day shift', 1),
                                                    ('Part Time', 'PART_TIME', '13:00:00', '17:00:00', 4.00, 'Part-time afternoon shift', 1)
                                                ");
                                            }
                                            
                                            $stmt = $pdo->query("SELECT * FROM shift_types WHERE is_active = 1 ORDER BY name");
                                            $modalShiftTypes = $stmt->fetchAll(PDO::FETCH_OBJ);
                                        } catch (Exception $e) {
                                            $modalShiftTypes = [];
                                        }
                                    @endphp
                                    @if(count($modalShiftTypes) > 0)
                                        @foreach($modalShiftTypes as $type)
                                            <option value="{{ $type->id }}">
                                                {{ $type->name }}
                                                @if(isset($type->start_time) && isset($type->end_time))
                                                    ({{ date('g:i A', strtotime($type->start_time)) }} - {{ date('g:i A', strtotime($type->end_time)) }})
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No shift types available</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-start-time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="shift-start-time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-end-time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="shift-end-time" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="shift-location" class="form-label">Location</label>
                        <select class="form-select" id="shift-location" name="location">
                            <option value="Main Office">Main Office</option>
                            <option value="Branch Office 1">Branch Office 1</option>
                            <option value="Branch Office 2">Branch Office 2</option>
                            <option value="Remote Work">Remote Work</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="shift-notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="shift-notes" name="notes" rows="3" placeholder="Any additional notes or special requests..."></textarea>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            <span id="calculated-hours">Hours will be calculated automatically</span>
                        </small>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-shift-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
              </div>
            </div>
            <div class="mt-4 d-flex gap-2">
              <button type="button" class="btn btn-secondary" onclick="hideShiftForm()">Cancel</button>
              <button type="submit" class="btn btn-jetlouge">
                <i class="bi bi-send me-2"></i>Submit Request
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('assets/js/working-modal-ess.js') }}"></script>
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

// Show/Hide shift form functions - GLOBAL SCOPE
function showShiftForm() {
  document.getElementById('shiftRequestContainer').style.display = 'block';
  document.getElementById('shiftRequestContainer').scrollIntoView({ behavior: 'smooth' });
}

function hideShiftForm() {
  document.getElementById('shiftRequestContainer').style.display = 'none';
  document.getElementById('shiftRequestForm').reset();
}

function submitShiftRequest() {
  // Basic form validation
  const form = document.getElementById('shiftRequestForm');
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
  showAlert('Shift request submitted successfully! You will receive a confirmation email shortly.', 'success');
  
  // Hide form and reset
  hideShiftForm();
}

// Additional DOMContentLoaded for form handling
document.addEventListener('DOMContentLoaded', function() {
  // Form submission with validation
  const shiftForm = document.getElementById('shiftRequestForm');
  if (shiftForm) {
    shiftForm.addEventListener('submit', function(e) {
      e.preventDefault();
      submitShiftRequest();
    });
  }
  
  // Set minimum date to today for shift requests
  const dateInput = document.getElementById('shift_date');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
  }
});

function refreshShiftData() {
  // Show loading state
  const btn = event.target;
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Refreshing...';
  btn.disabled = true;
  
  // Simulate refresh
  setTimeout(() => {
    btn.innerHTML = originalText;
    btn.disabled = false;
    showAlert('Shift data refreshed successfully!', 'success');
  }, 1500);
}

function viewShiftDetails(shiftId) {
  showAlert(`Viewing details for shift ID: ${shiftId}`, 'info');
}

function requestShiftSwap(shiftId) {
  if (confirm('Request a swap for this shift?')) {
    showAlert(`Swap request submitted for shift ID: ${shiftId}`, 'success');
  }
}

function filterSchedule() {
  const period = document.getElementById('periodFilter').value;
  showAlert(`Filtering schedule by: ${period.replace('_', ' ')}`, 'info');
}

function showAlert(message, type = 'info') {
  const alertClass = type === 'success' ? 'alert-success' : 
                    type === 'error' ? 'alert-danger' : 'alert-info';
  
  const alertHtml = `
    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
      <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
      ${message}
      <button type="button" class="working-modal-close" data-bs-dismiss="alert"></button>
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

// Set minimum date to today for shift requests
document.addEventListener('DOMContentLoaded', function() {
  const dateInput = document.getElementById('shift_date');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
  }
});

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
</script>
<script src="{{ asset('assets/js/working-modal-ess.js') }}"></script>
@endsection
