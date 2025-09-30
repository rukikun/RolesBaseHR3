@extends('layouts.employee_layout')

@section('title', 'Leave Balance')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-balance-scale me-2"></i>Leave Balance
                    </h4>
                    <div>
                        <a href="{{ route('employee.leave.management') }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-plane me-1"></i>Request Leave
                        </a>
                        <a href="{{ route('employee.dashboard.show') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Leave Balance Cards -->
                    <div class="row mb-4">
                        @forelse($leaveBalances as $balance)
                        @php
                            $used = $leaveUsage->where('leave_type_id', $balance->leave_type_id)->first();
                            $daysUsed = $used ? $used->days_used : 0;
                            $daysAllowed = $balance->days_allowed ?? 0;
                            $remaining = max(0, $daysAllowed - $daysUsed);
                            $percentage = $daysAllowed > 0 ? ($daysUsed / $daysAllowed) * 100 : 0;
                            $progressClass = $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title mb-0">{{ $balance->leave_type_name ?? 'Unknown' }}</h6>
                                        <span class="badge bg-primary">{{ $remaining }} days left</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 10px;">
                                        <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                                             style="width: {{ min(100, $percentage) }}%" 
                                             aria-valuenow="{{ $percentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">Used: {{ $daysUsed }} days</small>
                                        <small class="text-muted">Total: {{ $daysAllowed }} days</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                No leave balance information available. Please contact HR to set up your leave entitlements.
                            </div>
                        </div>
                        @endforelse
                    </div>

                    <!-- Leave Usage Summary -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>Leave Usage Summary
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Leave Type</th>
                                                    <th>Allocated</th>
                                                    <th>Used</th>
                                                    <th>Remaining</th>
                                                    <th>Requests</th>
                                                    <th>Usage %</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($leaveBalances as $balance)
                                                @php
                                                    $used = $leaveUsage->where('leave_type_id', $balance->leave_type_id)->first();
                                                    $daysUsed = $used ? $used->days_used : 0;
                                                    $totalRequests = $used ? $used->total_requests : 0;
                                                    $daysAllowed = $balance->days_allowed ?? 0;
                                                    $remaining = max(0, $daysAllowed - $daysUsed);
                                                    $percentage = $daysAllowed > 0 ? round(($daysUsed / $daysAllowed) * 100, 1) : 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $balance->leave_type_name ?? 'Unknown' }}</td>
                                                    <td>{{ $daysAllowed }} days</td>
                                                    <td>{{ $daysUsed }} days</td>
                                                    <td>
                                                        <span class="badge {{ $remaining > 5 ? 'bg-success' : ($remaining > 0 ? 'bg-warning' : 'bg-danger') }}">
                                                            {{ $remaining }} days
                                                        </span>
                                                    </td>
                                                    <td>{{ $totalRequests }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 me-2" style="height: 8px; width: 60px;">
                                                                <div class="progress-bar {{ $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success') }}" 
                                                                     style="width: {{ min(100, $percentage) }}%"></div>
                                                            </div>
                                                            <small>{{ $percentage }}%</small>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No leave balance data available</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-tachometer-alt me-2"></i>Quick Stats
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @php
                                        $totalAllocated = $leaveBalances->sum('days_allowed');
                                        $totalUsed = $leaveUsage->sum('days_used');
                                        $totalRemaining = max(0, $totalAllocated - $totalUsed);
                                        $overallUsage = $totalAllocated > 0 ? round(($totalUsed / $totalAllocated) * 100, 1) : 0;
                                    @endphp
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Total Allocated:</span>
                                            <strong>{{ $totalAllocated }} days</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Total Used:</span>
                                            <strong class="text-warning">{{ $totalUsed }} days</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Total Remaining:</span>
                                            <strong class="text-success">{{ $totalRemaining }} days</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Overall Usage:</span>
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="height: 8px; width: 60px;">
                                                    <div class="progress-bar {{ $overallUsage > 80 ? 'bg-danger' : ($overallUsage > 60 ? 'bg-warning' : 'bg-success') }}" 
                                                         style="width: {{ min(100, $overallUsage) }}%"></div>
                                                </div>
                                                <strong>{{ $overallUsage }}%</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    
                                    <div class="text-center">
                                        <button class="btn btn-primary btn-sm" onclick="openWorkingModal('ess-apply-leave-modal')">
                                            <i class="fas fa-plus me-1"></i>Apply for Leave
                                        </button>
                                        <a href="{{ route('employee.leave.management') }}" class="btn btn-outline-primary btn-sm mt-2 d-block">
                                            <i class="fas fa-list me-1"></i>View All Requests
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Leave Calendar -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-calendar me-2"></i>This Month
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <h4 class="text-primary">{{ now()->format('F Y') }}</h4>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Plan your leave requests wisely
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid var(--jetlouge-primary) !important;
}

