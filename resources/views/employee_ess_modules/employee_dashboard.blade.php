<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jetlouge Travels - Employee Self-Service Portal</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  
  <style>
    :root {
      --jetlouge-primary: #1e3a8a;
      --jetlouge-secondary: #3b82f6;
      --jetlouge-accent: #fbbf24;
      --jetlouge-light: #dbeafe;
      --jetlouge-dark: #1e40af;
    }
    
    .time-clock-card {
      background: linear-gradient(135deg, var(--jetlouge-primary), var(--jetlouge-secondary));
      color: white;
      border-radius: 15px;
      padding: 2rem;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .clock-display {
      font-size: 3rem;
      font-weight: bold;
      margin: 1rem 0;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .status-badge {
      padding: 0.5rem 1rem;
      border-radius: 25px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .btn-clock {
      padding: 1rem 2rem;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      margin: 0.5rem;
      min-width: 150px;
      transition: all 0.3s ease;
    }
    
    .btn-clock:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .module-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      height: 100%;
    }
    
    .module-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .module-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }

    /* Working Modal CSS for ESS */
    .working-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 2000;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, 0.5);
    }

    .working-modal-backdrop {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
    }

    .working-modal-dialog {
      position: relative;
      z-index: 2001;
      max-width: 500px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
    }

    .working-modal-content {
      background: white;
      border-radius: 8px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .working-modal-header {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #dee2e6;
      display: flex;
      justify-content: between;
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
      margin-left: auto;
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

    .working-modal .form-control,
    .working-modal .form-select {
      pointer-events: auto !important;
      background-color: white !important;
      border: 1px solid #ced4da !important;
    }
  </style>

</head>
<body style="background-color: #f8f9fa !important;">

  <!-- Employee Topbar -->
  @include('employee_ess_modules.partials.employee_topbar')

  <!-- Employee Sidebar -->
  @include('employee_ess_modules.partials.employee_sidebar')

  <!-- Overlay for mobile -->
  <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

  <!-- Main Content -->
  <main id="main-content">
    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Jetlouge Travels</h2>
            <p class="text-muted mb-0">
              @php
                $hour = (int)date('H');
                if ($hour >= 5 && $hour < 12) {
                  $greeting = 'Good morning';
                } elseif ($hour >= 12 && $hour < 18) {
                  $greeting = 'Good afternoon';
                } else {
                  $greeting = 'Good evening';
                }
              @endphp
              <h1>
    {{ $greeting }}, {{ trim(Auth::guard('employee')->user()->first_name . ' ' . Auth::guard('employee')->user()->last_name) ?: 'Employee' }}!
</h1>
            </p>
          </div>
        </div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Employee Dashboard</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>

    <!-- 1. Time and Attendance System Dashboard -->
    <div class="row mb-4">
      <div class="col-lg-6">
        <div class="time-clock-card">
          <h4 class="mb-3"><i class="bi bi-clock me-2"></i>Time & Attendance</h4>
          <div class="clock-display" id="currentTime">--:--:--</div>
          <div class="mb-3">
            <span class="status-badge" id="clockStatus" style="background: rgba(255,255,255,0.2);">Not Clocked In</span>
          </div>
          <div class="d-flex justify-content-center flex-wrap">
            <button class="btn btn-success btn-clock" id="clockInBtn" onclick="clockIn()">
              <i class="bi bi-play-circle me-2"></i>Clock In
            </button>
            <button class="btn btn-danger btn-clock" id="clockOutBtn" onclick="clockOut()" disabled>
              <i class="bi bi-stop-circle me-2"></i>Clock Out
            </button>
          </div>
          <div class="mt-3">
            <small>Today's Hours: <span id="todayHours" class="fw-bold">0:00</span></small>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-header bg-transparent border-0">
            <h5 class="card-title mb-0"><i class="bi bi-calendar-check me-2"></i>Attendance Log</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Hours</th>
                  </tr>
                </thead>
                <tbody id="attendanceLog">
                  <tr>
                    <td colspan="4" class="text-center text-muted">Loading...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

<!-- HR3 System Modules -->
<div class="row g-4 mb-4">
  <!-- 2. Timesheet Management -->
  <div class="col-lg-6 col-md-6">
    <div class="card module-card">
      <div class="card-body text-center">
        <div class="module-icon bg-primary bg-opacity-10 text-primary mx-auto">
          <i class="bi bi-file-earmark-text"></i>
        </div>
        <h5 class="card-title">Timesheet Management</h5>
        <p class="card-text text-muted">Submit timesheets, track overtime, and manage work hours</p>
        <div class="d-grid gap-2">
          <a href="{{ route('employee.timesheet') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Submit Timesheet
          </a>
          <a href="{{ route('employee.timesheet.history') }}" class="btn btn-outline-primary">
            <i class="bi bi-clock-history me-2"></i>View History
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- 3. Shift and Schedule Management -->
  <div class="col-lg-6 col-md-6">
    <div class="card module-card">
      <div class="card-body text-center">
        <div class="module-icon bg-success bg-opacity-10 text-success mx-auto">
          <i class="bi bi-calendar3"></i>
        </div>
        <h5 class="card-title">Shift & Schedule</h5>
        <p class="card-text text-muted">View schedules, request shift swaps, and manage availability</p>
        <div class="d-grid gap-2">
          <a href="{{ route('employee.schedule') }}" class="btn btn-success">
            <i class="bi bi-calendar-check me-2"></i>View Schedule
          </a>
          <a href="{{ route('employee.shift.request') }}" class="btn btn-outline-success">
            <i class="bi bi-arrow-left-right me-2"></i>Request Swap
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- 4. Leave Management -->
  <div class="col-lg-6 col-md-6">
    <div class="card module-card">
      <div class="card-body text-center">
        <div class="module-icon bg-warning bg-opacity-10 text-warning mx-auto">
          <i class="bi bi-calendar-x"></i>
        </div>
        <h5 class="card-title">Leave Management</h5>
        <p class="card-text text-muted">Apply for leave, check balances, and track requests</p>
        <div class="d-grid gap-2">
          <a href="{{ route('employee.leave_applications.index') }}" class="btn btn-warning">
            <i class="bi bi-calendar-plus me-2"></i>Apply for Leave
          </a>
          <a href="{{ route('employee.leave_applications.index') }}" class="btn btn-outline-warning">
            <i class="bi bi-pie-chart me-2"></i>Check Balance
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- 5. Claims and Reimbursement -->
  <div class="col-lg-6 col-md-6">
    <div class="card module-card">
      <div class="card-body text-center">
        <div class="module-icon bg-info bg-opacity-10 text-info mx-auto">
          <i class="bi bi-receipt-cutoff"></i>
        </div>
        <h5 class="card-title">Claims & Reimbursement</h5>
        <p class="card-text text-muted">Submit expense claims and track reimbursements</p>
        <div class="d-grid gap-2">
          <a href="{{ route('employee.claims.create') }}" class="btn btn-info">
            <i class="bi bi-plus-circle me-2"></i>Submit Claim
          </a>
          <a href="{{ route('employee.claims.history') }}" class="btn btn-outline-info">
            <i class="bi bi-list-check me-2"></i>Track Claims
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
            <i class="bi bi-calendar-event"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0" id="pendingLeaveCount">{{ $pendingLeaveRequests ?? 0 }}</h3>
            <p class="text-muted mb-0 small">Pending Leave Requests</p>
            <small class="text-success">This month</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
            <i class="bi bi-clock-history"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0" id="attendanceRate">{{ $attendanceRate ?? 95 }}%</h3>
            <p class="text-muted mb-0 small">Attendance This Month</p>
            <small class="text-success">Excellent</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
            <i class="bi bi-file-earmark-text"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0" id="pendingTimesheets">{{ $pendingTimesheets ?? 2 }}</h3>
            <p class="text-muted mb-0 small">Pending Timesheets</p>
            <small class="text-warning">Needs submission</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
            <i class="bi bi-receipt"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0" id="pendingClaims">{{ $pendingClaims ?? 1 }}</h3>
            <p class="text-muted mb-0 small">Pending Claims</p>
            <small class="text-info">Under review</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



  </main>

  <!-- Leave Application Modal -->
  <div class="working-modal" id="leaveApplicationModal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('leaveApplicationModal')"></div>
    <div class="working-modal-dialog">
      <div class="working-modal-content">
        <div class="working-modal-header">
          <h5 class="working-modal-title">Apply for Leave</h5>
          <button type="button" class="working-modal-close" onclick="closeWorkingModal('leaveApplicationModal')">&times;</button>
        </div>
        <form id="leaveApplicationForm" method="POST" action="{{ route('employee.leave.store') }}">
          @csrf
          <div class="working-modal-body">
            <div class="mb-3">
              <label for="leaveType" class="form-label">Leave Type</label>
              <select class="form-select" id="leaveType" name="leave_type" required>
                <option value="">Select leave type</option>
                <option value="Vacation Leave">Vacation Leave</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Emergency Leave">Emergency Leave</option>
                <option value="Maternity/Paternity Leave">Maternity/Paternity Leave</option>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="startDate" class="form-label">Start Date</label>
                  <input type="date" class="form-control" id="startDate" name="start_date" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="endDate" class="form-label">End Date</label>
                  <input type="date" class="form-control" id="endDate" name="end_date" required>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label for="leaveReason" class="form-label">Reason</label>
              <textarea class="form-control" id="leaveReason" name="reason" rows="3" required></textarea>
            </div>
          </div>
          <div class="working-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('leaveApplicationModal')">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit Application</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Profile Update Modal -->
  <div class="working-modal" id="profileUpdateModal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('profileUpdateModal')"></div>
    <div class="working-modal-dialog" style="max-width: 800px;">
      <div class="working-modal-content">
        <div class="working-modal-header">
          <h5 class="working-modal-title">Update My Profile</h5>
          <button type="button" class="working-modal-close" onclick="closeWorkingModal('profileUpdateModal')">&times;</button>
        </div>
        <form id="profileUpdateForm" method="POST" action="{{ route('employee.profile.update') }}" enctype="multipart/form-data">
          @csrf
          <div class="working-modal-body">
            <div class="row">
              <div class="col-md-4 text-center">
                <div class="mb-3">
                  @php
                    $firstName = $employee->first_name ?? 'Unknown';
                    $lastName = $employee->last_name ?? 'Employee';
                    
                    // Profile picture logic - consistent with other HR modules
                    $profilePicUrl = '';
                    if ($employee->profile_picture) {
                      $profilePicUrl = asset('storage/' . $employee->profile_picture);
                    } else {
                      // Fallback to UI Avatars with consistent color scheme
                      $employeeId = $employee->employee_id ?? 'EMP';
                      $initials = substr($firstName, 0, 1) . substr($lastName, 0, 1);
                      $colors = ['FF6B6B', '4ECDC4', '45B7D1', '96CEB4', 'FFEAA7', 'DDA0DD', 'FFB347', '87CEEB'];
                      $colorIndex = crc32($employeeId) % count($colors);
                      $bgColor = $colors[$colorIndex];
                      $profilePicUrl = "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background={$bgColor}&color=ffffff&size=128&bold=true";
                    }
                  @endphp
                  
                  <img id="profilePreview" 
                       src="{{ $profilePicUrl }}" 
                       class="rounded-circle mb-3" 
                       width="120" 
                       height="120" 
                       style="object-fit: cover; border: 3px solid #e9ecef;"
                       alt="Profile Picture">
                  <div>
                    <label for="profilePicture" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-camera me-1"></i> Change Photo
                    </label>
                    <input type="file" id="profilePicture" name="profile_picture" class="d-none" accept="image/*">
                  </div>
                  <small class="text-muted d-block mt-2">JPG, PNG, GIF up to 2MB</small>
                </div>
              </div>
              <div class="col-md-8">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="firstName" class="form-label">First Name*</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" value="{{ $employee->first_name }}" required>
                  </div>
                  <div class="col-md-6">
                    <label for="lastName" class="form-label">Last Name*</label>
                    <input type="text" class="form-control" id="lastName" name="last_name" value="{{ $employee->last_name }}" required>
                  </div>
                  <div class="col-md-6">
                    <label for="email" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ $employee->email }}" required>
                  </div>
                  <div class="col-md-6">
                    <label for="phoneNumber" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phoneNumber" name="phone_number" value="{{ $employee->phone_number }}">
                  </div>
                  <div class="col-12">
                    <div class="alert alert-info">
                      <i class="bi bi-info-circle me-2"></i>
                      <strong>Employee ID:</strong> {{ $employee->employee_id }} (cannot be changed)
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="working-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('profileUpdateModal')">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i> Update Profile
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Request Form Modal -->
  <div class="working-modal" id="requestFormModal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('requestFormModal')"></div>
    <div class="working-modal-dialog">
      <div class="working-modal-content">
        <div class="working-modal-header">
          <h5 class="working-modal-title">Submit Request Form</h5>
          <button type="button" class="working-modal-close" onclick="closeWorkingModal('requestFormModal')">&times;</button>
        </div>
        <form id="requestForm" method="POST" action="{{ route('employee.requests.store') }}">
          @csrf
          <div class="working-modal-body">
            <div class="mb-3">
              <label for="requestType" class="form-label">Request Type</label>
              <select class="form-select" id="requestType" name="request_type" required>
                <option value="">Select request type</option>
                <option value="Equipment Request">Equipment Request</option>
                <option value="Attendance Adjustment">Attendance Adjustment</option>
                <option value="Certificate Request">Certificate Request</option>
                <option value="Training Request">Training Request</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="requestReason" class="form-label">Details/Reason</label>
              <textarea class="form-control" id="requestReason" name="reason" rows="4" required></textarea>
            </div>
            <div class="mb-3">
              <label for="requestDate" class="form-label">Requested Date (if applicable)</label>
              <input type="date" class="form-control" id="requestDate" name="requested_date">
            </div>
          </div>
          <div class="working-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('requestFormModal')">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="agent-portal-script.js"></script>
  
  <script>
  // Working Modal Functions for ESS - Based on leave_management.blade.php pattern
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
        
        // Reset form if it exists
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Reset form action to create mode
            const originalAction = form.getAttribute('data-original-action') || form.action;
            form.action = originalAction;
            form.method = 'POST';
            
            // Remove method override if it exists
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.remove();
            }
            
            // Reset modal title
            const title = modal.querySelector('.working-modal-title');
            if (title && title.getAttribute('data-original-title')) {
                title.textContent = title.getAttribute('data-original-title');
            }
        }
    }
  }

  // Update button onclick handlers to use working modals
  document.addEventListener('DOMContentLoaded', function() {
    // Replace Bootstrap modal triggers with working modal triggers
    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    modalTriggers.forEach(trigger => {
      const targetModal = trigger.getAttribute('data-bs-target');
      if (targetModal) {
        const modalId = targetModal.replace('#', '');
        trigger.removeAttribute('data-bs-toggle');
        trigger.removeAttribute('data-bs-target');
        trigger.onclick = function() {
          openWorkingModal(modalId);
        };
      }
    });

    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('working-modal-backdrop')) {
        const modal = e.target.closest('.working-modal');
        if (modal) {
          modal.style.display = 'none';
          document.body.style.overflow = 'auto';
        }
      }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const visibleModals = document.querySelectorAll('.working-modal[style*="flex"]');
        visibleModals.forEach(modal => {
          modal.style.display = 'none';
        });
        document.body.style.overflow = 'auto';
      }
    });
  });
  </script>
  
  <script>
    // Initialize ESS Dashboard
    document.addEventListener('DOMContentLoaded', function() {
      // Set up CSRF token for AJAX requests
      const token = document.querySelector('meta[name="csrf-token"]');
      if (token) {
        window.Laravel = {
          csrfToken: token.getAttribute('content')
        };
        
        // Set default AJAX headers
        if (typeof $ !== 'undefined') {
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': token.getAttribute('content')
            }
          });
        }
      }
      
      // Initialize time clock
      updateClock();
      setInterval(updateClock, 1000);
      
      // Load initial data
      loadAttendanceLog();
      loadDashboardStats();
      
      // Check current clock status
      checkClockStatus();
      
      // Refresh CSRF token and dashboard data
      setInterval(function() {
        // Refresh CSRF token
        fetch('/csrf-token', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.csrf_token) {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
              metaTag.setAttribute('content', data.csrf_token);
            }
            if (window.Laravel) {
              window.Laravel.csrfToken = data.csrf_token;
            }
            if (typeof $ !== 'undefined') {
              $.ajaxSetup({
                headers: {
                  'X-CSRF-TOKEN': data.csrf_token
                }
              });
            }
          }
        })
        .catch(error => {
          console.warn('Failed to refresh CSRF token:', error);
        });
        
        // Refresh dashboard data every 5 minutes
        loadDashboardStats();
      }, 5 * 60 * 1000); // 5 minutes
    });
    
    function checkClockStatus() {
      fetch('/employee/clock-status', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data) {
          clockedIn = data.data.clocked_in;
          if (clockedIn) {
            clockInTime = new Date(data.data.clock_in_time);
            document.getElementById('clockStatus').textContent = 'Clocked In';
            document.getElementById('clockStatus').style.background = 'rgba(40, 167, 69, 0.8)';
            document.getElementById('clockInBtn').disabled = true;
            document.getElementById('clockOutBtn').disabled = false;
          } else {
            document.getElementById('clockStatus').textContent = 'Not Clocked In';
            document.getElementById('clockStatus').style.background = 'rgba(255,255,255,0.2)';
            document.getElementById('clockInBtn').disabled = false;
            document.getElementById('clockOutBtn').disabled = true;
          }
        }
      })
      .catch(error => {
        console.error('Error checking clock status:', error);
      });
    }
    
    function loadDashboardStats() {
      fetch('/employee/dashboard-stats', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('pendingLeaveCount').textContent = data.pending_leave_requests || 0;
          document.getElementById('attendanceRate').textContent = (data.attendance_rate || 95) + '%';
          document.getElementById('pendingTimesheets').textContent = data.pending_timesheets || 0;
          document.getElementById('pendingClaims').textContent = data.pending_claims || 0;
        }
      })
      .catch(error => {
        console.error('Error loading dashboard stats:', error);
      });
    }

    // Time and Attendance Functions
    let clockedIn = false;
    let clockInTime = null;
    
    function updateClock() {
      const now = new Date();
      const timeString = now.toLocaleTimeString('en-US', {
        hour12: false,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
      document.getElementById('currentTime').textContent = timeString;
      
      // Update today's hours if clocked in
      if (clockedIn && clockInTime) {
        const hoursWorked = (now - clockInTime) / (1000 * 60 * 60);
        const hours = Math.floor(hoursWorked);
        const minutes = Math.floor((hoursWorked - hours) * 60);
        document.getElementById('todayHours').textContent = `${hours}:${minutes.toString().padStart(2, '0')}`;
      }
    }
    
    function clockIn() {
      const now = new Date();
      
      fetch('/employee/clock-in', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          timestamp: now.toISOString()
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          clockedIn = true;
          clockInTime = now;
          document.getElementById('clockStatus').textContent = 'Clocked In';
          document.getElementById('clockStatus').style.background = 'rgba(40, 167, 69, 0.8)';
          document.getElementById('clockInBtn').disabled = true;
          document.getElementById('clockOutBtn').disabled = false;
          loadAttendanceLog();
          showNotification('Clocked in successfully!', 'success');
        } else {
          showNotification('Error clocking in: ' + (data.message || 'Unknown error'), 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Error clocking in. Please try again.', 'error');
      });
    }
    
    function clockOut() {
      const now = new Date();
      
      console.log('Attempting to clock out...');
      
      fetch('/employee/clock-out', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          timestamp: now.toISOString()
        })
      })
      .then(response => {
        console.log('Clock-out response status:', response.status);
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        console.log('Clock-out response data:', data);
        if (data.success) {
          clockedIn = false;
          clockInTime = null;
          document.getElementById('clockStatus').textContent = 'Not Clocked In';
          document.getElementById('clockStatus').style.background = 'rgba(255,255,255,0.2)';
          document.getElementById('clockInBtn').disabled = false;
          document.getElementById('clockOutBtn').disabled = true;
          document.getElementById('todayHours').textContent = '0:00';
          loadAttendanceLog();
          showNotification('Clocked out successfully! Total hours: ' + (data.data?.total_hours || '0') + 'h', 'success');
        } else {
          console.error('Clock-out failed:', data.message);
          showNotification('Error clocking out: ' + (data.message || 'Unknown error'), 'error');
        }
      })
      .catch(error => {
        console.error('Clock-out error:', error);
        showNotification('Error clocking out: ' + error.message, 'error');
      });
    }
    
    function loadAttendanceLog() {
      fetch('/employee/attendance-log', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        const tbody = document.getElementById('attendanceLog');
        if (data.success && data.data && data.data.length > 0) {
          tbody.innerHTML = data.data.map(log => `
            <tr>
              <td>${log.date}</td>
              <td>${log.clock_in || '--'}</td>
              <td>${log.clock_out || '--'}</td>
              <td>${log.hours || '0.00'}</td>
            </tr>
          `).join('');
        } else {
          tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No attendance records found</td></tr>';
        }
      })
      .catch(error => {
        console.error('Error loading attendance log:', error);
        document.getElementById('attendanceLog').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading data</td></tr>';
      });
    }
    
    function showNotification(message, type = 'info') {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      
      document.body.appendChild(notification);
      
      // Auto remove after 5 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 5000);
    }

    function viewPayslip() {
      // This would open payslip in new window/tab
      window.open('/employee/payslip/latest', '_blank');
    }

    // Remove AJAX form submissions - use Laravel server-side handling instead
    // Forms will now submit directly to Laravel routes with proper validation

    // Profile picture preview
    document.getElementById('profilePicture').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });

    // Remove AJAX form submissions - use Laravel server-side handling instead
    // Forms will now submit directly to Laravel routes with proper validation

    // Remove AJAX form submissions - use Laravel server-side handling instead
    // Forms will now submit directly to Laravel routes with proper validation
  </script>

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
  </script>

  <!-- CSRF Token Refresh System -->
  <script src="{{ asset('js/csrf-refresh.js') }}"></script>

</body>
</html>
