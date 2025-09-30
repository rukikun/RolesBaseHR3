@extends('layouts.hr')

@section('title', 'Claims and Reimbursement - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Claims and Reimbursement</h2>
        <p class="text-muted mb-0">Configure claim types, submit and process claims</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/admin/dashboard" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Claims Management</li>
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

@php
  // Statistics are now passed from controller, but keep fallback values
$totalClaims = $totalClaims ?? 0;
$pendingClaims = $pendingClaims ?? 0;
$approvedClaims = $approvedClaims ?? 0;
$totalAmount = $totalAmount ?? 0;

// DEBUG: Check what data is being passed
echo "<!-- DEBUG: Claim Types Count: " . (isset($claimTypes) ? $claimTypes->count() : 'NOT SET') . " -->";
echo "<!-- DEBUG: Claims Count: " . (isset($claims) ? $claims->count() : 'NOT SET') . " -->";
echo "<!-- DEBUG: Employees Count: " . (isset($employees) ? $employees->count() : 'NOT SET') . " -->";

if (isset($claimTypes) && $claimTypes->count() > 0) {
    echo "<!-- DEBUG: First Claim Type: " . $claimTypes->first()->name . " -->";
}

if (isset($employees) && $employees->count() > 0) {
    echo "<!-- DEBUG: First Employee: " . $employees->first()->first_name . " " . $employees->first()->last_name . " (ID: " . $employees->first()->id . ") -->";
    echo "<!-- DEBUG: All Employee IDs: ";
    foreach ($employees as $emp) {
        echo $emp->id . ":" . $emp->first_name . " ";
    }
    echo " -->";
} else {
    echo "<!-- DEBUG: NO EMPLOYEES FOUND IN VIEW DATA -->";
}
@endphp

<!-- Claims Statistics -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-receipt text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $totalClaims }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Claims</p>
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
          <h3 class="fw-bold mb-0 stat-number">{{ $pendingClaims }}</h3>
          <p class="text-muted mb-0 small stat-label">Pending Review</p>
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
          <h3 class="fw-bold mb-0 stat-number">{{ $approvedClaims }}</h3>
          <p class="text-muted mb-0 small stat-label">Approved</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-dollar-sign text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">${{ number_format($totalAmount, 2) }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Amount</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Claims Management Actions -->
