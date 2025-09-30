@extends('employee_ess_modules.partials.employee_layout')

@section('title', 'Leave Applications & Balance')

@section('content')
<!-- Page Header -->
<div class="page-header-container fade-in">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img" style="height: 40px;">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Leave Applications & Balance</h2>
        <p class="text-muted mb-0">Manage your leave requests and view balance</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Leave Applications</li>
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
    <p class="text-muted mb-0">Apply for leave or check your balance</p>
  </div>
  <div class="ess-card-body">
    <div class="d-flex gap-3 flex-wrap">
      <button class="btn btn-jetlouge" onclick="openWorkingModal('create-leave-modal')">
        <i class="bi bi-calendar-plus me-2"></i>Apply for Leave
      </button>
      <button class="btn btn-jetlouge-outline" onclick="refreshLeaveData()">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
      </button>
    </div>
  </div>
</div>

<!-- Leave Balance Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stats-card slide-up">
      <div class="stats-icon primary">
        <i class="bi bi-calendar-check"></i>
      </div>
      <div class="stats-value">15</div>
      <p class="stats-label">Annual Leave</p>
      <p class="stats-sublabel">Days Available</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.1s;">
      <div class="stats-icon success">
        <i class="bi bi-heart-pulse"></i>
      </div>
      <div class="stats-value">5</div>
      <p class="stats-label">Sick Leave</p>
      <p class="stats-sublabel">Days Available</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.2s;">
      <div class="stats-icon warning">
        <i class="bi bi-clock-history"></i>
      </div>
      <div class="stats-value">2</div>
      <p class="stats-label">Pending Requests</p>
      <p class="stats-sublabel">Awaiting Approval</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card slide-up" style="animation-delay: 0.3s;">
      <div class="stats-icon info">
        <i class="bi bi-calendar-event"></i>
      </div>
      <div class="stats-value">8</div>
      <p class="stats-label">Used This Year</p>
      <p class="stats-sublabel">Total Days</p>
    </div>
  </div>
</div>

