@extends('layouts.employee_layout')

@section('title', 'Attendance & Time Logs')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/working-modal-ess.css') }}">
<style>
  .timesheet-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
  }
  
  .timesheet-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
  }
  
  .status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
  }
  
  .working-modal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    z-index: 2000 !important;
    background: rgba(0, 0, 0, 0.5) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
  }

  .working-modal .modal-dialog {
    position: relative !important;
    z-index: 2001 !important;
    margin: 0 !important;
  }

  .working-modal input,
  .working-modal select,
  .working-modal textarea,
  .working-modal button {
    pointer-events: auto !important;
    z-index: 2002 !important;
  }

  .working-modal .form-control,
  .working-modal .form-select {
    background-color: #fff !important;
    border: 1px solid #ced4da !important;
    color: #495057 !important;
  }
</style>
<script src="{{ asset('assets/js/working-modal-ess.js') }}"></script>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Attendance & Time Logs</h2>
            <p class="text-muted mb-0">View your attendance records and time tracking</p>
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

    <!-- Alert Messages -->
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="working-modal-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="working-modal-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="working-modal-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card timesheet-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">
                  <i class="bi bi-plus-circle text-primary me-2"></i>Quick Actions
                </h5>
                <p class="text-muted mb-0">Submit new timesheet or manage existing ones</p>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-primary" onclick="openWorkingModal('timesheetModal')">
                  <i class="bi bi-plus-circle me-2"></i>Submit Timesheet
                </button>
                <button class="btn btn-outline-secondary" onclick="refreshTimesheets()">
                  <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card timesheet-card text-center">
          <div class="card-body">
            <div class="text-primary mb-2">
              <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
            </div>
            <h3 class="fw-bold mb-1" id="totalTimesheets">{{ $totalTimesheets ?? 0 }}</h3>
            <p class="text-muted mb-0 small">Total Timesheets</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card timesheet-card text-center">
          <div class="card-body">
            <div class="text-warning mb-2">
              <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
            </div>
            <h3 class="fw-bold mb-1" id="pendingTimesheets">{{ $pendingTimesheets ?? 0 }}</h3>
            <p class="text-muted mb-0 small">Pending Approval</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card timesheet-card text-center">
          <div class="card-body">
            <div class="text-success mb-2">
              <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
            </div>
            <h3 class="fw-bold mb-1" id="approvedTimesheets">{{ $approvedTimesheets ?? 0 }}</h3>
            <p class="text-muted mb-0 small">Approved</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card timesheet-card text-center">
          <div class="card-body">
            <div class="text-info mb-2">
              <i class="bi bi-stopwatch" style="font-size: 2rem;"></i>
            </div>
            <h3 class="fw-bold mb-1" id="totalHours">{{ $totalHours ?? '0.0' }}</h3>
            <p class="text-muted mb-0 small">Total Hours</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Timesheet List -->
    <div class="row">
      <div class="col-12">
        <div class="card timesheet-card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              <i class="bi bi-list-ul me-2"></i>My Timesheets
            </h5>
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm" id="statusFilter" onchange="filterTimesheets()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
              <select class="form-select form-select-sm" id="periodFilter" onchange="filterTimesheets()">
                <option value="">All Periods</option>
                <option value="current_week">Current Week</option>
                <option value="last_week">Last Week</option>
                <option value="current_month">Current Month</option>
                <option value="last_month">Last Month</option>
              </select>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Date</th>
                    <th>Hours Worked</th>
                    <th>Overtime</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="timesheetTableBody">
                  @forelse($timesheets ?? [] as $timesheet)
                    <tr>
                      <td>{{ \Carbon\Carbon::parse($timesheet->work_date)->format('M d, Y') }}</td>
                      <td>{{ $timesheet->hours_worked ?? '0.0' }} hrs</td>
                      <td>{{ $timesheet->overtime_hours ?? '0.0' }} hrs</td>
                      <td>{{ Str::limit($timesheet->description ?? 'No description', 50) }}</td>
                      <td>
                        @php
                          $statusClass = match($timesheet->status ?? 'pending') {
                            'approved' => 'bg-success',
                            'rejected' => 'bg-danger',
                            'pending' => 'bg-warning',
                            default => 'bg-secondary'
                          };
                        @endphp
                        <span class="badge {{ $statusClass }} status-badge">
                          {{ ucfirst($timesheet->status ?? 'pending') }}
                        </span>
                      </td>
                      <td>{{ \Carbon\Carbon::parse($timesheet->created_at)->format('M d, Y') }}</td>
                      <td>
                        <div class="btn-group btn-group-sm">
                          <button class="btn btn-outline-primary" onclick="viewTimesheet({{ $timesheet->id }})">
                            <i class="bi bi-eye"></i>
                          </button>
                          @if(($timesheet->status ?? 'pending') === 'pending')
                            <a href="{{ route('employee.timesheet.edit', $timesheet->id) }}" class="btn btn-outline-warning">
                              <i class="bi bi-pencil"></i>
                            </a>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                        No timesheets found. Submit your first timesheet to get started.
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