<div class="row mb-4">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-tasks me-2"></i>Claim Review & Approval
        </h5>
      </div>
      <div class="card-body">
        <button class="btn btn-info mb-2 me-2" id="validate-attachments-btn">
          <i class="fas fa-check me-2"></i>Validate Attachments
        </button>
        <button class="btn btn-warning mb-2 me-2" id="approve-reject-btn">
          <i class="fas fa-gavel me-2"></i>Approve/Reject
        </button>
        <button class="btn btn-success mb-2" id="forward-finance-btn">
          <i class="fas fa-share me-2"></i>Forward to Payroll
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Claim Types Configuration -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-list me-2"></i>Claim Types
    </h5>
    <button class="btn btn-primary" onclick="openWorkingModal('create-claim-type-modal')">
      <i class="fas fa-plus me-2"></i>Create Claim Type
    </button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="claim-types-table">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Max Amount</th>
            <th>Requires Attachment</th>
            <th>Auto Approve</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="claim-types-tbody">
          @forelse($claimTypes as $claimType)
            <tr>
              <td>{{ $claimType->name ?? 'N/A' }}</td>
              <td><span class="badge bg-primary">{{ $claimType->code ?? 'N/A' }}</span></td>
              <td>${{ number_format($claimType->max_amount ?? 0, 2) }}</td>
              <td>
                <span class="badge bg-{{ ($claimType->requires_attachment ?? 1) ? 'warning' : 'secondary' }}">
                  {{ ($claimType->requires_attachment ?? 1) ? 'Yes' : 'No' }}
                </span>
              </td>
              <td>
                <span class="badge bg-{{ ($claimType->auto_approve ?? 0) ? 'success' : 'info' }}">
                  {{ ($claimType->auto_approve ?? 0) ? 'Yes' : 'No' }}
                </span>
              </td>
              <td>
                @php
                  $status = ($claimType->is_active ?? 1) ? 'active' : 'inactive';
                  $badgeClass = match($status) {
                      'active' => 'bg-success',
                      'inactive' => 'bg-secondary',
                      default => 'bg-warning'
                  };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
              </td>
              <td>
                @if(isset($claimType->id))
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-info" onclick="viewClaimTypeDetails('{{ addslashes($claimType->name ?? '') }}', '{{ addslashes($claimType->code ?? '') }}', {{ $claimType->max_amount ?? 0 }}, '{{ ($claimType->requires_attachment ?? 1) ? 'Yes' : 'No' }}', '{{ ($claimType->auto_approve ?? 0) ? 'Yes' : 'No' }}')">
                      <i class="fas fa-eye"></i>
                    </button>
                    <form method="POST" action="{{ route('claim-types.delete', $claimType->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this claim type?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                @endif
              </td>
            </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-4">
              <i class="fas fa-receipt fa-3x mb-3 text-muted"></i><br>
              No claim types found. <a href="#" onclick="openWorkingModal('create-claim-type-modal')" class="text-primary">Create your first claim type</a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Claims Table -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-receipt me-2"></i>Claims
    </h5>
    <div>
      <select id="claim-status-filter" class="form-select form-select-sm d-inline-block w-auto me-2">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
        <option value="paid">Paid</option>
      </select>
      <button class="btn btn-primary" onclick="openWorkingModal('create-claim-modal')">
        <i class="fas fa-plus me-2"></i>New Claim
      </button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="claims-table">
        <thead class="table-light">
          <tr>
            <th>Employee</th>
            <th>Claim Type</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Description</th>
            <th>Attachments</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="claims-tbody">
          @forelse($claims as $claim)
            <tr>
              <td>{{ $claim->employee_name ?? 'Unknown Employee' }}</td>
              <td>{{ $claim->claim_type_name ?? 'Unknown Type' }}</td>
              <td>${{ number_format($claim->amount ?? 0, 2) }}</td>
              <td>{{ isset($claim->claim_date) ? date('M d, Y', strtotime($claim->claim_date)) : 'N/A' }}</td>
              <td>{{ isset($claim->description) ? Str::limit($claim->description, 30) : 'N/A' }}</td>
              <td>
                @if($claim->receipt_path || $claim->attachment_path)
                  <i class="fas fa-paperclip text-success" title="Has attachment"></i>
                @else
                  <i class="fas fa-times text-muted" title="No attachment"></i>
                @endif
              </td>
              <td>
                @php
                  $status = $claim->status ?? 'pending';
                  $badgeClass = match($status) {
                    'approved' => 'success',
                    'pending' => 'warning', 
                    'paid' => 'info',
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
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewClaimDetails({{ $claim->id ?? 0 }})" title="View">
                    <i class="fas fa-eye"></i>
                  </button>
                  @if($status === 'pending')
                        <form method="POST" action="{{ route('claims.approve', $claim->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to approve this claim?')">
                          @csrf
                          @method('PATCH')
                          <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                            <i class="fas fa-check"></i>
                          </button>
                        </form>
                        <form method="POST" action="{{ route('claims.reject', $claim->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this claim?')">
                          @csrf
                          @method('PATCH')
                          <button type="submit" class="btn btn-sm btn-outline-warning" title="Reject">
                            <i class="fas fa-times"></i>
                          </button>
                        </form>
                        @endif
                        @if($status === 'approved')
                        <form method="POST" action="{{ route('claims.pay', $claim->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to mark this claim as paid?')">
                          @csrf
                          @method('PATCH')
                          <button type="submit" class="btn btn-sm btn-outline-info" title="Mark as Paid">
                            <i class="fas fa-dollar-sign"></i>
                          </button>
                        </form>
                        @endif
                </div>
              </td>
            </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
              No claims found. <a href="#" onclick="openWorkingModal('create-claim-modal')" class="text-primary">Create your first claim</a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create Claim Type Modal -->
<div class="working-modal" id="create-claim-type-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('create-claim-type-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">{{ session('edit_claim_type') ? 'Edit Claim Type' : 'Create Claim Type' }}</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-claim-type-modal')">&times;</button>
            </div>
            <form id="create-claim-type-form" method="POST" action="{{ route('claim-types.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="mb-3">
                        <label for="claim-type-name" class="form-label">Claim Type Name</label>
                        <input type="text" class="form-control" id="claim-type-name" name="name" value="{{ session('edit_claim_type.name') ?? old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="claim-type-code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="claim-type-code" name="code" value="{{ session('edit_claim_type.code') ?? old('code') }}" required maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label for="max-amount" class="form-label">Maximum Amount</label>
                        <input type="number" class="form-control" id="max-amount" name="max_amount" value="{{ session('edit_claim_type.max_amount') ?? old('max_amount') }}" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3">{{ session('edit_claim_type.description') ?? old('description') }}</textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" id="requires-attachment" name="requires_attachment" value="1" {{ (session('edit_claim_type.requires_attachment') || old('requires_attachment')) ? 'checked' : '' }}>
                        <label class="form-check-label" for="requires-attachment">
                            Requires Attachment
                        </label>
                    </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" id="auto-approve" name="auto_approve" value="1" {{ (session('edit_claim_type.auto_approve') || old('auto_approve')) ? 'checked' : '' }}>
                        <label class="form-check-label" for="auto-approve">
                            Auto Approve (under limit)
                        </label>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-claim-type-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">{{ session('edit_claim_type') ? 'Update' : 'Create' }} Claim Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Claim Type Details Modal -->
<div class="working-modal" id="view-claim-type-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-claim-type-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Claim Type Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-claim-type-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Name:</strong>
                        <p id="view-claim-type-name" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Code:</strong>
                        <p id="view-claim-type-code" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Maximum Amount:</strong>
                        <p id="view-claim-type-max-amount" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Requires Attachment:</strong>
                        <p id="view-claim-type-attachment" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Auto Approve:</strong>
                        <p id="view-claim-type-auto-approve" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p id="view-claim-type-status" class="mb-2">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-claim-type-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Claim Details Modal -->
<div class="working-modal" id="view-claim-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-claim-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Claim Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-claim-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Employee:</strong>
                        <p id="view-claim-employee" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Claim Type:</strong>
                        <p id="view-claim-type" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Amount:</strong>
                        <p id="view-claim-amount" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date:</strong>
                        <p id="view-claim-date" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Description:</strong>
                        <p id="view-claim-description" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p id="view-claim-status" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Attachment:</strong>
                        <p id="view-claim-attachment" class="mb-2">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-claim-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Claim Modal -->
<div class="working-modal" id="create-claim-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('create-claim-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Create Claim</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-claim-modal')">&times;</button>
            </div>
            <form id="create-claim-form" method="POST" action="{{ route('claims.store') }}" enctype="multipart/form-data" onsubmit="submitClaimForm(event)">
                @csrf
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="employee-select" class="form-label">Employee</label>
                            <select class="form-select" id="employee-select" name="employee_id" required>
                                <option value="">Select Employee</option>
                                <!-- Debug: Show employee count -->
                                @if(isset($employees))
                                    <!-- Employees found: {{ $employees->count() }} -->
                                    @forelse($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                        </option>
                                    @empty
                                        <option value="" disabled>No employees in collection</option>
                                        <!-- Fallback employees for immediate functionality -->
                                        <option value="1">John Doe (Fallback)</option>
                                        <option value="2">Jane Smith (Fallback)</option>
                                        <option value="3">Mike Johnson (Fallback)</option>
                                        <option value="4">Sarah Wilson (Fallback)</option>
                                        <option value="5">Tom Brown (Fallback)</option>
                                    @endforelse
                                @else
                                    <option value="" disabled>Employees variable not set</option>
                                    <!-- Guaranteed fallback employees -->
                                    <option value="1">John Doe (Guaranteed)</option>
                                    <option value="2">Jane Smith (Guaranteed)</option>
                                    <option value="3">Mike Johnson (Guaranteed)</option>
                                    <option value="4">Sarah Wilson (Guaranteed)</option>
                                    <option value="5">Tom Brown (Guaranteed)</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="claim-type-select" class="form-label">Claim Type</label>
                            <select class="form-select" id="claim-type-select" name="claim_type_id" required>
                                <option value="">Select Claim Type</option>
                                @if(isset($claimTypes))
                                    <!-- Claim Types found: {{ $claimTypes->count() }} -->
                                    @forelse($claimTypes as $claimType)
                                        <option value="{{ $claimType->id }}" {{ old('claim_type_id') == $claimType->id ? 'selected' : '' }}>
                                            {{ $claimType->name }} ({{ $claimType->code ?? 'N/A' }})
                                        </option>
                                    @empty
                                        <option value="" disabled>No claim types in collection</option>
                                        <!-- Fallback claim types -->
                                        <option value="1">Travel Expenses (TRAVEL)</option>
                                        <option value="2">Meal Allowance (MEAL)</option>
                                        <option value="3">Office Supplies (OFFICE)</option>
                                        <option value="4">Training Costs (TRAIN)</option>
                                        <option value="5">Medical Expenses (MEDICAL)</option>
                                    @endforelse
                                @else
                                    <option value="" disabled>Claim types variable not set</option>
                                    <!-- Guaranteed fallback claim types -->
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
                            <input type="number" class="form-control" id="claim-amount" name="amount" step="0.01" min="0" value="{{ old('amount') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="claim-date" class="form-label">Claim Date</label>
                            <input type="date" class="form-control" id="claim-date" name="claim_date" value="{{ old('claim_date') }}" required>
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
                        <textarea class="form-control" id="claim-description" name="description" rows="3" required placeholder="Describe the expense...">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-claim-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('scripts')
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
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.setProperty('display', 'flex', 'important');
        modal.style.visibility = 'visible';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
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
            // Reset form action and method for create mode
            if (modalId === 'create-claim-type-modal') {
                form.action = "#";
                form.method = 'POST';
                // Remove any hidden method input
                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) {
                    methodInput.remove();
                }
                // Update modal title and button
                document.querySelector('#create-claim-type-modal .working-modal-title').textContent = 'Create Claim Type';
                document.querySelector('#create-claim-type-modal .btn-primary').textContent = 'Create Claim Type';
            }
        }
    }
}

