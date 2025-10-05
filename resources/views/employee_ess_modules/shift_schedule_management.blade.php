@extends('employee_ess_modules.partials.employee_layout')

@section('title', 'Shift & Schedule Management')

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

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

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
      <button class="btn btn-jetlouge" onclick="openShiftRequestModal()">
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

<!-- My Shifts List -->
<div class="ess-card slide-up" style="animation-delay: 0.4s;">
  <div class="ess-card-header">
    <h5 class="ess-card-title">
      <i class="bi bi-list-ul"></i>
      My Shifts
    </h5>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" id="statusFilter">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="completed">Completed</option>
      </select>
      <button class="btn btn-jetlouge btn-sm" onclick="openShiftRequestModal()">
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
            <th><i class="bi bi-clock me-2"></i>Time</th>
            <th><i class="bi bi-briefcase me-2"></i>Shift Type</th>
            <th><i class="bi bi-geo-alt me-2"></i>Location</th>
            <th><i class="bi bi-flag me-2"></i>Status</th>
            <th><i class="bi bi-gear me-2"></i>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div class="fw-semibold">Jan 15, 2024</div>
              <small class="text-muted">Monday</small>
            </td>
            <td>
              <div class="fw-semibold">9:00 AM - 5:00 PM</div>
              <small class="text-muted">8 hours</small>
            </td>
            <td>
              <div class="fw-semibold">Morning Shift</div>
              <small class="text-muted">Regular</small>
            </td>
            <td>
              <div class="fw-semibold">Main Office</div>
              <small class="text-muted">Downtown</small>
            </td>
            <td>
              <span class="badge-clean badge-success">Approved</span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <button class="btn btn-action btn-outline-primary btn-sm" title="View Details">
                  <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-action btn-outline-warning btn-sm" title="Edit">
                  <i class="bi bi-pencil"></i>
                </button>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="fw-semibold">Jan 16, 2024</div>
              <small class="text-muted">Tuesday</small>
            </td>
            <td>
              <div class="fw-semibold">2:00 PM - 10:00 PM</div>
              <small class="text-muted">8 hours</small>
            </td>
            <td>
              <div class="fw-semibold">Evening Shift</div>
              <small class="text-muted">Regular</small>
            </td>
            <td>
              <div class="fw-semibold">Branch Office</div>
              <small class="text-muted">Uptown</small>
            </td>
            <td>
              <span class="badge-clean badge-warning">Pending</span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <button class="btn btn-action btn-outline-primary btn-sm" title="View Details">
                  <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-action btn-outline-danger btn-sm" title="Cancel">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="fw-semibold">Jan 17, 2024</div>
              <small class="text-muted">Wednesday</small>
            </td>
            <td>
              <div class="fw-semibold">10:00 PM - 6:00 AM</div>
              <small class="text-muted">8 hours</small>
            </td>
            <td>
              <div class="fw-semibold">Night Shift</div>
              <small class="text-muted">Overtime</small>
            </td>
            <td>
              <div class="fw-semibold">Main Office</div>
              <small class="text-muted">Downtown</small>
            </td>
            <td>
              <span class="badge-clean badge-primary">Completed</span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <button class="btn btn-action btn-outline-primary btn-sm" title="View Details">
                  <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-action btn-outline-success btn-sm" title="Report">
                  <i class="bi bi-file-text"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Shift Request Modal - Pure Laravel Implementation -->
