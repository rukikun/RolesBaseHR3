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

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('info'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
  <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
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
    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content" id="modulesTabContent">
      <!-- Timesheets Tab -->
      <div class="tab-pane fade show active" id="timesheets" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>Timesheets Management
          </h5>
          <button class="btn btn-primary" onclick="openWorkingModal('timesheet-modal')">
            <i class="fas fa-plus me-2"></i>Add Timesheet
          </button>
        </div>
        <!-- Timesheets Table -->
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
              @forelse($timesheets ?? [] as $timesheet)
                <tr>
                  <td>#{{ str_pad($timesheet->id ?? 0, 4, '0', STR_PAD_LEFT) }}</td>
                  <td>{{ $timesheet->employee_name ?? 'Unknown Employee' }}</td>
                  <td>{{ isset($timesheet->work_date) ? \Carbon\Carbon::parse($timesheet->work_date)->format('M d, Y') : 'N/A' }}</td>
                  <td>{{ $timesheet->clock_in ?? $timesheet->clock_in_time ?? 'N/A' }}</td>
                  <td>{{ $timesheet->clock_out ?? $timesheet->clock_out_time ?? 'N/A' }}</td>
                  <td>{{ number_format($timesheet->hours_worked ?? 0, 2) }}h</td>
                  <td>
                    @php
                      $status = $timesheet->status ?? 'pending';
                      $badgeClass = match($status) {
                          'approved' => 'success',
                          'pending' => 'warning',
                          'rejected' => 'danger',
                          default => 'secondary'
                      };
                    @endphp
                    <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($status) }}</span>
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <a href="{{ route('timesheets.view', $timesheet->id) }}" class="btn btn-sm btn-outline-info" title="View">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="{{ route('timesheets.edit', $timesheet->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      @if($status === 'pending')
                      <form method="POST" action="{{ route('timesheets.approve', $timesheet->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to approve this timesheet?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                      <form method="POST" action="{{ route('timesheets.reject', $timesheet->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this timesheet?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Reject">
                          <i class="fas fa-times"></i>
                        </button>
                      </form>
                      @endif
                      <form method="POST" action="{{ route('timesheets.destroy', $timesheet->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this timesheet?')">
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
                  <td colspan="8" class="text-center text-muted py-4">
                    <i class="fas fa-clock fa-3x mb-3 text-muted"></i><br>
                    No timesheets found. <a href="#" onclick="openWorkingModal('timesheet-modal')" class="text-primary">Add your first timesheet</a>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
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
          <button class="btn btn-primary" onclick="openWorkingModal('shift-modal')">
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
                    <button class="btn btn-sm btn-outline-primary" onclick="viewShift({{ $shift->id }})" title="View">
                      <i class="fas fa-eye"></i>
                    </button>
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
          <button class="btn btn-primary" onclick="openWorkingModal('leave-modal')">
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
                    <button class="btn btn-sm btn-outline-primary" onclick="viewLeave({{ $leave->id }})" title="View">
                      <i class="fas fa-eye"></i>
                    </button>
                    @if($leave->status === 'pending')
                    <form method="POST" action="/leave-requests/{{ $leave->id }}/approve" style="display: inline;" onsubmit="return confirm('Are you sure you want to approve this leave request?')">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>
                    <form method="POST" action="/leave-requests/{{ $leave->id }}/reject" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this leave request?')">
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
          <button class="btn btn-primary" onclick="openWorkingModal('claim-modal')">
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
                    <button class="btn btn-sm btn-outline-primary" onclick="viewClaim({{ $claim->id }})" title="View">
                      <i class="fas fa-eye"></i>
                    </button>
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
            <form id="shiftForm">
                <div class="working-modal-body">
                    <input type="hidden" id="shift-record-id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-employee-id" class="form-label">Employee</label>
                                <select class="form-select" id="shift-employee-id" required>
                                    <option value="">Select Employee</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="shift-date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="shift-type-id" class="form-label">Shift Type</label>
                                <select class="form-select" id="shift-type-id" required>
                                    <option value="">Select Shift Type</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start-time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start-time" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="end-time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end-time" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="shift-notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="shift-notes" rows="3" placeholder="Optional shift notes"></textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('shift-modal')">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveShiftBtn">Save Shift</button>
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

@push('scripts')
<script>
// Essential modal and filter functions only
function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeWorkingModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal on backdrop click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('working-modal')) {
        e.target.style.display = 'none';
        document.body.style.overflow = '';
    }
});