// Handle edit claim data from session (only if explicitly requested)
@if(session('edit_claim') && session('show_edit_modal'))
document.addEventListener('DOMContentLoaded', function() {
    const editData = @json(session('edit_claim'));
    
    // Populate form with edit data
    document.getElementById('claim-type-select').value = editData.claim_type_id || '';
    document.getElementById('claim-amount').value = editData.amount || '';
    document.getElementById('claim-date').value = editData.claim_date || '';
    document.getElementById('claim-description').value = editData.description || '';
    
    // Update form action for editing
    const form = document.getElementById('create-claim-form');
    form.action = '#';
    form.innerHTML += '<input type="hidden" name="_method" value="PUT">';
    
    // Update modal title
    document.querySelector('#create-claim-modal .working-modal-title').textContent = 'Edit Claim';
    document.querySelector('#create-claim-modal .btn-primary').textContent = 'Update Claim';
    
    // Show modal
    openModal('create-claim-modal');
});
@endif

// SIMPLIFIED: Use regular form submission instead of AJAX
document.addEventListener('DOMContentLoaded', function() {
    const claimForm = document.getElementById('create-claim-form');
    if (claimForm) {
        claimForm.addEventListener('submit', function(e) {
            const employeeSelect = document.getElementById('employee-select');
            const claimTypeSelect = document.getElementById('claim-type-select');
            const amountInput = document.getElementById('claim-amount');
            const dateInput = document.getElementById('claim-date');
            const descriptionInput = document.getElementById('claim-description');
            
            // Simple validation
            if (!employeeSelect.value) {
                e.preventDefault();
                alert('❌ Please select an employee');
                employeeSelect.focus();
                return false;
            }
            
            if (!claimTypeSelect.value) {
                e.preventDefault();
                alert('❌ Please select a claim type');
                claimTypeSelect.focus();
                return false;
            }
            
            if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
                e.preventDefault();
                alert('❌ Please enter a valid amount');
                amountInput.focus();
                return false;
            }
            
            if (!dateInput.value) {
                e.preventDefault();
                alert('❌ Please select a claim date');
                dateInput.focus();
                return false;
            }
            
            if (!descriptionInput.value.trim()) {
                e.preventDefault();
                alert('❌ Please enter a description');
                descriptionInput.focus();
                return false;
            }
            
            // Debug: Log form data before submission
            console.log('Form validation passed. Submitting with data:');
            console.log('Employee ID:', employeeSelect.value);
            console.log('Employee Name:', employeeSelect.options[employeeSelect.selectedIndex].text);
            console.log('Claim Type ID:', claimTypeSelect.value);
            console.log('Amount:', amountInput.value);
            console.log('Date:', dateInput.value);
            console.log('Description:', descriptionInput.value);
            
            // Let the form submit normally
            const submitBtn = claimForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            }
            
            return true; // Allow normal form submission
        });
    }
    
    // Add loading states to action buttons
    const actionForms = document.querySelectorAll('form[action*="/claims/"]');
    actionForms.forEach(form => {
        // Skip the create claim form as it's handled above
        if (form.id === 'create-claim-form') return;
        
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                // Re-enable after 3 seconds in case of issues
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }, 3000);
            }
        });
    });
});