<script src="{{ asset('assets/js/working-modal-ess.js') }}"></script>
@endsection

<!-- Submit Timesheet Modal -->
<div class="working-modal" id="timesheetModal" style="display: none;">
  <div class="working-modal-backdrop" onclick="closeWorkingModal('timesheetModal')"></div>
  <div class="working-modal-dialog">
    <div class="working-modal-content">
      <div class="working-modal-header">
        <h5 class="working-modal-title">Submit Timesheet</h5>
        <button type="button" class="working-modal-close" onclick="closeWorkingModal('timesheetModal')">&times;</button>
      </div>
      <form action="{{ route('employee.timesheet.store') }}" method="POST" id="timesheetForm">
        @csrf
        <div class="working-modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="work_date" class="form-label">Work Date *</label>
              <input type="date" class="form-control" id="work_date" name="work_date" required max="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-6">
              <label for="clock_in_time" class="form-label">Clock In Time *</label>
              <input type="time" class="form-control" id="clock_in_time" name="clock_in_time" required>
            </div>
            <div class="col-md-6">
              <label for="clock_out_time" class="form-label">Clock Out Time *</label>
              <input type="time" class="form-control" id="clock_out_time" name="clock_out_time" required>
            </div>
            <div class="col-md-6">
              <label for="break_time" class="form-label">Break Time (hours)</label>
              <input type="number" class="form-control" id="break_time" name="break_time" 
                     min="0" max="8" step="0.5" value="1">
            </div>
            <div class="col-12">
              <label for="description" class="form-label">Work Description</label>
              <textarea class="form-control" id="description" name="description" rows="3" 
                        placeholder="Describe the work performed..."></textarea>
            </div>
          </div>
        </div>
        <div class="working-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('timesheetModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-2"></i>Submit Timesheet
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Timesheet Modal -->
<div class="working-modal" id="viewTimesheetModal" style="display: none;">
  <div class="working-modal-backdrop" onclick="closeWorkingModal('viewTimesheetModal')"></div>
  <div class="working-modal-dialog" style="max-width: 800px;">
    <div class="working-modal-content">
      <div class="working-modal-header">
        <h5 class="working-modal-title">Timesheet Details</h5>
        <button type="button" class="working-modal-close" onclick="closeWorkingModal('viewTimesheetModal')">&times;</button>
      </div>
      <div class="working-modal-body" id="timesheetDetails">
        <div class="text-center">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      <div class="working-modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('viewTimesheetModal')">Close</button>
      </div>
    </div>
  </div>
</div>

@section('scripts')
<script>
  // Initialize page
  document.addEventListener('DOMContentLoaded', function() {
    // Set default work date to today
    document.getElementById('work_date').value = new Date().toISOString().split('T')[0];
  });

  function viewTimesheet(timesheetId) {
    // Use working modal instead of Bootstrap modal
    openWorkingModal('viewTimesheetModal');
    
    // Load timesheet details via server-side route instead of AJAX
    window.location.href = `/employee/timesheet/${timesheetId}/view`;
  }
  
  // Working Modal Functions - Based on leave_management.blade.php pattern
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
  
  // Make functions globally available
  window.openWorkingModal = openWorkingModal;
  window.closeWorkingModal = closeWorkingModal;

  function refreshTimesheets() {
    location.reload();
  }

  function filterTimesheets() {
    const statusFilter = document.getElementById('statusFilter').value;
    const periodFilter = document.getElementById('periodFilter').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (statusFilter) params.append('status', statusFilter);
    if (periodFilter) params.append('period', periodFilter);
    
    // Redirect with filters
    window.location.href = `{{ route('employee.timesheet') }}?${params.toString()}`;
  }
</script>
<script src="{{ asset('assets/js/working-modal-ess.js') }}"></script>
@endsection