<!-- Leave Requests List -->
<div class="ess-card slide-up" style="animation-delay: 0.4s;">
  <div class="ess-card-header">
    <h5 class="ess-card-title">
      <i class="bi bi-list-ul"></i>
      My Leave Requests
    </h5>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" id="statusFilter">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>
  </div>
  <div class="ess-card-body p-0">
    <div class="table-responsive">
      <table class="table table-clean mb-0">
        <thead>
          <tr>
            <th>Leave Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Days</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Applied</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            // Direct database connection for leave requests (like leave_management.blade.php)
            try {
                $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Auto-create leave_requests table if not exists
                $pdo->exec("CREATE TABLE IF NOT EXISTS leave_requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id INT NOT NULL,
                    leave_type_id INT,
                    start_date DATE NOT NULL,
                    end_date DATE NOT NULL,
                    days_requested INT,
                    reason TEXT,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    applied_date DATE,
                    approved_date DATE,
                    approved_by INT,
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                
                // Auto-create leave_types table if not exists (reuse from leave_management)
                $pdo->exec("CREATE TABLE IF NOT EXISTS leave_types (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    code VARCHAR(10) NOT NULL UNIQUE,
                    description TEXT,
                    max_days_per_year INT DEFAULT 30,
                    carry_forward BOOLEAN DEFAULT FALSE,
                    requires_approval BOOLEAN DEFAULT TRUE,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                
                // Insert sample leave types if table is empty
                $countStmt = $pdo->query("SELECT COUNT(*) as count FROM leave_types");
                $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($count == 0) {
                    $pdo->exec("INSERT INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval) VALUES
                        ('Annual Leave', 'AL', 'Annual vacation leave', 21, TRUE, TRUE),
                        ('Sick Leave', 'SL', 'Medical sick leave', 10, FALSE, FALSE),
                        ('Emergency Leave', 'EL', 'Emergency family leave', 5, FALSE, TRUE),
                        ('Maternity Leave', 'ML', 'Maternity leave', 90, FALSE, TRUE),
                        ('Paternity Leave', 'PL', 'Paternity leave', 7, FALSE, TRUE)");
                }
                
                // Get current employee's leave requests
                $employeeId = Auth::guard('employee')->user()->id ?? 1;
                $stmt = $pdo->prepare("SELECT lr.*, lt.name as leave_type_name 
                    FROM leave_requests lr 
                    LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id 
                    WHERE lr.employee_id = ? 
                    ORDER BY lr.created_at DESC");
                $stmt->execute([$employeeId]);
                $directLeaves = $stmt->fetchAll(PDO::FETCH_OBJ);
                
                echo "<!-- Direct DB Query: Found " . count($directLeaves) . " leave requests -->"; 
            } catch (Exception $e) {
                $directLeaves = [];
                echo "<!-- Direct DB Query Error: " . $e->getMessage() . " -->";
            }
          @endphp
          
          @if(count($directLeaves) > 0)
            @foreach($directLeaves as $leave)
            <tr>
              <td>{{ $leave->leave_type_name ?? 'Annual Leave' }}</td>
              <td>{{ isset($leave->start_date) ? date('M d, Y', strtotime($leave->start_date)) : 'N/A' }}</td>
              <td>{{ isset($leave->end_date) ? date('M d, Y', strtotime($leave->end_date)) : 'N/A' }}</td>
              <td>{{ $leave->days_requested ?? '1' }} days</td>
              <td>{{ isset($leave->reason) ? Str::limit($leave->reason, 30) : 'Personal' }}</td>
              <td>
                @php
                  $status = $leave->status ?? 'pending';
                  $badgeClass = match($status) {
                    'approved' => 'success',
                    'pending' => 'warning', 
                    'rejected' => 'danger',
                    default => 'secondary'
                  };
                @endphp
                <span class="badge bg-{{ $badgeClass }}">
                  {{ ucfirst($status) }}
                </span>
              </td>
              <td>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-primary" 
                          onclick="openWorkingModal('view-leave-modal'); loadLeaveDetails({{ $leave->id ?? 0 }})" title="View">
                    <i class="fas fa-eye"></i>
                  </button>
                  @if(($leave->status ?? 'pending') === 'pending')
                  <form method="GET" action="/leave-requests/{{ $leave->id ?? 0 }}/edit" style="display: inline;">
                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                  </form>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          @else
          <tr>
            <td colspan="7" class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
              No leave applications found. <a href="#" data-modal-id="create-leave-modal" class="text-primary">Apply for your first leave</a>
              <br><small class="text-muted">Direct DB found: {{ count($directLeaves) }} leave requests</small>
            </td>
          </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Enhanced Create Leave Application Modal -->
<div class="working-modal" id="create-leave-modal">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('create-leave-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">New Leave Request</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-leave-modal')">&times;</button>
            </div>
            <form id="create-leave-form" method="POST" action="{{ route('leave.requests.store') }}">
                @csrf
                <input type="hidden" name="employee_id" value="{{ Auth::guard('employee')->user()->id ?? 1 }}">
                <div class="working-modal-body">
                    <div class="mb-3">
                        <label for="leave-type-id" class="form-label">Leave Type</label>
                        <select class="form-select" id="leave-type-id" name="leave_type_id" required onchange="updateLeaveTypeInfo()">
                            <option value="">Select Leave Type</option>
                            @php
                                // Get leave types with balance info from database
                                try {
                                    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    
                                    // Get leave types and calculate available days
                                    $employeeId = Auth::guard('employee')->user()->id ?? 1;
                                    $stmt = $pdo->prepare("
                                        SELECT lt.*, 
                                               COALESCE(lb.allocated_days, lt.max_days_per_year) as allocated_days,
                                               COALESCE(lb.used_days, 0) as used_days,
                                               (COALESCE(lb.allocated_days, lt.max_days_per_year) - COALESCE(lb.used_days, 0)) as available_days
                                        FROM leave_types lt
                                        LEFT JOIN leave_balances lb ON lt.id = lb.leave_type_id AND lb.employee_id = ?
                                        WHERE lt.is_active = 1 
                                        ORDER BY lt.name
                                    ");
                                    $stmt->execute([$employeeId]);
                                    $modalLeaveTypes = $stmt->fetchAll(PDO::FETCH_OBJ);
                                } catch (Exception $e) {
                                    // Fallback to basic leave types
                                    try {
                                        $stmt = $pdo->query("SELECT *, max_days_per_year as available_days FROM leave_types WHERE is_active = 1 ORDER BY name");
                                        $modalLeaveTypes = $stmt->fetchAll(PDO::FETCH_OBJ);
                                    } catch (Exception $e2) {
                                        $modalLeaveTypes = [];
                                    }
                                }
                            @endphp
                            @if(count($modalLeaveTypes) > 0)
                                @foreach($modalLeaveTypes as $type)
                                    <option value="{{ $type->id }}" 
                                            data-available-days="{{ $type->available_days ?? $type->max_days_per_year }}" 
                                            data-max-days="{{ $type->max_days_per_year }}">
                                        {{ $type->name }} ({{ $type->available_days ?? $type->max_days_per_year }} days available)
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>No leave types available</option>
                            @endif
                        </select>
                        <div class="form-text" id="leave-type-info" style="display: none;">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="leave-type-details">Select a leave type to see details</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leave-start-date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="leave-start-date" name="start_date" required 
                                       min="{{ date('Y-m-d') }}" onchange="calculateLeaveDays()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leave-end-date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="leave-end-date" name="end_date" required onchange="calculateLeaveDays()">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="days-requested" class="form-label">Days Requested</label>
                        <input type="number" class="form-control" id="days-requested" name="days_requested" min="1" readonly>
                        <div class="form-text">
                            <i class="fas fa-calendar me-1"></i>
                            <span id="calculated-days">Days will be calculated automatically</span>
                        </div>
                        <div class="alert alert-warning mt-2" id="days-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <span id="warning-message">Warning message</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="leave-reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="leave-reason" name="reason" rows="3" required 
                                  placeholder="Please provide a detailed reason for your leave request..."></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="urgent-request" name="is_urgent" value="1">
                            <label class="form-check-label" for="urgent-request">
                                <i class="fas fa-exclamation-circle text-warning me-1"></i>
                                Mark as urgent request
                            </label>
                        </div>
                        <div class="form-text">Urgent requests require immediate supervisor approval</div>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-leave-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submit-leave-btn">
                        <i class="fas fa-paper-plane me-1"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Leave Details Modal -->
<div class="working-modal" id="view-leave-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-leave-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Leave Request Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-leave-modal')">&times;</button>
            </div>
            <div class="working-modal-body" id="leave-details-content">
                <p>Loading leave request details...</p>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-leave-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Direct modal functions that work immediately
function openWorkingModal(modalId) {
    console.log('Opening modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.zIndex = '9999';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        document.body.style.overflow = 'hidden';
        console.log('Modal opened successfully');
    } else {
        console.error('Modal not found:', modalId);
        alert('Modal not found: ' + modalId);
    }
}

function closeWorkingModal(modalId) {
    console.log('Closing modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        console.log('Modal closed successfully');
    }
}

// Make functions globally available
window.openWorkingModal = openWorkingModal;
window.closeWorkingModal = closeWorkingModal;

// Close modal when clicking backdrop
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('working-modal-backdrop')) {
        const modal = e.target.closest('.working-modal');
        if (modal) {
            closeWorkingModal(modal.id);
        }
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.working-modal[style*="flex"]');
        if (openModal) {
            closeWorkingModal(openModal.id);
        }
    }
});

// Update leave type information
function updateLeaveTypeInfo() {
    const leaveTypeSelect = document.getElementById('leave-type-id');
    const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
    const leaveTypeInfo = document.getElementById('leave-type-info');
    const leaveTypeDetails = document.getElementById('leave-type-details');
    
    if (selectedOption.value && selectedOption.dataset.availableDays) {
        const availableDays = selectedOption.dataset.availableDays;
        const maxDays = selectedOption.dataset.maxDays;
        
        leaveTypeDetails.innerHTML = `
            <strong>${selectedOption.text.split(' (')[0]}</strong><br>
            Available: ${availableDays} days | Maximum per year: ${maxDays} days
        `;
        leaveTypeInfo.style.display = 'block';
    } else {
        leaveTypeInfo.style.display = 'none';
    }
    
    // Recalculate days if dates are already selected
    calculateLeaveDays();
}

// Calculate leave days and show warnings
function calculateLeaveDays() {
    const startDateInput = document.getElementById('leave-start-date');
    const endDateInput = document.getElementById('leave-end-date');
    const daysInput = document.getElementById('days-requested');
    const calculatedDays = document.getElementById('calculated-days');
    const warning = document.getElementById('days-warning');
    const warningMessage = document.getElementById('warning-message');
    const leaveTypeSelect = document.getElementById('leave-type-id');
    
    if (startDateInput.value && endDateInput.value) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (endDate >= startDate) {
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            daysInput.value = diffDays;
            
            // Calculate duration display
            let durationText = `${diffDays} day${diffDays !== 1 ? 's' : ''}`;
            if (diffDays > 7) {
                const weeks = Math.floor(diffDays / 7);
                const remainingDays = diffDays % 7;
                if (weeks > 0) {
                    durationText += ` (${weeks} week${weeks !== 1 ? 's' : ''}${remainingDays > 0 ? ` and ${remainingDays} day${remainingDays !== 1 ? 's' : ''}` : ''})`;
                }
            }
            
            calculatedDays.innerHTML = `<strong>${durationText}</strong> from ${startDate.toLocaleDateString()} to ${endDate.toLocaleDateString()}`;
            
            // Check available days and show warning if needed
            const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
            if (selectedOption.value && selectedOption.dataset.availableDays) {
                const availableDays = parseInt(selectedOption.dataset.availableDays);
                
                if (diffDays > availableDays) {
                    warningMessage.textContent = `You are requesting ${diffDays} days but only have ${availableDays} days available for ${selectedOption.text.split(' (')[0]}.`;
                    warning.style.display = 'block';
                } else {
                    warning.style.display = 'none';
                }
            } else {
                warning.style.display = 'none';
            }
            
            // Update end date minimum
            endDateInput.min = startDateInput.value;
        } else {
            calculatedDays.textContent = 'End date must be after start date';
            daysInput.value = '';
            warning.style.display = 'none';
        }
    } else {
        calculatedDays.textContent = 'Days will be calculated automatically';
        daysInput.value = '';
        warning.style.display = 'none';
    }
}

// Form validation and submission
function validateLeaveForm() {
    const leaveTypeId = document.getElementById('leave-type-id').value;
    const startDate = document.getElementById('leave-start-date').value;
    const endDate = document.getElementById('leave-end-date').value;
    const reason = document.getElementById('leave-reason').value.trim();
    
    if (!leaveTypeId) {
        showAlert('Please select a leave type.', 'error');
        return false;
    }
    
    if (!startDate) {
        showAlert('Please select a start date.', 'error');
        return false;
    }
    
    if (!endDate) {
        showAlert('Please select an end date.', 'error');
        return false;
    }
    
    if (new Date(endDate) < new Date(startDate)) {
        showAlert('End date cannot be before start date.', 'error');
        return false;
    }
    
    if (!reason) {
        showAlert('Please provide a reason for your leave request.', 'error');
        return false;
    }
    
    if (reason.length < 10) {
        showAlert('Please provide a more detailed reason (at least 10 characters).', 'error');
        return false;
    }
    
    return true;
}

// Enhanced DOMContentLoaded with all functionality
document.addEventListener('DOMContentLoaded', function() {
    // Form submission with enhanced validation
    const leaveForm = document.getElementById('create-leave-form');
    if (leaveForm) {
        leaveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateLeaveForm()) {
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submit-leave-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
            submitBtn.disabled = true;
            
            // Simulate form submission (replace with actual submission)
            setTimeout(() => {
                showAlert('Leave application submitted successfully! You will receive a confirmation email shortly.', 'success');
                closeWorkingModal('create-leave-modal');
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                // Optionally refresh the page to show new request
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }, 1500);
        });
    }
    
    // Set minimum date to today for start date
    const startDateInput = document.getElementById('leave-start-date');
    if (startDateInput) {
        const today = new Date().toISOString().split('T')[0];
        startDateInput.min = today;
    }
});