<div class="modal fade" id="shiftRequestModal" tabindex="-1" style="display: none;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-calendar-plus me-2"></i>Request New Shift
        </h5>
        <button type="button" class="btn-close" onclick="closeShiftRequestModal()"></button>
      </div>
      <form method="POST" action="{{ route('employee.shift.store') }}">
        @csrf
        <div class="modal-body">
          <!-- Request Type Selection -->
          <div class="mb-3">
            <label for="request_type" class="form-label">Request Type *</label>
            <select class="form-select" id="request_type" name="request_type" required>
              <option value="">Select Request Type</option>
              @php
                $requestTypes = [
                  'shift_change' => 'Shift Change Request',
                  'time_off' => 'Time Off Request', 
                  'overtime' => 'Overtime Request',
                  'swap' => 'Shift Swap Request',
                  'coverage' => 'Coverage Request',
                  'schedule_adjustment' => 'Schedule Adjustment'
                ];
              @endphp
              @foreach($requestTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          
          <!-- Shift Type Selection with Database Integration -->
          <div class="mb-3">
            <label for="shift_type_id" class="form-label">Available Shift Types</label>
            <select class="form-select" id="shift_type_id" name="shift_type_id">
              <option value="">Select Shift Type</option>
              @php
                // Use Laravel Eloquent Model instead of raw PDO
                use App\Models\ShiftType;
                
                try {
                  $shiftTypes = ShiftType::where('status', 'active')
                                        ->orderBy('name', 'ASC')
                                        ->get();
                } catch(Exception $e) {
                  // Fallback to raw query if model fails
                  try {
                    $host = config('database.connections.mysql.host', '127.0.0.1');
                    $port = config('database.connections.mysql.port', '3306');
                    $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
                    $username = config('database.connections.mysql.username', 'root');
                    $password = config('database.connections.mysql.password', '');
                    
                    $dsn = "mysql:host={$host};port={$port};dbname={$database}";
                    $pdo = new PDO($dsn, $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $pdo->prepare("SELECT * FROM shift_types WHERE status = 'active' ORDER BY name ASC");
                    $stmt->execute();
                    $shiftTypes = collect($stmt->fetchAll(PDO::FETCH_ASSOC));
                  } catch(PDOException $e) {
                    $shiftTypes = collect([]);
                  }
                }
              @endphp
              
              @forelse($shiftTypes as $shiftType)
                <option value="{{ $shiftType['id'] ?? $shiftType->id }}" 
                        data-start="{{ $shiftType['default_start_time'] ?? $shiftType->default_start_time }}" 
                        data-end="{{ $shiftType['default_end_time'] ?? $shiftType->default_end_time }}"
                        data-rate="{{ $shiftType['hourly_rate'] ?? $shiftType->hourly_rate ?? 0 }}">
                  {{ $shiftType['name'] ?? $shiftType->name }} 
                  ({{ date('g:i A', strtotime($shiftType['default_start_time'] ?? $shiftType->default_start_time)) }} - 
                   {{ date('g:i A', strtotime($shiftType['default_end_time'] ?? $shiftType->default_end_time)) }})
                  @if(isset($shiftType['hourly_rate']) || isset($shiftType->hourly_rate))
                    - ${{ number_format($shiftType['hourly_rate'] ?? $shiftType->hourly_rate, 2) }}/hr
                  @endif
                </option>
              @empty
                <!-- Fallback options if no data available -->
                <option value="1" data-start="08:00" data-end="16:00">Morning Shift (8:00 AM - 4:00 PM)</option>
                <option value="2" data-start="16:00" data-end="00:00">Evening Shift (4:00 PM - 12:00 AM)</option>
                <option value="3" data-start="00:00" data-end="08:00">Night Shift (12:00 AM - 8:00 AM)</option>
                <option value="4" data-start="09:00" data-end="17:00">Weekend Day (9:00 AM - 5:00 PM)</option>
              @endforelse
            </select>
            <div class="form-text">Select a shift type to auto-fill time fields</div>
          </div>

          <!-- Department Selection -->
          <div class="mb-3">
            <label for="department_id" class="form-label">Department</label>
            <select class="form-select" id="department_id" name="department_id">
              <option value="">Select Department</option>
              @php
                // Use Laravel Eloquent Model for departments
                try {
                  $departments = DB::table('departments')
                                  ->where('status', 'active')
                                  ->orderBy('name', 'ASC')
                                  ->get();
                } catch(Exception $e) {
                  $departments = collect([]);
                }
              @endphp
              
              @forelse($departments as $department)
                <option value="{{ $department->id }}">
                  {{ $department->name }}
                  @if(isset($department->location))
                    - {{ $department->location }}
                  @endif
                </option>
              @empty
                <option value="1">Customer Service</option>
                <option value="2">Operations</option>
                <option value="3">Administration</option>
              @endforelse
            </select>
          </div>
          
          <!-- Date and Time Selection -->
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="requested_date" class="form-label">Requested Date *</label>
                <input type="date" class="form-control" id="requested_date" name="requested_date" 
                       required min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d', strtotime('+90 days')) }}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="priority" class="form-label">Priority Level</label>
                <select class="form-select" id="priority" name="priority">
                  <option value="normal">Normal</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="requested_start_time" class="form-label">Start Time</label>
                <input type="time" class="form-control" id="requested_start_time" name="requested_start_time">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="requested_end_time" class="form-label">End Time</label>
                <input type="time" class="form-control" id="requested_end_time" name="requested_end_time">
              </div>
            </div>
          </div>

          <!-- Additional Options -->
          <div class="mb-3">
            <label for="location" class="form-label">Work Location</label>
            <select class="form-select" id="location" name="location">
              <option value="">Select Location</option>
              @php
                $locations = [
                  'main_office' => 'Main Office - Downtown',
                  'branch_office' => 'Branch Office - Uptown', 
                  'remote' => 'Remote Work',
                  'client_site' => 'Client Site',
                  'field_work' => 'Field Work'
                ];
              @endphp
              @foreach($locations as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          
          <!-- Reason and Notes -->
          <div class="mb-3">
            <label for="reason" class="form-label">Reason for Request *</label>
            <textarea class="form-control" id="reason" name="reason" rows="3" required 
                      placeholder="Please explain the reason for this shift request..."></textarea>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Additional Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="2" 
                      placeholder="Any additional information or special requirements..."></textarea>
          </div>

          <!-- Estimated Hours Display -->
          <div class="alert alert-info" id="hoursEstimate" style="display: none;">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Estimated Hours:</strong> <span id="estimatedHours">0</span> hours
            <br><strong>Estimated Pay:</strong> $<span id="estimatedPay">0.00</span>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeShiftRequestModal()">
            <i class="bi bi-x-lg me-2"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-2"></i>Submit Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Shift Details Modal -->
<div class="modal fade" id="viewShiftModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-eye me-2"></i>Shift Details
        </h5>
        <button type="button" class="btn-close" onclick="closeShiftRequestModal()"></button>
      </div>
      <div class="modal-body">
        <div id="shiftDetailsContent">
          <!-- Content will be loaded dynamically -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@endsection


@section('scripts')
<script>
// Pure Laravel Modal Functions - No External Dependencies
function openShiftRequestModal() {
  const modal = document.getElementById('shiftRequestModal');
  if (modal) {
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');
    document.body.style.overflow = 'hidden';
    
    // Add backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'modalBackdrop';
    document.body.appendChild(backdrop);
    
    console.log('Shift request modal opened');
  }
}

function closeShiftRequestModal() {
  const modal = document.getElementById('shiftRequestModal');
  const backdrop = document.getElementById('modalBackdrop');
  
  if (modal) {
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    
    if (backdrop) {
      backdrop.remove();
    }
    
    // Reset form
    const form = modal.querySelector('form');
    if (form) {
      form.reset();
    }
    
    console.log('Shift request modal closed');
  }
}

function refreshShiftData() {
  location.reload();
}

// Auto-fill time fields when shift type is selected
document.addEventListener('DOMContentLoaded', function() {
  const shiftTypeSelect = document.getElementById('shift_type_id');
  const startTimeInput = document.getElementById('requested_start_time');
  const endTimeInput = document.getElementById('requested_end_time');
  const hoursEstimate = document.getElementById('hoursEstimate');
  const estimatedHours = document.getElementById('estimatedHours');
  const estimatedPay = document.getElementById('estimatedPay');

  if (shiftTypeSelect) {
    shiftTypeSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      
      if (selectedOption.value) {
        const startTime = selectedOption.getAttribute('data-start');
        const endTime = selectedOption.getAttribute('data-end');
        const hourlyRate = parseFloat(selectedOption.getAttribute('data-rate')) || 0;
        
        if (startTime && endTime) {
          startTimeInput.value = startTime;
          endTimeInput.value = endTime;
          
          // Calculate estimated hours and pay
          const start = new Date(`2000-01-01 ${startTime}`);
          const end = new Date(`2000-01-01 ${endTime}`);
          
          // Handle overnight shifts
          if (end < start) {
            end.setDate(end.getDate() + 1);
          }
          
          const hours = (end - start) / (1000 * 60 * 60);
          const pay = hours * hourlyRate;
          
          estimatedHours.textContent = hours.toFixed(1);
          estimatedPay.textContent = pay.toFixed(2);
          hoursEstimate.style.display = 'block';
        }
      } else {
        startTimeInput.value = '';
        endTimeInput.value = '';
        hoursEstimate.style.display = 'none';
      }
    });
  }

  // Simple filter functionality
  const statusFilter = document.getElementById('statusFilter');
  if (statusFilter) {
    statusFilter.addEventListener('change', function() {
      const filterValue = this.value;
      const rows = document.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        if (filterValue === '') {
          row.style.display = '';
        } else {
          const statusBadge = row.querySelector('.badge-clean');
          if (statusBadge) {
            const status = statusBadge.textContent.toLowerCase();
            row.style.display = status.includes(filterValue) ? '' : 'none';
          }
        }
      });
    });
  }

  // Form validation
  const shiftForm = document.querySelector('#shiftRequestModal form');
  if (shiftForm) {
    shiftForm.addEventListener('submit', function(e) {
      const requestType = document.getElementById('request_type').value;
      const reason = document.getElementById('reason').value;
      
      if (!requestType || !reason.trim()) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return false;
      }
      
      // Show loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting...';
      submitBtn.disabled = true;
    });
  }
});

// Function to view shift details
function viewShiftDetails(shiftId) {
  // This would typically make an AJAX call to get shift details
  const modal = new bootstrap.Modal(document.getElementById('viewShiftModal'));
  const content = document.getElementById('shiftDetailsContent');
  
  content.innerHTML = `
    <div class="text-center">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading shift details...</p>
    </div>
  `;
  
  modal.show();
  
  // Simulate loading shift details (replace with actual AJAX call)
  setTimeout(() => {
    content.innerHTML = `
      <div class="row">
        <div class="col-md-6">
          <h6>Shift Information</h6>
          <p><strong>Date:</strong> January 15, 2024</p>
          <p><strong>Time:</strong> 9:00 AM - 5:00 PM</p>
          <p><strong>Type:</strong> Morning Shift</p>
        </div>
        <div class="col-md-6">
          <h6>Status</h6>
          <p><strong>Status:</strong> <span class="badge bg-success">Approved</span></p>
          <p><strong>Location:</strong> Main Office</p>
          <p><strong>Department:</strong> Customer Service</p>
        </div>
      </div>
      <hr>
      <h6>Notes</h6>
      <p>Regular shift assignment for customer service department.</p>
    `;
  }, 1000);
}
</script>
@endsection
