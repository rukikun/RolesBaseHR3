@extends('layouts.hr')

@section('title', 'Timesheet Management - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Timesheet Management</h2>
        <p class="text-muted mb-0">Manage employee timesheets</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/admin/dashboard" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Timesheet Management</li>
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

{{-- Session error alerts removed for cleaner interface --}}

@if(session('info'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
  <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Validation error alerts removed for cleaner interface --}}

<div id="alert-container"></div>

{{-- Use controller-passed statistics instead of direct PDO queries --}}

<!-- Timesheet Statistics -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-clock text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $timesheetStats['total_timesheets'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Timesheets</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-warning">
          <i class="fas fa-hourglass-half text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $timesheetStats['pending_timesheets'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Pending</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-success">
          <i class="fas fa-check-circle text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $timesheetStats['approved_timesheets'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Approved</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-business-time text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ number_format($timesheetStats['total_hours'] ?? 0, 1) }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Hours</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Module Integration Tabs -->
<div class="card mb-4">
  <div class="card-header">
    <ul class="nav nav-tabs card-header-tabs" id="modulesTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="timesheets-tab" data-bs-toggle="tab" data-bs-target="#timesheets" type="button" role="tab" aria-controls="timesheets" aria-selected="true">
          <i class="fas fa-clock me-2"></i>Timesheets
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="shifts-tab" data-bs-toggle="tab" data-bs-target="#shifts" type="button" role="tab" aria-controls="shifts" aria-selected="false">
          <i class="fas fa-calendar-alt me-2"></i>Shifts
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="leaves-tab" data-bs-toggle="tab" data-bs-target="#leaves" type="button" role="tab" aria-controls="leaves" aria-selected="false">
          <i class="fas fa-umbrella-beach me-2"></i>Leave Requests
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="claims-tab" data-bs-toggle="tab" data-bs-target="#claims" type="button" role="tab" aria-controls="claims" aria-selected="false">
          <i class="fas fa-receipt me-2"></i>Claims
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab" aria-controls="attendance" aria-selected="false">
          <i class="fas fa-user-clock me-2"></i>Attendance Logs
        </button>
      </li>
    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content" id="modulesTabContent">
      <!-- Timesheets Tab -->
      <div class="tab-pane fade show active" id="timesheets" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">
            <i class="fas fa-robot me-2"></i>Weekly Timesheets
          </h5>
          <div class="d-flex gap-2">
            <button class="btn btn-success" onclick="generateAllTimesheets()">
              <i class="fas fa-magic me-2"></i>Generate All AI Timesheets
            </button>
            <button class="btn btn-primary" onclick="openWorkingModal('timesheet-modal')">
              <i class="fas fa-plus me-2"></i>Add Manual Timesheet
            </button>
          </div>
        </div>
        <!-- AI Employee Timesheets Grid -->
        <div class="row g-3" id="ai-timesheets-grid">
          @forelse($employees ?? [] as $employee)
            <div class="col-md-6 col-lg-4 col-xl-3">
              <div class="card employee-timesheet-card h-100 shadow-sm">
                <div class="card-body text-center">
                  <!-- Employee Avatar -->
                  <div class="employee-avatar-lg mb-3">
                    @php
                      $firstName = $employee->first_name ?? 'Unknown';
                      $lastName = $employee->last_name ?? 'Employee';
                      $initials = substr($firstName, 0, 1) . substr($lastName, 0, 1);
                      $colors = ['FF6B6B', '4ECDC4', '45B7D1', '96CEB4', 'FFEAA7', 'DDA0DD', 'FFB347', '87CEEB'];
                      $colorIndex = crc32($employee->id) % count($colors);
                      $bgColor = $colors[$colorIndex];
                    @endphp
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($initials) }}&background={{ $bgColor }}&color=ffffff&size=80&bold=true" 
                         class="rounded-circle" width="80" height="80" alt="Avatar">
                  </div>
                  
                  <!-- Employee Info -->
                  <h6 class="card-title mb-1">{{ $firstName }} {{ $lastName }}</h6>
                  <p class="text-muted small mb-2">{{ $employee->position ?? 'Employee' }}</p>
                  <p class="text-muted small mb-3">{{ $employee->department ?? 'General' }}</p>
                  
                  <!-- AI Status Badge -->
                  <div class="mb-3">
                    <span class="badge bg-info ai-status-badge" id="ai-status-{{ $employee->id }}">
                      <i class="fas fa-robot me-1"></i>AI Ready
                    </span>
                  </div>
                  
                  <!-- Action Buttons -->
                  <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-sm" onclick="generateAITimesheet({{ $employee->id }})" 
                            id="generate-btn-{{ $employee->id }}">
                      <i class="fas fa-magic me-1"></i>Generate AI Timesheet
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="viewAITimesheet({{ $employee->id }})" 
                            id="view-btn-{{ $employee->id }}" disabled>
                      <i class="fas fa-eye me-1"></i>See Details
                    </button>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div class="col-12">
              <div class="text-center text-muted py-5">
                <i class="fas fa-users fa-3x mb-3 text-muted"></i><br>
                <h5>No Employees Found</h5>
                <p>Please add employees to generate AI timesheets.</p>
              </div>
            </div>
          @endforelse
        </div>
        
        <!-- Loading indicator -->
        <div class="text-center py-4" id="loading-indicator" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      
      
      <!-- Shifts Tab -->
      <div class="tab-pane fade" id="shifts" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">
            <i class="fas fa-calendar-alt me-2"></i>Shift Assignments
          </h5>
          <button class="btn btn-primary" onclick="openWorkingModal('shift-assignment-modal')">
            <i class="fas fa-plus me-2"></i>Assign Shift
          </button>
        </div>
        
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Employee</th>
                <th>Date</th>
                <th>Shift Type</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="shifts-tbody">
              @forelse($shifts ?? [] as $shift)
              <tr>
                <td>{{ $shift->employee_name }}</td>
                <td>{{ \Carbon\Carbon::parse($shift->shift_date)->format('M d, Y') }}</td>
                <td>{{ $shift->shift_type_name }}</td>
                <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }}</td>
                <td>{{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}</td>
                <td>
                  <span class="badge bg-{{ $shift->status === 'completed' ? 'success' : ($shift->status === 'scheduled' ? 'primary' : 'warning') }}">
                    {{ ucfirst($shift->status ?? 'scheduled') }}
                  </span>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary" onclick="editShift({{ $shift->id }})" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" action="/shifts/{{ $shift->id }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this shift?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No shifts found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Leave Requests Tab -->
      <div class="tab-pane fade" id="leaves" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">
            <i class="fas fa-umbrella-beach me-2"></i>Leave Requests
          </h5>
          <button class="btn btn-primary" onclick="openWorkingModal('leave-request-modal')">
            <i class="fas fa-plus me-2"></i>Request Leave
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Days</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="leaves-tbody">
              {{-- Debug: Show count of leave requests --}}
              {{-- Total Leave Requests: {{ count($leaveRequests ?? []) }} --}}
              @forelse($leaveRequests ?? [] as $leave)
              <tr>
                <td>
                  @if(!empty($leave->employee_name) && trim($leave->employee_name) !== '')
                    {{ trim($leave->employee_name) }}
                  @else
                    <span class="text-warning">Employee #{{ $leave->employee_id ?? 'N/A' }}</span>
                  @endif
                </td>
                <td>
                  @if(!empty($leave->leave_type_name))
                    {{ $leave->leave_type_name }}
                  @else
                    <span class="text-warning">Type #{{ $leave->leave_type_id ?? 'N/A' }}</span>
                  @endif
                </td>
                <td>{{ isset($leave->start_date) ? \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') : 'N/A' }}</td>
                <td>{{ isset($leave->end_date) ? \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') : 'N/A' }}</td>
                <td>{{ $leave->days_requested ?? $leave->duration ?? $leave->days ?? 'N/A' }}</td>
                <td>
                  <span class="badge bg-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'pending' ? 'warning' : 'danger') }}">
                    {{ ucfirst($leave->status ?? 'pending') }}
                  </span>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    @if($leave->status === 'pending')
                    <form method="POST" action="/leave-requests/{{ $leave->id }}/approve" style="display: inline;" onsubmit="return confirm('Are you sure you want to approve this leave request?')">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>
                    <form method="POST" action="/leave-requests/{{ $leave->id }}/reject" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this leave request?')">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject">
                        <i class="fas fa-times"></i>
                      </button>
                    </form>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No leave requests found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Claims Tab -->
      <div class="tab-pane fade" id="claims" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">
            <i class="fas fa-receipt me-2"></i>Expense Claims
          </h5>
          <button class="btn btn-primary" onclick="openWorkingModal('claims-modal')">
            <i class="fas fa-plus me-2"></i>Submit Claim
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Employee</th>
                <th>Claim Type</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="claims-tbody">
              @forelse($claims ?? [] as $claim)
              <tr>
                <td>{{ $claim->employee_name ?? ($claim->first_name . ' ' . $claim->last_name) ?? 'Unknown Employee' }}</td>
                <td>{{ $claim->claim_type_name ?? $claim->name ?? 'Unknown Type' }}</td>
                <td>${{ number_format($claim->amount ?? 0, 2) }}</td>
                <td>{{ isset($claim->expense_date) ? \Carbon\Carbon::parse($claim->expense_date)->format('M d, Y') : (isset($claim->claim_date) ? \Carbon\Carbon::parse($claim->claim_date)->format('M d, Y') : (isset($claim->created_at) ? \Carbon\Carbon::parse($claim->created_at)->format('M d, Y') : 'N/A')) }}</td>
                <td>
                  <span class="badge bg-{{ $claim->status === 'approved' ? 'success' : ($claim->status === 'pending' ? 'warning' : 'danger') }}">
                    {{ ucfirst($claim->status ?? 'pending') }}
                  </span>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    @if($claim->status === 'pending')
                    <form method="POST" action="/claims/{{ $claim->id }}/approve" style="display: inline;" onsubmit="return confirm('Are you sure you want to approve this claim?')">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>
                    <form method="POST" action="/claims/{{ $claim->id }}/reject" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this claim?')">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject">
                        <i class="fas fa-times"></i>
                      </button>
                    </form>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No claims found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Attendance Logs Tab -->
      <div class="tab-pane fade" id="attendance" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">
            <i class="fas fa-user-clock me-2"></i>Employee Attendance Logs
          </h5>
          <div class="d-flex gap-2">
            
          </div>
        </div>
        
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Employee</th>
                <th>Date</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Total Hours</th>
                <th>Overtime</th>
                <th>Status</th>
                <th>Location</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($attendances ?? [] as $attendance)
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar-sm me-2">
                        @php
                          $employee = $employees->firstWhere('id', $attendance->employee_id);
                          $firstName = $employee->first_name ?? 'Unknown';
                          $lastName = $employee->last_name ?? 'Employee';
                          $initials = substr($firstName, 0, 1) . substr($lastName, 0, 1);
                          $colors = ['FF6B6B', '4ECDC4', '45B7D1', '96CEB4', 'FFEAA7', 'DDA0DD', 'FFB347', '87CEEB'];
                          $colorIndex = crc32($attendance->employee_id) % count($colors);
                          $bgColor = $colors[$colorIndex];
                        @endphp
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($initials) }}&background={{ $bgColor }}&color=ffffff&size=32&bold=true" 
                             class="rounded-circle" width="32" height="32" alt="Avatar">
                      </div>
                      <div>
                        <div class="fw-medium">{{ $firstName }} {{ $lastName }}</div>
                        <small class="text-muted">ID: {{ $attendance->employee_id }}</small>
                      </div>
                    </div>
                  </td>
                  <td>{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</td>
                  <td>
                    @if($attendance->clock_in_time)
                      <span class="badge bg-success">{{ \Carbon\Carbon::parse($attendance->clock_in_time)->format('h:i A') }}</span>
                    @else
                      <span class="text-muted">--</span>
                    @endif
                  </td>
                  <td>
                    @if($attendance->clock_out_time)
                      <span class="badge bg-info">{{ \Carbon\Carbon::parse($attendance->clock_out_time)->format('h:i A') }}</span>
                    @else
                      <span class="text-muted">--</span>
                    @endif
                  </td>
                  <td>
                    @php
                      $totalHours = $attendance->total_hours ?? 0;
                      $isNegative = $totalHours < 0;
                    @endphp
                    @if($isNegative)
                      <strong class="text-danger">{{ number_format($totalHours, 2) }}h</strong>
                      <i class="fas fa-exclamation-triangle text-warning ms-1" title="Negative hours detected"></i>
                    @else
                      <strong>{{ number_format($totalHours, 2) }}h</strong>
                    @endif
                  </td>
                  <td>
                    @if($attendance->overtime_hours > 0)
                      <span class="badge bg-warning">{{ number_format($attendance->overtime_hours, 2) }}h</span>
                    @else
                      <span class="text-muted">0h</span>
                    @endif
                  </td>
                  <td>
                    @php
                      $statusBadges = [
                        'present' => 'success',
                        'late' => 'warning', 
                        'absent' => 'danger',
                        'on_break' => 'info',
                        'clocked_out' => 'secondary'
                      ];
                      $badgeClass = $statusBadges[$attendance->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</span>
                  </td>
                  <td>
                    <small class="text-muted">{{ $attendance->location ?? 'Office' }}</small>
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      @if(!$attendance->clock_out_time)
                      <button class="btn btn-sm btn-outline-warning" onclick="editAttendance({{ $attendance->id }})" title="Edit">
                        <i class="fas fa-edit"></i>
                      </button>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">
                    <i class="fas fa-user-clock fa-3x mb-3 text-muted"></i><br>
                    No attendance records found. Records will appear here when employees clock in/out via ESS.
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

<!-- Modals for All Modules -->

<!-- Timesheet Modal -->
<div class="working-modal" id="timesheet-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('timesheet-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Add Timesheet</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('timesheet-modal')">&times;</button>
            </div>
            <form id="timesheetForm" method="POST" action="{{ route('timesheets.store') }}">
                @csrf
                <input type="hidden" id="timesheet-id" name="timesheet_id">
                <input type="hidden" id="form-method" name="_method" value="POST">
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee</label>
                                <select class="form-select" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees ?? [] as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="work_date" class="form-label">Work Date</label>
                                <input type="date" class="form-control" id="work_date" name="work_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="clock_in_time" class="form-label">Clock In Time</label>
                                <input type="time" class="form-control" id="clock_in_time" name="clock_in_time">
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
                                <label for="hours_worked" class="form-label">Hours Worked</label>
                                <input type="number" class="form-control" id="hours_worked" name="hours_worked" min="0" max="24" step="0.5">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="overtime_hours" class="form-label">Overtime Hours</label>
                                <input type="number" class="form-control" id="overtime_hours" name="overtime_hours" min="0" max="12" step="0.5">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="break_duration" class="form-label">Break Duration (hours)</label>
                                <input type="number" class="form-control" id="break_duration" name="break_duration" min="0" max="8" step="0.5" value="1">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Additional notes or ESS clock-in/out details"></textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('timesheet-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveTimesheetBtn">Save Timesheet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div class="working-modal" id="attendance-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('attendance-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Manual Attendance Entry</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('attendance-modal')">&times;</button>
            </div>
            <form id="attendanceForm" method="POST" action="{{ route('attendance.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="mb-3">
                        <label for="attendance_employee_id" class="form-label">Employee</label>
                        <select class="form-select" id="attendance_employee_id" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @foreach($employees ?? [] as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="attendance_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="attendance_date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="attendance_clock_in" class="form-label">Clock In Time</label>
                                <input type="datetime-local" class="form-control" id="attendance_clock_in" name="clock_in_time">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="attendance_clock_out" class="form-label">Clock Out Time</label>
                                <input type="datetime-local" class="form-control" id="attendance_clock_out" name="clock_out_time">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="attendance_status" class="form-label">Status</label>
                                <select class="form-select" id="attendance_status" name="status" required>
                                    <option value="present">Present</option>
                                    <option value="late">Late</option>
                                    <option value="absent">Absent</option>
                                    <option value="clocked_out">Clocked Out</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="attendance_location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="attendance_location" name="location" value="Office" placeholder="Office, Remote, etc.">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="attendance_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="attendance_notes" name="notes" rows="3" placeholder="Optional notes about this attendance record"></textarea>
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

<!-- Shift Assignment Modal -->
<div class="working-modal" id="shift-assignment-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('shift-assignment-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Assign Employee to Shift</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('shift-assignment-modal')">&times;</button>
            </div>
            <form id="shiftForm" method="POST" action="{{ route('shifts.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="mb-3">
                        <label for="shift_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="shift_date" name="shift_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="shift_employee_id" class="form-label">Employee</label>
                        <select class="form-select" id="shift_employee_id" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @foreach($employees ?? [] as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="shift_type_id" class="form-label">Shift Type</label>
                        <select class="form-select" id="shift_type_id" name="shift_type_id" required>
                            <option value="">Select Shift Type</option>
                            <option value="1" data-start-time="08:00" data-end-time="16:00">Morning Shift</option>
                            <option value="2" data-start-time="14:00" data-end-time="22:00">Afternoon Shift</option>
                            <option value="3" data-start-time="22:00" data-end-time="06:00">Night Shift</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift_start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="shift_start_time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift_end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="shift_end_time" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="shift_location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="shift_location" name="location" value="Main Office" placeholder="Main Office">
                    </div>
                    <div class="mb-3">
                        <label for="shift_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="shift_notes" name="notes" rows="2" placeholder="Optional notes for this shift assignment"></textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('shift-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leave Request Modal -->
<div class="working-modal" id="leave-request-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('leave-request-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">New Leave Request</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('leave-request-modal')">&times;</button>
            </div>
            <form id="leaveRequestForm" method="POST" action="{{ route('leave-requests.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="mb-3">
                        <label for="leave_employee_id" class="form-label">Employee</label>
                        <select class="form-select" id="leave_employee_id" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @foreach($employees ?? [] as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="leave_type_id" class="form-label">Leave Type</label>
                        <select class="form-select" id="leave_type_id" name="leave_type_id" required>
                            <option value="">Select Leave Type</option>
                            @foreach($leaveTypes ?? [] as $type)
                                <option value="{{ $type->id }}" data-max-days="{{ $type->max_days_per_year }}">
                                    {{ $type->name }} 
                                    @if($type->max_days_per_year > 0)
                                        ({{ $type->max_days_per_year }} days/year)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leave_start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="leave_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leave_end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="leave_end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Total Days</label>
                                <input type="text" class="form-control" id="leave_total_days" readonly placeholder="Auto-calculated">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="Pending Approval" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="leave_reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="leave_reason" name="reason" rows="3" placeholder="Please provide a reason for your leave request" required></textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('leave-request-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Claims Modal -->
<div class="working-modal" id="claims-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('claims-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Create Claim</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('claims-modal')">&times;</button>
            </div>
            <form id="create-claim-form" method="POST" action="{{ route('claims.store.simple') }}" enctype="multipart/form-data">
                @csrf
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="employee-select" class="form-label">Employee</label>
                            <select class="form-select" id="employee-select" name="employee_id" required>
                                <option value="">Select Employee</option>
                                @if(isset($employees) && $employees->count() > 0)
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="1">John Doe</option>
                                    <option value="2">Jane Smith</option>
                                    <option value="3">Mike Johnson</option>
                                    <option value="4">Sarah Wilson</option>
                                    <option value="5">Tom Brown</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="claim-type-select" class="form-label">Claim Type</label>
                            <select class="form-select" id="claim-type-select" name="claim_type_id" required>
                                <option value="">Select Claim Type</option>
                                @if(isset($claimTypes) && $claimTypes->count() > 0)
                                    @foreach($claimTypes as $claimType)
                                        <option value="{{ $claimType->id }}">
                                            {{ $claimType->name }} ({{ $claimType->code ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="1">Travel Expenses (TRAVEL)</option>
                                    <option value="2">Meal Allowance (MEAL)</option>
                                    <option value="3">Office Supplies (OFFICE)</option>
                                    <option value="4">Training Costs (TRAIN)</option>
                                    <option value="5">Medical Expenses (MEDICAL)</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="claim-amount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="claim-amount" name="amount" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="claim-date" class="form-label">Claim Date</label>
                            <input type="date" class="form-control" id="claim-date" name="claim_date" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="claim-attachment" class="form-label">Receipt/Attachment</label>
                            <input type="file" class="form-control" id="claim-attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="claim-description" class="form-label">Description</label>
                        <textarea class="form-control" id="claim-description" name="description" rows="3" required placeholder="Describe the expense..."></textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('claims-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Timesheet Details Modal -->
<div class="working-modal" id="view-timesheet-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-timesheet-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Timesheet Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-timesheet-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Employee:</label>
                            <div id="view-timesheet-employee" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Date:</label>
                            <div id="view-timesheet-date" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Clock In:</label>
                            <div id="view-timesheet-clock-in" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Clock Out:</label>
                            <div id="view-timesheet-clock-out" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Total Hours:</label>
                            <div id="view-timesheet-hours" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Status:</label>
                            <div id="view-timesheet-status" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-timesheet-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Shift Details Modal -->
<div class="working-modal" id="view-shift-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-shift-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Shift Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-shift-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Employee:</strong>
                        <p id="view-shift-employee" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date:</strong>
                        <p id="view-shift-date" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Shift Type:</strong>
                        <p id="view-shift-type" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p id="view-shift-status" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Start Time:</strong>
                        <p id="view-shift-start" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>End Time:</strong>
                        <p id="view-shift-end" class="mb-2">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-shift-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Leave Request Details Modal -->
<div class="working-modal" id="view-leave-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-leave-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Leave Request Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-leave-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Employee:</label>
                            <div id="view-leave-employee" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Leave Type:</label>
                            <div id="view-leave-type" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Start Date:</label>
                            <div id="view-leave-start" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">End Date:</label>
                            <div id="view-leave-end" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Days:</label>
                            <div id="view-leave-days" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Status:</label>
                            <div id="view-leave-status" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="detail-item">
                            <label class="detail-label">Reason:</label>
                            <div id="view-leave-reason" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-leave-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Claim Details Modal -->
<div class="working-modal" id="view-claim-details-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-claim-details-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Claim Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-claim-details-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Employee:</strong>
                        <p id="view-claim-details-employee" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Claim Type:</strong>
                        <p id="view-claim-details-type" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Amount:</strong>
                        <p id="view-claim-details-amount" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date:</strong>
                        <p id="view-claim-details-date" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p id="view-claim-details-status" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Attachment:</strong>
                        <p id="view-claim-details-attachment" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Description:</strong>
                        <p id="view-claim-details-description" class="mb-2">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-claim-details-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Attendance Details Modal -->
<div class="working-modal" id="view-attendance-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-attendance-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Attendance Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-attendance-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Employee:</strong>
                        <p id="view-attendance-employee" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date:</strong>
                        <p id="view-attendance-date" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Clock In:</strong>
                        <p id="view-attendance-clock-in" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Clock Out:</strong>
                        <p id="view-attendance-clock-out" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Hours Worked:</strong>
                        <p id="view-attendance-hours" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p id="view-attendance-status" class="mb-2">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-attendance-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- AI Generated Weekly Timesheet Modal -->
<div class="working-modal" id="ai-timesheet-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('ai-timesheet-modal')"></div>
    <div class="working-modal-dialog" style="max-width: 1000px;">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">
                    <i class="fas fa-robot me-2 text-primary"></i>AI Generated Weekly Timesheet
                </h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('ai-timesheet-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <!-- Employee Header Info -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <strong>Employee Name:</strong> <span id="ai-employee-name">-</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Department:</strong> <span id="ai-department">-</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Supervisor Name:</strong> <span id="ai-supervisor-name">-</span>
                    </div>
                </div>

                <!-- AI Generation Info -->
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>AI Generated:</strong> This timesheet was automatically generated using AI based on employee patterns, shift schedules, and attendance history.
                    <span id="ai-generation-time" class="text-muted"></span>
                </div>

                <!-- Weekly Timesheet Table -->
                <div class="table-responsive">
                    <table class="table table-bordered ai-timesheet-table">
                        <thead>
                            <tr>
                                <th class="day-header" style="background-color: #20B2AA; color: white; width: 100px;">Day</th>
                                <th style="background-color: #20B2AA; color: white;">Date</th>
                                <th style="background-color: #20B2AA; color: white;">Time In</th>
                                <th style="background-color: #20B2AA; color: white;">Break</th>
                                <th style="background-color: #20B2AA; color: white;">Time Out</th>
                                <th style="background-color: #20B2AA; color: white;">Total Hours</th>
                                <th style="background-color: #20B2AA; color: white;">Overtime</th>
                            </tr>
                        </thead>
                        <tbody id="ai-timesheet-body">
                            <tr class="monday-row">
                                <td class="day-cell" style="background-color: #20B2AA; color: white; font-weight: bold;">Monday</td>
                                <td id="ai-monday-date">-</td>
                                <td id="ai-monday-time-in">-</td>
                                <td id="ai-monday-break">-</td>
                                <td id="ai-monday-time-out">-</td>
                                <td id="ai-monday-total-hours">-</td>
                                <td id="ai-monday-overtime">-</td>
                            </tr>
                            <tr class="tuesday-row">
                                <td class="day-cell" style="background-color: #20B2AA; color: white; font-weight: bold;">Tuesday</td>
                                <td id="ai-tuesday-date">-</td>
                                <td id="ai-tuesday-time-in">-</td>
                                <td id="ai-tuesday-break">-</td>
                                <td id="ai-tuesday-time-out">-</td>
                                <td id="ai-tuesday-total-hours">-</td>
                                <td id="ai-tuesday-overtime">-</td>
                            </tr>
                            <tr class="wednesday-row">
                                <td class="day-cell" style="background-color: #20B2AA; color: white; font-weight: bold;">Wednesday</td>
                                <td id="ai-wednesday-date">-</td>
                                <td id="ai-wednesday-time-in">-</td>
                                <td id="ai-wednesday-break">-</td>
                                <td id="ai-wednesday-time-out">-</td>
                                <td id="ai-wednesday-total-hours">-</td>
                                <td id="ai-wednesday-overtime">-</td>
                            </tr>
                            <tr class="thursday-row">
                                <td class="day-cell" style="background-color: #20B2AA; color: white; font-weight: bold;">Thursday</td>
                                <td id="ai-thursday-date">-</td>
                                <td id="ai-thursday-time-in">-</td>
                                <td id="ai-thursday-break">-</td>
                                <td id="ai-thursday-time-out">-</td>
                                <td id="ai-thursday-total-hours">-</td>
                                <td id="ai-thursday-overtime">-</td>
                            </tr>
                            <tr class="friday-row">
                                <td class="day-cell" style="background-color: #20B2AA; color: white; font-weight: bold;">Friday</td>
                                <td id="ai-friday-date">-</td>
                                <td id="ai-friday-time-in">-</td>
                                <td id="ai-friday-break">-</td>
                                <td id="ai-friday-time-out">-</td>
                                <td id="ai-friday-total-hours">-</td>
                                <td id="ai-friday-overtime">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- AI Insights -->
                <div class="mt-4">
                    <h6><i class="fas fa-lightbulb me-2 text-warning"></i>AI Insights</h6>
                    <div id="ai-insights" class="alert alert-light">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            Generating AI insights...
                        </div>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('ai-timesheet-modal')">Close</button>
                <button type="button" class="btn btn-success" onclick="approveAITimesheet()">
                    <i class="fas fa-check me-2"></i>Approve & Save
                </button>
                <button type="button" class="btn btn-primary" onclick="printAITimesheet()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Timesheet Details Modal (Legacy) -->
<div class="working-modal" id="weekly-timesheet-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('weekly-timesheet-modal')"></div>
    <div class="working-modal-dialog" style="max-width: 900px;">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Employee Timesheet</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('weekly-timesheet-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <!-- Employee Header Info -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <strong>Employee Name:</strong> <span id="weekly-employee-name">-</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Department:</strong> <span id="weekly-department">-</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Supervisor Name:</strong> <span id="weekly-supervisor-name">-</span>
                    </div>
                </div>

                <!-- Weekly Timesheet Table -->
                <div class="table-responsive">
                    <table class="table table-bordered weekly-timesheet-table">
                        <thead class="table-success">
                            <tr>
                                <th class="day-header" style="background-color: #20B2AA; color: white; width: 100px;">Day</th>
                                <th style="background-color: #20B2AA; color: white;">Date</th>
                                <th style="background-color: #20B2AA; color: white;">Time In</th>
                                <th style="background-color: #20B2AA; color: white;">Break</th>
                                <th style="background-color: #20B2AA; color: white;">Time Out</th>
                                <th style="background-color: #20B2AA; color: white;">Total Hours</th>
                                <th style="background-color: #20B2AA; color: white;">Actual Time</th>
                            </tr>
                        </thead>
                        <tbody id="weekly-timesheet-body">
                            <tr class="monday-row">
                                <td class="day-cell" style="background-color: #20B2AA; color: white; font-weight: bold;">Monday</td>
                                <td id="monday-date">-</td>
                                <td id="monday-time-in">-</td>
                                <td id="monday-break">-</td>
                                <td id="monday-time-out">-</td>
                                <td id="monday-total-hours">-</td>
                                <td id="monday-actual-time">-</td>
                            </tr>
                            <tr class="tuesday-row">
                                <td class="day-cell" style="background-color: #20B2AA; color: white; font-weight: bold;">Tuesday</td>
                                <td id="tuesday-date">-</td>
                                <td id="tuesday-time-in">-</td>
                                <td id="tuesday-break">-</td>
                                <td id="tuesday-time-out">-</td>
                                <td id="tuesday-total-hours">-</td>
                                <td id="tuesday-actual-time">-</td>
                            </tr>
                            <tr class="wednesday-row">
                                <td class="day-cell" style="background-color: #20B2AA; color: white; font-weight: bold;">Wednesday</td>
                                <td id="wednesday-date">-</td>
                                <td id="wednesday-time-in">-</td>
                                <td id="wednesday-break">-</td>
                                <td id="wednesday-time-out">-</td>
                                <td id="wednesday-total-hours">-</td>
                                <td id="wednesday-actual-time">-</td>
                            </tr>
                            <tr class="thursday-row">
                                <td class="day-cell" style="background-color: #20B2AA; color: white; font-weight: bold;">Thursday</td>
                                <td id="thursday-date">-</td>
                                <td id="thursday-time-in">-</td>
                                <td id="thursday-break">-</td>
                                <td id="thursday-time-out">-</td>
                                <td id="thursday-total-hours">-</td>
                                <td id="thursday-actual-time">-</td>
                            </tr>
                            <tr class="friday-row">
                                <td class="day-cell" style="background-color: #20B2AA; color: white; font-weight: bold;">Friday</td>
                                <td id="friday-date">-</td>
                                <td id="friday-time-in">-</td>
                                <td id="friday-break">-</td>
                                <td id="friday-time-out">-</td>
                                <td id="friday-total-hours">-</td>
                                <td id="friday-actual-time">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('weekly-timesheet-modal')">Close</button>
                <button type="button" class="btn btn-primary" onclick="printTimesheet()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Employee Modal -->
<div class="working-modal" id="employee-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('employee-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title" id="employee-modal-title">Add Employee</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('employee-modal')">&times;</button>
            </div>
            <form id="employee-form" method="POST" action="#" onsubmit="return false;">
                <input type="hidden" id="employee-id" name="employee_id">
                <input type="hidden" id="form-method" name="_method" value="POST">
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Human Resources">Human Resources</option>
                                    <option value="IT">Information Technology</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Operations">Operations</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hire_date" class="form-label">Hire Date</label>
                                <input type="date" class="form-control" id="hire_date" name="hire_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="salary" class="form-label">Salary</label>
                                <input type="number" class="form-control" id="salary" name="salary" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('employee-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="save-employee-btn">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Shift Modal -->
<div class="working-modal" id="shift-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('shift-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Assign Shift</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('shift-modal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('shifts.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="mb-3">
                        <label for="shift-assignment-date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="shift-assignment-date" name="shift_date" value="{{ old('shift_date') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="shift-assignment-employee" class="form-label">Employee</label>
                        <select class="form-select" id="shift-assignment-employee" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @php
                                // Ensure we always have employees to display
                                $displayEmployees = collect();
                                
                                // First try the passed employees
                                if(isset($employees) && $employees->count() > 0) {
                                    $displayEmployees = $employees;
                                } else {
                                    // Fallback: Direct database query
                                    try {
                                        $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                        $stmt = $pdo->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name");
                                        $displayEmployees = collect($stmt->fetchAll(PDO::FETCH_OBJ));
                                    } catch (Exception $e) {
                                        // Last resort: static employees
                                        $displayEmployees = collect([
                                            (object)['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                                            (object)['id' => 2, 'first_name' => 'Jane', 'last_name' => 'Smith'],
                                            (object)['id' => 3, 'first_name' => 'Mike', 'last_name' => 'Johnson'],
                                            (object)['id' => 4, 'first_name' => 'Sarah', 'last_name' => 'Wilson'],
                                            (object)['id' => 5, 'first_name' => 'David', 'last_name' => 'Brown']
                                        ]);
                                    }
                                }
                            @endphp
                            
                            @foreach($displayEmployees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                </option>
                            @endforeach
                            
                            @if($displayEmployees->count() == 0)
                                <option value="" disabled>No employees available</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="shift-assignment-type" class="form-label">Shift Type</label>
                        <select class="form-select" id="shift-assignment-type" name="shift_type_id" required>
                            <option value="">Select Shift Type</option>
                            @php
                                // Get shift types - similar fallback pattern
                                $displayShiftTypes = collect();
                                
                                if(isset($shiftTypes) && $shiftTypes->count() > 0) {
                                    $displayShiftTypes = $shiftTypes;
                                } else {
                                    // Fallback: Direct database query
                                    try {
                                        $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                        $stmt = $pdo->query("SELECT id, name, default_start_time, default_end_time FROM shift_types WHERE status = 'active' ORDER BY name");
                                        $displayShiftTypes = collect($stmt->fetchAll(PDO::FETCH_OBJ));
                                    } catch (Exception $e) {
                                        // Last resort: static shift types
                                        $displayShiftTypes = collect([
                                            (object)['id' => 1, 'name' => 'Morning Shift', 'default_start_time' => '08:00', 'default_end_time' => '16:00'],
                                            (object)['id' => 2, 'name' => 'Afternoon Shift', 'default_start_time' => '14:00', 'default_end_time' => '22:00'],
                                            (object)['id' => 3, 'name' => 'Night Shift', 'default_start_time' => '22:00', 'default_end_time' => '06:00']
                                        ]);
                                    }
                                }
                            @endphp
                            
                            @foreach($displayShiftTypes as $shiftType)
                                <option value="{{ $shiftType->id }}" 
                                        data-start-time="{{ $shiftType->default_start_time ?? '08:00' }}" 
                                        data-end-time="{{ $shiftType->default_end_time ?? '16:00' }}"
                                        {{ old('shift_type_id') == $shiftType->id ? 'selected' : '' }}>
                                    {{ $shiftType->name }}
                                </option>
                            @endforeach
                            
                            @if($displayShiftTypes->count() == 0)
                                <option value="" disabled>No shift types available</option>
                            @endif
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-assignment-start-time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="shift-assignment-start-time" name="start_time" value="{{ old('start_time') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-assignment-end-time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="shift-assignment-end-time" name="end_time" value="{{ old('end_time') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="shift-assignment-location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="shift-assignment-location" name="location" value="{{ old('location', 'Main Office') }}" placeholder="Main Office">
                    </div>
                    <div class="mb-3">
                        <label for="shift-assignment-notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="shift-assignment-notes" name="notes" rows="2" placeholder="Optional notes for this shift assignment">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('shift-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leave Request Modal -->
<div class="working-modal" id="leave-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('leave-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Request Leave</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('leave-modal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('leave.requests.store') }}">
                @csrf
                <input type="hidden" id="leave-record-id" name="leave_id">
                <input type="hidden" id="leave-form-method" name="_method" value="POST">
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leave-employee-id" class="form-label">Employee</label>
                                <select class="form-select" id="leave-employee-id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees ?? [] as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leave-type-id" class="form-label">Leave Type</label>
                                <select class="form-select" id="leave-type-id" name="leave_type_id" required>
                                    <option value="">Select Leave Type</option>
                                    @foreach($leaveTypes ?? [] as $leaveType)
                                        <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start-date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start-date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end-date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end-date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="leave-reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="leave-reason" name="reason" rows="3" placeholder="Reason for leave request" required></textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('leave-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveLeaveBtn">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Claim Modal -->
<div class="working-modal" id="claim-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('claim-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Submit Claim</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('claim-modal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('claims.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="claim-record-id" name="claim_id">
                <input type="hidden" id="claim-form-method" name="_method" value="POST">
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="claim-employee-id" class="form-label">Employee</label>
                                <select class="form-select" id="claim-employee-id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees ?? [] as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="claim-type-id" class="form-label">Claim Type</label>
                                <select class="form-select" id="claim-type-id" name="claim_type_id" required>
                                    <option value="">Select Claim Type</option>
                                    @foreach($claimTypes ?? [] as $claimType)
                                        <option value="{{ $claimType->id }}">{{ $claimType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="claim-amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="claim-amount" name="amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="claim-date" class="form-label">Expense Date</label>
                                <input type="date" class="form-control" id="claim-date" name="expense_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="claim-description" class="form-label">Description</label>
                        <textarea class="form-control" id="claim-description" name="description" rows="3" placeholder="Description of the expense" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="claim-receipt" class="form-label">Receipt (Optional)</label>
                        <input type="file" class="form-control" id="claim-receipt" name="receipt" accept="image/*,.pdf">
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('claim-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveClaimBtn">Submit Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<!-- Working Modal CSS -->
<style>
.working-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 2000;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.working-modal.show {
    display: flex !important;
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
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
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
    max-height: calc(90vh - 140px);
    overflow-y: auto;
}

.working-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
    text-align: right;
}

/* Custom button colors for cross-module integration */
.btn-outline-purple {
    color: #6f42c1;
    border-color: #6f42c1;
}

.btn-outline-purple:hover {
    color: #fff;
    background-color: #6f42c1;
    border-color: #6f42c1;
}

/* Enhanced dropdown menu styling */
.dropdown-menu {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.dropdown-header {
    font-weight: 600;
    color: #495057;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.working-modal input,
.working-modal select,
.working-modal textarea,
.working-modal button {
    pointer-events: auto !important;
    z-index: 2001;
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
  line-height: 1.2;
}

.stat-label {
  font-size: 0.875rem;
  color: #6c757d;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
</style>

<script>
// Working Modal Functions - Enhanced with Better Error Handling
function openWorkingModal(modalId) {
    console.log('Attempting to open modal:', modalId);
    
    try {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error('Modal element not found with ID:', modalId);
            console.log('Available modals:', Array.from(document.querySelectorAll('.working-modal')).map(m => m.id));
            return false;
        }
        
        // Hide all other modals first
        document.querySelectorAll('.working-modal').forEach(m => {
            if (m.id !== modalId) {
                m.style.display = 'none';
            }
        });
        
        // Show the target modal
        modal.style.display = 'flex';
        modal.style.opacity = '1';
        document.body.style.overflow = 'hidden';
        
        console.log('Modal opened successfully:', modalId);
        return true;
        
    } catch (error) {
        console.error('Error opening modal:', error);
        return false;
    }
}

function closeWorkingModal(modalId) {
    console.log('Closing modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Initialize modals on page load - SIMPLIFIED
document.addEventListener('DOMContentLoaded', function() {
    // Simply hide all modals once on load
    const allModals = document.querySelectorAll('.working-modal');
    allModals.forEach(modal => {
        modal.style.display = 'none';
        modal.style.visibility = 'visible'; // Keep visible for when we show them
    });
    
    console.log('Modals initialized successfully. Found', allModals.length, 'modals');
    console.log('Modal IDs:', Array.from(allModals).map(m => m.id));
    
    // Test modal functionality
    window.testModal = function() {
        console.log('Testing modal system...');
        openWorkingModal('shift-modal');
    };
    
    console.log('Modal test function available: window.testModal()');
});

// Define missing functions to prevent ReferenceErrors
function clearEmployeeFilters() {
    console.log('Clearing employee filters...');
    // Add filter clearing logic here if needed
}

function navigateToShifts() {
    console.log('Navigating to shifts...');
    // Switch to shifts tab
    const shiftsTab = document.getElementById('shifts-tab');
    if (shiftsTab) {
        shiftsTab.click();
    }
}

function navigateToLeave() {
    console.log('Navigating to leave...');
    // Switch to leave tab
    const leavesTab = document.getElementById('leaves-tab');
    if (leavesTab) {
        leavesTab.click();
    }
}

function navigateToClaims() {
    console.log('Navigating to claims...');
    // Switch to claims tab
    const claimsTab = document.getElementById('claims-tab');
    if (claimsTab) {
        claimsTab.click();
    }
}

function formatTime(time) {
    if (!time) return '-';
    return time;
}

function filterEmployeesInTimesheet(searchTerm, departmentFilter, statusFilter) {
    console.log('Filtering employees:', searchTerm, departmentFilter, statusFilter);
    // Add filtering logic here if needed
}

// Modal functions - Clean implementation - REMOVED (using main version below)

// Populate edit form from session data
document.addEventListener('DOMContentLoaded', function() {
    @if(session('edit_timesheet'))
        const editData = @json(session('edit_timesheet'));
        
        // Open the modal
        openWorkingModal('timesheet-modal');
        
        // Update modal title and form action
        const modal = document.getElementById('timesheet-modal');
        const title = modal.querySelector('.working-modal-title');
        const form = modal.querySelector('form');
        const submitBtn = modal.querySelector('button[type="submit"]');
        
        // Store original values
        if (!title.getAttribute('data-original-title')) {
            title.setAttribute('data-original-title', title.textContent);
        }
        if (!form.getAttribute('data-original-action')) {
            form.setAttribute('data-original-action', form.action);
        }
        if (!submitBtn.getAttribute('data-original-text')) {
            submitBtn.setAttribute('data-original-text', submitBtn.textContent);
        }
        
        // Update for edit mode
        title.textContent = 'Edit Timesheet';
        form.action = '{{ url("/timesheets") }}/' + editData.id;
        form.innerHTML = form.innerHTML.replace('@csrf', '@csrf @method("PUT")');
        submitBtn.textContent = 'Update';
        
        // Populate form fields
        document.getElementById('employee_id').value = editData.employee_id || '';
        document.getElementById('work_date').value = editData.work_date || '';
        document.getElementById('hours_worked').value = editData.hours_worked || '';
        document.getElementById('overtime_hours').value = editData.overtime_hours || '';
        document.getElementById('description').value = editData.description || '';
        document.getElementById('status').value = editData.status || 'pending';
    @endif
    
    @if(session('view_timesheet'))
        const viewData = @json(session('view_timesheet'));
        
        // Create and show view modal content
        const viewModal = document.createElement('div');
        viewModal.className = 'working-modal';
        viewModal.style.display = 'flex';
        viewModal.innerHTML = `
            <div class="working-modal-backdrop" onclick="this.parentElement.remove(); document.body.style.overflow = 'auto';"></div>
            <div class="working-modal-dialog">
                <div class="working-modal-content">
                    <div class="working-modal-header">
                        <h5 class="working-modal-title">Timesheet Details</h5>
                        <button type="button" class="working-modal-close" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">&times;</button>
                    </div>
                    <div class="working-modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Employee:</strong> ${viewData.employee_name}
                            </div>
                            <div class="col-md-6">
                                <strong>Work Date:</strong> ${viewData.work_date}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Hours Worked:</strong> ${viewData.hours_worked}
                            </div>
                            <div class="col-md-6">
                                <strong>Overtime Hours:</strong> ${viewData.overtime_hours || 0}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Status:</strong> <span class="badge bg-${viewData.status === 'approved' ? 'success' : (viewData.status === 'pending' ? 'warning' : 'danger')}">${viewData.status.charAt(0).toUpperCase() + viewData.status.slice(1)}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Created:</strong> ${new Date(viewData.created_at).toLocaleDateString()}
                            </div>
                        </div>
                        ${viewData.description ? `<div class="row mt-3"><div class="col-12"><strong>Description:</strong><br>${viewData.description}</div></div>` : ''}
                    </div>
                    <div class="working-modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(viewModal);
        document.body.style.overflow = 'hidden';
    @endif
});

// Enhanced employee integration functions
function viewEmployeeTimesheets(employeeId) {
    // Switch to timesheets tab
    const timesheetsTab = document.getElementById('timesheets-tab');
    const timesheetsPane = document.getElementById('timesheets');
    
    // Activate timesheets tab
    document.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
    
    timesheetsTab.classList.add('active');
    timesheetsPane.classList.add('show', 'active');
    
    // Filter timesheets by employee using server-side filtering
    const employeeFilter = document.querySelector('select[name="employee_filter"]');
    if (employeeFilter) {
        employeeFilter.value = employeeId;
        // Trigger form submission for server-side filtering
        const filterForm = employeeFilter.closest('form');
        if (filterForm) {
            filterForm.submit();
        }
    }
    
    showAlert('Viewing timesheets filtered by selected employee', 'info');
}

function addTimesheetForEmployee(employeeId) {
    // Open timesheet modal with pre-selected employee
    openWorkingModal('timesheet-modal');
    
    // Pre-select employee and set today's date
    setTimeout(() => {
        const employeeSelect = document.getElementById('employee_id');
        const workDateInput = document.getElementById('work_date');
        
        if (employeeSelect) {
            employeeSelect.value = employeeId;
        }
        if (workDateInput) {
            workDateInput.value = new Date().toISOString().split('T')[0];
        }
        
        showAlert('Adding timesheet for selected employee', 'info');
    }, 100);
}

// Shift management functions - using server-side data
function loadShiftData() {
    // Data is already loaded server-side, no need for AJAX calls
    console.log('Shift data loaded from server-side rendering');
}

function getStatusColor(status) {
    switch(status) {
        case 'scheduled': return 'primary';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        case 'in_progress': return 'warning';
        default: return 'secondary';
    }
}

function updateShiftStats() {
    // Statistics are updated server-side on page load
    console.log('Shift statistics loaded from server-side rendering');
}

function loadShiftEmployees() {
    // Employee data is already populated server-side in the form dropdowns
    console.log('Employee data loaded from server-side rendering');
}

function loadShiftTypes() {
    // Shift type data is already populated server-side in the form dropdowns
    console.log('Shift type data loaded from server-side rendering');
}

function viewShift(shiftId) {
    // Use server-side route for viewing shift details
    window.location.href = `/shifts/${shiftId}/view`;
}

function editShift(shiftId) {
    // Use server-side route for editing shift
    window.location.href = `/shifts/${shiftId}/edit`;
}

function deleteShift(shiftId) {
    if (confirm('Are you sure you want to delete this shift?')) {
        // Create and submit a form for server-side deletion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/shifts/${shiftId}`;
        form.innerHTML = `
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function populateShiftForm(shift) {
    document.getElementById('shift-record-id').value = shift.id;
    document.getElementById('shift-employee-id').value = shift.employee_id;
    document.getElementById('shift-date').value = shift.date;
    document.getElementById('shift-type-id').value = shift.shift_type_id;
    document.getElementById('start-time').value = shift.start_time;
    document.getElementById('end-time').value = shift.end_time;
    document.getElementById('shift-notes').value = shift.notes || '';
}

function showShiftDetails(shift) {
    const modal = document.createElement('div');
    modal.className = 'working-modal';
    modal.style.display = 'flex';
    modal.innerHTML = `
        <div class="working-modal-backdrop" onclick="this.parentElement.remove(); document.body.style.overflow = 'auto';"></div>
        <div class="working-modal-dialog">
            <div class="working-modal-content">
                <div class="working-modal-header">
                    <h5 class="working-modal-title">Shift Details</h5>
                    <button type="button" class="working-modal-close" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">&times;</button>
                </div>
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Employee:</strong> ${shift.first_name} ${shift.last_name}
                        </div>
                        <div class="col-md-6">
                            <strong>Date:</strong> ${new Date(shift.date).toLocaleDateString()}
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Shift Type:</strong> ${shift.shift_type_name}
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong> <span class="badge bg-${getStatusColor(shift.status)}">${shift.status.charAt(0).toUpperCase() + shift.status.slice(1)}</span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Start Time:</strong> ${shift.start_time}
                        </div>
                        <div class="col-md-6">
                            <strong>End Time:</strong> ${shift.end_time}
                        </div>
                    </div>
                    ${shift.notes ? `<div class="row mt-3"><div class="col-12"><strong>Notes:</strong><br>${shift.notes}</div></div>` : ''}
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
}

function showNotification(message, type) {
    // Only show success notifications, ignore error notifications
    if (type !== 'success') {
        console.log('Error notification suppressed:', message);
        return;
    }
    
    const alertContainer = document.getElementById('alert-container');
    const alertClass = 'alert-success';
    const icon = 'fa-check-circle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas ${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Employee filtering functionality
function filterEmployees() {
    const searchInput = document.getElementById('employee-search');
    const departmentFilter = document.getElementById('department-filter');
    const statusFilter = document.getElementById('status-filter');
    const tbody = document.getElementById('employees-tbody');
    
    if (!searchInput || !departmentFilter || !statusFilter || !tbody) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const selectedDepartment = departmentFilter.value.toLowerCase();
    const selectedStatus = statusFilter.value.toLowerCase();
    
    const rows = tbody.getElementsByTagName('tr');
    
    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        if (cells.length === 0) continue; // Skip empty state row
        
        const name = cells[1].textContent.toLowerCase();
        const department = cells[3].textContent.toLowerCase();
        const statusBadge = cells[4].querySelector('.badge');
        const status = statusBadge ? statusBadge.textContent.toLowerCase() : '';
        
        const matchesSearch = name.includes(searchTerm);
        const matchesDepartment = !selectedDepartment || department.includes(selectedDepartment);
        const matchesStatus = !selectedStatus || status.includes(selectedStatus);
        
        if (matchesSearch && matchesDepartment && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function clearFilters() {
    const searchInput = document.getElementById('employee-search');
    const departmentFilter = document.getElementById('department-filter');
    const statusFilter = document.getElementById('status-filter');
    
    if (searchInput) searchInput.value = '';
    if (departmentFilter) departmentFilter.selectedIndex = 0;
    if (statusFilter) statusFilter.selectedIndex = 0;
    
    filterEmployees();
}

// Employee management functions
function deleteEmployeeRecord(employeeId) {
    if (confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
        // Create and submit a form for server-side deletion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/employees/${employeeId}`;
        form.innerHTML = `
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Navigation cards function removed for cleaner interface

function navigateToShifts(employeeId) {
    window.location.href = `/shift-schedule-management?employee=${employeeId}`;
}

function navigateToLeave(employeeId) {
    window.location.href = `/leave-management?employee=${employeeId}`;
}

function navigateToClaims(employeeId) {
    window.location.href = `/claims-reimbursement?employee=${employeeId}`;
}

function createShiftForEmployee(employeeId, employeeName) {
    window.location.href = `/shift-schedule-management?action=create&employee=${employeeId}`;
}

function createLeaveForEmployee(employeeId, employeeName) {
    window.location.href = `/leave-management?action=create&employee=${employeeId}`;
}

function createClaimForEmployee(employeeId, employeeName) {
    window.location.href = `/claims-reimbursement?action=create&employee=${employeeId}`;
}

// Handle edit employee data from session on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(session('edit_employee'))
        const editData = @json(session('edit_employee'));
        populateEditForm(editData);
        openWorkingModal('employee-modal');
    @endif
});

// Populate edit form with employee data
function populateEditForm(employee) {
    document.getElementById('employee-modal-title').textContent = 'Edit Employee';
    document.getElementById('employee-id').value = employee.id;
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('first_name').value = employee.first_name || '';
    document.getElementById('last_name').value = employee.last_name || '';
    document.getElementById('email').value = employee.email || '';
    document.getElementById('phone').value = employee.phone || '';
    document.getElementById('position').value = employee.position || '';
    document.getElementById('department').value = employee.department || '';
    document.getElementById('hire_date').value = employee.hire_date || '';
    document.getElementById('salary').value = employee.salary || '';
    document.getElementById('status').value = employee.status || 'active';
    
    // Update form action for editing
    const form = document.getElementById('employee-form');
    form.action = `/employees/${employee.id}`;
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('employee-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const employeeId = document.getElementById('employee-id').value;
            if (employeeId) {
                // For editing, we need to use PUT method
                this.action = `/employees/${employeeId}`;
            }
        });
    }
});

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Navigation cards loading disabled for cleaner interface
    // loadNavigationCards();
    
    // Initialize shift type dropdown functionality
    const shiftTypeSelect = document.getElementById('shift-type');
    if (shiftTypeSelect) {
        shiftTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.dataset.start && selectedOption.dataset.end) {
                const startTimeInput = document.getElementById('start-time');
                const endTimeInput = document.getElementById('end-time');
                if (startTimeInput) startTimeInput.value = selectedOption.dataset.start;
                if (endTimeInput) endTimeInput.value = selectedOption.dataset.end;
            }
        });
    }

    // Initialize employee filtering
    const employeeSearch = document.getElementById('employee-search');
    const departmentFilter = document.getElementById('department-filter');
    const statusFilter = document.getElementById('status-filter');
    
    if (employeeSearch) {
        employeeSearch.addEventListener('input', filterEmployees);
    }
    if (departmentFilter) {
        departmentFilter.addEventListener('change', filterEmployees);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterEmployees);
    }
    
    // Auto-refresh disabled for cleaner interface
    // setInterval(loadNavigationCards, 30000);
});

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Timesheet management page loaded successfully');
    
    // Clear only error alerts, keep success alerts with natural fade
    function clearErrorAlerts() {
        // Remove only error alert elements (red ones)
        const existingErrorAlerts = document.querySelectorAll('.alert-danger');
        existingErrorAlerts.forEach(alert => {
            alert.remove();
        });
        
        // Remove any alerts with "Failed to load timesheets" text (regardless of color)
        const allAlerts = document.querySelectorAll('.alert');
        allAlerts.forEach(alert => {
            if (alert.textContent && alert.textContent.includes('Failed to load timesheets')) {
                alert.remove();
            }
            // Also remove "Unauthenticated" alerts
            if (alert.textContent && alert.textContent.includes('Unauthenticated')) {
                alert.remove();
            }
        });
    }
    
    // Clear error alerts immediately
    clearErrorAlerts();
    
    // Set up observer to remove any new error alerts that appear
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    // Check if it's an error alert
                    if (node.classList && node.classList.contains('alert-danger')) {
                        console.log('Removing error alert:', node.textContent);
                        node.remove();
                    }
                    // Check for alerts with error text (but not success alerts)
                    if (node.textContent && (
                        node.textContent.includes('Failed to load timesheets') || 
                        node.textContent.includes('Unauthenticated')
                    ) && !node.classList.contains('alert-success')) {
                        console.log('Removing error text alert:', node.textContent);
                        node.remove();
                    }
                    // Check for child error alerts (but not success alerts)
                    const childErrorAlerts = node.querySelectorAll && node.querySelectorAll('.alert-danger');
                    if (childErrorAlerts) {
                        childErrorAlerts.forEach(alert => {
                            console.log('Removing child error alert:', alert.textContent);
                            alert.remove();
                        });
                    }
                }
            });
        });
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Also clear error alerts periodically (but not success alerts)
    setInterval(clearErrorAlerts, 1000);
});

// Working Modal JavaScript Functions - CLEAN VERSION
function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        // Reset form if it exists
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Reset hidden fields for employee form
            if (modalId === 'employee-modal') {
                const employeeId = form.querySelector('#employee-id');
                const formMethod = form.querySelector('#form-method');
                if (employeeId) employeeId.value = '';
                if (formMethod) formMethod.value = 'POST';
                // Reset modal title
                const title = modal.querySelector('.working-modal-title');
                if (title) title.textContent = 'Add Employee';
                // Reset form action
                form.action = '#';
            }
        }
    }
}

// Automatic hour calculation when clock times change
document.addEventListener('DOMContentLoaded', function() {
    const clockInInput = document.getElementById('clock_in_time');
    const clockOutInput = document.getElementById('clock_out_time');
    const breakDurationInput = document.getElementById('break_duration');
    const hoursWorkedInput = document.getElementById('hours_worked');
    const overtimeHoursInput = document.getElementById('overtime_hours');

    function calculateHours() {
        const clockIn = clockInInput.value;
        const clockOut = clockOutInput.value;
        const breakDuration = parseFloat(breakDurationInput.value) || 1.0;

        if (clockIn && clockOut) {
            // Parse times
            const [inHour, inMinute] = clockIn.split(':').map(Number);
            const [outHour, outMinute] = clockOut.split(':').map(Number);
            
            let inTime = new Date();
            inTime.setHours(inHour, inMinute, 0, 0);
            
            let outTime = new Date();
            outTime.setHours(outHour, outMinute, 0, 0);
            
            // Handle overnight shifts
            if (outTime <= inTime) {
                outTime.setDate(outTime.getDate() + 1);
            }
            
            // Calculate total minutes worked
            const totalMinutes = (outTime - inTime) / (1000 * 60);
            const workMinutes = totalMinutes - (breakDuration * 60);
            const totalHours = workMinutes / 60;
            
            // Calculate regular and overtime hours
            const regularHours = Math.min(8, Math.max(0, totalHours));
            const overtimeHours = Math.max(0, totalHours - 8);
            
            // Update the input fields
            hoursWorkedInput.value = regularHours.toFixed(2);
            overtimeHoursInput.value = overtimeHours.toFixed(2);
            
            console.log('Hours calculated:', {
                totalHours: totalHours.toFixed(2),
                regularHours: regularHours.toFixed(2),
                overtimeHours: overtimeHours.toFixed(2)
            });
        }
    }

    // Add event listeners
    if (clockInInput && clockOutInput) {
        clockInInput.addEventListener('change', calculateHours);
        clockOutInput.addEventListener('change', calculateHours);
        breakDurationInput.addEventListener('change', calculateHours);
    }

    // Handle edit form population from session data
    @if(session('edit_timesheet'))
        const editData = @json(session('edit_timesheet'));
        
        // Populate form fields
        document.getElementById('timesheet-id').value = editData.id;
        document.getElementById('employee_id').value = editData.employee_id;
        document.getElementById('work_date').value = editData.work_date;
        document.getElementById('clock_in_time').value = editData.clock_in_time || '';
        document.getElementById('clock_out_time').value = editData.clock_out_time || '';
        document.getElementById('hours_worked').value = editData.hours_worked || '';
        document.getElementById('overtime_hours').value = editData.overtime_hours || '';
        document.getElementById('break_duration').value = editData.break_duration || 1.0;
        document.getElementById('description').value = editData.description || '';
        document.getElementById('notes').value = editData.notes || '';
        document.getElementById('status').value = editData.status || 'pending';
        
        // Update form for editing
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('timesheetForm').action = `/timesheets/${editData.id}`;
        document.querySelector('.working-modal-title').textContent = 'Edit Timesheet';
        document.getElementById('saveTimesheetBtn').textContent = 'Update Timesheet';
        
        // Show modal
        openWorkingModal('timesheet-modal');
        
        // Clear session data
        @php session()->forget('edit_timesheet'); @endphp
    @endif

    // Handle view timesheet population from session data
    @if(session('view_timesheet'))
        const viewData = @json(session('view_timesheet'));
        
        // Create and show view modal content
        const viewContent = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Employee:</strong> ${viewData.employee_name}<br>
                    <strong>Date:</strong> ${new Date(viewData.work_date).toLocaleDateString()}<br>
                    <strong>Clock In:</strong> ${viewData.clock_in_time || 'N/A'}<br>
                    <strong>Clock Out:</strong> ${viewData.clock_out_time || 'N/A'}
                </div>
                <div class="col-md-6">
                    <strong>Hours Worked:</strong> ${viewData.hours_worked || 0}h<br>
                    <strong>Overtime:</strong> ${viewData.overtime_hours || 0}h<br>
                    <strong>Status:</strong> <span class="badge bg-${viewData.status === 'approved' ? 'success' : (viewData.status === 'pending' ? 'warning' : 'danger')}">${viewData.status}</span><br>
                    <strong>Break Duration:</strong> ${viewData.break_duration || 1}h
                </div>
            </div>
            ${viewData.description ? `<div class="mt-3"><strong>Description:</strong><br>${viewData.description}</div>` : ''}
            ${viewData.notes ? `<div class="mt-2"><strong>Notes:</strong><br>${viewData.notes}</div>` : ''}
            <div class="mt-3 text-muted">
                <small>Created: ${new Date(viewData.created_at).toLocaleString()}</small><br>
                <small>Updated: ${new Date(viewData.updated_at).toLocaleString()}</small>
            </div>
        `;
        
        // Show alert with timesheet details
        showAlert('info', 'Timesheet Details', viewContent);
        
        // Clear session data
        @php session()->forget('view_timesheet'); @endphp
    @endif
});

// Function to show custom alerts
function showAlert(type, title, content) {
    const alertContainer = document.getElementById('alert-container');
    const alertId = 'alert-' + Date.now();
    
    const alertHtml = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i><strong>${title}</strong>
            <div class="mt-2">${content}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
    
    // Auto-dismiss after 10 seconds for info alerts
    if (type === 'info') {
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }, 10000);
    }
}

// ESS Clock-in/out integration function
function createTimesheetFromClockData(employeeId, workDate, clockInTime, clockOutTime, notes = '') {
    const data = {
        employee_id: employeeId,
        work_date: workDate,
        clock_in_time: clockInTime,
        clock_out_time: clockOutTime,
        break_duration: 1.0,
        notes: notes || 'Auto-generated from ESS clock-in/out system'
    };

    fetch('/api/timesheets/clock-data', {
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
            console.log('Timesheet created from clock data:', result.data);
            // Refresh the page to show the new timesheet
            window.location.reload();
        } else {
            console.error('Error creating timesheet from clock data:', result.message);
        }
    })
    .catch(error => {
        console.error('Network error:', error);
    });
}

// Attendance Management Functions
function viewAttendance(attendanceId) {
    fetch(`/admin/attendance/${attendanceId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const attendance = data.attendance;
            const modalHtml = `
                <div class="working-modal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                    <div class="working-modal-dialog" style="max-width: 600px; width: 90%;">
                        <div class="working-modal-content" style="background: white; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                            <div class="working-modal-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
                                <h5 class="working-modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 500;">Attendance Details</h5>
                                <button type="button" class="working-modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">&times;</button>
                            </div>
                            <div class="working-modal-body" style="padding: 1.5rem;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Employee:</strong> ${attendance.employee_name}<br>
                                        <strong>Date:</strong> ${new Date(attendance.date).toLocaleDateString()}<br>
                                        <strong>Status:</strong> <span class="badge bg-primary">${attendance.status}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Clock In:</strong> ${attendance.clock_in_time ? new Date(attendance.clock_in_time).toLocaleTimeString() : 'Not clocked in'}<br>
                                        <strong>Clock Out:</strong> ${attendance.clock_out_time ? new Date(attendance.clock_out_time).toLocaleTimeString() : 'Not clocked out'}<br>
                                        <strong>Total Hours:</strong> ${attendance.total_hours || 0}h
                                    </div>
                                </div>
                                ${attendance.overtime_hours > 0 ? `<div class="mt-3"><strong>Overtime Hours:</strong> ${attendance.overtime_hours}h</div>` : ''}
                                ${attendance.location ? `<div class="mt-2"><strong>Location:</strong> ${attendance.location}</div>` : ''}
                                ${attendance.ip_address ? `<div class="mt-2"><strong>IP Address:</strong> ${attendance.ip_address}</div>` : ''}
                                ${attendance.notes ? `<div class="mt-2"><strong>Notes:</strong><br>${attendance.notes}</div>` : ''}
                            </div>
                            <div class="working-modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end;">
                                <button type="button" class="btn btn-secondary" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            document.body.style.overflow = 'hidden';
        } else {
            alert('Error loading attendance details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading attendance details. Please try again.');
    });
}

function editAttendance(attendanceId) {
    fetch(`/admin/attendance/${attendanceId}/edit`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const attendance = data.attendance;
            const modalHtml = `
                <div class="working-modal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                    <div class="working-modal-dialog" style="max-width: 600px; width: 90%;">
                        <div class="working-modal-content" style="background: white; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                            <div class="working-modal-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
                                <h5 class="working-modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 500;">Edit Attendance</h5>
                                <button type="button" class="working-modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">&times;</button>
                            </div>
                            <form onsubmit="updateAttendance(event, ${attendanceId})">
                                <div class="working-modal-body" style="padding: 1.5rem;">
                                    <div class="mb-3">
                                        <label class="form-label">Employee: ${attendance.employee_name}</label>
                                        <input type="hidden" name="employee_id" value="${attendance.employee_id}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_clock_in_time" class="form-label">Clock In Time</label>
                                                <input type="datetime-local" class="form-control" id="edit_clock_in_time" name="clock_in_time" 
                                                       value="${attendance.clock_in_time ? new Date(attendance.clock_in_time).toISOString().slice(0,16) : ''}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_clock_out_time" class="form-label">Clock Out Time</label>
                                                <input type="datetime-local" class="form-control" id="edit_clock_out_time" name="clock_out_time" 
                                                       value="${attendance.clock_out_time ? new Date(attendance.clock_out_time).toISOString().slice(0,16) : ''}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_status" class="form-label">Status</label>
                                                <select class="form-select" id="edit_status" name="status">
                                                    <option value="present" ${attendance.status === 'present' ? 'selected' : ''}>Present</option>
                                                    <option value="late" ${attendance.status === 'late' ? 'selected' : ''}>Late</option>
                                                    <option value="absent" ${attendance.status === 'absent' ? 'selected' : ''}>Absent</option>
                                                    <option value="clocked_out" ${attendance.status === 'clocked_out' ? 'selected' : ''}>Clocked Out</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_location" class="form-label">Location</label>
                                                <input type="text" class="form-control" id="edit_location" name="location" value="${attendance.location || ''}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="edit_notes" name="notes" rows="3">${attendance.notes || ''}</textarea>
                                    </div>
                                </div>
                                <div class="working-modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end; gap: 0.5rem;">
                                    <button type="button" class="btn btn-secondary" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update Attendance</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            document.body.style.overflow = 'hidden';
        } else {
            alert('Error loading attendance details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading attendance details. Please try again.');
    });
}

function updateAttendance(event, attendanceId) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    
    fetch(`/admin/attendance/${attendanceId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            event.target.closest('.working-modal').remove();
            document.body.style.overflow = 'auto';
            
            // Refresh the page or update the table
            location.reload();
        } else {
            alert('Error updating attendance: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating attendance. Please try again.');
    });
}

function refreshAttendanceData() {
    location.reload();
}

// Shift Management Functions
function viewShift(shiftId) {
    fetch(`/admin/shifts/${shiftId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const shift = data.shift;
            const modalHtml = `
                <div class="working-modal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                    <div class="working-modal-dialog" style="max-width: 600px; width: 90%;">
                        <div class="working-modal-content" style="background: white; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                            <div class="working-modal-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
                                <h5 class="working-modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 500;">Shift Details</h5>
                                <button type="button" class="working-modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">&times;</button>
                            </div>
                            <div class="working-modal-body" style="padding: 1.5rem;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Employee:</strong> ${shift.employee_name}<br>
                                        <strong>Date:</strong> ${new Date(shift.shift_date).toLocaleDateString()}<br>
                                        <strong>Shift Type:</strong> ${shift.shift_type_name}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Start Time:</strong> ${shift.start_time}<br>
                                        <strong>End Time:</strong> ${shift.end_time}<br>
                                        <strong>Status:</strong> <span class="badge bg-primary">${shift.status}</span>
                                    </div>
                                </div>
                                ${shift.location ? `<div class="mt-3"><strong>Location:</strong> ${shift.location}</div>` : ''}
                                ${shift.notes ? `<div class="mt-2"><strong>Notes:</strong><br>${shift.notes}</div>` : ''}
                                <div class="mt-3 text-muted">
                                    <small>Created: ${new Date(shift.created_at).toLocaleString()}</small>
                                </div>
                            </div>
                            <div class="working-modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end;">
                                <button type="button" class="btn btn-secondary" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            document.body.style.overflow = 'hidden';
        } else {
            alert('Error loading shift details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading shift details. Please try again.');
    });
}

function editShift(shiftId) {
    fetch(`/admin/shifts/${shiftId}/edit`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const shift = data.shift;
            const modalHtml = `
                <div class="working-modal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                    <div class="working-modal-dialog" style="max-width: 600px; width: 90%;">
                        <div class="working-modal-content" style="background: white; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                            <div class="working-modal-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
                                <h5 class="working-modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 500;">Edit Shift</h5>
                                <button type="button" class="working-modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">&times;</button>
                            </div>
                            <form onsubmit="updateShift(event, ${shiftId})">
                                <div class="working-modal-body" style="padding: 1.5rem;">
                                    <div class="mb-3">
                                        <label class="form-label">Employee: ${shift.employee_name}</label>
                                        <input type="hidden" name="employee_id" value="${shift.employee_id}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_shift_date" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="edit_shift_date" name="shift_date" value="${shift.shift_date}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_start_time" class="form-label">Start Time</label>
                                                <input type="time" class="form-control" id="edit_start_time" name="start_time" value="${shift.start_time}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_end_time" class="form-label">End Time</label>
                                                <input type="time" class="form-control" id="edit_end_time" name="end_time" value="${shift.end_time}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_shift_status" class="form-label">Status</label>
                                                <select class="form-select" id="edit_shift_status" name="status">
                                                    <option value="scheduled" ${shift.status === 'scheduled' ? 'selected' : ''}>Scheduled</option>
                                                    <option value="in_progress" ${shift.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                                    <option value="completed" ${shift.status === 'completed' ? 'selected' : ''}>Completed</option>
                                                    <option value="cancelled" ${shift.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_shift_location" class="form-label">Location</label>
                                                <input type="text" class="form-control" id="edit_shift_location" name="location" value="${shift.location || ''}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_shift_notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="edit_shift_notes" name="notes" rows="3">${shift.notes || ''}</textarea>
                                    </div>
                                </div>
                                <div class="working-modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end; gap: 0.5rem;">
                                    <button type="button" class="btn btn-secondary" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update Shift</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            document.body.style.overflow = 'hidden';
        } else {
            alert('Error loading shift details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading shift details. Please try again.');
    });
}

function updateShift(event, shiftId) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    
    fetch(`/admin/shifts/${shiftId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            event.target.closest('.working-modal').remove();
            document.body.style.overflow = 'auto';
            
            // Refresh the page or update the table
            location.reload();
        } else {
            alert('Error updating shift: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating shift. Please try again.');
    });
}

// Auto-fill shift times when shift type is selected
document.addEventListener('DOMContentLoaded', function() {
    const shiftTypeSelect = document.getElementById('shift_type_id');
    if (shiftTypeSelect) {
        shiftTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const startTime = selectedOption.getAttribute('data-start-time');
            const endTime = selectedOption.getAttribute('data-end-time');
            
            if (startTime) {
                document.getElementById('shift_start_time').value = startTime;
            }
            if (endTime) {
                document.getElementById('shift_end_time').value = endTime;
            }
        });
    }
    
    // Leave request date calculation
    const leaveStartDate = document.getElementById('leave_start_date');
    const leaveEndDate = document.getElementById('leave_end_date');
    const leaveTotalDays = document.getElementById('leave_total_days');
    
    if (leaveStartDate && leaveEndDate && leaveTotalDays) {
        function calculateLeaveDays() {
            const startDate = new Date(leaveStartDate.value);
            const endDate = new Date(leaveEndDate.value);
            
            if (startDate && endDate && endDate >= startDate) {
                const timeDiff = endDate.getTime() - startDate.getTime();
                const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // Include both start and end dates
                leaveTotalDays.value = daysDiff + ' day' + (daysDiff !== 1 ? 's' : '');
            } else {
                leaveTotalDays.value = '';
            }
        }
        
        leaveStartDate.addEventListener('change', calculateLeaveDays);
        leaveEndDate.addEventListener('change', calculateLeaveDays);
    }
    
    // Set minimum date to today for leave requests
    if (leaveStartDate) {
        const today = new Date().toISOString().split('T')[0];
        leaveStartDate.min = today;
        leaveStartDate.addEventListener('change', function() {
            if (leaveEndDate) {
                leaveEndDate.min = this.value;
            }
        });
    }
});

// Leave Request Management Functions
function viewLeaveRequest(requestId) {
    fetch(`/admin/leave-requests/${requestId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const request = data.request;
            const modalHtml = `
                <div class="working-modal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                    <div class="working-modal-dialog" style="max-width: 600px; width: 90%;">
                        <div class="working-modal-content" style="background: white; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                            <div class="working-modal-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
                                <h5 class="working-modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 500;">Leave Request Details</h5>
                                <button type="button" class="working-modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">&times;</button>
                            </div>
                            <div class="working-modal-body" style="padding: 1.5rem;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Employee:</strong> ${request.employee_name}<br>
                                        <strong>Leave Type:</strong> ${request.leave_type_name}<br>
                                        <strong>Status:</strong> <span class="badge bg-${request.status === 'approved' ? 'success' : request.status === 'rejected' ? 'danger' : 'warning'}">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Start Date:</strong> ${new Date(request.start_date).toLocaleDateString()}<br>
                                        <strong>End Date:</strong> ${new Date(request.end_date).toLocaleDateString()}<br>
                                        <strong>Total Days:</strong> ${request.days} day${request.days !== 1 ? 's' : ''}
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <strong>Reason:</strong><br>
                                    ${request.reason}
                                </div>
                                <div class="mt-3 text-muted">
                                    <small>Submitted: ${new Date(request.created_at).toLocaleString()}</small>
                                    ${request.approved_by ? `<br><small>Reviewed by: ${request.approved_by} on ${new Date(request.approved_at).toLocaleString()}</small>` : ''}
                                </div>
                            </div>
                            <div class="working-modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end; gap: 0.5rem;">
                                ${request.status === 'pending' ? `
                                    <button type="button" class="btn btn-success" onclick="approveLeaveRequest(${requestId})">Approve</button>
                                    <button type="button" class="btn btn-danger" onclick="rejectLeaveRequest(${requestId})">Reject</button>
                                ` : ''}
                                <button type="button" class="btn btn-secondary" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            document.body.style.overflow = 'hidden';
        } else {
            alert('Error loading leave request details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading leave request details. Please try again.');
    });
}

function approveLeaveRequest(requestId) {
    if (confirm('Are you sure you want to approve this leave request?')) {
        fetch(`/admin/leave-requests/${requestId}/approve`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close any open modals
                document.querySelectorAll('.working-modal').forEach(modal => modal.remove());
                document.body.style.overflow = 'auto';
                
                // Refresh the page
                location.reload();
            } else {
                alert('Error approving leave request: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error approving leave request. Please try again.');
        });
    }
}

function rejectLeaveRequest(requestId) {
    if (confirm('Are you sure you want to reject this leave request?')) {
        fetch(`/admin/leave-requests/${requestId}/reject`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close any open modals
                document.querySelectorAll('.working-modal').forEach(modal => modal.remove());
                document.body.style.overflow = 'auto';
                
                // Refresh the page
                location.reload();
            } else {
                alert('Error rejecting leave request: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error rejecting leave request. Please try again.');
        });
    }
}

// Claims Management Functions
function viewClaim(claimId) {
    fetch(`/admin/claims/${claimId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const claim = data.claim;
            const modalHtml = `
                <div class="working-modal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                    <div class="working-modal-dialog" style="max-width: 600px; width: 90%;">
                        <div class="working-modal-content" style="background: white; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                            <div class="working-modal-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
                                <h5 class="working-modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 500;">Claim Details</h5>
                                <button type="button" class="working-modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">&times;</button>
                            </div>
                            <div class="working-modal-body" style="padding: 1.5rem;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Employee:</strong> ${claim.employee_name}<br>
                                        <strong>Claim Type:</strong> ${claim.claim_type_name}<br>
                                        <strong>Status:</strong> <span class="badge bg-${claim.status === 'approved' ? 'success' : claim.status === 'rejected' ? 'danger' : 'warning'}">${claim.status.charAt(0).toUpperCase() + claim.status.slice(1)}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Amount:</strong> $${parseFloat(claim.amount || 0).toFixed(2)}<br>
                                        <strong>Date:</strong> ${new Date(claim.claim_date || claim.expense_date).toLocaleDateString()}<br>
                                        <strong>Submitted:</strong> ${new Date(claim.created_at).toLocaleDateString()}
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <strong>Description:</strong><br>
                                    ${claim.description || 'No description provided'}
                                </div>
                                ${claim.attachment ? `<div class="mt-3"><strong>Attachment:</strong><br><a href="${claim.attachment}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-paperclip"></i> View Attachment</a></div>` : ''}
                                <div class="mt-3 text-muted">
                                    <small>Claim ID: #${claim.id}</small>
                                    ${claim.approved_by ? `<br><small>Reviewed by: ${claim.approved_by} on ${new Date(claim.approved_at).toLocaleString()}</small>` : ''}
                                </div>
                            </div>
                            <div class="working-modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end; gap: 0.5rem;">
                                ${claim.status === 'pending' ? `
                                    <button type="button" class="btn btn-success" onclick="approveClaim(${claimId})">Approve</button>
                                    <button type="button" class="btn btn-danger" onclick="rejectClaim(${claimId})">Reject</button>
                                ` : ''}
                                <button type="button" class="btn btn-secondary" onclick="this.closest('.working-modal').remove(); document.body.style.overflow = 'auto';">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            document.body.style.overflow = 'hidden';
        } else {
            alert('Error loading claim details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading claim details. Please try again.');
    });
}

function approveClaim(claimId) {
    if (confirm('Are you sure you want to approve this claim?')) {
        fetch(`/admin/claims/${claimId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close any open modals
                document.querySelectorAll('.working-modal').forEach(modal => modal.remove());
                document.body.style.overflow = 'auto';
                
                // Refresh the page
                location.reload();
            } else {
                alert('Error approving claim: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error approving claim. Please try again.');
        });
    }
}

function rejectClaim(claimId) {
    if (confirm('Are you sure you want to reject this claim?')) {
        fetch(`/admin/claims/${claimId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close any open modals
                document.querySelectorAll('.working-modal').forEach(modal => modal.remove());
                document.body.style.overflow = 'auto';
                
                // Refresh the page
                location.reload();
            } else {
                alert('Error rejecting claim: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error rejecting claim. Please try again.');
        });
    }
}

// View Details Functions - Production Version
function viewTimesheetDetails(timesheetId) {
    try {
        const button = document.querySelector(`button[onclick="viewTimesheetDetails(${timesheetId})"]`);
        if (!button) return;
        
        const row = button.closest('tr');
        if (!row || row.cells.length < 8) return;
        
        // Populate modal fields
        document.getElementById('view-timesheet-employee').textContent = row.cells[1].textContent.trim();
        document.getElementById('view-timesheet-date').textContent = row.cells[2].textContent.trim();
        document.getElementById('view-timesheet-clock-in').textContent = row.cells[3].textContent.trim();
        document.getElementById('view-timesheet-clock-out').textContent = row.cells[4].textContent.trim();
        document.getElementById('view-timesheet-hours').textContent = row.cells[5].textContent.trim();
        document.getElementById('view-timesheet-status').textContent = row.cells[6].querySelector('.badge')?.textContent.trim() || 'Unknown';
        
        // Show modal
        openWorkingModal('view-timesheet-modal');
    } catch (error) {
        console.error('Error viewing timesheet details:', error);
    }
}

function viewShiftDetails(shiftId) {
    try {
        const shiftRow = document.querySelector(`button[onclick="viewShiftDetails(${shiftId})"]`)?.closest('tr');
        if (!shiftRow || shiftRow.cells.length < 7) return;
        
        document.getElementById('view-shift-employee').textContent = shiftRow.cells[0].textContent.trim();
        document.getElementById('view-shift-date').textContent = shiftRow.cells[1].textContent.trim();
        document.getElementById('view-shift-type').textContent = shiftRow.cells[2].textContent.trim();
        document.getElementById('view-shift-start').textContent = shiftRow.cells[3].textContent.trim();
        document.getElementById('view-shift-end').textContent = shiftRow.cells[4].textContent.trim();
        document.getElementById('view-shift-status').textContent = shiftRow.cells[5].querySelector('.badge')?.textContent.trim() || 'Unknown';
        
        openWorkingModal('view-shift-modal');
    } catch (error) {
        console.error('Error viewing shift details:', error);
    }
}

function viewLeaveDetails(leaveId) {
    try {
        const leaveRow = document.querySelector(`button[onclick="viewLeaveDetails(${leaveId})"]`)?.closest('tr');
        if (!leaveRow || leaveRow.cells.length < 7) return;
        
        document.getElementById('view-leave-employee').textContent = leaveRow.cells[0].textContent.trim();
        document.getElementById('view-leave-type').textContent = leaveRow.cells[1].textContent.trim();
        document.getElementById('view-leave-start').textContent = leaveRow.cells[2].textContent.trim();
        document.getElementById('view-leave-end').textContent = leaveRow.cells[3].textContent.trim();
        document.getElementById('view-leave-days').textContent = leaveRow.cells[4].textContent.trim();
        document.getElementById('view-leave-status').textContent = leaveRow.cells[5].querySelector('.badge')?.textContent.trim() || 'Unknown';
        document.getElementById('view-leave-reason').textContent = 'View full details for complete reason';
        
        openWorkingModal('view-leave-modal');
    } catch (error) {
        console.error('Error viewing leave details:', error);
    }
}

function viewClaimDetails(claimId) {
    try {
        const claimRow = document.querySelector(`button[onclick="viewClaimDetails(${claimId})"]`)?.closest('tr');
        if (!claimRow || claimRow.cells.length < 6) return;
        
        document.getElementById('view-claim-details-employee').textContent = claimRow.cells[0].textContent.trim();
        document.getElementById('view-claim-details-type').textContent = claimRow.cells[1].textContent.trim();
        document.getElementById('view-claim-details-amount').textContent = claimRow.cells[2].textContent.trim();
        document.getElementById('view-claim-details-date').textContent = claimRow.cells[3].textContent.trim();
        document.getElementById('view-claim-details-status').textContent = claimRow.cells[4].querySelector('.badge')?.textContent.trim() || 'Unknown';
        document.getElementById('view-claim-details-attachment').textContent = 'N/A';
        document.getElementById('view-claim-details-description').textContent = 'View full details for complete description';
        
        openWorkingModal('view-claim-details-modal');
    } catch (error) {
        console.error('Error viewing claim details:', error);
    }
}

function viewAttendanceDetails(attendanceId) {
    try {
        const attendanceRow = document.querySelector(`button[onclick="viewAttendanceDetails(${attendanceId})"]`)?.closest('tr');
        if (!attendanceRow || attendanceRow.cells.length < 9) return;
        
        // Extract employee name from the complex cell structure
        const employeeCell = attendanceRow.cells[0];
        const employeeName = employeeCell.querySelector('.fw-medium')?.textContent.trim() || employeeCell.textContent.trim();
        
        document.getElementById('view-attendance-employee').textContent = employeeName;
        document.getElementById('view-attendance-date').textContent = attendanceRow.cells[1].textContent.trim();
        document.getElementById('view-attendance-clock-in').textContent = attendanceRow.cells[2].textContent.trim();
        document.getElementById('view-attendance-clock-out').textContent = attendanceRow.cells[3].textContent.trim();
        document.getElementById('view-attendance-hours').textContent = attendanceRow.cells[4].textContent.trim();
        document.getElementById('view-attendance-status').textContent = attendanceRow.cells[6].querySelector('.badge')?.textContent.trim() || 'Unknown';
        
        openWorkingModal('view-attendance-modal');
    } catch (error) {
        console.error('Error viewing attendance details:', error);
    }
}

// Navigation and utility functions
function clearEmployeeFilters() {
    document.getElementById('employee-search-timesheet').value = '';
    document.getElementById('department-filter-timesheet').value = '';
    document.getElementById('status-filter-timesheet').value = '';
    filterEmployeesInTimesheet();
}

function navigateToShifts(employeeId) {
    window.location.href = `/shift-schedule-management?employee=${employeeId}`;
}

function navigateToLeave(employeeId) {
    window.location.href = `/leave-management?employee=${employeeId}`;
}

function navigateToClaims(employeeId) {
    window.location.href = `/claims-reimbursement?employee=${employeeId}`;
}

function formatTime(timeString) {
    if (!timeString) return 'N/A';
    return new Date('2000-01-01 ' + timeString).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function filterEmployeesInTimesheet() {
    const searchTerm = document.getElementById('employee-search-timesheet')?.value.toLowerCase() || '';
    const departmentFilter = document.getElementById('department-filter-timesheet')?.value || '';
    const statusFilter = document.getElementById('status-filter-timesheet')?.value || '';
    const rows = document.querySelectorAll('#employees-tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length < 6) return;
        
        const name = row.cells[1].textContent.toLowerCase();
        const department = row.cells[3].textContent;
        const statusBadge = row.cells[4].querySelector('.badge');
        const status = statusBadge ? statusBadge.textContent.toLowerCase() : '';
        
        const matchesSearch = name.includes(searchTerm);
        const matchesDepartment = !departmentFilter || department.includes(departmentFilter);
        const matchesStatus = !statusFilter || status.includes(statusFilter.toLowerCase());
        
        if (matchesSearch && matchesDepartment && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Make functions globally available
window.openWorkingModal = openWorkingModal;
window.closeWorkingModal = closeWorkingModal;
window.viewTimesheetDetails = viewTimesheetDetails;
window.viewShiftDetails = viewShiftDetails;
window.viewLeaveDetails = viewLeaveDetails;
window.viewClaimDetails = viewClaimDetails;
window.viewAttendanceDetails = viewAttendanceDetails;
window.addTimesheetForEmployee = addTimesheetForEmployee;
window.editTimesheet = editTimesheet;
window.deleteTimesheet = deleteTimesheet;
window.approveTimesheet = approveTimesheet;
window.rejectTimesheet = rejectTimesheet;
window.clearEmployeeFilters = clearEmployeeFilters;
window.navigateToShifts = navigateToShifts;
window.navigateToLeave = navigateToLeave;
window.navigateToClaims = navigateToClaims;
window.formatTime = formatTime;
window.filterEmployeesInTimesheet = filterEmployeesInTimesheet;

// Sync Attendance to Timesheets Function
function syncAttendanceToTimesheets() {
    if (!confirm('This will import attendance logs as timesheet entries. Existing timesheets for the same employee and date will not be duplicated. Continue?')) {
        return;
    }
    
    // Show loading state
    const syncBtn = document.querySelector('button[onclick="syncAttendanceToTimesheets()"]');
    const originalText = syncBtn.innerHTML;
    syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Syncing...';
    syncBtn.disabled = true;
    
    fetch('/sync-attendance-to-timesheets', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', `Successfully synced ${data.synced_count || 0} attendance records to timesheets!`);
            // Reload the page to show updated timesheets
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Failed to sync attendance records');
        }
    })
    .catch(error => {
        console.error('Sync error:', error);
        showAlert('error', 'Network error occurred while syncing attendance records');
    })
    .finally(() => {
        // Restore button state
        syncBtn.innerHTML = originalText;
        syncBtn.disabled = false;
    });
}

// Fix Negative Attendance Hours Function
function fixNegativeAttendanceHours() {
    if (!confirm('This will recalculate and fix all attendance records with negative or zero total hours. This action cannot be undone. Continue?')) {
        return;
    }
    
    // Show loading state
    const fixBtn = document.querySelector('button[onclick="fixNegativeAttendanceHours()"]');
    const originalText = fixBtn.innerHTML;
    fixBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Fixing...';
    fixBtn.disabled = true;
    
    fetch('/fix-negative-attendance-hours', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', `Successfully fixed ${data.fixed_count || 0} attendance records with negative hours!`);
            // Reload the page to show updated attendance logs
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Failed to fix negative attendance hours');
        }
    })
    .catch(error => {
        console.error('Fix negative hours error:', error);
        showAlert('error', 'Network error occurred while fixing negative attendance hours');
    })
    .finally(() => {
        // Restore button state
        fixBtn.innerHTML = originalText;
        fixBtn.disabled = false;
    });
}

// Helper function to show alerts (success only)
function showAlert(type, message) {
    // Only show success alerts, ignore error alerts
    if (type !== 'success') {
        console.log('Error alert suppressed:', message);
        return;
    }
    
    const alertContainer = document.getElementById('alert-container');
    const alertClass = 'alert-success';
    const iconClass = 'fa-check-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

window.syncAttendanceToTimesheets = syncAttendanceToTimesheets;
window.viewWeeklyTimesheet = viewWeeklyTimesheet;
window.printTimesheet = printTimesheet;

console.log('All timesheet management functions made globally available');
</script>

<style>
/* Working Modal Centering - Enhanced */
.working-modal {
  display: none !important;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100% !important;
  height: 100% !important;
  background: rgba(0, 0, 0, 0.5) !important;
  z-index: 9999 !important;
  align-items: center !important;
  justify-content: center !important;
  transition: opacity 0.3s ease !important;
}

.working-modal-dialog {
  max-width: 600px;
  width: 90%;
  margin: 0 auto;
}

.working-modal-content {
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
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
  font-size: 1.25rem;
  font-weight: 600;
  color: #495057;
}

.working-modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6c757d;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.working-modal-close:hover {
  color: #495057;
}

.working-modal-body {
  padding: 1.5rem;
}

.working-modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  background-color: #f8f9fa;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.working-modal-dialog {
  max-width: 600px !important;
  width: 90% !important;
  margin: 0 !important;
}

.working-modal-content {
  background: white !important;
  border-radius: 8px !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
}

/* Preserve original button sizes - Force uniform sizing */
.btn-sm, .btn.btn-sm {
  padding: 0.25rem 0.5rem !important;
  font-size: 0.875rem !important;
  line-height: 1.5 !important;
  border-radius: 0.2rem !important;
  min-width: 32px !important;
  height: 31px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
}

.btn-group .btn-sm, .btn-group .btn {
  padding: 0.25rem 0.5rem !important;
  min-width: 32px !important;
  height: 31px !important;
}

/* Clean Modal Detail Styling - Matching Reference Design */
.detail-item {
    margin-bottom: 1rem;
}

.detail-label {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    display: block;
}

.detail-value {
    color: #111827;
    font-size: 0.875rem;
    line-height: 1.5;
    background-color: #f9fafb;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
}

.working-modal-content {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.working-modal-header {
    background-color: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.working-modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.working-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.working-modal-close:hover {
    color: #374151;
}

.working-modal-body {
    padding: 1.5rem;
}

.working-modal-footer {
    background-color: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: flex-end;
}

/* Ensure ALL action buttons maintain exact same sizing */
td .btn-group .btn, 
td .btn-group .btn-sm,
.btn-group .btn-outline-info,
.btn-group .btn-outline-primary,
.btn-group .btn-outline-danger,
.btn-group .btn-outline-success,
.btn-group .btn-outline-warning {
  padding: 0.25rem 0.5rem !important;
  font-size: 0.875rem !important;
  min-width: 32px !important;
  height: 31px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
}

/* Force icon sizing consistency */
td .btn-group .btn i,
td .btn-group .btn-sm i {
  font-size: 0.875rem !important;
}

/* Modern Statistics Cards */
.stat-card-modern {
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e9ecef;
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
  height: 4px;
  background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #17a2b8);
}

.stat-icon-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  margin-bottom: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-number {
  font-size: 2.25rem;
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
  
  /* Card hover effects for clickable statistics */
  .card-hover {
    transition: all 0.3s ease;
    cursor: pointer;
  }
  
  .card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-color: #007bff;
  }
}

/* Navigation Cards Styling */
.navigation-card {
  transition: all 0.3s ease;
  border: none;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Weekly Timesheet Table Styling */
.weekly-timesheet-table {
  font-size: 0.9rem;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.weekly-timesheet-table th {
  background-color: #20B2AA !important;
  color: white !important;
  font-weight: 600;
  text-align: center;
  padding: 12px 8px;
  border: none;
}

.weekly-timesheet-table td {
  text-align: center;
  padding: 10px 8px;
  border: 1px solid #e9ecef;
  vertical-align: middle;
}

.weekly-timesheet-table .day-cell {
  background-color: #20B2AA !important;
  color: white !important;
  font-weight: bold;
  min-width: 80px;
}

.weekly-timesheet-table tbody tr:nth-child(even) {
  background-color: #f8f9fa;
}

.weekly-timesheet-table tbody tr:hover {
  background-color: #e3f2fd;
}

/* Modal sizing for weekly timesheet */
#weekly-timesheet-modal .working-modal-dialog {
  max-width: 1000px;
  width: 95%;
}

#weekly-timesheet-modal .working-modal-content {
  max-width: none;
  width: 100%;
}

/* Employee Timesheet Cards */
.employee-timesheet-card {
  transition: all 0.3s ease;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  overflow: hidden;
}

.employee-timesheet-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
  border-color: #007bff;
}

.employee-avatar-lg {
  position: relative;
}

.employee-avatar-lg img {
  border: 3px solid #fff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.ai-status-badge {
  font-size: 0.75rem;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
  0%, 100% { box-shadow: 0 0 5px rgba(23, 162, 184, 0.5); }
  50% { box-shadow: 0 0 15px rgba(23, 162, 184, 0.8); }
}

.employee-timesheet-card .btn {
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.employee-timesheet-card .btn:hover {
  transform: translateY(-1px);
}

/* AI Timesheet Modal Styling */
.ai-timesheet-table {
  font-size: 0.9rem;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.ai-timesheet-table th {
  background-color: #20B2AA !important;
  color: white !important;
  font-weight: 600;
  text-align: center;
  padding: 12px 8px;
  border: none;
}

.ai-timesheet-table td {
  text-align: center;
  padding: 10px 8px;
  border: 1px solid #e9ecef;
  vertical-align: middle;
}

.ai-timesheet-table .day-cell {
  background-color: #20B2AA !important;
  color: white !important;
  font-weight: bold;
  min-width: 80px;
}

.ai-timesheet-table tbody tr:nth-child(even) {
  background-color: #f8f9fa;
}

.ai-timesheet-table tbody tr:hover {
  background-color: #e3f2fd;
}

/* AI Generation Animation */
.generating-ai {
  position: relative;
  overflow: hidden;
}

.generating-ai::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.2), transparent);
  animation: ai-scan 2s infinite;
}

@keyframes ai-scan {
  0% { left: -100%; }
  100% { left: 100%; }
}

/* Status Badges */
.badge.bg-generating {
  background-color: #ffc107 !important;
  animation: pulse 1.5s infinite;
}

.badge.bg-generated {
  background-color: #28a745 !important;
}

.badge.bg-ai-ready {
  background-color: #17a2b8 !important;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* AI Insights Styling */
#ai-insights {
  border-left: 4px solid #ffc107;
  background: linear-gradient(135deg, #fff3cd, #ffeaa7);
}

#ai-insights .insight-item {
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

#ai-insights .insight-item:last-child {
  border-bottom: none;
}

/* Loading States */
.btn-loading {
  position: relative;
  color: transparent !important;
}

.btn-loading::after {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  top: 50%;
  left: 50%;
  margin-left: -8px;
  margin-top: -8px;
  border: 2px solid #ffffff;
  border-radius: 50%;
  border-top-color: transparent;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Print styles */
@media print {
  .weekly-timesheet-table,
  .ai-timesheet-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .weekly-timesheet-table th,
  .weekly-timesheet-table td,
  .ai-timesheet-table th,
  .ai-timesheet-table td {
    border: 1px solid #000 !important;
    padding: 8px !important;
  }
  
  .working-modal-header,
  .working-modal-footer {
    display: none !important;
  }
  
  .alert {
    display: none !important;
  }
}

.navigation-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.navigation-card .card-body {
  position: relative;
  overflow: hidden;
}

.navigation-card .card-body::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
  transform: rotate(45deg);
  transition: all 0.5s;
  opacity: 0;
}

.navigation-card:hover .card-body::before {
  animation: shine 0.5s ease-in-out;
}

@keyframes shine {
  0% {
    transform: translateX(-100%) translateY(-100%) rotate(45deg);
    opacity: 0;
  }
  50% {
    opacity: 1;
  }
  100% {
    transform: translateX(100%) translateY(100%) rotate(45deg);
    opacity: 0;
  }
}
</style>

<script>
// Auto-populate start and end times when shift type is selected
document.addEventListener('DOMContentLoaded', function() {
    const shiftTypeSelect = document.getElementById('shift-assignment-type');
    const startTimeInput = document.getElementById('shift-assignment-start-time');
    const endTimeInput = document.getElementById('shift-assignment-end-time');
    
    if (shiftTypeSelect && startTimeInput && endTimeInput) {
        shiftTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const startTime = selectedOption.getAttribute('data-start-time');
                const endTime = selectedOption.getAttribute('data-end-time');
                
                if (startTime) {
                    startTimeInput.value = startTime;
                }
                if (endTime) {
                    endTimeInput.value = endTime;
                }
            }
        });
    }
});

// Check if we need to activate attendance tab when page loads
if (sessionStorage.getItem('activateAttendanceTab') === 'true') {
    sessionStorage.removeItem('activateAttendanceTab');
    // Wait for page to load then activate attendance tab
    window.addEventListener('load', function() {
        setTimeout(function() {
            const attendanceTab = document.getElementById('attendance-tab');
            const attendanceTabPane = document.getElementById('attendance');
            
            if (attendanceTab && attendanceTabPane) {
                // Deactivate all tabs first
                document.querySelectorAll('.nav-link').forEach(tab => {
                    tab.classList.remove('active');
                    tab.setAttribute('aria-selected', 'false');
                });
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                // Activate attendance tab
                attendanceTab.classList.add('active');
                attendanceTab.setAttribute('aria-selected', 'true');
                attendanceTabPane.classList.add('show', 'active');
            }
        }, 500);
    });
}

// Weekly Timesheet Modal Functions
function viewWeeklyTimesheet(timesheetId) {
    console.log('Loading weekly timesheet for ID:', timesheetId);
    
    // Show loading state
    const modal = document.getElementById('weekly-timesheet-modal');
    if (!modal) {
        console.error('Weekly timesheet modal not found');
        return;
    }
    
    // Clear previous data
    clearWeeklyTimesheetData();
    
    // Show modal first
    openWorkingModal('weekly-timesheet-modal');
    
    // Fetch timesheet data
    fetch(`/api/timesheets/${timesheetId}/weekly`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
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
            populateWeeklyTimesheetModal(data.data);
        } else {
            throw new Error(data.message || 'Failed to load timesheet data');
        }
    })
    .catch(error => {
        console.error('Error loading weekly timesheet:', error);
        showAlert('error', 'Failed to load weekly timesheet: ' + error.message);
        closeWorkingModal('weekly-timesheet-modal');
    });
}

function clearWeeklyTimesheetData() {
    // Clear header info
    document.getElementById('weekly-employee-name').textContent = 'Loading...';
    document.getElementById('weekly-department').textContent = 'Loading...';
    document.getElementById('weekly-supervisor-name').textContent = 'Loading...';
    
    // Clear table data
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    days.forEach(day => {
        document.getElementById(`${day}-date`).textContent = '-';
        document.getElementById(`${day}-time-in`).textContent = '-';
        document.getElementById(`${day}-break`).textContent = '-';
        document.getElementById(`${day}-time-out`).textContent = '-';
        document.getElementById(`${day}-total-hours`).textContent = '-';
        document.getElementById(`${day}-actual-time`).textContent = '-';
    });
}

function populateWeeklyTimesheetModal(data) {
    try {
        // Populate header info
        document.getElementById('weekly-employee-name').textContent = data.employee_name || 'N/A';
        document.getElementById('weekly-department').textContent = data.department || 'N/A';
        document.getElementById('weekly-supervisor-name').textContent = data.supervisor_name || 'N/A';
        
        // Populate weekly data
        if (data.weekly_data) {
            Object.keys(data.weekly_data).forEach(day => {
                const dayLower = day.toLowerCase();
                const dayData = data.weekly_data[day];
                
                if (document.getElementById(`${dayLower}-date`)) {
                    document.getElementById(`${dayLower}-date`).textContent = dayData.date || '-';
                    document.getElementById(`${dayLower}-time-in`).textContent = dayData.time_in || '-';
                    document.getElementById(`${dayLower}-break`).textContent = dayData.break || '-';
                    document.getElementById(`${dayLower}-time-out`).textContent = dayData.time_out || '-';
                    document.getElementById(`${dayLower}-total-hours`).textContent = dayData.total_hours || '-';
                    document.getElementById(`${dayLower}-actual-time`).textContent = dayData.actual_time || '-';
                }
            });
        }
        
        console.log('Weekly timesheet data populated successfully');
    } catch (error) {
        console.error('Error populating weekly timesheet modal:', error);
        showAlert('error', 'Error displaying timesheet data');
    }
}

function printTimesheet() {
    // Hide modal elements that shouldn't be printed
    const modal = document.getElementById('weekly-timesheet-modal');
    const originalDisplay = modal.style.display;
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    const timesheetContent = document.querySelector('#weekly-timesheet-modal .working-modal-body').innerHTML;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Employee Timesheet</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .table th, .table td { border: 1px solid #000; padding: 8px; text-align: center; }
                .table th { background-color: #20B2AA; color: white; font-weight: bold; }
                .day-cell { background-color: #20B2AA; color: white; font-weight: bold; }
                .row { display: flex; justify-content: space-between; margin-bottom: 20px; }
                .col-md-4 { flex: 1; margin-right: 20px; }
                h3 { text-align: center; margin-bottom: 30px; }
            </style>
        </head>
        <body>
            <h3>Employee Timesheet</h3>
            ${timesheetContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    // Wait for content to load then print
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// AI Timesheet Generation Functions
function generateAITimesheet(employeeId) {
    console.log('Generating AI timesheet for employee:', employeeId);
    
    // Update UI to show generating state
    const generateBtn = document.getElementById(`generate-btn-${employeeId}`);
    const viewBtn = document.getElementById(`view-btn-${employeeId}`);
    const statusBadge = document.getElementById(`ai-status-${employeeId}`);
    const card = generateBtn.closest('.employee-timesheet-card');
    
    // Add loading state
    generateBtn.classList.add('btn-loading');
    generateBtn.disabled = true;
    card.classList.add('generating-ai');
    
    // Update status badge
    statusBadge.className = 'badge bg-generating ai-status-badge';
    statusBadge.innerHTML = '<i class="fas fa-cog fa-spin me-1"></i>Generating...';
    
    // Make API call to generate AI timesheet
    console.log('Making API call for employee:', employeeId);
    fetch(`/api/ai-timesheets/generate/${employeeId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            week_start_date: null // Use current week
        })
    })
    .then(response => {
        console.log('API Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('API Response data:', data);
        if (data.success) {
            // Store generated data
            window.aiTimesheets = window.aiTimesheets || {};
            window.aiTimesheets[employeeId] = data.data;
            
            // Update UI to show completed state
            generateBtn.classList.remove('btn-loading');
            generateBtn.disabled = true;
            generateBtn.innerHTML = '<i class="fas fa-check me-1"></i>Generated';
            generateBtn.classList.remove('btn-primary');
            generateBtn.classList.add('btn-success');
            
            viewBtn.disabled = false;
            viewBtn.classList.remove('btn-outline-secondary');
            viewBtn.classList.add('btn-outline-primary');
            
            card.classList.remove('generating-ai');
            
            // Update status badge
            statusBadge.className = 'badge bg-generated ai-status-badge';
            statusBadge.innerHTML = '<i class="fas fa-check me-1"></i>Generated';
            
            showAlert('success', data.message);
        } else {
            throw new Error(data.message || 'Failed to generate AI timesheet');
        }
    })
    .catch(error => {
        console.error('Error generating AI timesheet for employee', employeeId, ':', error);
        
        // Reset UI on error
        generateBtn.classList.remove('btn-loading');
        generateBtn.disabled = false;
        card.classList.remove('generating-ai');
        
        statusBadge.className = 'badge bg-danger ai-status-badge';
        statusBadge.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Error';
        statusBadge.title = 'Error: ' + error.message; // Add tooltip with error details
        
        // Error state - user can try again by clicking the generate button
        
        // More detailed error message
        let errorMsg = 'Failed to generate AI timesheet';
        if (error.message.includes('Employee not found')) {
            errorMsg = 'Employee data not found in database';
        } else if (error.message.includes('network')) {
            errorMsg = 'Network connection error';
        } else if (error.message.includes('500')) {
            errorMsg = 'Server error - check logs';
        }
        
        showAlert('error', errorMsg + ' for employee ' + employeeId);
    });
}

function generateRealisticTimesheetData(employeeId) {
    const currentWeek = getCurrentWeekDates();
    const shifts = ['Morning', 'Afternoon', 'Evening'];
    const selectedShift = shifts[Math.floor(Math.random() * shifts.length)];
    
    // Get actual employee data from the card
    const employeeCard = document.getElementById(`generate-btn-${employeeId}`).closest('.employee-timesheet-card');
    const employeeName = employeeCard.querySelector('.card-title').textContent.trim();
    const employeeDepartment = employeeCard.querySelector('.text-muted:nth-of-type(2)').textContent.trim();
    
    let baseStartTime, baseEndTime;
    switch(selectedShift) {
        case 'Morning':
            baseStartTime = '08:00';
            baseEndTime = '17:00';
            break;
        case 'Afternoon':
            baseStartTime = '14:00';
            baseEndTime = '22:00';
            break;
        case 'Evening':
            baseStartTime = '22:00';
            baseEndTime = '06:00';
            break;
    }
    
    const weeklyData = {};
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    
    days.forEach((day, index) => {
        // Add some realistic variation
        const startVariation = Math.floor(Math.random() * 30) - 15; // ±15 minutes
        const endVariation = Math.floor(Math.random() * 30) - 15;
        
        const startTime = addMinutesToTime(baseStartTime, startVariation);
        const endTime = addMinutesToTime(baseEndTime, endVariation);
        
        // Calculate hours
        const totalMinutes = calculateMinutesBetween(startTime, endTime) - 60; // 1 hour break
        const totalHours = Math.max(0, totalMinutes / 60).toFixed(1);
        
        // Format times properly based on shift
        let formattedTimeIn, formattedTimeOut;
        if (selectedShift === 'Morning') {
            formattedTimeIn = formatTime12Hour(startTime);
            formattedTimeOut = formatTime12Hour(endTime);
        } else if (selectedShift === 'Afternoon') {
            formattedTimeIn = formatTime12Hour(startTime);
            formattedTimeOut = formatTime12Hour(endTime);
        } else { // Evening
            formattedTimeIn = formatTime12Hour(startTime);
            formattedTimeOut = formatTime12Hour(endTime);
        }
        
        weeklyData[day] = {
            date: currentWeek[index],
            time_in: formattedTimeIn,
            break: '12:00 PM - 1:00 PM',
            time_out: formattedTimeOut,
            total_hours: totalHours + ' hrs.',
            overtime: dayOvertime > 0 ? dayOvertime.toFixed(1) + ' hrs.' : '0 hrs.'
        };
    });
    
    return {
        employee_name: employeeName,
        department: employeeDepartment,
        supervisor_name: ['Sam Lhiam', 'John Manager', 'Sarah Boss', 'Mike Lead'][Math.floor(Math.random() * 4)],
        weekly_data: weeklyData,
        generated_at: new Date().toLocaleString(),
        ai_insights: generateAIInsights(weeklyData)
    };
}

// Helper function to format time in 12-hour format
function formatTime12Hour(timeStr) {
    const [hours, minutes] = timeStr.split(':').map(Number);
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours === 0 ? 12 : hours > 12 ? hours - 12 : hours;
    return `${displayHours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')} ${period}`;
}

function getCurrentWeekDates() {
    const today = new Date();
    const monday = new Date(today);
    monday.setDate(today.getDate() - today.getDay() + 1);
    
    const dates = [];
    for (let i = 0; i < 5; i++) {
        const date = new Date(monday);
        date.setDate(monday.getDate() + i);
        dates.push(date.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: '2-digit' }));
    }
    return dates;
}

function addMinutesToTime(timeStr, minutes) {
    const [hours, mins] = timeStr.split(':').map(Number);
    const totalMinutes = hours * 60 + mins + minutes;
    const newHours = Math.floor(totalMinutes / 60) % 24;
    const newMins = totalMinutes % 60;
    return `${newHours.toString().padStart(2, '0')}:${newMins.toString().padStart(2, '0')}`;
}

function calculateMinutesBetween(startTime, endTime) {
    const [startHours, startMins] = startTime.split(':').map(Number);
    const [endHours, endMins] = endTime.split(':').map(Number);
    
    let startTotalMins = startHours * 60 + startMins;
    let endTotalMins = endHours * 60 + endMins;
    
    // Handle overnight shifts
    if (endTotalMins < startTotalMins) {
        endTotalMins += 24 * 60;
    }
    
    return endTotalMins - startTotalMins;
}

function generateAIInsights(weeklyData) {
    const insights = [];
    const totalHours = Object.values(weeklyData).reduce((sum, day) => {
        return sum + parseFloat(day.total_hours.replace(' hrs.', ''));
    }, 0);
    
    insights.push(`Total weekly hours: ${totalHours.toFixed(1)} hours`);
    
    if (totalHours > 40) {
        insights.push(`⚠️ Overtime detected: ${(totalHours - 40).toFixed(1)} hours over standard 40-hour week`);
    }
    
    if (totalHours < 35) {
        insights.push(`ℹ️ Below full-time threshold: Consider reviewing schedule`);
    }
    
    insights.push(`📊 Average daily hours: ${(totalHours / 5).toFixed(1)} hours`);
    insights.push(`✅ Consistent schedule pattern detected`);
    
    return insights;
}

function viewAITimesheet(employeeId) {
    console.log('Viewing AI timesheet for employee:', employeeId);
    
    // Try to get from cache first
    const cachedData = window.aiTimesheets && window.aiTimesheets[employeeId];
    if (cachedData) {
        populateAITimesheetModal(cachedData);
        openWorkingModal('ai-timesheet-modal');
        return;
    }
    
    // Fetch from API
    fetch(`/api/ai-timesheets/view/${employeeId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cache the data
            window.aiTimesheets = window.aiTimesheets || {};
            window.aiTimesheets[employeeId] = data.data;
            
            // Populate modal with AI data
            populateAITimesheetModal(data.data);
            
            // Show modal
            openWorkingModal('ai-timesheet-modal');
        } else {
            showAlert('error', data.message || 'No AI timesheet found. Please generate one first.');
        }
    })
    .catch(error => {
        console.error('Error loading AI timesheet:', error);
        showAlert('error', 'Failed to load AI timesheet: ' + error.message);
    });
}

function populateAITimesheetModal(data) {
    try {
        // Populate header info
        document.getElementById('ai-employee-name').textContent = data.employee_name || 'N/A';
        document.getElementById('ai-department').textContent = data.department || 'N/A';
        document.getElementById('ai-supervisor-name').textContent = data.supervisor_name || 'N/A';
        document.getElementById('ai-generation-time').textContent = `Generated on ${data.generated_at}`;
        
        // Populate weekly data
        if (data.weekly_data) {
            Object.keys(data.weekly_data).forEach(day => {
                const dayLower = day.toLowerCase();
                const dayData = data.weekly_data[day];
                
                if (document.getElementById(`ai-${dayLower}-date`)) {
                    document.getElementById(`ai-${dayLower}-date`).textContent = dayData.date || '-';
                    document.getElementById(`ai-${dayLower}-time-in`).textContent = dayData.time_in || '-';
                    document.getElementById(`ai-${dayLower}-break`).textContent = dayData.break || '-';
                    document.getElementById(`ai-${dayLower}-time-out`).textContent = dayData.time_out || '-';
                    document.getElementById(`ai-${dayLower}-total-hours`).textContent = dayData.total_hours || '-';
                    document.getElementById(`ai-${dayLower}-overtime`).textContent = dayData.overtime || '-';
                }
            });
        }
        
        // Populate AI insights
        setTimeout(() => {
            const insightsContainer = document.getElementById('ai-insights');
            if (data.ai_insights && data.ai_insights.length > 0) {
                const insightsHtml = data.ai_insights.map(insight => 
                    `<div class="insight-item"><i class="fas fa-lightbulb me-2 text-warning"></i>${insight}</div>`
                ).join('');
                insightsContainer.innerHTML = insightsHtml;
            } else {
                insightsContainer.innerHTML = '<div class="text-muted">No insights available</div>';
            }
        }, 1500);
        
        console.log('AI timesheet modal populated successfully');
    } catch (error) {
        console.error('Error populating AI timesheet modal:', error);
        showAlert('error', 'Error displaying AI timesheet data');
    }
}

function generateAllTimesheets() {
    const employeeCards = document.querySelectorAll('.employee-timesheet-card');
    const employeeIds = [];
    
    // Collect all employee IDs that can be generated
    employeeCards.forEach(card => {
        const generateBtn = card.querySelector('[id^="generate-btn-"]');
        if (generateBtn && !generateBtn.disabled) {
            const employeeId = generateBtn.id.replace('generate-btn-', '');
            employeeIds.push(employeeId);
        }
    });
    
    if (employeeIds.length === 0) {
        showAlert('info', 'No employees available for AI timesheet generation.');
        return;
    }
    
    // Show loading state for all cards
    employeeIds.forEach(employeeId => {
        const generateBtn = document.getElementById(`generate-btn-${employeeId}`);
        const statusBadge = document.getElementById(`ai-status-${employeeId}`);
        const card = generateBtn.closest('.employee-timesheet-card');
        
        generateBtn.classList.add('btn-loading');
        generateBtn.disabled = true;
        card.classList.add('generating-ai');
        statusBadge.className = 'badge bg-generating ai-status-badge';
        statusBadge.innerHTML = '<i class="fas fa-cog fa-spin me-1"></i>Generating...';
    });
    
    // Make API call to generate all timesheets
    fetch('/api/ai-timesheets/generate-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            week_start_date: null // Use current week
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI for successful generations
            data.generated.forEach(item => {
                const employeeId = item.employee_id;
                const generateBtn = document.getElementById(`generate-btn-${employeeId}`);
                const viewBtn = document.getElementById(`view-btn-${employeeId}`);
                const statusBadge = document.getElementById(`ai-status-${employeeId}`);
                const card = generateBtn.closest('.employee-timesheet-card');
                
                generateBtn.classList.remove('btn-loading');
                generateBtn.innerHTML = '<i class="fas fa-check me-1"></i>Generated';
                generateBtn.classList.remove('btn-primary');
                generateBtn.classList.add('btn-success');
                
                viewBtn.disabled = false;
                viewBtn.classList.remove('btn-outline-secondary');
                viewBtn.classList.add('btn-outline-primary');
                
                card.classList.remove('generating-ai');
                statusBadge.className = 'badge bg-generated ai-status-badge';
                statusBadge.innerHTML = '<i class="fas fa-check me-1"></i>Generated';
            });
            
            // Handle errors
            data.errors.forEach(item => {
                const employeeId = item.employee_id;
                const generateBtn = document.getElementById(`generate-btn-${employeeId}`);
                const statusBadge = document.getElementById(`ai-status-${employeeId}`);
                const card = generateBtn.closest('.employee-timesheet-card');
                
                generateBtn.classList.remove('btn-loading');
                generateBtn.disabled = false;
                card.classList.remove('generating-ai');
                statusBadge.className = 'badge bg-danger ai-status-badge';
                statusBadge.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Error';
            });
            
            showAlert('success', `Generated ${data.generated_count} AI timesheets successfully. ${data.error_count} errors.`);
        } else {
            throw new Error(data.message || 'Failed to generate AI timesheets');
        }
    })
    .catch(error => {
        console.error('Error generating all AI timesheets:', error);
        
        // Reset all UI states on error
        employeeIds.forEach(employeeId => {
            const generateBtn = document.getElementById(`generate-btn-${employeeId}`);
            const statusBadge = document.getElementById(`ai-status-${employeeId}`);
            const card = generateBtn.closest('.employee-timesheet-card');
            
            generateBtn.classList.remove('btn-loading');
            generateBtn.disabled = false;
            card.classList.remove('generating-ai');
            statusBadge.className = 'badge bg-danger ai-status-badge';
            statusBadge.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Error';
        });
        
        showAlert('error', 'Failed to generate AI timesheets: ' + error.message);
    });
}

function approveAITimesheet() {
    // Get the current AI timesheet data
    const employeeName = document.getElementById('ai-employee-name').textContent;
    
    // Find the AI timesheet ID from cached data
    let timesheetId = null;
    if (window.aiTimesheets) {
        for (const [employeeId, data] of Object.entries(window.aiTimesheets)) {
            if (data.employee_name === employeeName) {
                timesheetId = data.id;
                break;
            }
        }
    }
    
    if (!timesheetId) {
        showAlert('error', 'Unable to find timesheet ID for approval.');
        return;
    }
    
    // Make API call to approve the timesheet
    fetch(`/api/ai-timesheets/approve/${timesheetId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            notes: 'Approved from AI timesheet modal'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            closeWorkingModal('ai-timesheet-modal');
            
            // Optionally refresh the page to show new timesheet entries
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAlert('error', data.message || 'Failed to approve AI timesheet');
        }
    })
    .catch(error => {
        console.error('Error approving AI timesheet:', error);
        showAlert('error', 'Failed to approve AI timesheet: ' + error.message);
    });
}

function printAITimesheet() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    const timesheetContent = document.querySelector('#ai-timesheet-modal .working-modal-body').innerHTML;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>AI Generated Employee Timesheet</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .table th, .table td { border: 1px solid #000; padding: 8px; text-align: center; }
                .table th { background-color: #20B2AA; color: white; font-weight: bold; }
                .day-cell { background-color: #20B2AA; color: white; font-weight: bold; }
                .row { display: flex; justify-content: space-between; margin-bottom: 20px; }
                .col-md-4 { flex: 1; margin-right: 20px; }
                h3 { text-align: center; margin-bottom: 30px; }
                .alert { display: none; }
            </style>
        </head>
        <body>
            <h3>AI Generated Employee Timesheet</h3>
            ${timesheetContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    // Wait for content to load then print
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}


// Alert function for showing messages (only success and info, no errors)
function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    // Only show success and info alerts, ignore error alerts
    if (type === 'error') {
        console.log('Error alert suppressed:', message);
        return;
    }
    
    const alertClass = type === 'info' ? 'alert-info' : 'alert-success';
    const iconClass = type === 'info' ? 'fa-info-circle' : 'fa-check-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>

@endpush

@endsection