// Note: Working Modal CSS is now provided by working-modal-ess.css

// Load leave details for view modal
function loadLeaveDetails(leaveId) {
    // Load leave details via server-side route
    fetch(`/leave-requests/${leaveId}/view`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('leave-details-content').innerHTML = `
                <div class="row">
                    <div class="col-md-6"><strong>Leave Type:</strong> ${data.leave_type_name || 'N/A'}</div>
                    <div class="col-md-6"><strong>Status:</strong> <span class="badge bg-${data.status === 'approved' ? 'success' : data.status === 'pending' ? 'warning' : 'danger'}">${data.status || 'pending'}</span></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Start Date:</strong> ${data.start_date || 'N/A'}</div>
                    <div class="col-md-6"><strong>End Date:</strong> ${data.end_date || 'N/A'}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Days Requested:</strong> ${data.days_requested || '1'} days</div>
                    <div class="col-md-6"><strong>Applied Date:</strong> ${data.created_at || 'N/A'}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-12"><strong>Reason:</strong><br>${data.reason || 'No reason provided'}</div>
                </div>
                ${data.notes ? `<div class="row mt-2"><div class="col-12"><strong>Notes:</strong><br>${data.notes}</div></div>` : ''}
            `;
        })
        .catch(error => {
            document.getElementById('leave-details-content').innerHTML = '<p class="text-danger">Error loading leave request details.</p>';
        });
}

