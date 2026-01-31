@extends('layouts.hr')

@section('title', 'Leave Management - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Leave Management</h2>
        <p class="text-muted mb-0">Configure leave types, submit and approve requests</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Leave Management</li>
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

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('info'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
  {{ session('info') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <ul class="mb-0">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Data is now passed from LeaveController instead of direct PDO queries --}}

<!-- Leave Balance Overview -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-wallet me-2"></i>Leave Balance Overview
    </h5>
    <span class="badge bg-primary">{{ $currentLeaveYear }}</span>
  </div>
  <div class="card-body">
    <div class="row g-4" id="leave-balance-section">
      @forelse($leaveBalanceSummaries as $summary)
        <div class="col-lg-4 col-md-6">
          <div class="leave-balance-box">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1">{{ $summary->employee_name }}</h6>
                <p class="text-muted small mb-0">{{ $summary->department ?? 'Department N/A' }}</p>
              </div>
              <span class="badge bg-info">Balances</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
              <span class="text-muted small">Balances hidden</span>
              <button type="button" class="btn btn-view-balance" onclick="openWorkingModal('leave-balance-modal-{{ $summary->employee_id ?? $loop->index }}')">
                <i class="fas fa-plus me-2"></i>View Balances
              </button>
            </div>
          </div>
        </div>

        <div class="working-modal" id="leave-balance-modal-{{ $summary->employee_id ?? $loop->index }}" style="display: none;">
          <div class="working-modal-backdrop" onclick="closeWorkingModal('leave-balance-modal-{{ $summary->employee_id ?? $loop->index }}')"></div>
          <div class="working-modal-dialog">
            <div class="working-modal-content">
              <div class="working-modal-header">
                <h5 class="working-modal-title">Leave Balances - {{ $summary->employee_name }}</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('leave-balance-modal-{{ $summary->employee_id ?? $loop->index }}')">&times;</button>
              </div>
              <div class="working-modal-body">
                <p class="text-muted small mb-3">Department: {{ $summary->department ?? 'Department N/A' }} • {{ $currentLeaveYear }}</p>
                <div class="leave-balance-list">
                  @foreach($summary->balances as $balance)
                    <div class="leave-balance-row">
                      <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-semibold">
                          {{ $balance->leave_type_name }}
                          @if(!empty($balance->leave_type_code))
                            <span class="text-muted">({{ $balance->leave_type_code }})</span>
                          @endif
                        </div>
                        <span class="leave-balance-remaining">{{ $balance->remaining_days }} days</span>
                      </div>
                      <div class="small text-muted">Allocated {{ $balance->allocated_days }} • Used {{ $balance->used_days }}</div>
                    </div>
                  @endforeach
                </div>
              </div>
              <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('leave-balance-modal-{{ $summary->employee_id ?? $loop->index }}')">Close</button>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="alert alert-light border text-center mb-0">
            No leave balances available for {{ $currentLeaveYear }}.
          </div>
        </div>
      @endforelse
    </div>
  </div>
</div>

<!-- Leave Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-umbrella-beach text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="total-leave-requests">{{ $totalLeaveTypes }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Leave Types</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-warning">
          <i class="fas fa-clock text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="pending-requests">{{ $assignedEmployees }}</h3>
          <p class="text-muted mb-0 small stat-label">Assigned Employees</p>
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
          <h3 class="fw-bold mb-0 stat-number" id="approved-requests">{{ $pendingRequests }}</h3>
          <p class="text-muted mb-0 small stat-label">Pending Requests</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-user-clock text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number" id="remaining-balance">{{ number_format($weeklyHours, 1) }}</h3>
          <p class="text-muted mb-0 small stat-label">Weekly Hours</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Leave Management Actions -->
<div class="row mb-4">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-tasks me-2"></i>Leave Approval Workflow
        </h5>
      </div>
      <div class="card-body">
        <button class="btn btn-info mb-2 me-2" id="review-leave-requests-btn" onclick="scrollToLeaveRequests()">
          <i class="fas fa-eye me-2"></i>Review Requests
        </button>
        <button class="btn btn-success mb-2 me-2" id="approve-deny-btn" onclick="scrollToLeaveRequests()">
          <i class="fas fa-check me-2"></i>Approve/Deny
        </button>
        <button class="btn btn-warning mb-2" id="update-balance-btn">
          <i class="fas fa-sync me-2"></i>Update Balance
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Leave Types Configuration -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-list me-2"></i>Leave Types
    </h5>
    <button class="btn btn-primary" onclick="showAuthModal('create', 'leave_type', 0)">
      <i class="fas fa-plus me-2"></i>Create Leave Type
    </button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="leave-types-table">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Max Days Per Year</th>
            <th>Carry Forward</th>
            <th>Requires Approval</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="leave-types-tbody">
          {{-- Debug: Total leave types count: {{ count($leaveTypes ?? []) }} --}}
          @forelse($leaveTypes as $leaveType)
              <tr data-leave-type-id="{{ $leaveType->id ?? 'unknown' }}">
                <td>{{ $leaveType->name ?? 'N/A' }} </td>
                <td><span class="badge bg-primary">{{ $leaveType->code ?? 'N/A' }}</span></td>
                <td>{{ $leaveType->max_days_per_year ?? 0 }}</td>
                <td>{{ ($leaveType->carry_forward ?? 0) ? 'Yes' : 'No' }}</td>
                <td>{{ ($leaveType->requires_approval ?? 1) ? 'Yes' : 'No' }}</td>
                <td>
                  @php
                    $status = $leaveType->status ?? 'active';
                    $badgeClass = match($status) {
                        'active' => 'bg-success',
                        'inactive' => 'bg-secondary',
                        default => 'bg-warning'
                    };
                  @endphp
                  <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                </td>
                <td>
                  @if(isset($leaveType->id))
                    <div class="btn-group" role="group">
                      <button class="btn btn-sm btn-outline-info" onclick="viewLeaveTypeDetails('{{ addslashes($leaveType->name ?? '') }}', '{{ addslashes($leaveType->code ?? '') }}', {{ $leaveType->max_days_per_year ?? 0 }}, '{{ ($leaveType->carry_forward ?? 0) ? 'Yes' : 'No' }}', '{{ ($leaveType->requires_approval ?? 1) ? 'Yes' : 'No' }}')">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-primary" onclick="showAuthModal('edit', 'leave_type', {{ $leaveType->id }}, { name: '{{ addslashes($leaveType->name ?? '') }}', code: '{{ addslashes($leaveType->code ?? '') }}', description: '{{ addslashes($leaveType->description ?? '') }}', max_days: {{ $leaveType->max_days_per_year ?? 0 }}, carry_forward: {{ ($leaveType->carry_forward ?? 0) ? 'true' : 'false' }}, requires_approval: {{ ($leaveType->requires_approval ?? 1) ? 'true' : 'false' }} })">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-danger" onclick="showAuthModal('delete', 'leave_type', {{ $leaveType->id }})">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  @endif
                </td>
              </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                No leave types found. <a href="#" onclick="openWorkingModal('create-leave-type-modal')" class="text-primary">Create your first leave type</a>
              </td>
            </tr>
            @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Leave Requests -->
<div class="card mb-4" id="leave-requests-section">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-calendar-check me-2"></i>Leave Requests
    </h5>
    <div>
      <select id="request-status-filter" class="form-select form-select-sm d-inline-block w-auto me-2">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
      <button class="btn btn-primary" onclick="openWorkingModal('create-leave-request-modal')">
        <i class="fas fa-plus me-2"></i>New Request
      </button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="leave-requests-table">
        <thead class="table-light">
          <tr>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Days</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="leave-requests-tbody">
          @forelse($leaves as $leave)
            <tr>
              <td>{{ $leave->employee_name ?? 'Unknown Employee' }}</td>
              <td>{{ $leave->leave_type_name ?? 'Unknown Type' }}</td>
              <td>{{ isset($leave->start_date) ? date('M d, Y', strtotime($leave->start_date)) : 'N/A' }}</td>
              <td>{{ isset($leave->end_date) ? date('M d, Y', strtotime($leave->end_date)) : 'N/A' }}</td>
              <td>{{ $leave->days_requested ?? 0 }}</td>
              <td>{{ isset($leave->reason) ? Str::limit($leave->reason, 30) : 'N/A' }}</td>
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
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewLeaveRequestDetails({{ $leave->id ?? 0 }})" title="View">
                    <i class="fas fa-eye"></i>
                  </button>
                  @if($status === 'pending')
                        <button type="button" class="btn btn-sm btn-outline-success" title="Approve" onclick="showAuthModal('approve', 'leave_request', {{ $leave->id }})">
                          <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" title="Reject" onclick="showAuthModal('reject', 'leave_request', {{ $leave->id }})">
                          <i class="fas fa-times"></i>
                        </button>
                        @endif
                        @if(isset($leave->id))
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="showAuthModal('delete', 'leave_request', {{ $leave->id }})">
                          <i class="fas fa-trash"></i>
                        </button>
                        @endif
                </div>
              </td>
            </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
              No leave requests found. <a href="#" onclick="openWorkingModal('create-leave-request-modal')" class="text-primary">Create your first leave request</a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- HR2 Requests Employees (External API Data) -->
<div class="card mb-4" id="hr2-requests-employees-section">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-users me-2"></i>Requests Employees
      <span class="badge bg-info ms-2">HR2 System</span>
    </h5>
    <div>
      <button class="btn btn-outline-primary btn-sm me-2" onclick="refreshHr2LeaveApplications()" title="Refresh data from HR2">
        <i class="fas fa-sync-alt me-1"></i>Refresh
      </button>
      <select id="hr2-status-filter" class="form-select form-select-sm d-inline-block w-auto">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="hr2-requests-employees-table">
        <thead class="table-light">
          <tr>
            <th>Employee ID</th>
            <th>Leave ID</th>
            <th>Leave Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Days</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Applied Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="hr2-requests-employees-tbody">
          @forelse($hr2LeaveApplications ?? [] as $hr2Leave)
            <tr data-status="{{ strtolower($hr2Leave->status ?? 'pending') }}" data-hr2-id="{{ $hr2Leave->id ?? '' }}">
              <td>
                <span class="fw-semibold">{{ $hr2Leave->employee_id ?? 'N/A' }}</span>
              </td>
              <td>
                <span class="badge bg-secondary">{{ $hr2Leave->leave_id ?? 'N/A' }}</span>
              </td>
              <td>{{ $hr2Leave->leave_type ?? 'Unknown' }}</td>
              <td>{{ isset($hr2Leave->start_date) ? date('M d, Y', strtotime($hr2Leave->start_date)) : 'N/A' }}</td>
              <td>{{ isset($hr2Leave->end_date) ? date('M d, Y', strtotime($hr2Leave->end_date)) : 'N/A' }}</td>
              <td>
                <span class="badge bg-primary">{{ $hr2Leave->days_requested ?? $hr2Leave->leave_days ?? 0 }} days</span>
              </td>
              <td>{{ isset($hr2Leave->reason) ? Str::limit($hr2Leave->reason, 40) : 'N/A' }}</td>
              <td>
                <span class="badge bg-{{ $hr2Leave->status_badge_class ?? 'secondary' }}">
                  {{ ucfirst($hr2Leave->status ?? 'Pending') }}
                </span>
              </td>
              <td>{{ isset($hr2Leave->applied_date) ? date('M d, Y H:i', strtotime($hr2Leave->applied_date)) : 'N/A' }}</td>
              <td>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-info" title="View Details" 
                    onclick="viewHr2LeaveDetails('{{ $hr2Leave->id ?? '' }}', '{{ $hr2Leave->employee_id ?? 'N/A' }}', '{{ $hr2Leave->leave_id ?? 'N/A' }}', '{{ $hr2Leave->leave_type ?? 'Unknown' }}', '{{ isset($hr2Leave->start_date) ? date('M d, Y', strtotime($hr2Leave->start_date)) : 'N/A' }}', '{{ isset($hr2Leave->end_date) ? date('M d, Y', strtotime($hr2Leave->end_date)) : 'N/A' }}', '{{ $hr2Leave->days_requested ?? $hr2Leave->leave_days ?? 0 }}', '{{ addslashes($hr2Leave->reason ?? 'N/A') }}', '{{ ucfirst($hr2Leave->status ?? 'Pending') }}', '{{ isset($hr2Leave->applied_date) ? date('M d, Y H:i', strtotime($hr2Leave->applied_date)) : 'N/A' }}', '{{ $hr2Leave->contact_info ?? 'N/A' }}', '{{ $hr2Leave->approved_by ?? 'N/A' }}', '{{ isset($hr2Leave->approved_date) ? date('M d, Y H:i', strtotime($hr2Leave->approved_date)) : 'N/A' }}', '{{ addslashes($hr2Leave->remarks ?? '') }}')">
                    <i class="fas fa-eye"></i>
                  </button>
                  @if(strtolower($hr2Leave->status ?? 'pending') === 'pending')
                    <button type="button" class="btn btn-sm btn-outline-success" title="Approve" 
                      onclick="approveHr2Leave('{{ $hr2Leave->id ?? '' }}', '{{ $hr2Leave->employee_id ?? 'N/A' }}')">
                      <i class="fas fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Reject" 
                      onclick="rejectHr2Leave('{{ $hr2Leave->id ?? '' }}', '{{ $hr2Leave->employee_id ?? 'N/A' }}')">
                      <i class="fas fa-times"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
          <tr>
            <td colspan="10" class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
              No leave applications found from HR2 system.
              <a href="#" onclick="refreshHr2LeaveApplications()" class="text-primary">Click to refresh</a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- HR2 Leave Details Modal -->
<div class="working-modal" id="hr2-leave-details-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('hr2-leave-details-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title"><i class="fas fa-file-alt me-2"></i>HR2 Leave Request Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('hr2-leave-details-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Employee ID</label>
                        <p id="hr2-detail-employee-id" class="fw-semibold mb-0">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Leave ID</label>
                        <p id="hr2-detail-leave-id" class="fw-semibold mb-0">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Leave Type</label>
                        <p id="hr2-detail-leave-type" class="fw-semibold mb-0">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Status</label>
                        <p id="hr2-detail-status" class="mb-0">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Start Date</label>
                        <p id="hr2-detail-start-date" class="fw-semibold mb-0">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">End Date</label>
                        <p id="hr2-detail-end-date" class="fw-semibold mb-0">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Days Requested</label>
                        <p id="hr2-detail-days" class="fw-semibold mb-0">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Contact Info</label>
                        <p id="hr2-detail-contact" class="fw-semibold mb-0">-</p>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label text-muted small">Reason</label>
                        <p id="hr2-detail-reason" class="mb-0" style="white-space: pre-wrap;">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Applied Date</label>
                        <p id="hr2-detail-applied-date" class="fw-semibold mb-0">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Approved By</label>
                        <p id="hr2-detail-approved-by" class="fw-semibold mb-0">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Approved Date</label>
                        <p id="hr2-detail-approved-date" class="fw-semibold mb-0">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Remarks</label>
                        <p id="hr2-detail-remarks" class="mb-0">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('hr2-leave-details-modal')">Close</button>
                <button type="button" class="btn btn-success" id="hr2-modal-approve-btn" style="display: none;" onclick="approveHr2LeaveFromModal()">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
                <button type="button" class="btn btn-danger" id="hr2-modal-reject-btn" style="display: none;" onclick="rejectHr2LeaveFromModal()">
                    <i class="fas fa-times me-1"></i>Reject
                </button>
            </div>
        </div>
    </div>
</div>

<!-- HR2 Approve/Reject with Authorization Modal -->
<div class="working-modal" id="hr2-action-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('hr2-action-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title" id="hr2-action-modal-title">HR Authorization Required</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('hr2-action-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Authorization Required.</strong> Please authenticate to proceed with this action.
                </div>
                <p id="hr2-action-modal-message" class="mb-3 fw-semibold"></p>
                <div class="mb-3">
                    <label for="hr2-auth-email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="hr2-auth-email" required placeholder="Enter your email address">
                </div>
                <div class="mb-3">
                    <label for="hr2-auth-password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="hr2-auth-password" required placeholder="Enter your password">
                </div>
                <input type="hidden" id="hr2-action-id">
                <input type="hidden" id="hr2-action-type">
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('hr2-action-modal')">Cancel</button>
                <button type="button" class="btn btn-primary" id="hr2-action-confirm-btn" onclick="confirmHr2ActionWithAuth()">
                    <i class="fas fa-lock me-1"></i>Authenticate & Proceed
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Leave Type Modal -->
<div class="working-modal" id="create-leave-type-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('create-leave-type-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Create Leave Type</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-leave-type-modal')">&times;</button>
            </div>
            <form id="create-leave-type-form" method="POST" action="{{ route('leave-types.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="mb-3">
                        <label for="leave-type-name" class="form-label">Leave Type Name</label>
                        <input type="text" class="form-control" id="leave-type-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="leave-type-code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="leave-type-code" name="code" maxlength="10" required>
                    </div>
                    <div class="mb-3">
                        <label for="leave-type-description" class="form-label">Description</label>
                        <textarea class="form-control" id="leave-type-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="max-days-per-year" class="form-label">Max Days Per Year</label>
                        <input type="number" class="form-control" id="max-days-per-year" name="max_days_per_year" min="1" max="365" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="carry-forward" name="carry_forward">
                        <label class="form-check-label" for="carry-forward">Allow Carry Forward</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="requires-approval" name="requires_approval" checked>
                        <label class="form-check-label" for="requires-approval">Requires Approval</label>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-leave-type-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Leave Request Modal -->
<div class="working-modal" id="create-leave-request-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('create-leave-request-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">New Leave Request</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-leave-request-modal')">&times;</button>
            </div>
            <form id="create-leave-request-form" method="POST" action="{{ route('leave-requests.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="mb-3">
                        <label for="request-employee" class="form-label">Employee</label>
                        <select class="form-select" id="request-employee" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @forelse($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                            @empty
                                <option value="" disabled>No employees available</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="request-leave-type" class="form-label">Leave Type</label>
                        <select class="form-select" id="request-leave-type" name="leave_type_id" required>
                            <option value="">Select Leave Type</option>
                            @forelse($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @empty
                                <option value="" disabled>No leave types available</option>
                            @endforelse
                        </select>
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
                        <textarea class="form-control" id="leave-reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-leave-request-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- HR Authentication Modal -->
<div class="working-modal" id="hr-auth-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('hr-auth-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">HR Authorization Required</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('hr-auth-modal')">&times;</button>
            </div>
            <form id="hr-auth-form" method="POST">
                @csrf
                <div class="working-modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Authorization Required.</strong>
                    </div>
                    <div class="mb-3">
                        <label for="auth-email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="auth-email" name="email" required placeholder="Enter your email address">
                    </div>
                    <div class="mb-3">
                        <label for="auth-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="auth-password" name="password" required placeholder="Enter your password">
                    </div>
                    <input type="hidden" id="auth-action" name="action" value="">
                    <input type="hidden" id="auth-type" name="type" value="">
                    <input type="hidden" id="auth-item-id" name="item_id" value="">
                    <input type="hidden" id="auth-extra-data" name="extra_data" value="">
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('hr-auth-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-lock me-2"></i>Authenticate & Proceed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Working Modal Functions - Critical for form functionality
function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
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

// Show alert message helper function
function showAlert(type, message) {
    // Map type to Bootstrap alert class
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to document
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 5000);
}

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

// Auto-calculate days when dates change
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    function calculateDays() {
        if (startDateInput.value && endDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            if (endDate >= startDate) {
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                // Show calculated days (you can add a display element for this)
                console.log(`Leave duration: ${diffDays} days`);
            }
        }
    }
    
    if (startDateInput) startDateInput.addEventListener('change', calculateDays);
    if (endDateInput) endDateInput.addEventListener('change', calculateDays);
});

// Form validation enhancement
function validateLeaveRequestForm() {
    const employeeId = document.getElementById('request-employee').value;
    const leaveTypeId = document.getElementById('request-leave-type').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    const reason = document.getElementById('leave-reason').value.trim();
    
    if (!employeeId) {
        alert('Please select an employee.');
        return false;
    }
    
    if (!leaveTypeId) {
        alert('Please select a leave type.');
        return false;
    }
    
    if (!startDate) {
        alert('Please select a start date.');
        return false;
    }
    
    if (!endDate) {
        alert('Please select an end date.');
        return false;
    }
    
    if (new Date(endDate) < new Date(startDate)) {
        alert('End date cannot be before start date.');
        return false;
    }
    
    if (!reason) {
        alert('Please provide a reason for the leave request.');
        return false;
    }
    
    return true;
}

// Add form validation to leave request form
document.addEventListener('DOMContentLoaded', function() {
    const leaveRequestForm = document.getElementById('create-leave-request-form');
    if (leaveRequestForm) {
        leaveRequestForm.addEventListener('submit', function(e) {
            if (!validateLeaveRequestForm()) {
                e.preventDefault();
                return false;
            }
        });
    }
});

// Handle session-based edit data population
document.addEventListener('DOMContentLoaded', function() {
    @if(session('edit_leave_type'))
        populateLeaveTypeEditForm(@json(session('edit_leave_type')));
        {{ session()->forget('edit_leave_type') }}
    @endif
    
    @if(session('edit_leave_request'))
        populateLeaveRequestEditForm(@json(session('edit_leave_request')));
        {{ session()->forget('edit_leave_request') }}
    @endif
    
    // Employee integration functions
    window.createLeaveForEmployee = function(employeeId, employeeName) {
        // Pre-select employee in leave request modal
        const employeeSelect = document.getElementById('request-employee');
        if (employeeSelect) {
            employeeSelect.value = employeeId;
        }
        
        // Set today's date as default
        const startDateInput = document.getElementById('start-date');
        if (startDateInput) {
            startDateInput.value = new Date().toISOString().split('T')[0];
        }
        
        // Show info message
        alert(`Creating leave request for: ${employeeName}`);
        
        // Open modal
        openWorkingModal('create-leave-request-modal');
    };
    
    window.viewEmployeeLeaveHistory = function(employeeId) {
        // Filter leave requests table by employee
        const rows = document.querySelectorAll('#leave-requests-tbody tr');
        let found = false;
        
        rows.forEach(row => {
            if (row.querySelector('.text-center')) return; // Skip "no data" row
            
            const employeeCell = row.querySelector('td:first-child');
            if (employeeCell && employeeCell.textContent.includes('Employee')) {
                row.style.display = '';
                found = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        if (!found) {
            alert('No leave history found for this employee.');
            // Reset display
            rows.forEach(row => {
                row.style.display = '';
            });
        } else {
            // Scroll to leave requests section
            document.getElementById('leave-requests-table').scrollIntoView({ behavior: 'smooth' });
        }
    };
});

// Populate leave type edit form with session data
function populateLeaveTypeEditForm(leaveType) {
    const modal = document.getElementById('create-leave-type-modal');
    const form = modal.querySelector('form');
    const title = modal.querySelector('.working-modal-title');
    
    // Update modal title and form action
    title.textContent = 'Edit Leave Type';
    form.action = `/leave/types/${leaveType.id}`;
    form.method = 'POST';
    
    // Add method override for PUT
    let methodInput = form.querySelector('input[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        form.appendChild(methodInput);
    }
    methodInput.value = 'PUT';
    
    // Populate form fields
    document.getElementById('leave-type-name').value = leaveType.name || '';
    document.getElementById('leave-type-code').value = leaveType.code || '';
    document.getElementById('leave-type-description').value = leaveType.description || '';
    document.getElementById('max-days-per-year').value = leaveType.max_days_per_year || '';
    document.getElementById('carry-forward').checked = leaveType.carry_forward || false;
    document.getElementById('requires-approval').checked = leaveType.requires_approval || false;
    
    // Open modal
    openWorkingModal('create-leave-type-modal');
}

// Populate leave request edit form with session data
function populateLeaveRequestEditForm(leaveRequest) {
    const modal = document.getElementById('create-leave-request-modal');
    const form = modal.querySelector('form');
    const title = modal.querySelector('.working-modal-title');
    
    // Update modal title and form action
    title.textContent = 'Edit Leave Request';
    form.action = `/leave/requests/${leaveRequest.id}`;
    form.method = 'POST';
    
    // Add method override for PUT
    let methodInput = form.querySelector('input[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        form.appendChild(methodInput);
    }
    methodInput.value = 'PUT';
    
    // Populate form fields
    document.getElementById('request-employee').value = leaveRequest.employee_id || '';
    document.getElementById('request-leave-type').value = leaveRequest.leave_type_id || '';
    document.getElementById('start-date').value = leaveRequest.start_date || '';
    document.getElementById('end-date').value = leaveRequest.end_date || '';
    document.getElementById('leave-reason').value = leaveRequest.reason || '';
    
    // Open modal
    openWorkingModal('create-leave-request-modal');
}

// Action button functions for leave types
function viewLeaveTypeDetails(id, name, code, description, maxDays, carryForward, requiresApproval) {
    const details = `Leave Type Details:\n\n` +
        `Name: ${name}\n` +
        `Code: ${code}\n` +
        `Description: ${description || 'N/A'}\n` +
        `Max Days Per Year: ${maxDays}\n` +
        `Carry Forward: ${carryForward ? 'Yes' : 'No'}\n` +
        `Requires Approval: ${requiresApproval ? 'Yes' : 'No'}`;
    
    alert(details);
}

function editLeaveTypeForm(id, name, code, description, maxDays, carryForward, requiresApproval) {
    const modal = document.getElementById('create-leave-type-modal');
    const form = modal.querySelector('form');
    const title = modal.querySelector('.working-modal-title');
    
    // Update modal title and form action
    title.textContent = 'Edit Leave Type';
    form.action = `/leave/types/${id}`;
    form.method = 'POST';
    
    // Add method override for PUT
    let methodInput = form.querySelector('input[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        form.appendChild(methodInput);
    }
    methodInput.value = 'PUT';
    
    // Populate form fields
    document.getElementById('leave-type-name').value = name || '';
    document.getElementById('leave-type-code').value = code || '';
    document.getElementById('leave-type-description').value = description || '';
    document.getElementById('max-days-per-year').value = maxDays || '';
    document.getElementById('carry-forward').checked = carryForward || false;
    document.getElementById('requires-approval').checked = requiresApproval || false;
    
    // Open modal
    openWorkingModal('create-leave-type-modal');
}

// Action button functions for leave requests
function viewLeaveRequestDetails(id) {
    // Find the leave request data from the table
    const row = document.querySelector(`button[onclick*="viewLeaveRequestDetails(${id})"]`).closest('tr');
    const cells = row.querySelectorAll('td');
    
    const details = `Leave Request Details:\n\n` +
        `Employee: ${cells[0].textContent}\n` +
        `Leave Type: ${cells[1].textContent}\n` +
        `Start Date: ${cells[2].textContent}\n` +
        `End Date: ${cells[3].textContent}\n` +
        `Days Requested: ${cells[4].textContent}\n` +
        `Reason: ${cells[5].textContent}\n` +
        `Status: ${cells[6].textContent.trim()}`;
    
    alert(details);
}

// Filter functionality
function filterLeaveRequests() {
    const statusFilter = document.getElementById('request-status-filter').value;
    const rows = document.querySelectorAll('#leave-requests-tbody tr');
    
    rows.forEach(row => {
        if (row.querySelector('.text-center')) return; // Skip "no data" row
        
        const statusCell = row.querySelector('td:nth-child(7)');
        if (!statusCell) return;
        
        const status = statusCell.textContent.toLowerCase().trim();
        
        if (!statusFilter || status.includes(statusFilter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Add event listener for status filter
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('request-status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterLeaveRequests);
    }
});

// HR2 Requests Employees Filter functionality
function filterHr2LeaveApplications() {
    const statusFilter = document.getElementById('hr2-status-filter').value;
    const rows = document.querySelectorAll('#hr2-requests-employees-tbody tr');
    
    rows.forEach(row => {
        if (row.querySelector('.text-center')) return; // Skip "no data" row
        
        const status = row.getAttribute('data-status') || '';
        
        if (!statusFilter || status.includes(statusFilter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Add event listener for HR2 status filter
document.addEventListener('DOMContentLoaded', function() {
    const hr2StatusFilter = document.getElementById('hr2-status-filter');
    if (hr2StatusFilter) {
        hr2StatusFilter.addEventListener('change', filterHr2LeaveApplications);
    }
});

// Refresh HR2 Leave Applications from API
function refreshHr2LeaveApplications() {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
    
    // Reload the page to fetch fresh data from the API
    // For a more seamless experience, you could use AJAX here
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

// Store current HR2 leave details for modal actions
let currentHr2LeaveId = null;
let currentHr2EmployeeId = null;

// View HR2 Leave Details
function viewHr2LeaveDetails(id, employeeId, leaveId, leaveType, startDate, endDate, days, reason, status, appliedDate, contactInfo, approvedBy, approvedDate, remarks) {
    // Store current leave ID for potential actions
    currentHr2LeaveId = id;
    currentHr2EmployeeId = employeeId;
    
    // Populate modal fields
    document.getElementById('hr2-detail-employee-id').textContent = employeeId;
    document.getElementById('hr2-detail-leave-id').textContent = leaveId;
    document.getElementById('hr2-detail-leave-type').textContent = leaveType;
    document.getElementById('hr2-detail-start-date').textContent = startDate;
    document.getElementById('hr2-detail-end-date').textContent = endDate;
    document.getElementById('hr2-detail-days').textContent = days + ' days';
    document.getElementById('hr2-detail-reason').textContent = reason;
    document.getElementById('hr2-detail-applied-date').textContent = appliedDate;
    document.getElementById('hr2-detail-contact').textContent = contactInfo || 'N/A';
    document.getElementById('hr2-detail-approved-by').textContent = approvedBy || 'N/A';
    document.getElementById('hr2-detail-approved-date').textContent = approvedDate || 'N/A';
    document.getElementById('hr2-detail-remarks').textContent = remarks || 'N/A';
    
    // Set status with badge
    const statusBadgeClass = status.toLowerCase() === 'approved' ? 'success' : 
                             status.toLowerCase() === 'pending' ? 'warning' : 
                             status.toLowerCase() === 'rejected' ? 'danger' : 'secondary';
    document.getElementById('hr2-detail-status').innerHTML = `<span class="badge bg-${statusBadgeClass}">${status}</span>`;
    
    // Show/hide approve/reject buttons based on status
    const approveBtn = document.getElementById('hr2-modal-approve-btn');
    const rejectBtn = document.getElementById('hr2-modal-reject-btn');
    
    if (status.toLowerCase() === 'pending') {
        approveBtn.style.display = 'inline-block';
        rejectBtn.style.display = 'inline-block';
    } else {
        approveBtn.style.display = 'none';
        rejectBtn.style.display = 'none';
    }
    
    // Open modal
    openWorkingModal('hr2-leave-details-modal');
}

// Approve HR2 Leave from table
function approveHr2Leave(id, employeeId) {
    currentHr2LeaveId = id;
    currentHr2EmployeeId = employeeId;
    
    document.getElementById('hr2-action-modal-title').innerHTML = '<i class="fas fa-check-circle me-2 text-success"></i>Approve Leave Request - HR Authorization Required';
    document.getElementById('hr2-action-modal-message').textContent = `Approve leave request for Employee ${employeeId}`;
    document.getElementById('hr2-action-id').value = id;
    document.getElementById('hr2-action-type').value = 'approve';
    document.getElementById('hr2-auth-email').value = '';
    document.getElementById('hr2-auth-password').value = '';
    document.getElementById('hr2-action-confirm-btn').className = 'btn btn-success';
    document.getElementById('hr2-action-confirm-btn').innerHTML = '<i class="fas fa-lock me-1"></i>Authenticate & Approve';
    
    openWorkingModal('hr2-action-modal');
}

// Reject HR2 Leave from table
function rejectHr2Leave(id, employeeId) {
    currentHr2LeaveId = id;
    currentHr2EmployeeId = employeeId;
    
    document.getElementById('hr2-action-modal-title').innerHTML = '<i class="fas fa-times-circle me-2 text-danger"></i>Reject Leave Request - HR Authorization Required';
    document.getElementById('hr2-action-modal-message').textContent = `Reject leave request for Employee ${employeeId}`;
    document.getElementById('hr2-action-id').value = id;
    document.getElementById('hr2-action-type').value = 'reject';
    document.getElementById('hr2-auth-email').value = '';
    document.getElementById('hr2-auth-password').value = '';
    document.getElementById('hr2-action-confirm-btn').className = 'btn btn-danger';
    document.getElementById('hr2-action-confirm-btn').innerHTML = '<i class="fas fa-lock me-1"></i>Authenticate & Reject';
    
    openWorkingModal('hr2-action-modal');
}

// Approve from details modal
function approveHr2LeaveFromModal() {
    closeWorkingModal('hr2-leave-details-modal');
    approveHr2Leave(currentHr2LeaveId, currentHr2EmployeeId);
}

// Reject from details modal
function rejectHr2LeaveFromModal() {
    closeWorkingModal('hr2-leave-details-modal');
    rejectHr2Leave(currentHr2LeaveId, currentHr2EmployeeId);
}

// Confirm HR2 Action (Approve/Reject) with HR Authorization using PATCH method
function confirmHr2ActionWithAuth() {
    const id = document.getElementById('hr2-action-id').value;
    const actionType = document.getElementById('hr2-action-type').value;
    const email = document.getElementById('hr2-auth-email').value.trim();
    const password = document.getElementById('hr2-auth-password').value;
    const confirmBtn = document.getElementById('hr2-action-confirm-btn');
    const originalBtnHtml = confirmBtn.innerHTML;
    
    // Validate email and password
    if (!email) {
        showAlert('error', 'Please enter your email address');
        document.getElementById('hr2-auth-email').focus();
        return;
    }
    
    if (!password) {
        showAlert('error', 'Please enter your password');
        document.getElementById('hr2-auth-password').focus();
        return;
    }
    
    // Show loading state
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Authenticating...';
    
    // Send PATCH request to Laravel backend to update leave status via HR2 API
    const apiUrl = actionType === 'approve' 
        ? `/hr2/leave-applications/${id}/approve`
        : `/hr2/leave-applications/${id}/reject`;
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || document.querySelector('input[name="_token"]')?.value;
    
    fetch(apiUrl, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            email: email,
            password: password
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Authentication failed or API request failed');
            });
        }
        return response.json();
    })
    .then(data => {
        // Close modal
        closeWorkingModal('hr2-action-modal');
        
        if (data.success) {
            // Show success message
            const actionText = actionType === 'approve' ? 'approved' : 'rejected';
            showAlert('success', `Leave request has been ${actionText} successfully!`);
            
            // Update the row status visually immediately
            updateHr2RowStatus(id, actionType);
            
            // Reload page to refresh data after a delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Failed to update leave status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', `Error: ${error.message || 'Authentication failed or failed to update leave status'}`);
    })
    .finally(() => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalBtnHtml;
    });
}

// Update HR2 row status visually
function updateHr2RowStatus(id, actionType) {
    const row = document.querySelector(`tr[data-hr2-id="${id}"]`);
    if (row) {
        const statusCell = row.querySelector('td:nth-child(8) .badge');
        if (statusCell) {
            statusCell.className = `badge bg-${actionType === 'approve' ? 'success' : 'danger'}`;
            statusCell.textContent = actionType === 'approve' ? 'Approved' : 'Rejected';
        }
        row.setAttribute('data-status', actionType === 'approve' ? 'approved' : 'rejected');
        
        // Hide approve/reject buttons
        const actionBtns = row.querySelectorAll('.btn-outline-success, .btn-outline-danger');
        actionBtns.forEach(btn => btn.style.display = 'none');
    }
}

// Check for session-based modal opening




@if(session('show_edit_modal'))
document.addEventListener('DOMContentLoaded', function() {
    const editData = @json(session('edit_leave_type'));
    if (editData) {
        editLeaveTypeForm(
            editData.id, 
            editData.name || '', 
            editData.code || '', 
            editData.description || '', 
            editData.max_days_per_year || 0, 
            editData.carry_forward ? 'true' : 'false', 
            editData.requires_approval ? 'true' : 'false'
        );
    }
});
@endif
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

/* Leave balance overview */
.leave-balance-box {
  background: #ffffff;
  border-radius: 16px;
  padding: 18px;
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
  height: 100%;
}

.leave-balance-list {
  margin-top: 12px;
  display: grid;
  gap: 10px;
}

.leave-balance-row {
  background: #f7f9fc;
  border-radius: 12px;
  padding: 10px 12px;
  border: 1px solid #e3e8f1;
}

.leave-balance-remaining {
  font-weight: 700;
  color: #1f7a8c;
}

.btn-view-balance {
  background: linear-gradient(135deg, #1f5fbf, #193f91);
  color: #ffffff;
  border: none;
  border-radius: 6px;
  padding: 6px 14px;
  font-size: 0.85rem;
  font-weight: 600;
  box-shadow: 0 4px 10px rgba(25, 63, 145, 0.3);
}

.btn-view-balance:hover,
.btn-view-balance:focus {
  color: #ffffff;
  background: linear-gradient(135deg, #235fce, #1a4fb5);
  box-shadow: 0 6px 14px rgba(25, 63, 145, 0.35);
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

/* Working Modal Styles - Essential for form functionality */
.working-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 2000;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: auto !important;
}

.working-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2000;
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
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    pointer-events: auto !important;
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
    color: #212529;
}

.working-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    color: #6c757d;
    cursor: pointer;
    margin-left: auto;
    padding: 0;
    width: 1.5rem;
    height: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.working-modal-close:hover {
    color: #000;
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

/* Critical: Ensure form elements remain interactive */
.working-modal input,
.working-modal select,
.working-modal textarea,
.working-modal button {
    pointer-events: auto !important;
}

.working-modal .form-control,
.working-modal .form-select {
    pointer-events: auto !important;
    background-color: #fff !important;
    border: 1px solid #ced4da !important;
    color: #495057 !important;
}

.working-modal .form-control:focus,
.working-modal .form-select:focus {
    border-color: #80bdff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    outline: 0 !important;
}

.working-modal .btn {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Employee integration styles */
.employee-leave-actions .btn {
    margin-right: 5px;
    margin-bottom: 5px;
}

/* Enhanced table styling */
.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

/* Status filter styling */
#request-status-filter {
    min-width: 120px;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 2px;
        border-radius: 4px !important;
    }
    
    .card-header .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Additional modal functions for enhanced functionality
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

// Override any other scripts that might show the modal
setTimeout(function() {
    const modal = document.getElementById('create-leave-request-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
    }
}, 100);

// View Details Functions (following claims reimbursement pattern)
function viewLeaveTypeDetails(name, code, maxDays, carryForward, requiresApproval) {
    document.getElementById('view-leave-type-name').textContent = name || '-';
    document.getElementById('view-leave-type-code').textContent = code || '-';
    document.getElementById('view-leave-type-max-days').textContent = maxDays + ' days/year';
    document.getElementById('view-leave-type-carry-forward').textContent = carryForward || 'No';
    document.getElementById('view-leave-type-approval').textContent = requiresApproval || 'Yes';
    document.getElementById('view-leave-type-status').textContent = 'Active';
    openWorkingModal('view-leave-type-modal');
}

function viewLeaveRequestDetails(requestId) {
    const requestRow = document.querySelector(`button[onclick="viewLeaveRequestDetails(${requestId})"]`)?.closest('tr');
    
    if (requestRow && requestRow.cells.length >= 7) {
        document.getElementById('view-leave-request-employee').textContent = requestRow.cells[0].textContent.trim();
        document.getElementById('view-leave-request-type').textContent = requestRow.cells[1].textContent.trim();
        document.getElementById('view-leave-request-start').textContent = requestRow.cells[2].textContent.trim();
        document.getElementById('view-leave-request-end').textContent = requestRow.cells[3].textContent.trim();
        document.getElementById('view-leave-request-days').textContent = requestRow.cells[4].textContent.trim();
        document.getElementById('view-leave-request-reason').textContent = requestRow.cells[5].textContent.trim();
        document.getElementById('view-leave-request-status').textContent = requestRow.cells[6].querySelector('.badge')?.textContent.trim() || 'Unknown';
        openWorkingModal('view-leave-request-modal');
    }
}
</script>

<!-- View Leave Type Details Modal -->
<div class="working-modal" id="view-leave-type-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-leave-type-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Leave Type Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-leave-type-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Name:</strong>
                        <p id="view-leave-type-name" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Code:</strong>
                        <p id="view-leave-type-code" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Max Days Per Year:</strong>
                        <p id="view-leave-type-max-days" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Carry Forward:</strong>
                        <p id="view-leave-type-carry-forward" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Requires Approval:</strong>
                        <p id="view-leave-type-approval" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p id="view-leave-type-status" class="mb-2">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-leave-type-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Leave Request Details Modal -->
<div class="working-modal" id="view-leave-request-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-leave-request-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Leave Request Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-leave-request-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Employee:</strong>
                        <p id="view-leave-request-employee" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Leave Type:</strong>
                        <p id="view-leave-request-type" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Start Date:</strong>
                        <p id="view-leave-request-start" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>End Date:</strong>
                        <p id="view-leave-request-end" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Days:</strong>
                        <p id="view-leave-request-days" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p id="view-leave-request-status" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Reason:</strong>
                        <p id="view-leave-request-reason" class="mb-2">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-leave-request-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/working-modal-ess.js') }}"></script>
@endpush

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
  overflow-y: auto !important;
  padding: 20px !important;
}

.working-modal-dialog {
  max-width: 600px !important;
  width: 90% !important;
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
</style>

<script>
// Function to scroll to leave requests section (for Review Requests and Approve/Deny buttons)
function scrollToLeaveRequests() {
  const leaveRequestsSection = document.getElementById('leave-requests-section');
  if (leaveRequestsSection) {
    leaveRequestsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    // Add a subtle highlight effect
    leaveRequestsSection.style.boxShadow = '0 0 20px rgba(0, 123, 255, 0.3)';
    setTimeout(() => {
      leaveRequestsSection.style.boxShadow = '';
    }, 2000);
  }
}

// HR Authentication Modal Functions
function showAuthModal(action, type, itemId, extraData = null) {
    // Set the form data
    document.getElementById('auth-action').value = action;
    document.getElementById('auth-type').value = type;
    document.getElementById('auth-item-id').value = itemId;
    document.getElementById('auth-extra-data').value = extraData ? JSON.stringify(extraData) : '';
    
    // Clear previous form data
    document.getElementById('auth-email').value = '';
    document.getElementById('auth-password').value = '';
    
    // Update modal title based on action
    const modalTitle = document.querySelector('#hr-auth-modal .working-modal-title');
    const actionText = {
        'approve': 'Approve Leave Request',
        'reject': 'Reject Leave Request', 
        'delete': 'Delete Record',
        'edit': 'Edit Record',
        'create': type === 'leave_type' ? 'Create Leave Type' : 'Create Leave Request'
    };
    modalTitle.textContent = `HR Authorization Required - ${actionText[action] || 'Perform Action'}`;
    
    // Set form action URL based on type and action
    const form = document.getElementById('hr-auth-form');
    form.action = '/leave/hr-auth';
    
    // Show modal
    openWorkingModal('hr-auth-modal');
}

// Handle HR authentication form submission
document.addEventListener('DOMContentLoaded', function() {
    const hrAuthForm = document.getElementById('hr-auth-form');
    if (hrAuthForm) {
        hrAuthForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Authenticating...';
            
            fetch('/leave/hr-auth', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Clear all modals first
                    clearAllModals();
                    
                    // Show success message
                    showAlert('success', data.message || 'Authentication successful!');
                    
                    // Get the action and type from the form
                    const action = document.getElementById('auth-action').value;
                    const type = document.getElementById('auth-type').value;
                    const itemId = document.getElementById('auth-item-id').value;
                    const extraData = document.getElementById('auth-extra-data').value;
                    
                    // Open the appropriate modal based on action and type
                    setTimeout(() => {
                        if (action === 'create' && type === 'leave_type') {
                            openWorkingModal('create-leave-type-modal');
                        } else if (action === 'create' && type === 'leave_request') {
                            openWorkingModal('create-leave-request-modal');
                        } else if (action === 'edit' && type === 'leave_type' && extraData) {
                            const editData = JSON.parse(extraData);
                            editLeaveTypeForm(itemId, editData.name, editData.code, editData.description, editData.max_days, editData.carry_forward, editData.requires_approval);
                        } else {
                            // For other actions, reload the page to see changes
                            window.location.reload();
                        }
                    }, 500);
                } else {
                    showAlert('error', data.message || 'Authentication failed. Please check your credentials.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});

// Helper function to clear all modal states
function clearAllModals() {
    const modals = document.querySelectorAll('.working-modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
        modal.classList.remove('show');
    });
    document.body.classList.remove('modal-open');
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
}

// Helper function to show alerts
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert alert at the top of the page
    const pageHeader = document.querySelector('.page-header-container');
    if (pageHeader) {
        pageHeader.insertAdjacentHTML('afterend', alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}

// Check for session-based modal opening




@if(session('show_edit_modal'))
document.addEventListener('DOMContentLoaded', function() {
    const editData = @json(session('edit_leave_type'));
    if (editData) {
        editLeaveTypeForm(
            editData.id, 
            editData.name || '', 
            editData.code || '', 
            editData.description || '', 
            editData.max_days_per_year || 0, 
            editData.carry_forward ? 'true' : 'false', 
            editData.requires_approval ? 'true' : 'false'
        );
    }
});
@endif
</script>