.progress-bar {
    transition: width 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: box-shadow 0.3s ease;
}

/* Working Modal Styles */
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

.working-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.working-modal-dialog {
    position: relative !important;
    z-index: 2001 !important;
    margin: 0 !important;
    max-width: 600px;
    width: 90%;
}

.working-modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
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
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
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
    z-index: 2002 !important;
}

.working-modal .form-control,
.working-modal .form-select {
    background-color: #fff !important;
    border: 1px solid #ced4da !important;
    color: #495057 !important;
}
</style>

<!-- ESS Apply for Leave Modal -->
<div class="working-modal" id="ess-apply-leave-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('ess-apply-leave-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">
                    <i class="fas fa-plane me-2"></i>Apply for Leave
                </h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('ess-apply-leave-modal')">&times;</button>
            </div>
            <form id="ess-apply-leave-form" method="POST" action="{{ route('employee.leave.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="ess-leave-type" class="form-label">Leave Type *</label>
                            <select class="form-select" id="ess-leave-type" name="leave_type_id" required onchange="updateLeaveTypeInfo()">
                                <option value="">Select leave type...</option>
                                @forelse($leaveBalances as $balance)
                                    <option value="{{ $balance->leave_type_id }}" 
                                            data-available="{{ $balance->days_allowed - ($leaveUsage->where('leave_type_id', $balance->leave_type_id)->first()->days_used ?? 0) }}"
                                            data-max-days="{{ $balance->days_allowed }}">
                                        {{ $balance->leave_type_name }} ({{ $balance->days_allowed - ($leaveUsage->where('leave_type_id', $balance->leave_type_id)->first()->days_used ?? 0) }} days available)
                                    </option>
                                @empty
                                    <option value="" disabled>No leave types available</option>
                                @endforelse
                            </select>
                            <div id="leave-type-info" class="mt-2" style="display: none;">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <span id="leave-type-details"></span>
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="ess-start-date" class="form-label">Start Date *</label>
                            <input type="date" class="form-control" id="ess-start-date" name="start_date" required onchange="calculateEssLeaveDays()">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="ess-end-date" class="form-label">End Date *</label>
                            <input type="date" class="form-control" id="ess-end-date" name="end_date" required onchange="calculateEssLeaveDays()">
                        </div>
                        
                        <div class="col-12">
                            <div class="alert alert-success" id="ess-leave-days-info" style="display: none;">
                                <i class="fas fa-calendar-check me-2"></i>
                                <strong>Total Leave Days: <span id="ess-total-days">0</span></strong>
                                <div class="mt-1">
                                    <small>
                                        <i class="fas fa-clock me-1"></i>
                                        Duration: <span id="ess-leave-duration"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label for="ess-reason" class="form-label">Reason for Leave *</label>
                            <textarea class="form-control" id="ess-reason" name="reason" rows="4" 
                                      placeholder="Please provide a detailed reason for this leave request..." required></textarea>
                            <div class="form-text">
                                <i class="fas fa-lightbulb me-1"></i>
                                Tip: Include any relevant details that will help with the approval process
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ess-urgent-request" name="urgent_request">
                                <label class="form-check-label" for="ess-urgent-request">
                                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                    Mark as urgent request
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('ess-apply-leave-modal')">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="ess-submit-btn">
                        <i class="fas fa-paper-plane me-2"></i>Submit Leave Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Working Modal Functions
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
        }
    }
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