// Working Modal Functions
function openWorkingModal(modalId) {
    console.log('Opening modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.setProperty('display', 'flex', 'important');
        modal.style.visibility = 'visible';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Debug employee dropdown content
        if (modalId === 'create-claim-modal') {
            setTimeout(() => {
                const employeeSelect = document.getElementById('employee-select');
                if (employeeSelect) {
                    console.log('Employee dropdown options:', employeeSelect.innerHTML);
                    console.log('Employee dropdown option count:', employeeSelect.options.length);
                    
                    // Auto-select first employee if none selected and employees exist
                    if (!employeeSelect.value && employeeSelect.options.length > 1) {
                        employeeSelect.selectedIndex = 1; // Skip the "Select Employee" option
                        console.log('Auto-selected employee:', employeeSelect.value);
                    }
                }
            }, 100);
        }
        
        // Focus first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    }
}

function closeWorkingModal(modalId) {
    console.log('Closing modal:', modalId);
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
            
            // Reset form action and method for create mode
            if (modalId === 'create-claim-type-modal') {
                form.action = "{{ route('claim-types.store') }}";
                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) {
                    methodInput.remove();
                }
                // Reset modal title and button
                document.querySelector('#create-claim-type-modal .working-modal-title').textContent = 'Create Claim Type';
                document.querySelector('#create-claim-type-modal .btn-primary').textContent = 'Create Claim Type';
            } else if (modalId === 'create-claim-modal') {
                form.action = "{{ route('claims.store') }}";
                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) {
                    methodInput.remove();
                }
                // Reset modal title and button
                document.querySelector('#create-claim-modal .working-modal-title').textContent = 'Create Claim';
                document.querySelector('#create-claim-modal .btn-primary').textContent = 'Submit Claim';
            }
        }
    }
}