function editLeave(leaveId, leaveType, startDate, endDate, reason) {
    // Pre-populate the form with existing data
    document.getElementById('leave-type-id').value = leaveType;
    document.getElementById('leave-start-date').value = startDate;
    document.getElementById('leave-end-date').value = endDate;
    document.getElementById('leave-reason').value = reason;
    
    // Update modal title
    document.querySelector('.working-modal-title').textContent = 'Edit Leave Request';
    
    // Calculate days
    calculateLeaveDays();
    
    // Open modal
    openWorkingModal('create-leave-modal');
    
    showAlert(`Editing leave request #${leaveId}`, 'info');
}

function refreshLeaveData() {
  // Show loading state
  const btn = event.target;
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Refreshing...';
  btn.disabled = true;
  
  // Simulate refresh
  setTimeout(() => {
    btn.innerHTML = originalText;
    btn.disabled = false;
    showAlert('Leave data refreshed successfully!', 'success');
    // Optionally reload the page to get fresh data
    window.location.reload();
  }, 1500);
}

function viewLeaveRequest(id) {
  viewLeaveDetails(id);
}

function cancelLeaveRequest(id) {
  if (confirm('Are you sure you want to cancel this leave request?')) {
    showAlert(`Leave request ${id} cancelled successfully`, 'success');
    // TODO: Implement actual cancellation logic
    setTimeout(() => {
      window.location.reload();
    }, 1500);
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

// Enhanced filter functionality
document.addEventListener('DOMContentLoaded', function() {
  const statusFilter = document.getElementById('statusFilter');
  
  if (statusFilter) {
    statusFilter.addEventListener('change', function() {
      const filterValue = this.value;
      const rows = document.querySelectorAll('tbody tr');
      let visibleCount = 0;
      
      rows.forEach(row => {
        // Skip empty state row
        if (row.querySelector('.text-center')) {
          return;
        }
        
        if (filterValue === '') {
          row.style.display = '';
          visibleCount++;
        } else {
          const statusBadge = row.querySelector('.badge');
          if (statusBadge) {
            const status = statusBadge.textContent.toLowerCase().trim();
            if (status.includes(filterValue)) {
              row.style.display = '';
              visibleCount++;
            } else {
              row.style.display = 'none';
            }
          }
        }
      });
      
      // Show filter feedback
      if (filterValue) {
        showAlert(`Showing ${visibleCount} requests with status: ${filterValue}`, 'info');
      }
    });
  }
});

// Global calculateDays function for backward compatibility
function calculateDays() {
  calculateLeaveDays();
}
</script>

<style>
/* Working Modal CSS - Essential for form functionality */
.working-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
}

.working-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: transparent;
}

.working-modal-dialog {
    position: relative;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 2001;
}

.working-modal-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.working-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.working-modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 500;
}

.working-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.working-modal-close:before {
    content: '×';
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

/* Ensure form elements are interactive */
.working-modal input,
.working-modal select,
.working-modal textarea,
.working-modal button {
    pointer-events: auto !important;
}

.working-modal .form-control,
.working-modal .form-select {
    pointer-events: auto !important;
    position: relative;
    z-index: auto;
}
</style>
@endsection
