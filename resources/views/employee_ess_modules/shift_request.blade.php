@extends('layouts.employee_layout')

@section('title', 'Shift Requests')

@section('content')

<!-- Alert Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('info'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Please correct the following errors:</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>Shift Requests
                    </h4>
                    <div>
                        <button type="button" class="btn btn-success me-2" onclick="showModal()" title="Create New Shift Request">
                            <i class="fas fa-plus me-1"></i>New Request
                        </button>
                        <a href="{{ route('employee.shift.schedule') }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-calendar me-1"></i>View Schedule
                        </a>
                        <a href="{{ route('employee.dashboard.show') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Requests</h6>
                                            <h3 class="mb-0">{{ $shiftRequests->count() }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-file-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Pending</h6>
                                            <h3 class="mb-0">{{ $shiftRequests->where('status', 'pending')->count() }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-hourglass-half fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Approved</h6>
                                            <h3 class="mb-0">{{ $shiftRequests->where('status', 'approved')->count() }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Rejected</h6>
                                            <h3 class="mb-0">{{ $shiftRequests->where('status', 'rejected')->count() }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-times-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Options -->
                    <form method="GET" action="{{ route('employee.shift.request') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <select class="form-select" name="status" onchange="this.form.submit()" title="Filter by Status">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="type" onchange="this.form.submit()" title="Filter by Request Type">
                                    <option value="">All Types</option>
                                    <option value="shift_change" {{ request('type') == 'shift_change' ? 'selected' : '' }}>Shift Change</option>
                                    <option value="shift_swap" {{ request('type') == 'shift_swap' ? 'selected' : '' }}>Shift Swap</option>
                                    <option value="overtime" {{ request('type') == 'overtime' ? 'selected' : '' }}>Overtime Request</option>
                                    <option value="time_off" {{ request('type') == 'time_off' ? 'selected' : '' }}>Time Off</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('employee.shift.request') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Shift Requests Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="shiftRequestsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Request Type</th>
                                    <th>Shift Type</th>
                                    <th>Requested Date</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shiftRequests as $request)
                                <tr data-status="{{ $request->status ?? 'pending' }}" data-type="{{ $request->request_type ?? '' }}">
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $request->request_type ?? 'General')) }}</span>
                                    </td>
                                    <td>{{ $request->shift_type_name ?? 'N/A' }}</td>
                                    <td>{{ isset($request->requested_date) ? date('M d, Y', strtotime($request->requested_date)) : 'N/A' }}</td>
                                    <td>{{ $request->reason ?? 'No reason provided' }}</td>
                                    <td>
                                        @php
                                            $status = $request->status ?? 'pending';
                                            $badgeClass = match($status) {
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'pending' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td>{{ isset($request->created_at) ? date('M d, Y', strtotime($request->created_at)) : 'N/A' }}</td>
                                    <td>
                                        @if(isset($request->id))
                                        <a href="{{ route('employee.shift.request.view', $request->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(($request->status ?? 'pending') === 'pending')
                                        <form method="POST" action="{{ route('employee.shift.request.cancel', $request->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Request">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No shift requests found
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
</div>

<!-- Create Shift Request Modal -->
<div class="modal fade" id="createShiftRequestModal" tabindex="-1" aria-labelledby="createShiftRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createShiftRequestModalLabel">New Shift Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('employee.shift.request.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="request_type" class="form-label">Request Type</label>
                        <select class="form-select" id="request_type" name="request_type" required>
                            <option value="">Select request type</option>
                            <option value="shift_change" {{ old('request_type') == 'shift_change' ? 'selected' : '' }}>Shift Change</option>
                            <option value="shift_swap" {{ old('request_type') == 'shift_swap' ? 'selected' : '' }}>Shift Swap</option>
                            <option value="overtime" {{ old('request_type') == 'overtime' ? 'selected' : '' }}>Overtime Request</option>
                            <option value="time_off" {{ old('request_type') == 'time_off' ? 'selected' : '' }}>Time Off Request</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="shift_type_id" class="form-label">Shift Type (if applicable)</label>
                        <select class="form-select" id="shift_type_id" name="shift_type_id">
                            <option value="">Select shift type</option>
                            @if(isset($shiftTypes))
                                @foreach($shiftTypes as $shiftType)
                                <option value="{{ $shiftType->id ?? '' }}" {{ old('shift_type_id') == ($shiftType->id ?? '') ? 'selected' : '' }}>
                                    {{ $shiftType->name ?? 'Unknown' }}
                                </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="requested_date" class="form-label">Requested Date</label>
                        <input type="date" class="form-control" id="requested_date" name="requested_date" value="{{ old('requested_date') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Please provide a reason for your request">{{ old('reason') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Auto-show modal if editing -->
@if(session('edit_request'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show the modal using Bootstrap
    const modal = new bootstrap.Modal(document.getElementById('createShiftRequestModal'));
    modal.show();
    
    // Populate form with edit data
    const editData = @json(session('edit_request'));
    if (editData) {
        document.getElementById('request_type').value = editData.request_type || '';
        document.getElementById('shift_type_id').value = editData.shift_type_id || '';
        document.getElementById('requested_date').value = editData.requested_date || '';
        document.getElementById('reason').value = editData.reason || '';
        
        // Update form action for editing
        const form = document.querySelector('#createShiftRequestModal form');
        form.action = '/employee/shift-requests/' + editData.id + '/update';
        
        // Add method spoofing for PUT request
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        form.appendChild(methodInput);
        
        // Update modal title
        document.querySelector('#createShiftRequestModalLabel').textContent = 'Edit Shift Request';
    }
});
</script>
@endif

<script>
// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert && alert.parentNode) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert && alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }
        }, 5000);
    });
});

</script>
@endsection

@push('scripts')
<script>
// Simple modal function
function showModal() {
    console.log('showModal called');
    const modal = document.getElementById('createShiftRequestModal');
    
    if (modal) {
        console.log('Modal found, attempting to show');
        
        // Try Bootstrap first
        if (typeof bootstrap !== 'undefined') {
            console.log('Using Bootstrap modal');
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else {
            console.log('Bootstrap not available, using manual modal');
            // Manual modal display
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.style.paddingRight = '17px';
            document.body.classList.add('modal-open');
            document.body.style.paddingRight = '17px';
            
            // Add backdrop
            let backdrop = document.querySelector('.modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
            
            // Close modal functionality
            const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
            closeButtons.forEach(btn => {
                btn.onclick = function() {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    document.body.style.paddingRight = '';
                    if (backdrop) backdrop.remove();
                };
            });
            
            // Close on backdrop click
            backdrop.onclick = function() {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
                document.body.style.paddingRight = '';
                backdrop.remove();
            };
        }
    } else {
        console.error('Modal not found');
        alert('Modal not found!');
    }
}

// Test on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded');
    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
    console.log('Modal exists:', document.getElementById('createShiftRequestModal') !== null);
});
</script>
@endpush