// View claim type details function
function viewClaimTypeDetails(name, code, maxAmount, requiresAttachment, autoApprove) {
    console.log('Viewing claim type details:', name);
    
    // Populate modal with data
    document.getElementById('view-claim-type-name').textContent = name || 'N/A';
    document.getElementById('view-claim-type-code').textContent = code || 'N/A';
    document.getElementById('view-claim-type-max-amount').textContent = maxAmount ? `$${parseFloat(maxAmount).toFixed(2)}` : 'No limit';
    document.getElementById('view-claim-type-attachment').textContent = requiresAttachment || 'No';
    document.getElementById('view-claim-type-auto-approve').textContent = autoApprove || 'No';
    document.getElementById('view-claim-type-status').textContent = 'Active';
    
    // Show modal
    openWorkingModal('view-claim-type-modal');
}

// Edit claim type function
function editClaimType(id, name, code, maxAmount, description, requiresAttachment, autoApprove) {
    console.log('Editing claim type:', id, name);
    
    // Populate form with data
    document.getElementById('claim-type-name').value = name || '';
    document.getElementById('claim-type-code').value = code || '';
    document.getElementById('max-amount').value = maxAmount || '';
    document.getElementById('description').value = description || '';
    document.getElementById('requires-attachment').checked = requiresAttachment == 1;
    document.getElementById('auto-approve').checked = autoApprove == 1;
    
    // Update form action and method for editing
    const form = document.getElementById('create-claim-type-form');
    form.action = `/claim-types/${id}`;
    
    // Add method spoofing for PUT request
    let methodInput = form.querySelector('input[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        form.appendChild(methodInput);
    }
    methodInput.value = 'PUT';
    
    // Update modal title and button
    document.querySelector('#create-claim-type-modal .working-modal-title').textContent = 'Edit Claim Type';
    document.querySelector('#create-claim-type-modal .btn-primary').textContent = 'Update Claim Type';
    
    // Show modal
    openWorkingModal('create-claim-type-modal');
}