// Employee Records filtering
function filterEmployeesInTimesheet() {
    const searchTerm = document.getElementById('employee-search-timesheet').value.toLowerCase();
    const departmentFilter = document.getElementById('department-filter-timesheet').value;
    const statusFilter = document.getElementById('status-filter-timesheet').value;
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
        
        row.style.display = (matchesSearch && matchesDepartment && matchesStatus) ? '' : 'none';
    });
}

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

function createShiftForEmployee(employeeId, employeeName) {
    sessionStorage.setItem('preselectedEmployee', JSON.stringify({
        id: employeeId,
        name: employeeName
    }));
    window.location.href = `/shift-schedule-management?action=create-shift&employee=${employeeId}`;
}

function createLeaveForEmployee(employeeId, employeeName) {
    sessionStorage.setItem('preselectedEmployee', JSON.stringify({
        id: employeeId,
        name: employeeName
    }));
    window.location.href = `/leave-management?action=create-request&employee=${employeeId}`;
}

function createClaimForEmployee(employeeId, employeeName) {
    sessionStorage.setItem('preselectedEmployee', JSON.stringify({
        id: employeeId,
        name: employeeName
    }));
    window.location.href = `/claims-reimbursement?action=create-claim&employee=${employeeId}`;
}

function editEmployeeRecord(employeeId) {
    window.location.href = `/employees?employee=${employeeId}&action=edit`;
}

function deleteEmployeeRecord(employeeId) {
    if (confirm('Are you sure you want to delete this employee?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/employees/${employeeId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    const employeeSearchInput = document.getElementById('employee-search-timesheet');
    const departmentFilter = document.getElementById('department-filter-timesheet');
    const statusFilter = document.getElementById('status-filter-timesheet');
    
    if (employeeSearchInput) {
        employeeSearchInput.addEventListener('input', filterEmployeesInTimesheet);
    }
    if (departmentFilter) {
        departmentFilter.addEventListener('change', filterEmployeesInTimesheet);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterEmployeesInTimesheet);
    }
});

// UTILITY FUNCTIONS
function formatTime(timeString) {
    if (!timeString) return 'N/A';
    return new Date('2000-01-01 ' + timeString).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

// All timesheet functions now use server-side rendering
</script>
@endpush

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
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
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
// Force hide all modals on page load - CRITICAL FIX
document.addEventListener('DOMContentLoaded', function() {
    function forceHideAllModals() {
        const allModals = document.querySelectorAll('.working-modal');
        allModals.forEach(modal => {
            modal.style.setProperty('display', 'none', 'important');
            modal.style.visibility = 'hidden';
            modal.classList.remove('show');
        });
    }
    
    forceHideAllModals();
    setTimeout(forceHideAllModals, 50);
    setTimeout(forceHideAllModals, 100);
    setTimeout(forceHideAllModals, 200);
    setTimeout(forceHideAllModals, 500);
    setTimeout(forceHideAllModals, 1000);
});

// Modal functions - Clean implementation
function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.setProperty('display', 'flex', 'important');
        modal.style.visibility = 'visible';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.setProperty('display', 'none', 'important');
        modal.style.visibility = 'hidden';
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        
        // Reset form if it exists
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        const title = modal.querySelector('.working-modal-title');
        const originalTitle = title.getAttribute('data-original-title');
        if (originalTitle) {
            title.textContent = originalTitle;
        }
        // Reset submit button text
        const submitBtn = modal.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.getAttribute('data-original-text');
        if (originalBtnText) {
            submitBtn.textContent = originalBtnText;
        }
    }
}

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
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
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

// Load navigation cards on page load
function loadNavigationCards() {
    fetch('/api/unified-hr/navigation-cards')
        .then(response => response.json())
        .then(cards => {
            const container = document.getElementById('navigation-cards');
            container.innerHTML = '';
            
            cards.forEach(card => {
                const cardHtml = `
                    <div class="col-md-4 col-lg-2">
                        <a href="/${card.route}" class="text-decoration-none">
                            <div class="card navigation-card bg-${card.color} text-white h-100">
                                <div class="card-body text-center p-3">
                                    <i class="${card.icon} fs-2 mb-2"></i>
                                    <h6 class="card-title mb-1">${card.title}</h6>
                                    <div class="badge bg-light text-dark mb-2">
                                        ${card.count} ${card.label}
                                    </div>
                                    <p class="card-text small mb-0">${card.description}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                `;
                container.innerHTML += cardHtml;
            });
        })
        .catch(error => {
            console.error('Error loading navigation cards:', error);
        });
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
    // Load navigation cards
    loadNavigationCards();
    
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
    
    // Auto-refresh navigation cards every 30 seconds
    setInterval(loadNavigationCards, 30000);
});

// Working Modal JavaScript Functions
function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        // Focus first input if available
        const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
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
</script>

<style>
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
@endpush

@endsection