function updateLeaveTypeInfo() {
    const leaveTypeSelect = document.getElementById('ess-leave-type');
    const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
    const infoDiv = document.getElementById('leave-type-info');
    const detailsSpan = document.getElementById('leave-type-details');
    
    if (selectedOption.value) {
        const availableDays = selectedOption.getAttribute('data-available');
        const maxDays = selectedOption.getAttribute('data-max-days');
        
        detailsSpan.innerHTML = `
            You have <strong>${availableDays}</strong> days available out of <strong>${maxDays}</strong> total days for this leave type.
        `;
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
}

function calculateEssLeaveDays() {
    const startDate = document.getElementById('ess-start-date').value;
    const endDate = document.getElementById('ess-end-date').value;
    const infoDiv = document.getElementById('ess-leave-days-info');
    const totalDaysSpan = document.getElementById('ess-total-days');
    const durationSpan = document.getElementById('ess-leave-duration');
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (end >= start) {
            const timeDiff = end.getTime() - start.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
            
            totalDaysSpan.textContent = daysDiff;
            
            // Calculate duration description
            if (daysDiff === 1) {
                durationSpan.textContent = 'Single day leave';
            } else if (daysDiff <= 7) {
                durationSpan.textContent = `${daysDiff} days (${Math.ceil(daysDiff/5)} week${daysDiff > 5 ? 's' : ''})`;
            } else {
                const weeks = Math.floor(daysDiff / 7);
                const remainingDays = daysDiff % 7;
                durationSpan.textContent = `${daysDiff} days (${weeks} week${weeks > 1 ? 's' : ''}${remainingDays > 0 ? ` ${remainingDays} day${remainingDays > 1 ? 's' : ''}` : ''})`;
            }
            
            infoDiv.style.display = 'block';
            
            // Update end date minimum
            document.getElementById('ess-end-date').min = startDate;
            
            // Check if requested days exceed available days
            const leaveTypeSelect = document.getElementById('ess-leave-type');
            const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
            if (selectedOption.value) {
                const availableDays = parseInt(selectedOption.getAttribute('data-available'));
                if (daysDiff > availableDays) {
                    infoDiv.className = 'alert alert-warning';
                    totalDaysSpan.parentElement.innerHTML = `
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning: Requesting ${daysDiff} days but only ${availableDays} days available!</strong>
                    `;
                } else {
                    infoDiv.className = 'alert alert-success';
                    totalDaysSpan.parentElement.innerHTML = `
                        <i class="fas fa-calendar-check me-2"></i>
                        <strong>Total Leave Days: <span id="ess-total-days">${daysDiff}</span></strong>
                    `;
                }
            }
        } else {
            infoDiv.style.display = 'none';
        }
    } else {
        infoDiv.style.display = 'none';
    }
}

// Set minimum date to today for ESS form
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const essStartDate = document.getElementById('ess-start-date');
    const essEndDate = document.getElementById('ess-end-date');
    
    if (essStartDate) essStartDate.min = today;
    if (essEndDate) essEndDate.min = today;
    
    // ESS form validation
    const essForm = document.getElementById('ess-apply-leave-form');
    if (essForm) {
        essForm.addEventListener('submit', function(e) {
            const leaveTypeId = document.getElementById('ess-leave-type').value;
            const startDate = document.getElementById('ess-start-date').value;
            const endDate = document.getElementById('ess-end-date').value;
            const reason = document.getElementById('ess-reason').value.trim();
            
            if (!leaveTypeId) {
                alert('Please select a leave type.');
                e.preventDefault();
                return false;
            }
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                e.preventDefault();
                return false;
            }
            
            if (new Date(endDate) < new Date(startDate)) {
                alert('End date cannot be before start date.');
                e.preventDefault();
                return false;
            }
            
            if (!reason) {
                alert('Please provide a reason for the leave request.');
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('ess-submit-btn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
        });
    }
});
</script>

@endsection