// Edit claim function
function editClaim(claimId) {
    console.log('Editing claim:', claimId);
    
    // Find the claim row in the table
    const claimRow = document.querySelector(`button[onclick="viewClaimDetails(${claimId})"]`)?.closest('tr');
    
    if (claimRow && claimRow.cells.length >= 7) {
        // Extract data from table row
        const employee = claimRow.cells[0].textContent.trim();
        const claimType = claimRow.cells[1].textContent.trim();
        const amount = claimRow.cells[2].textContent.trim().replace('$', '');
        const date = claimRow.cells[3].textContent.trim();
        const description = claimRow.cells[4].textContent.trim();
        
        // Find employee ID from dropdown
        const employeeSelect = document.getElementById('employee-select');
        let employeeId = '';
        for (let option of employeeSelect.options) {
            if (option.text.includes(employee.split(' ')[0])) {
                employeeId = option.value;
                break;
            }
        }
        
        // Find claim type ID from dropdown
        const claimTypeSelect = document.getElementById('claim-type-select');
        let claimTypeId = '';
        for (let option of claimTypeSelect.options) {
            if (option.text.includes(claimType)) {
                claimTypeId = option.value;
                break;
            }
        }
        
        // Convert date format (from "Mon dd, yyyy" to "yyyy-mm-dd")
        let formattedDate = '';
        if (date && date !== 'N/A') {
            try {
                const dateObj = new Date(date);
                if (!isNaN(dateObj.getTime())) {
                    formattedDate = dateObj.toISOString().split('T')[0];
                }
            } catch (e) {
                console.warn('Date parsing failed:', e);
            }
        }
        
        // Populate form with data
        document.getElementById('employee-select').value = employeeId;
        document.getElementById('claim-type-select').value = claimTypeId;
        document.getElementById('claim-amount').value = amount;
        document.getElementById('claim-date').value = formattedDate;
        document.getElementById('claim-description').value = description;
        
        // Update form action and method for editing
        const form = document.getElementById('create-claim-form');
        form.action = `/claims/${claimId}`;
        
        // Add method spoofing for PUT request
        let methodInput = form.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            form.appendChild(methodInput);
        }
        methodInput.value = 'PUT';
        
        // Update modal title and button
        document.querySelector('#create-claim-modal .working-modal-title').textContent = 'Edit Claim';
        document.querySelector('#create-claim-modal .btn-primary').textContent = 'Update Claim';
        
        // Show modal
        openWorkingModal('create-claim-modal');
    } else {
        alert('Unable to load claim data for editing. Please try again.');
    }
}

// Submit claim form with AJAX to handle JSON response
function submitClaimForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Disable submit button
    submitButton.disabled = true;
    submitButton.textContent = 'Submitting...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            closeWorkingModal('create-claim-modal');
            // Optionally reload the page to show updated data
            // window.location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error submitting claim. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.textContent = 'Submit Claim';
    });
}

// View claim details function
function viewClaimDetails(claimId) {
    console.log('Viewing claim details for ID:', claimId);
    
    // Find the claim row in the table
    const claimRow = document.querySelector(`button[onclick="viewClaimDetails(${claimId})"]`)?.closest('tr');
    
    if (claimRow && claimRow.cells.length >= 7) {
        // Extract data from table row
        const employee = claimRow.cells[0].textContent.trim();
        const claimType = claimRow.cells[1].textContent.trim();
        const amount = claimRow.cells[2].textContent.trim();
        const date = claimRow.cells[3].textContent.trim();
        const description = claimRow.cells[4].textContent.trim();
        const hasAttachment = claimRow.cells[5].querySelector('.fa-paperclip') ? 'Yes' : 'No';
        const status = claimRow.cells[6].querySelector('.badge')?.textContent.trim() || 'Unknown';
        
        // Populate modal with data
        document.getElementById('view-claim-employee').textContent = employee;
        document.getElementById('view-claim-type').textContent = claimType;
        document.getElementById('view-claim-amount').textContent = amount;
        document.getElementById('view-claim-date').textContent = date;
        document.getElementById('view-claim-description').textContent = description;
        document.getElementById('view-claim-attachment').textContent = hasAttachment;
        document.getElementById('view-claim-status').textContent = status;
        
        // Show modal
        openWorkingModal('view-claim-modal');
    } else {
        alert('Unable to load claim details. Please try again.');
    }
}

// Filter claims by status (matching leave management pattern)
function filterClaims() {
    const statusFilter = document.getElementById('claim-status-filter');
    const tbody = document.getElementById('claims-tbody');
    const rows = tbody.querySelectorAll('tr');
    
    if (!statusFilter) return;
    
    const selectedStatus = statusFilter.value.toLowerCase();
    
    rows.forEach(row => {
        if (row.cells.length < 7) return; // Skip empty state row
        
        const statusCell = row.cells[6]; // Status column
        const statusBadge = statusCell.querySelector('.badge');
        
        if (!statusBadge) return;
        
        const rowStatus = statusBadge.textContent.toLowerCase().trim();
        
        if (selectedStatus === '' || rowStatus === selectedStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Claims management page loaded');
    
    // Add event listener for status filter
    const statusFilter = document.getElementById('claim-status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterClaims);
    }
    
    // Auto-show modal if editing claim type
    @if(session('edit_claim_type'))
        openWorkingModal('create-claim-type-modal');
        
        // Update form action for editing
        const form = document.getElementById('create-claim-type-form');
        if (form) {
            form.action = "{{ route('claim-types.update', session('edit_claim_type.id')) }}";
            form.insertAdjacentHTML('afterbegin', '@method("PUT")');
        }
    @endif
});
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
    position: relative !important;
    z-index: 2002 !important;
}

.working-modal .btn {
    pointer-events: auto !important;
    cursor: pointer !important;
    position: relative !important;
    z-index: 2002 !important;
}

.working-modal .form-control:focus,
.working-modal .form-select:focus {
    z-index: 2003 !important;
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
</style>
@endpush

@endsection
