@extends('layouts.hr')

@section('title', 'Shift Schedule Management - HR System')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('styles')
<style>
.shift-item {
  cursor: pointer;
  transition: all 0.3s ease;
}

.shift-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.stat-card {
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.bg-purple {
  background-color: var(--jetlouge-primary) !important;
}

.day-header {
  font-size: 12px;
  padding: 12px 8px !important;
  background-color: var(--jetlouge-primary) !important;
  color: white !important;
}

.calendar-cell {
  background-color: white;
  border: 1px solid #e0e0e0 !important;
  transition: all 0.2s ease;
}

.calendar-cell:hover {
  background-color: #f8f9fa;
  border-color: var(--jetlouge-primary) !important;
}

.bg-jetlouge-light {
  background-color: rgba(var(--jetlouge-primary-rgb), 0.1) !important;
}

.border-jetlouge-primary {
  border-color: var(--jetlouge-primary) !important;
  border-width: 2px !important;
}

.text-jetlouge-primary {
  color: var(--jetlouge-primary) !important;
}

.shift-indicator {
  display: inline-block;
  font-size: 10px;
  padding: 2px 6px;
  margin: 1px;
  border-radius: 10px;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.shift-indicator:hover {
  transform: scale(1.1);
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Calendar Styles */
.calendar-cell {
  border-color: #dee2e6 !important;
}

.calendar-cell:hover {
  background-color: #f8f9fa !important;
}

.calendar-date-number {
  font-weight: 500;
  margin-bottom: 4px;
}

.shift-indicator {
  font-size: 0.65rem;
  padding: 2px 6px;
  margin: 1px;
  border-radius: 12px;
  display: inline-block;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 600;
  letter-spacing: 0.3px;
}

.shift-indicator:hover {
  transform: scale(1.1);
  box-shadow: 0 3px 6px rgba(0,0,0,0.3);
  z-index: 10;
  position: relative;
}

.day-header {
  font-size: 0.85rem;
  letter-spacing: 1px;
  font-weight: 700;
}

/* Smooth scrolling for calendar navigation */
#monthly-calendar {
  scroll-behavior: smooth;
}

/* Real-time date highlighting */
.today-highlight {
  background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%);
  border: 2px solid #0d6efd;
}

/* Month transition animation */
.calendar-grid-container {
  animation: fadeInCalendar 0.3s ease-in-out;
}

@keyframes fadeInCalendar {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
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

/* Working Modal Styles - Essential for form functionality */
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

.working-modal .form-control:focus,
.working-modal .form-select:focus {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

.working-modal .btn {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Calendar cell cursor for clickable dates */
.calendar-cell {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.calendar-cell:hover {
    background-color: #f8f9fa !important;
}

/* Shift item hover effects */
.shift-item {
    transition: all 0.2s ease;
}

.shift-item:hover {
    background-color: #bbdefb !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.shift-item:hover .shift-actions {
    display: block !important;
}

.shift-actions button {
    border-radius: 2px;
    transition: all 0.2s ease;
}

.shift-actions button:hover {
    transform: scale(1.1);
}

/* Fade out animation for deleted shifts */
.shift-item.deleting {
    opacity: 0.5;
    transform: scale(0.95);
    transition: all 0.5s ease-out;
}

.shift-item.fade-out {
    opacity: 0;
    transform: scale(0.8) translateY(-10px);
    transition: all 0.6s ease-out;
}

/* Loading state for delete button */
.btn-deleting {
    opacity: 0.7;
    pointer-events: none;
}

.btn-deleting .fas {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.shift-item {
    cursor: pointer;
    transition: all 0.2s ease;
}

.shift-item:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
</style>
@endpush

@section('content')

@php
// Define $displayMonth at the top level for global access
$requestedMonth = request('month');
if ($requestedMonth) {
    if (strlen($requestedMonth) > 7) {
        $requestedMonth = substr($requestedMonth, 0, 7);
    }
    $displayMonth = \Carbon\Carbon::createFromFormat('Y-m', $requestedMonth);
} else {
    $displayMonth = \Carbon\Carbon::now();
}
@endphp

@push('scripts')
<script>
// Working Modal Functions - Based on Claims Pattern
function openWorkingModal(modalId) {
    console.log('Opening modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.setProperty('display', 'flex', 'important');
        modal.style.setProperty('align-items', 'center', 'important');
        modal.style.setProperty('justify-content', 'center', 'important');
        modal.style.visibility = 'visible';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Auto-populate shift type times when selected
        if (modalId === 'create-shift-modal' || modalId === 'create-shift-request-modal') {
            setupShiftTypeAutoFill(modalId);
        }
        
        console.log('Modal opened successfully:', modalId);
    } else {
        console.error('Modal not found:', modalId);
        alert('Modal not found: ' + modalId);
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
            if (modalId === 'create-shift-type-modal') {
                form.action = "{{ route('shift-types.store') }}";
                form.method = 'POST';
                // Remove any hidden method input
                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) {
                    methodInput.remove();
                }
                // Update modal title and button
                const titleElement = document.querySelector('#create-shift-type-modal .working-modal-title');
                const buttonElement = document.querySelector('#create-shift-type-modal .btn-primary');
                if (titleElement) titleElement.textContent = 'Create Shift Type';
                if (buttonElement) buttonElement.textContent = 'Create Shift Type';
            }
        }
    }
}

// Setup shift type auto-fill functionality
function setupShiftTypeAutoFill(modalId) {
    let shiftTypeSelect, startTimeInput, endTimeInput;
    
    if (modalId === 'create-shift-modal') {
        shiftTypeSelect = document.getElementById('shift-assignment-type');
        startTimeInput = document.getElementById('shift-assignment-start-time');
        endTimeInput = document.getElementById('shift-assignment-end-time');
    } else if (modalId === 'create-shift-request-modal') {
        shiftTypeSelect = document.getElementById('shift-request-type');
        startTimeInput = document.getElementById('shift-request-start-time');
        endTimeInput = document.getElementById('shift-request-end-time');
    }
    
    if (shiftTypeSelect && startTimeInput && endTimeInput) {
        shiftTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.startTime && selectedOption.dataset.endTime) {
                startTimeInput.value = selectedOption.dataset.startTime;
                endTimeInput.value = selectedOption.dataset.endTime;
            }
        });
    }
}

// Edit shift type function - MAIN VERSION
function editShiftTypeForm(id, name, code, startTime, endTime, duration, breakDuration) {
    console.log('Editing shift type:', {id, name, code, startTime, endTime, duration, breakDuration});
    
    // Populate the form with existing data
    const nameField = document.getElementById('shift-type-name');
    const codeField = document.getElementById('shift-type-code');
    const startTimeField = document.getElementById('shift-type-start-time');
    const endTimeField = document.getElementById('shift-type-end-time');
    const durationField = document.getElementById('shift-type-duration');
    const breakField = document.getElementById('shift-type-break');
    
    if (nameField) nameField.value = name || '';
    if (codeField) codeField.value = code || '';
    if (startTimeField) startTimeField.value = startTime || '';
    if (endTimeField) endTimeField.value = endTime || '';
    if (durationField) durationField.value = duration || 8;
    if (breakField) breakField.value = breakDuration || 60;
    
    // Update form for editing
    const form = document.querySelector('#create-shift-type-modal form');
    if (form) {
        form.action = `/shift-types/${id}`;
        form.method = 'POST';
        
        // Add method spoofing for PUT
        let methodInput = form.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            form.appendChild(methodInput);
        }
        methodInput.value = 'PUT';
    }
    
    // Update modal title and button
    const titleElement = document.querySelector('#create-shift-type-modal .working-modal-title');
    const buttonElement = document.querySelector('#create-shift-type-modal .btn-primary');
    if (titleElement) titleElement.textContent = 'Edit Shift Type';
    if (buttonElement) buttonElement.textContent = 'Update Shift Type';
    
    // Open the modal
    openWorkingModal('create-shift-type-modal');
}

// View shift type details function - REMOVED (replaced with modal version below)

// Open create shift modal with selected date
function openCreateShiftModal(selectedDate) {
    document.getElementById('shift-assignment-date').value = selectedDate;
    openWorkingModal('create-shift-modal');
}

// Auto-fill shift times when shift type is selected
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
                
                if (startTime) startTimeInput.value = startTime;
                if (endTime) endTimeInput.value = endTime;
            }
        });
    }
    
    // Debug: Check employee dropdown options
    const employeeSelect = document.getElementById('shift-assignment-employee');
    if (employeeSelect) {
        console.log('Employee dropdown found with', employeeSelect.options.length, 'options');
        for (let i = 0; i < employeeSelect.options.length; i++) {
            console.log('Option', i, ':', employeeSelect.options[i].value, '-', employeeSelect.options[i].text);
        }
    } else {
        console.log('Employee dropdown not found!');
    }
});

// Calendar cell click handler
function handleCalendarCellClick(event, date) {
    // Only open modal if clicking on empty space, not on existing shifts
    if (event.target.classList.contains('calendar-cell') || event.target.closest('.p-2')) {
        if (!event.target.closest('.shift-item')) {
            openCreateShiftModal(date);
        }
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const visibleModals = document.querySelectorAll('.working-modal[style*="flex"]');
        visibleModals.forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = '';
    }
});

// Functions will be made globally available after they are defined

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Shift schedule management page loaded');
    
    // Auto-show modal if editing shift type
    @if(session('edit_shift_type'))
        openWorkingModal('create-shift-type-modal');
    @endif
    
    // Add click handlers to calendar cells
    document.querySelectorAll('.calendar-cell').forEach(cell => {
        const dateElement = cell.querySelector('.fw-bold');
        if (dateElement) {
            const dateText = dateElement.textContent.trim();
            const currentMonth = '{{ $displayMonth->format("Y-m") }}';
            const fullDate = `${currentMonth}-${dateText.padStart(2, '0')}`;
            
            cell.addEventListener('click', function(event) {
                handleCalendarCellClick(event, fullDate);
            });
        }
    });
});
</script>
@endpush

<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Shift Schedule Management</h2>
        <p class="text-muted mb-0">Manage employee shifts, schedules, and requests</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/admin/dashboard" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Shift Schedule Management</li>
      </ol>
    </nav>
    
  </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
  <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<script>
// Auto-dismiss success message after 5 seconds
setTimeout(function() {
    const successAlert = document.getElementById('success-alert');
    if (successAlert) {
        const bsAlert = new bootstrap.Alert(successAlert);
        bsAlert.close();
    }
}, 5000);
</script>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
  <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<script>
// Auto-dismiss error message after 8 seconds (longer than success for user to read)
setTimeout(function() {
    const errorAlert = document.getElementById('error-alert');
    if (errorAlert) {
        const bsAlert = new bootstrap.Alert(errorAlert);
        bsAlert.close();
    }
}, 8000);
</script>
@endif

@if(session('info'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
  {{ session('info') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif


@if(isset($errors) && $errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <ul class="mb-0">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Statistics data is now passed from ShiftController --}}

@php
    $dashboardCards = [
        [
            'icon' => 'fa-calendar-alt',
            'bg' => 'bg-primary',
            'id' => 'total-shifts',
            'value' => $stats['active_shifts'] ?? 0,
            'label' => 'Total Shifts',
        ],
        [
            'icon' => 'fa-users',
            'bg' => 'bg-success',
            'id' => 'assigned-employees',
            'value' => $stats['total_employees'] ?? 0,
            'label' => 'Assigned Employees',
        ],
        [
            'icon' => 'fa-exchange-alt',
            'bg' => 'bg-warning',
            'id' => 'pending-requests',
            'value' => $stats['pending_requests'] ?? 0,
            'label' => 'Pending Requests',
        ],
        [
            'icon' => 'fa-calendar-week',
            'bg' => 'bg-info',
            'id' => 'weekly-hours',
            'value' => number_format($stats['weekly_hours'] ?? 0.0, 1),
            'label' => 'Weekly Hours',
        ],
    ];
@endphp
<div class="row g-4 mb-4">
    @foreach($dashboardCards as $card)
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-circle {{ $card['bg'] }}">
                        <i class="fas {{ $card['icon'] }} text-white"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 stat-number" id="{{ $card['id'] }}">{{ $card['value'] }}</h3>
                        <p class="text-muted mb-0 small stat-label">{{ $card['label'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>


<!-- Tab Content -->
<div class="tab-content" id="shiftTabContent">
  <!-- Shifts Tab -->
  <div class="tab-pane fade show active" id="shifts" role="tabpanel" aria-labelledby="shifts-tab">

<!-- Quick Actions -->
<div class="row mb-4">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-users me-2"></i>Employee Scheduling
        </h5>
      </div>
      <div class="card-body">
        <button class="btn btn-primary mb-2 me-2" onclick="openWorkingModal('create-shift-modal')">
          <i class="fas fa-plus me-2"></i>Add Shift
        </button>
        <button class="btn btn-outline-primary mb-2" onclick="scrollToCalendarSection()">
          <i class="fas fa-calendar-day me-2"></i>View Schedule
        </button>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-exchange-alt me-2"></i>Shift Request and Swap
        </h5>
      </div>
      <div class="card-body">
        <button class="btn btn-info mb-2 me-2" onclick="scrollToShiftRequests()">
          <i class="fas fa-eye me-2"></i>Review Requests
        </button>
        <button class="btn btn-success mb-2" onclick="scrollToShiftRequests()">
          <i class="fas fa-check me-2"></i>Approve/Reject
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Shift Types Configuration -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-list me-2"></i>Shift Types
    </h5>
    <button class="btn btn-primary" onclick="openWorkingModal('create-shift-type-modal')">
      <i class="fas fa-plus me-2"></i>Create Shift Type
    </button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="shift-types-table">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Break Duration</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="shift-types-tbody">
          @forelse($shiftTypes ?? collect() as $shiftType)
            <tr>
              <td>{{ $shiftType->name ?? $shiftType['name'] ?? 'N/A' }}</td>
              <td><span class="badge bg-primary">{{ $shiftType->code ?? $shiftType['code'] ?? 'N/A' }}</span></td>
              <td>
                @php
                  $startTime = $shiftType->start_time ?? $shiftType->default_start_time ?? $shiftType['start_time'] ?? $shiftType['default_start_time'] ?? null;
                @endphp
                {{ $startTime ? date('g:i A', strtotime($startTime)) : 'N/A' }}
              </td>
              <td>
                @php
                  $endTime = $shiftType->end_time ?? $shiftType->default_end_time ?? $shiftType['end_time'] ?? $shiftType['default_end_time'] ?? null;
                @endphp
                {{ $endTime ? date('g:i A', strtotime($endTime)) : 'N/A' }}
              </td>
              <td>{{ $shiftType->break_duration_minutes ?? $shiftType['break_duration_minutes'] ?? 60 }} min</td>
              <td>
                <span class="badge bg-{{ ($shiftType->is_active ?? $shiftType['is_active'] ?? 1) ? 'success' : 'danger' }}">
                  {{ ($shiftType->is_active ?? $shiftType['is_active'] ?? 1) ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                @php
                  $shiftTypeId = $shiftType->id ?? $shiftType['id'] ?? null;
                  $shiftTypeName = $shiftType->name ?? $shiftType['name'] ?? '';
                  $shiftTypeCode = $shiftType->code ?? $shiftType['code'] ?? '';
                @endphp
                @if($shiftTypeId)
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-info" onclick="viewShiftTypeDetails({{ $shiftTypeId }})" title="View">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="editShiftTypeForm({{ $shiftTypeId }}, '{{ addslashes($shiftTypeName) }}', '{{ addslashes($shiftTypeCode) }}', '{{ $startTime ?? '' }}', '{{ $endTime ?? '' }}', {{ $shiftType->duration_hours ?? $shiftType['duration_hours'] ?? 8 }}, {{ $shiftType->break_duration_minutes ?? $shiftType['break_duration_minutes'] ?? 60 }})" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('shift-types.destroy', $shiftTypeId) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this shift type?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
                <i class="fas fa-calendar-alt fa-3x mb-3 text-muted"></i><br>
                No shift types found. <a href="#" onclick="openWorkingModal('create-shift-type-modal')" class="text-primary">Create your first shift type</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Schedule Calendar -->
<div class="card mb-4" id="calendar-section">
  <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 0.75rem 1.25rem;">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <i class="fas fa-calendar" style="color: #007bff; font-size: 20px; margin-right: 8px;"></i>
        <span style="color: #007bff; font-weight: 600; font-size: 18px;">Schedule Calendar</span>
      </div>
      <div class="d-flex align-items-center" style="background-color: white; border-radius: 6px; padding: 4px; border: 1px solid #dee2e6;">
        <span class="fw-bold" style="color: #495057; font-size: 16px; margin: 0 12px;" id="calendar-month-year">
          {{ $displayMonth->format('F Y') }}
        </span>
        <a href="/shift-schedule-management?month={{ $displayMonth->copy()->subMonth()->format('Y-m') }}" class="btn btn-sm" title="Previous Month" style="border: none; background: none; color: #6c757d; padding: 4px 8px;">
          <i class="fas fa-chevron-left"></i>
        </a>
        <a href="/shift-schedule-management" class="btn btn-sm" title="Today" style="border: 1px solid #dee2e6; background: #f8f9fa; color: #495057; padding: 4px 12px; margin: 0 4px; border-radius: 4px; font-size: 12px; text-decoration: none;">
          Today
        </a>
        <a href="/shift-schedule-management?month={{ $displayMonth->copy()->addMonth()->format('Y-m') }}" class="btn btn-sm" title="Next Month" style="border: none; background: none; color: #6c757d; padding: 4px 8px;">
          <i class="fas fa-chevron-right"></i>
        </a>
      </div>
    </div>
  </div>
  <div class="card-body">
    <div id="schedule-calendar">
      <!-- Monthly Calendar View -->
      <div id="monthly-calendar" class="calendar-view">
        <!-- Day Headers -->
        <div class="row g-0">
          <div class="col text-center border-end border-bottom">
            <div class="day-header p-3 fw-bold" style="background-color: #34495e; color: white; font-size: 14px; letter-spacing: 1px;">
              MON
            </div>
          </div>
          <div class="col text-center border-end border-bottom">
            <div class="day-header p-3 fw-bold" style="background-color: #34495e; color: white; font-size: 14px; letter-spacing: 1px;">
              TUE
            </div>
          </div>
          <div class="col text-center border-end border-bottom">
            <div class="day-header p-3 fw-bold" style="background-color: #34495e; color: white; font-size: 14px; letter-spacing: 1px;">
              WED
            </div>
          </div>
          <div class="col text-center border-end border-bottom">
            <div class="day-header p-3 fw-bold" style="background-color: #34495e; color: white; font-size: 14px; letter-spacing: 1px;">
              THU
            </div>
          </div>
          <div class="col text-center border-end border-bottom">
            <div class="day-header p-3 fw-bold" style="background-color: #34495e; color: white; font-size: 14px; letter-spacing: 1px;">
              FRI
            </div>
          </div>
          <div class="col text-center border-end border-bottom">
            <div class="day-header p-3 fw-bold" style="background-color: #34495e; color: white; font-size: 14px; letter-spacing: 1px;">
              SAT
            </div>
          </div>
          <div class="col text-center border-bottom">
            <div class="day-header p-3 fw-bold" style="background-color: #34495e; color: white; font-size: 14px; letter-spacing: 1px;">
              SUN
            </div>
          </div>
        </div>
        
        <!-- Calendar Grid -->
        <div id="monthly-calendar-grid" class="border">
          @php
            // Use the display month from the header logic
            $calendarDate = $displayMonth->copy();
            $startOfMonth = $calendarDate->copy()->startOfMonth();
            $endOfMonth = $calendarDate->copy()->endOfMonth();
            $startOfCalendar = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            $endOfCalendar = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
            
            $calendarDays = [];
            $date = $startOfCalendar->copy();
            while ($date <= $endOfCalendar) {
                $calendarDays[] = $date->copy();
                $date->addDay();
            }
            
            $weeks = array_chunk($calendarDays, 7);
          @endphp
          
          @foreach($weeks as $week)
          <div class="row g-0">
            @foreach($week as $day)
            <div class="col calendar-cell" style="min-height: 120px; border: 1px solid #e0e0e0;">
              <div class="p-2">
                <!-- Date Number -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="fw-bold {{ $day->month !== $calendarDate->month ? 'text-muted' : ($day->isToday() ? 'text-primary' : 'text-dark') }}">
                    {{ $day->day }}
                  </span>
                  @if($day->isToday())
                    <span class="badge bg-primary">Today</span>
                  @endif
                </div>
                
                <!-- Shifts for this day -->
                @php
                  $dayShifts = isset($calendarShifts) && is_array($calendarShifts) ? ($calendarShifts[$day->format('Y-m-d')] ?? []) : [];
                @endphp
                
                @if(count($dayShifts) > 0)
                  @foreach($dayShifts as $shift)
                    <div class="shift-item mb-1 p-1 rounded position-relative" style="background-color: #e3f2fd; border-left: 3px solid #2196f3; font-size: 10px;" 
                         data-shift-id="{{ $shift['id'] ?? 0 }}">
                      <!-- Shift Info (clickable for details) -->
                      <div onclick="viewShiftDetails({{ $shift['employee_id'] ?? 0 }}, '{{ $day->format('Y-m-d') }}')" style="cursor: pointer;">
                        <div class="fw-bold text-primary">{{ $shift['employee_initials'] ?? 'UN' }}</div>
                        <div class="text-muted" style="font-size: 9px;">
                          {{ isset($shift['start_time']) ? date('H:i', strtotime($shift['start_time'])) : '' }}-{{ isset($shift['end_time']) ? date('H:i', strtotime($shift['end_time'])) : '' }}
                        </div>
                        <div class="text-muted" style="font-size: 8px;">{{ $shift['shift_type'] ?? 'Unknown' }}</div>
                      </div>
                      
                      <!-- Action Buttons -->
                      <div class="shift-actions position-absolute" style="top: 2px; right: 2px; display: none;">
                        @if(isset($shift['id']) && $shift['id'] > 0)
                        <button type="button" class="btn btn-sm btn-outline-primary" style="padding: 1px 4px; font-size: 8px; line-height: 1;" 
                                onclick="event.stopPropagation(); editShift({{ $shift['id'] }})" title="Edit Shift">
                          <i class="fas fa-edit"></i>
                        </button>
                        @else
                        <!-- Fallback for any remaining invalid shifts -->
                        <button type="button" class="btn btn-sm btn-outline-secondary" style="padding: 1px 4px; font-size: 8px; line-height: 1;" 
                                onclick="event.stopPropagation(); alert('Invalid shift data. Please refresh the page.')" title="Invalid Shift">
                          <i class="fas fa-exclamation-triangle"></i>
                        </button>
                        @endif
                        
                        <!-- Delete Form with Fade Effect -->
                        <form method="POST" action="{{ route('shifts.destroy', $shift['id'] ?? 0) }}" style="display: inline;" 
                              onsubmit="event.stopPropagation(); return handleShiftDelete(this, '{{ addslashes($shift['employee_name'] ?? 'Unknown') }}', '{{ $day->format('Y-m-d') }}')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger delete-shift-btn" style="padding: 1px 4px; font-size: 8px; line-height: 1; margin-left: 2px;" 
                                  title="Delete Shift">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      </div>
                    </div>
                    @endforeach
                @else
                  @if($day->month === $calendarDate->month)
                  <div class="text-center text-muted" style="font-size: 10px; margin-top: 20px; cursor: pointer;" 
                       onclick="openCreateShiftModal('{{ $day->format('Y-m-d') }}')">
                    <i class="fas fa-plus-circle"></i><br>Add Shift
                  </div>
                  @endif
                @endif
              </div>
            </div>
            @endforeach
          </div>
          @endforeach
        </div>

      </div>

    </div>
  </div>
</div>

<!-- Shift Requests -->
<div class="card mb-4" id="shift-requests-section">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-exchange-alt me-2"></i>Shift Requests
    </h5>
    <button class="btn btn-primary" onclick="openWorkingModal('new-shift-request-modal')">
      <i class="fas fa-plus me-2"></i>New Request
    </button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="shift-requests-table">
        <thead class="table-light">
          <tr>
            <th>Employee</th>
            <th>Shift Type</th>
            <th>Date</th>
            <th>Time</th>
            <th>Location</th>
            <th>Notes</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="shift-requests-tbody">
          @forelse($shiftRequests ?? collect() as $request)
            <tr>
              <td>
                {{ $request->employee->first_name ?? 'Unknown' }} {{ $request->employee->last_name ?? 'Employee' }}
              </td>
              <td>
                <span class="badge bg-primary">{{ $request->shiftType->name ?? 'Unknown Shift' }}</span>
              </td>
              <td>
                @if($request->shift_date)
                  @if(is_string($request->shift_date))
                    {{ \Carbon\Carbon::parse($request->shift_date)->format('M d, Y') }}
                  @else
                    {{ $request->shift_date->format('M d, Y') }}
                  @endif
                @else
                  N/A
                @endif
              </td>
              <td>
                @if($request->start_time && $request->end_time)
                  @if(is_string($request->start_time))
                    {{ \Carbon\Carbon::parse($request->start_time)->format('H:i') }} - 
                    {{ \Carbon\Carbon::parse($request->end_time)->format('H:i') }}
                  @else
                    {{ $request->start_time->format('H:i') }} - 
                    {{ $request->end_time->format('H:i') }}
                  @endif
                @else
                  N/A
                @endif
              </td>
              <td>{{ $request->location ?? 'Main Office' }}</td>
              <td>{{ $request->notes ?? 'No notes provided' }}</td>
              <td>
                @php
                  $badgeClass = $request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning');
                @endphp
                <span class="badge bg-{{ $badgeClass }}">
                  {{ ucfirst($request->status) }}
                </span>
              </td>
              <td>
                @if($request->status === 'pending')
                  <form method="POST" action="{{ route('shift-requests.approve', $request->id) }}" class="d-inline" onsubmit="handleApprovalSubmit(event, this)">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success me-1" title="Approve" onclick="return confirm('Approve this shift request? This will automatically add the shift to the schedule calendar.')">
                      <i class="fas fa-check"></i>
                    </button>
                  </form>
                  <form method="POST" action="{{ route('shift-requests.reject', $request->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Reject this shift request?')">
                      <i class="fas fa-times"></i>
                    </button>
                  </form>
                @else
                  <span class="text-muted small">
                    {{ $request->status === 'approved' ? 'Approved' : 'Rejected' }}
                    @if($request->approved_at)
                      on 
                      @if(is_string($request->approved_at))
                        {{ \Carbon\Carbon::parse($request->approved_at)->format('M d, Y') }}
                      @else
                        {{ $request->approved_at->format('M d, Y') }}
                      @endif
                    @endif
                  </span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                <i class="fas fa-exchange-alt fa-3x mb-3 text-muted"></i><br>
                No shift requests found. <a href="#" onclick="openWorkingModal('new-shift-request-modal')" class="text-primary">Create your first request</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

  </div>
  <!-- End Shifts Tab -->


</div>
<!-- End Tab Content -->

<!-- Create Shift Type Modal -->
<div class="working-modal" id="create-shift-type-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('create-shift-type-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Create Shift Type</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-shift-type-modal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('shift-types.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="mb-3">
                        <label for="shift-type-name" class="form-label">Shift Name</label>
                        <input type="text" class="form-control" id="shift-type-name" name="name" value="{{ old('name', session('edit_shift_type.name')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="shift-type-code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="shift-type-code" name="code" value="{{ old('code', session('edit_shift_type.code')) }}" maxlength="10" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-type-start-time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="shift-type-start-time" name="start_time" value="{{ old('start_time', session('edit_shift_type.start_time')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-type-end-time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="shift-type-end-time" name="end_time" value="{{ old('end_time', session('edit_shift_type.end_time')) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-type-duration" class="form-label">Duration (hours)</label>
                                <input type="number" class="form-control" id="shift-type-duration" name="duration_hours" value="{{ old('duration_hours', session('edit_shift_type.duration_hours', 8)) }}" min="1" max="24" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift-type-break" class="form-label">Break Duration (minutes)</label>
                                <input type="number" class="form-control" id="shift-type-break" name="break_duration_minutes" value="{{ old('break_duration_minutes', session('edit_shift_type.break_duration_minutes', 60)) }}" min="0" max="480" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="shift-type-description" class="form-label">Description</label>
                        <textarea class="form-control" id="shift-type-description" name="description" rows="3" placeholder="Optional description">{{ old('description', session('edit_shift_type.description')) }}</textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-shift-type-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Shift Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Shift Assignment Modal -->
<div class="working-modal" id="create-shift-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('create-shift-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Assign Employee to Shift</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('create-shift-modal')">&times;</button>
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
                        <!-- Debug info -->
                        @if(config('app.debug'))
                            <small class="text-muted">
                                Debug: {{ isset($employees) ? $employees->count() : 'No employees variable' }} employees available
                            </small>
                        @endif
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
                            @if(isset($shiftTypes))
                                @foreach($shiftTypes as $shiftType)
                                    <option value="{{ $shiftType->id ?? $shiftType['id'] }}" 
                                            data-start-time="{{ $shiftType->default_start_time ?? $shiftType['default_start_time'] ?? '08:00' }}" 
                                            data-end-time="{{ $shiftType->default_end_time ?? $shiftType['default_end_time'] ?? '16:00' }}"
                                            {{ old('shift_type_id') == ($shiftType->id ?? $shiftType['id']) ? 'selected' : '' }}>
                                        {{ $shiftType->name ?? $shiftType['name'] }}
                                    </option>
                                @endforeach
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
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('create-shift-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Shift Request Modal -->
<div class="working-modal" id="new-shift-request-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('new-shift-request-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">New Shift Request</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('new-shift-request-modal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('shift-requests.store') }}">
                @csrf
                <div class="working-modal-body">
                    <!-- DEBUG INFO -->
                    @php
                        $debugEmployees = collect();
                        try {
                            $debugEmployees = \App\Models\Employee::select('id', 'first_name', 'last_name')->get();
                        } catch (\Exception $e) {
                            // Fallback to direct DB query
                            try {
                                $debugEmployees = \DB::table('employees')->select('id', 'first_name', 'last_name')->get();
                            } catch (\Exception $e2) {
                                $debugEmployees = collect();
                            }
                        }
                    @endphp
                    
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select class="form-select" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @if($debugEmployees->count() > 0)
                                @foreach($debugEmployees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                @endforeach
                            @else
                                <option value="" disabled>No employees found in database</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="shift_type_id" class="form-label">Shift Type</label>
                        <select class="form-select" name="shift_type_id" required>
                            <option value="">Select Shift Type</option>
                            @php
                                $debugShiftTypes = collect();
                                try {
                                    $debugShiftTypes = \App\Models\ShiftType::select('id', 'name')->where('is_active', true)->get();
                                } catch (\Exception $e) {
                                    try {
                                        $debugShiftTypes = \DB::table('shift_types')->select('id', 'name')->where('is_active', 1)->get();
                                    } catch (\Exception $e2) {
                                        $debugShiftTypes = collect();
                                    }
                                }
                            @endphp
                            @if($debugShiftTypes->count() > 0)
                                @foreach($debugShiftTypes as $shiftType)
                                    <option value="{{ $shiftType->id }}">{{ $shiftType->name }}</option>
                                @endforeach
                            @else
                                <option value="" disabled>No shift types found in database</option>
                            @endif
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift_date" class="form-label">Shift Date</label>
                                <input type="date" class="form-control" name="shift_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hours" class="form-label">Hours</label>
                                <input type="number" class="form-control" name="hours" min="1" max="24" step="0.5" value="8" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" name="start_time" value="08:00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" name="end_time" value="16:00" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" value="Main Office" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes for this shift request..."></textarea>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('new-shift-request-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');


// Initialize shift management with server-side data
function initializeShiftManagement() {
  console.log('Initializing shift management with server data');
  
  // Use server-provided data instead of AJAX calls
  const shiftTypesData = @json($shiftTypes ?? collect());
  const shiftRequestsData = @json($shiftRequests ?? collect());
  
  console.log('Shift Types from server:', shiftTypesData.length);
  console.log('Shift Requests from server:', shiftRequestsData.length);
  
  // Initialize calendar with server data
  setupCalendarNavigation();
}

// Bulk request management functions
function toggleAllRequests() {
  const selectAll = document.getElementById('select-all-requests');
  const checkboxes = document.querySelectorAll('.request-checkbox');
  checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function bulkApproveRequests() {
  const selectedIds = getSelectedRequestIds();
  if (selectedIds.length === 0) {
    alert('Please select at least one request to approve.');
    return;
  }
  
  if (confirm(`Approve ${selectedIds.length} selected request(s)?`)) {
    submitBulkAction(selectedIds, 'approve');
  }
}

function bulkRejectRequests() {
  const selectedIds = getSelectedRequestIds();
  if (selectedIds.length === 0) {
    alert('Please select at least one request to reject.');
    return;
  }
  
  if (confirm(`Reject ${selectedIds.length} selected request(s)?`)) {
    submitBulkAction(selectedIds, 'reject');
  }
}

function getSelectedRequestIds() {
  const checkboxes = document.querySelectorAll('.request-checkbox:checked');
  return Array.from(checkboxes).map(cb => cb.value);
}

function submitBulkAction(requestIds, action) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `/shift/requests/bulk-${action}`;
  
  let formHTML = `<input type="hidden" name="_token" value="${csrfToken}">`;
  requestIds.forEach(id => {
    formHTML += `<input type="hidden" name="request_ids[]" value="${id}">`;
  });
  
  form.innerHTML = formHTML;
  document.body.appendChild(form);
  form.submit();
}

// Setup calendar navigation
function setupCalendarNavigation() {
  // Month navigation buttons
  const prevBtn = document.querySelector('[onclick="changeMonth(-1)"]');
  const nextBtn = document.querySelector('[onclick="changeMonth(1)"]');
  const todayBtn = document.querySelector('[onclick="goToCurrentMonth()"]');
  
  if (prevBtn) prevBtn.addEventListener('click', () => changeMonth(-1));
  if (nextBtn) nextBtn.addEventListener('click', () => changeMonth(1));
  if (todayBtn) todayBtn.addEventListener('click', () => goToCurrentMonth());
}

// Calendar navigation now uses HTML links - no JavaScript needed

// Working hours configuration - removed blocking functionality
// Templates saving - removed blocking functionality

// All blocking AJAX functions removed - using server-side form submission

// Legacy functions - now handled by new modal system
// These are kept for compatibility but functionality moved to new system

// View shift details
function viewShiftDetails(employeeId, date) {
  // Find shift data for the specific employee and date
  @if(isset($calendarShifts))
    const dayShifts = @json($calendarShifts);
    const dateShifts = dayShifts[date] || [];
    const shift = dateShifts.find(s => s.employee_id == employeeId);
    
    if (shift) {
      document.getElementById('shift-employee-name').textContent = shift.first_name + ' ' + shift.last_name;
      document.getElementById('shift-type-name').textContent = shift.shift_type_name || 'N/A';
      document.getElementById('shift-date').textContent = new Date(date).toLocaleDateString();
      document.getElementById('shift-time-range').textContent = 
        (shift.start_time ? shift.start_time.substring(0,5) : '00:00') + ' - ' + 
        (shift.end_time ? shift.end_time.substring(0,5) : '00:00');
      document.getElementById('shift-status').textContent = shift.status || 'scheduled';
      document.getElementById('shift-notes-content').textContent = shift.notes || 'No notes available';
      
      // new bootstrap.Modal(document.getElementById('shiftDetailsModal')).show();
    }
  @endif
}

// Auto-fill times when shift type is selected
document.addEventListener('change', function(e) {
  if (e.target && e.target.id === 'shift-type-select') {
    const selectedOption = e.target.selectedOptions[0];
    if (selectedOption && selectedOption.dataset.startTime) {
      document.getElementById('shift-start-time').value = selectedOption.dataset.startTime;
      document.getElementById('shift-end-time').value = selectedOption.dataset.endTime;
    }
  }
});

// REMOVED - Duplicate modal functions, using main working modal system

// REMOVED - Global function exports, using working modal system

// REMOVED - Duplicate function with incorrect field IDs

// View shift request details
function viewShiftRequest(id) {
  alert(`Viewing shift request #${id}`);
}

// Bulk actions for shift requests
function reviewShiftRequests() {
  alert('Review shift requests functionality');
}

function bulkApproveReject() {
  alert('Bulk approve/reject functionality');
}



// Helper functions
function getShiftTypeColor(type) {
  switch (type) {
    case 'day': return 'primary';
    case 'night': return 'dark';
    case 'split': return 'warning';
    default: return 'secondary';
  }
}

function getRequestStatusColor(status) {
  switch (status) {
    case 'approved': return 'success';
    case 'pending': return 'warning';
    case 'rejected': return 'danger';
    default: return 'secondary';
  }
}

function renderScheduleCalendar(scheduleData, view) {
  const calendar = document.getElementById('schedule-calendar');
  if (!Array.isArray(scheduleData) || scheduleData.length === 0) {
    calendar.innerHTML = '<div class="text-center p-4"><p class="text-muted">No schedules found for this period.</p></div>';
    return;
  }

  let html = '';
  if (view === 'week') {
    html += '<table class="table table-bordered"><thead><tr>';
    // Get unique days in the week
    const days = [...new Set(scheduleData.map(item => item.shift_date))];
    days.forEach(day => {
      html += `<th>${day}</th>`;
    });
    html += '</tr></thead><tbody><tr>';
    days.forEach(day => {
      const dayShifts = scheduleData.filter(item => item.shift_date === day);
      html += '<td>';
      dayShifts.forEach(shift => {
        html += `<div class="mb-2 p-2 border rounded">
          <strong>${shift.shift_name}</strong><br>
          <small>${shift.start_time} - ${shift.end_time}</small><br>
          <span class="badge bg-info">${shift.employee_name}</span>
        </div>`;
      });
      html += '</td>';
    });
    html += '</tr></tbody></table>';
  } else {
    // Month view: group by date
    html += '<div class="row">';
    const days = [...new Set(scheduleData.map(item => item.shift_date))];
    days.forEach(day => {
      html += '<div class="col-md-3 mb-3">';
      html += `<div class="border p-2 rounded"><strong>${day}</strong><br>`;
      const dayShifts = scheduleData.filter(item => item.shift_date === day);
      dayShifts.forEach(shift => {
        html += `<div class="mb-1">
          <span class="badge bg-primary">${shift.shift_name}</span>
          <small>${shift.start_time} - ${shift.end_time}</small><br>
          <span class="badge bg-info">${shift.employee_name}</span>
        </div>`;
      });
      html += '</div></div>';
    });
    html += '</div>';
  }
  calendar.innerHTML = html;
}




// Calendar initialization - using server-side navigation
function initializeCalendar() {
  console.log('Calendar initialized with server-side data');
}

let currentWeekStart = new Date();
let currentMonth = new Date();

function updatePeriodDisplay() {
  const periodDisplay = document.getElementById('current-period-display');
  const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'];
  periodDisplay.textContent = `${monthNames[currentMonth.getMonth()]} ${currentMonth.getFullYear()}`;
}


// Calendar grid generation - using server-side rendering
// Monthly schedule loading - using server-side data
  }
}

function getWeekStart(date) {
  const d = new Date(date);
  const day = d.getDay();
  const diff = d.getDate() - day;
  return new Date(d.setDate(diff));
}


function navigateMonth(direction) {
  currentMonth.setMonth(currentMonth.getMonth() + direction);
  loadMonthlySchedule();
  updatePeriodDisplay();
}

// Add mouse wheel scrolling for months
document.addEventListener('DOMContentLoaded', function() {
  const calendarContainer = document.getElementById('monthly-calendar');
  if (calendarContainer) {
    calendarContainer.addEventListener('wheel', function(e) {
      e.preventDefault();
      if (e.deltaY > 0) {
        // Scroll down - next month
        navigateMonth(1);
      } else {
        // Scroll up - previous month
        navigateMonth(-1);
      }
    });
  }
});

function loadShiftsForWeek(weekStart) {
  const weekEnd = new Date(weekStart);
  weekEnd.setDate(weekEnd.getDate() + 6);
  
  // Use server-side data for weekly shifts
  displayWeeklyShifts(@json($shifts ?? []));
}

function displayWeeklyShifts(shifts) {
  // Clear existing shifts
  const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
  days.forEach(day => {
    document.getElementById(`${day}-shifts`).innerHTML = '';
  });
  
  // Group shifts by day
  const shiftsByDay = {};
  shifts.forEach(shift => {
    const shiftDate = new Date(shift.date);
    const dayName = days[shiftDate.getDay()];
    
    if (!shiftsByDay[dayName]) {
      shiftsByDay[dayName] = [];
    }
    shiftsByDay[dayName].push(shift);
  });
  
  // Display shifts for each day
  Object.keys(shiftsByDay).forEach(day => {
    const dayContainer = document.getElementById(`${day}-shifts`);
    shiftsByDay[day].forEach(shift => {
      const shiftElement = document.createElement('div');
      shiftElement.className = 'shift-item border rounded p-2 mb-2 cursor-pointer';
      shiftElement.style.backgroundColor = shift.color_code + '20';
      shiftElement.style.borderColor = shift.color_code;
      
      const startTime = formatTime(shift.start_time);
      const endTime = formatTime(shift.end_time);
      
      shiftElement.innerHTML = `
        <div class="fw-bold" style="color: ${shift.color_code}">${shift.first_name} ${shift.last_name}</div>
        <div class="small text-muted">${startTime} - ${endTime}</div>
        <div class="small">${shift.shift_type_name}</div>
        ${shift.notes ? `<div class="small text-info">${shift.notes}</div>` : ''}
      `;
      
      shiftElement.addEventListener('click', () => showShiftDetails(shift));
      dayContainer.appendChild(shiftElement);
    });
  });
}

function loadMonthlyShifts() {
  const year = currentMonth.getFullYear();
  const month = currentMonth.getMonth();
  
  // Use server-side data for monthly shifts
  displayMonthlyShifts(@json($shifts ?? []));
}

function displayMonthlyShifts(shifts) {
  // Use calendar shifts data from server if available
  const calendarShifts = @json($calendarShifts ?? collect());
  
  // Clear existing shift indicators
  document.querySelectorAll('.shift-indicators').forEach(container => {
    container.innerHTML = '';
  });
  
  // Process calendar shifts data
  Object.keys(calendarShifts).forEach(date => {
    const dayShifts = calendarShifts[date];
    
    // Find calendar cell for this date
    const calendarCells = document.querySelectorAll('#monthly-calendar-grid .calendar-cell');
    calendarCells.forEach(cell => {
      const cellOnclick = cell.getAttribute('onclick');
      if (cellOnclick && cellOnclick.includes(date)) {
        const shiftContainer = cell.querySelector('.shift-indicators');
        if (shiftContainer) {
          dayShifts.forEach(shift => {
            const shiftIndicator = document.createElement('div');
            shiftIndicator.className = 'shift-indicator mb-1';
            shiftIndicator.style.cssText = `
              background-color: ${shift.color_code || '#007bff'};
              color: white;
              font-size: 10px;
              padding: 2px 6px;
              margin: 1px;
              border-radius: 10px;
              cursor: pointer;
              display: inline-block;
              font-weight: 600;
              letter-spacing: 0.3px;
              transition: all 0.2s ease;
            `;
            
            // Show employee initials
            const initials = `${shift.first_name?.charAt(0) || ''}${shift.last_name?.charAt(0) || ''}`;
            shiftIndicator.textContent = initials;
            shiftIndicator.title = `${shift.first_name} ${shift.last_name} - ${shift.shift_type_name} (${formatTime(shift.start_time)} - ${formatTime(shift.end_time)})`;
            
            // Add hover effects
            shiftIndicator.addEventListener('mouseenter', function() {
              this.style.transform = 'scale(1.1)';
              this.style.boxShadow = '0 3px 6px rgba(0,0,0,0.3)';
              this.style.zIndex = '10';
            });
            
            shiftIndicator.addEventListener('mouseleave', function() {
              this.style.transform = 'scale(1)';
              this.style.boxShadow = 'none';
              this.style.zIndex = 'auto';
            });
            
            // Add click handler to show shift details
            shiftIndicator.addEventListener('click', (e) => {
              e.stopPropagation();
              viewShiftDetails(shift.employee_id, date);
            });
            
            shiftContainer.appendChild(shiftIndicator);
          });
        }
      }
    });
  });
}

function viewShiftDetails(employeeId, date) {
  // Find shift details from calendar data
  const calendarShifts = @json($calendarShifts ?? collect());
  const dayShifts = calendarShifts[date] || [];
  const shift = dayShifts.find(s => s.employee_id == employeeId);
  
  if (shift) {
    // Populate shift details modal
    document.getElementById('shift-employee-name').textContent = `${shift.first_name} ${shift.last_name}`;
    document.getElementById('shift-type-name').textContent = shift.shift_type_name;
    document.getElementById('shift-date').textContent = new Date(shift.date).toLocaleDateString();
    document.getElementById('shift-time-range').textContent = `${formatTime(shift.start_time)} - ${formatTime(shift.end_time)}`;
    document.getElementById('shift-status').innerHTML = `<span class="badge bg-${getStatusColor(shift.status)}">${ucfirst(shift.status)}</span>`;
    document.getElementById('shift-notes-content').textContent = shift.notes || 'No notes available';
    
    // new bootstrap.Modal(document.getElementById('shiftDetailsModal')).show();
  }
}

function getStatusColor(status) {
  switch(status) {
    case 'completed': return 'success';
    case 'in_progress': return 'warning';
    case 'cancelled': return 'danger';
    default: return 'primary';
  }
}

function ucfirst(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatTime(timeString) {
  if (!timeString) return '';
  const [hours, minutes] = timeString.split(':');
  const hour = parseInt(hours);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const displayHour = hour % 12 || 12;
  return `${displayHour}:${minutes} ${ampm}`;
}

// Modal functions - cleaned up to avoid conflicts

// Emergency cleanup function
function emergencyCloseAllModals() {
    // Force close all modals
    const allModals = document.querySelectorAll('.modal, [id*="modal"]');
    allModals.forEach(modal => {
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.classList.remove('show');
    });
    
    // Reset body state
    document.body.style.overflow = 'auto';
    document.body.style.paddingRight = '0';
    document.body.classList.remove('modal-open');
    
    // Remove any modal backdrops
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.remove();
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  // Force close all modals first
  emergencyCloseAllModals();
  
  // Initialize shift management with server data
  initializeShiftManagement();
  initializeCalendar();
  
  // Handle employee pre-selection if coming from employees page
  @if(isset($selectedEmployee) && $selectedEmployee && isset($action) && $action === 'assign')
    setTimeout(function() {
      // Pre-select employee and open modal
  const urlParams = new URLSearchParams(window.location.search);
  const selectedEmployee = urlParams.get('employee');
  const selectedEmployeeName = urlParams.get('name');
  const action = urlParams.get('action');
  
  if (selectedEmployee && action === 'assign') {
    // Pre-select employee in shift assignment modal
    setTimeout(() => {
      const employeeSelect = document.getElementById('shift-employee');
      if (employeeSelect) {
        employeeSelect.value = selectedEmployee;
      }
      
      // Show notification about pre-selected employee
      if (selectedEmployeeName) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-info alert-dismissible fade show';
        alertDiv.innerHTML = `
          <strong>Employee Pre-selected:</strong> ${decodeURIComponent(selectedEmployeeName)} is ready for shift assignment.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('.container-fluid');
        if (container) {
          container.insertAdjacentElement('afterbegin', alertDiv);
        }
      }
      
      // Auto-open shift assignment modal - DISABLED to prevent unwanted modal
      // new bootstrap.Modal(document.getElementById('createShiftModal')).show();
    }, 500);
  }
  
  // Debug: Log data availability
  console.log('Page loaded with data:', {
    shiftTypes: @json(isset($shiftTypes) ? count($shiftTypes) : 0),
    shiftRequests: @json(isset($shiftRequests) ? count($shiftRequests) : 0),
    employees: @json(isset($employeesList) ? count($employeesList) : 0)
  });
});
@endif

function closeModalProperly(modalId) {
  const modal = document.getElementById(modalId);
  const modalInstance = bootstrap.Modal.getInstance(modal);
  
  if (modalInstance) {
    modalInstance.hide();
  }
  
  // Force cleanup of modal backdrop and body classes
  setTimeout(() => {
    cleanupModalBackdrops();
  }, 300);
}

// Clean up modal backdrops and body classes
function cleanupModalBackdrops() {
  const backdrops = document.querySelectorAll('.modal-backdrop');
  backdrops.forEach(backdrop => backdrop.remove());
  document.body.classList.remove('modal-open');
  document.body.style.overflow = '';
  document.body.style.paddingRight = '';
}

// REMOVED - Duplicate closeModal function

// Initialize shift dropdowns for request form
function initializeShiftDropdowns() {
  // Use server-side data for shift dropdowns
  const currentShiftSelect = document.getElementById('current-shift');
  const requestedShiftSelect = document.getElementById('requested-shift');
  
  // Clear existing options
  currentShiftSelect.innerHTML = '<option value="">Select Current Shift</option>';
  requestedShiftSelect.innerHTML = '<option value="">Select Requested Shift</option>';
  
  // Add shift type options from server-side data
  @if(isset($shiftTypes) && count($shiftTypes) > 0)
    @foreach($shiftTypes as $shiftType)
      const option1_{{ $shiftType['id'] }} = new Option('{{ $shiftType['name'] }}', '{{ $shiftType['id'] }}');
      const option2_{{ $shiftType['id'] }} = new Option('{{ $shiftType['name'] }}', '{{ $shiftType['id'] }}');
      currentShiftSelect.add(option1_{{ $shiftType['id'] }});
      requestedShiftSelect.add(option2_{{ $shiftType['id'] }});
    @endforeach
  @endif
}

<!-- REMOVED - Duplicate modal moved to proper location above -->

<!-- Removed duplicate script block - functions now defined in main script section -->

// CONSOLIDATED SCRIPT BLOCK - Removed redundant script tags

// REMOVED - Session edit handling moved to main script block

// REMOVED - Session edit handling moved to main script block

// Open create shift modal with date - using global function
function openCreateShiftModal(date) {
    document.getElementById('shift-assignment-date').value = date;
    openWorkingModal('create-shift-modal');
}

// Old edit shift function removed - replaced with working version below

// Handle shift deletion with fade effect
function handleShiftDelete(form, employeeName, shiftDate) {
    // Check if already being deleted
    const deleteButton = form.querySelector('.delete-shift-btn');
    if (deleteButton && deleteButton.classList.contains('btn-deleting')) {
        return false; // Already being deleted
    }
    
    if (confirm(`Are you sure you want to delete the shift for ${employeeName} on ${shiftDate}?\n\nThis action cannot be undone.`)) {
        // Get the shift item container
        const shiftItem = form.closest('.shift-item');
        
        if (shiftItem && deleteButton) {
            // Disable all action buttons to prevent multiple clicks
            const allButtons = shiftItem.querySelectorAll('button');
            allButtons.forEach(btn => btn.disabled = true);
            
            // Add deleting class for visual feedback
            shiftItem.classList.add('deleting');
            deleteButton.classList.add('btn-deleting');
            
            // Change button icon to spinner
            const icon = deleteButton.querySelector('.fas');
            if (icon) {
                icon.className = 'fas fa-spinner fa-spin';
            }
            
            // Add fade out effect after a short delay
            setTimeout(() => {
                shiftItem.classList.add('fade-out');
            }, 300);
            
            // Submit the form after fade animation starts
            setTimeout(() => {
                // Add a hidden input to track deletion attempt
                const trackingInput = document.createElement('input');
                trackingInput.type = 'hidden';
                trackingInput.name = 'deletion_timestamp';
                trackingInput.value = Date.now();
                form.appendChild(trackingInput);
                
                form.submit();
            }, 500);
            
            return false; // Prevent immediate form submission
        }
        
        return true; // Allow form submission if elements not found
    }
    
    return false; // Cancel submission if user cancels
}

// Delete shift function with enhanced debugging
function deleteShift(shiftId, employeeName, shiftDate) {
    console.log('Delete shift called with:', { shiftId, employeeName, shiftDate });
    
    if (!shiftId || shiftId === 0) {
        alert('Error: Invalid shift ID');
        return;
    }
    
    if (confirm(`Are you sure you want to delete the shift for ${employeeName} on ${shiftDate}?\n\nThis action cannot be undone.`)) {
        console.log('User confirmed deletion');
        
        // Show loading state
        const button = event.target.closest('button');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
        
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/shifts/${shiftId}`;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        console.log('CSRF token found:', !!csrfToken);
        
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
            console.log('CSRF token added to form');
        } else {
            console.error('CSRF token not found!');
            alert('Error: Security token not found. Please refresh the page and try again.');
            return;
        }
        
        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        console.log('Form created, submitting...');
        document.body.appendChild(form);
        
        // Add error handling
        form.addEventListener('submit', function(e) {
            console.log('Form submission started');
        });
        
        try {
            form.submit();
        } catch (error) {
            console.error('Form submission error:', error);
            alert('Error submitting form: ' + error.message);
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-trash"></i>';
            }
        }
    } else {
        console.log('User cancelled deletion');
    }
}

// Handle calendar cell clicks
function handleCalendarClick(element) {
    const date = element.getAttribute('data-date');
    if (date) {
        openCreateShiftModal(date);
    }
}

// Test delete functionality
function testDeleteFunctionality() {
    console.log('=== TESTING DELETE FUNCTIONALITY ===');
    
    // Check CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    console.log('CSRF Token Element:', csrfToken);
    console.log('CSRF Token Value:', csrfToken ? csrfToken.getAttribute('content') : 'NOT FOUND');
    
    // Check for shift elements
    const shiftElements = document.querySelectorAll('[data-shift-id]');
    console.log('Found shift elements:', shiftElements.length);
    
    shiftElements.forEach((element, index) => {
        const shiftId = element.getAttribute('data-shift-id');
        const actionButtons = element.querySelector('.shift-actions');
        console.log(`Shift ${index + 1}: ID=${shiftId}, Has Actions=${!!actionButtons}`);
        
        if (actionButtons) {
            const deleteButton = actionButtons.querySelector('button[title="Delete Shift"]');
            const deleteForm = actionButtons.querySelector('form');
            console.log(`  Delete Button: ${!!deleteButton}, Delete Form: ${!!deleteForm}`);
            
            if (deleteForm) {
                console.log(`  Form Action: ${deleteForm.action}`);
                console.log(`  Form Method: ${deleteForm.method}`);
            }
        }
    });
    
    // Test route generation
    if (shiftElements.length > 0) {
        const firstShiftId = shiftElements[0].getAttribute('data-shift-id');
        console.log(`Test delete URL would be: /shifts/${firstShiftId}`);
    }
    
    alert(`Debug info logged to console. Found ${shiftElements.length} shifts. Check browser console for details.`);
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        emergencyCloseAllModals();
    }
});

// Calendar navigation functions
@php
    $requestedMonth = request('month');
    if ($requestedMonth) {
        if (strlen($requestedMonth) > 7) {
            $requestedMonth = substr($requestedMonth, 0, 7);
        }
        try {
            $currentDisplayMonth = \Carbon\Carbon::createFromFormat('Y-m', $requestedMonth);
        } catch (\Exception $e) {
            $currentDisplayMonth = \Carbon\Carbon::now();
        }
    } else {
        $currentDisplayMonth = \Carbon\Carbon::now();
    }
@endphp

// Removed duplicate changeMonth and goToCurrentMonth functions
// Using the URL-based implementation above

function loadCalendarData() {
    const year = currentCalendarDate.getFullYear();
    const month = currentCalendarDate.getMonth() + 1;
    
    // Reload page with new month data
    window.location.href = `/shift-schedule-management?month=${year}-${month.toString().padStart(2, '0')}`;
}

// View shift details function
function viewShiftDetails(employeeId, date) {
    // You can implement a modal or redirect to show shift details
    console.log('Viewing shift details for employee:', employeeId, 'on date:', date);
    
    // Example: Open a modal with shift details
    // This would require additional modal implementation
    alert(`Viewing shift details for Employee ID: ${employeeId} on ${date}`);
}

// Script cleanup completed
</script>


<!-- CONSOLIDATED SCRIPTS - All functionality moved to main script block -->
@push('scripts')
<script>
// Main consolidated script for shift schedule management
document.addEventListener('DOMContentLoaded', function() {
    console.log('Shift schedule management initialized');
    
    // Initialize working modal system
    initializeShiftManagement();
    
    // Handle session-based edit data if present
    @if(session('edit_shift_type'))
        const editShiftTypeData = @json(session('edit_shift_type'));
        if (editShiftTypeData) {
            openWorkingModal('create-shift-type-modal');
        }
    @endif
    
    // Auto-fill shift times when shift type is selected
    const shiftTypeSelects = document.querySelectorAll('[id*="shift-type"]');
    shiftTypeSelects.forEach(select => {
        select.addEventListener('change', function() {
            const selectedOption = this.selectedOptions[0];
            if (selectedOption && selectedOption.dataset.startTime) {
                const modalId = this.closest('.working-modal').id;
                const startTimeField = document.querySelector(`#${modalId} [name="start_time"]`);
                const endTimeField = document.querySelector(`#${modalId} [name="end_time"]`);
                if (startTimeField) startTimeField.value = selectedOption.dataset.startTime;
                if (endTimeField) endTimeField.value = selectedOption.dataset.endTime;
            }
        });
    });
});

// View shift type details in modal - REMOVED (replaced with modal version below)

// REMOVED - All duplicate Bootstrap modal functions

// Shift request functions - using server-side forms instead of JavaScript
// All shift request actions are handled by existing server-side forms in the template

// View Details Functions - Production Version (Following Consistent Pattern)
function viewShiftTypeDetails(shiftTypeId) {
    try {
        const button = document.querySelector(`button[onclick*="viewShiftTypeDetails(${shiftTypeId}"]`);
        if (!button) return;
        
        const row = button.closest('tr');
        if (!row || row.cells.length < 6) return;
        
        // Extract data from table row
        document.getElementById('view-shift-type-name').textContent = row.cells[0].textContent.trim();
        document.getElementById('view-shift-type-code').textContent = row.cells[1].querySelector('.badge')?.textContent.trim() || '-';
        document.getElementById('view-shift-type-start').textContent = row.cells[2].textContent.trim();
        document.getElementById('view-shift-type-end').textContent = row.cells[3].textContent.trim();
        document.getElementById('view-shift-type-break').textContent = row.cells[4].textContent.trim();
        document.getElementById('view-shift-type-duration').textContent = '8 hours'; // Default duration
        
        openWorkingModal('view-shift-type-modal');
    } catch (error) {
        console.error('Error viewing shift type details:', error);
    }
}

function viewShiftAssignmentDetails(shiftId) {
    const shiftRow = document.querySelector(`button[onclick*="viewShiftAssignmentDetails(${shiftId})"]`)?.closest('tr');
    
    if (shiftRow && shiftRow.cells.length >= 6) {
        document.getElementById('view-shift-assignment-employee').textContent = shiftRow.cells[0].textContent.trim();
        document.getElementById('view-shift-assignment-date').textContent = shiftRow.cells[1].textContent.trim();
        document.getElementById('view-shift-assignment-type').textContent = shiftRow.cells[2].textContent.trim();
        document.getElementById('view-shift-assignment-time').textContent = shiftRow.cells[3].textContent.trim();
        document.getElementById('view-shift-assignment-location').textContent = shiftRow.cells[4].textContent.trim();
        document.getElementById('view-shift-assignment-status').textContent = shiftRow.cells[5].querySelector('.badge')?.textContent.trim() || 'Unknown';
        openWorkingModal('view-shift-assignment-modal');
    }
}

function viewShiftRequestDetails(requestId) {
    const requestRow = document.querySelector(`button[onclick*="viewShiftRequestDetails(${requestId})"]`)?.closest('tr');
    
    if (requestRow && requestRow.cells.length >= 7) {
        document.getElementById('view-shift-request-employee').textContent = requestRow.cells[0].textContent.trim();
        document.getElementById('view-shift-request-type').textContent = requestRow.cells[1].textContent.trim();
        document.getElementById('view-shift-request-date').textContent = requestRow.cells[2].textContent.trim();
        document.getElementById('view-shift-request-time').textContent = requestRow.cells[3].textContent.trim();
        document.getElementById('view-shift-request-reason').textContent = requestRow.cells[4].textContent.trim();
        document.getElementById('view-shift-request-status').textContent = requestRow.cells[5].querySelector('.badge')?.textContent.trim() || 'Unknown';
        document.getElementById('view-shift-request-submitted').textContent = requestRow.cells[6].textContent.trim();
        openWorkingModal('view-shift-request-modal');
    }
}

// Edit Shift Function for Calendar
function editShift(shiftId) {
    console.log('editShift called with ID:', shiftId, 'Type:', typeof shiftId);
    
    // Validate shift ID
    if (!shiftId || shiftId === 0 || shiftId === '0' || isNaN(shiftId)) {
        console.error('Invalid shift ID provided:', shiftId);
        alert('Invalid shift ID: ' + shiftId + '. Please refresh the page and try again.');
        return;
    }

    console.log('Fetching shift data for valid ID:', shiftId);

    // Fetch shift data from server using the correct endpoint
    fetch(`/shifts/${shiftId}/edit`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            if (data.success && data.shift) {
                populateEditShiftModal(data.shift);
                openWorkingModal('edit-shift-modal');
            } else {
                alert('Failed to load shift data: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error fetching shift data:', error);
            alert('Error loading shift data: ' + error.message + '. Please try again.');
        });
}

// Populate Edit Shift Modal with data
function populateEditShiftModal(shift) {
    document.getElementById('edit-shift-id').value = shift.id;
    document.getElementById('edit-shift-date').value = shift.shift_date;
    document.getElementById('edit-shift-start-time').value = shift.start_time;
    document.getElementById('edit-shift-end-time').value = shift.end_time;
    document.getElementById('edit-shift-status').value = shift.status;
    document.getElementById('edit-shift-location').value = shift.location || '';
    document.getElementById('edit-shift-notes').value = shift.notes || '';
    
    // Update modal title
    document.getElementById('edit-shift-modal-title').textContent = `Edit Shift - ${shift.employee_name || 'Unknown Employee'}`;
}

// Save Edit Shift
function saveEditShift() {
    const form = document.getElementById('edit-shift-form');
    const formData = new FormData(form);
    const shiftId = document.getElementById('edit-shift-id').value;

    console.log('Saving shift with ID:', shiftId);
    console.log('Form data:', {
        shift_date: formData.get('shift_date'),
        start_time: formData.get('start_time'),
        end_time: formData.get('end_time'),
        status: formData.get('status'),
        location: formData.get('location'),
        notes: formData.get('notes')
    });

    fetch(`/shifts/${shiftId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            shift_date: formData.get('shift_date'),
            start_time: formData.get('start_time'),
            end_time: formData.get('end_time'),
            status: formData.get('status'),
            location: formData.get('location'),
            notes: formData.get('notes')
        })
    })
    .then(response => {
        console.log('Update response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Update response data:', data);
        if (data.success) {
            closeWorkingModal('edit-shift-modal');
            location.reload(); // Refresh page to show updated data
        } else {
            alert('Failed to update shift: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error updating shift:', error);
        alert('Error updating shift: ' + error.message + '. Please try again.');
    });
}

// Handle invalid shift data - try to fix or provide detailed debugging
function handleInvalidShift(shiftData) {
    console.log('Invalid shift data:', shiftData);
    
    const message = `Invalid Shift Detected:\n\n` +
        `ID: ${shiftData.id || 'Missing'}\n` +
        `Employee: ${shiftData.employee_name || 'Unknown'}\n` +
        `Date: ${shiftData.shift_date || 'Unknown'}\n` +
        `Type: ${shiftData.shift_type || 'Unknown'}\n` +
        `Time: ${shiftData.start_time || 'N/A'} - ${shiftData.end_time || 'N/A'}\n\n` +
        `This shift has an invalid ID (${shiftData.id}). Would you like to:\n` +
        `• Create a new shift entry\n` +
        `• View debug information\n` +
        `• Report this issue`;
    
    if (confirm(message + '\n\nClick OK to create a new shift entry, Cancel to view debug info.')) {
        // Try to create a new shift entry
        createNewShiftFromData(shiftData);
    } else {
        // Show detailed debug information
        showShiftDebugInfo(shiftData);
    }
}

// Create a new shift entry from invalid shift data
function createNewShiftFromData(shiftData) {
    if (!shiftData.employee_id || !shiftData.shift_date) {
        alert('Cannot create shift: Missing employee ID or date');
        return;
    }
    
    // Pre-populate the create shift modal with existing data
    document.getElementById('shift-assignment-employee').value = shiftData.employee_id || '';
    document.getElementById('shift-assignment-date').value = shiftData.shift_date || '';
    document.getElementById('shift-assignment-type').value = shiftData.shift_type_id || '';
    document.getElementById('shift-assignment-start-time').value = shiftData.start_time || '';
    document.getElementById('shift-assignment-end-time').value = shiftData.end_time || '';
    document.getElementById('shift-assignment-location').value = shiftData.location || 'Main Office';
    document.getElementById('shift-assignment-notes').value = 'Recreated from invalid shift data';
    
    openWorkingModal('create-shift-modal');
}

// Show detailed debug information for invalid shift
function showShiftDebugInfo(shiftData) {
    const debugInfo = `SHIFT DEBUG INFORMATION\n\n` +
        `Raw Data: ${JSON.stringify(shiftData, null, 2)}\n\n` +
        `Issues Found:\n` +
        `${!shiftData.id || shiftData.id <= 0 ? '• Invalid or missing ID\n' : ''}` +
        `${!shiftData.employee_id ? '• Missing employee ID\n' : ''}` +
        `${!shiftData.shift_date ? '• Missing shift date\n' : ''}` +
        `${!shiftData.start_time ? '• Missing start time\n' : ''}` +
        `${!shiftData.end_time ? '• Missing end time\n' : ''}\n` +
        `Recommendation: This shift should be recreated in the database with proper validation.`;
    
    alert(debugInfo);
    console.error('Invalid shift debug info:', debugInfo);
}

// Make functions globally available
window.openWorkingModal = openWorkingModal;
window.closeWorkingModal = closeWorkingModal;
window.editShiftTypeForm = editShiftTypeForm;
window.viewShiftTypeDetails = viewShiftTypeDetails;
window.openCreateShiftModal = openCreateShiftModal;
window.handleCalendarCellClick = handleCalendarCellClick;
window.viewShiftAssignmentDetails = viewShiftAssignmentDetails;
window.viewShiftRequestDetails = viewShiftRequestDetails;
window.editShift = editShift;
window.populateEditShiftModal = populateEditShiftModal;
window.saveEditShift = saveEditShift;
window.handleApprovalSubmit = handleApprovalSubmit;
window.handleInvalidShift = handleInvalidShift;
window.createNewShiftFromData = createNewShiftFromData;
window.showShiftDebugInfo = showShiftDebugInfo;

// Handle approval form submission with auto-refresh
function handleApprovalSubmit(event, form) {
    // Let the form submit normally, but add a flag to refresh after success
    sessionStorage.setItem('refreshAfterApproval', 'true');
    return true;
}

// Check if we need to refresh after approval
document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('refreshAfterApproval') === 'true') {
        sessionStorage.removeItem('refreshAfterApproval');
        
        // Check for success message and refresh calendar if found
        const successAlert = document.getElementById('success-alert');
        if (successAlert && successAlert.textContent.includes('approved')) {
            console.log('Shift approved successfully, refreshing calendar...');
            
            // Refresh the page after a short delay to show the success message
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    }
});

console.log('All functions made globally available');
</script>

<!-- View Shift Type Details Modal -->
<div class="working-modal" id="view-shift-type-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-shift-type-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Shift Type Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-shift-type-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Name:</label>
                            <div id="view-shift-type-name" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Code:</label>
                            <div id="view-shift-type-code" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Start Time:</label>
                            <div id="view-shift-type-start" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">End Time:</label>
                            <div id="view-shift-type-end" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Duration:</label>
                            <div id="view-shift-type-duration" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Break Duration:</label>
                            <div id="view-shift-type-break" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-shift-type-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Shift Assignment Details Modal -->
<div class="working-modal" id="view-shift-assignment-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-shift-assignment-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Shift Assignment Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-shift-assignment-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Employee:</label>
                            <div id="view-shift-assignment-employee" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Date:</label>
                            <div id="view-shift-assignment-date" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Shift Type:</label>
                            <div id="view-shift-assignment-type" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Time:</label>
                            <div id="view-shift-assignment-time" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Location:</label>
                            <div id="view-shift-assignment-location" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Status:</label>
                            <div id="view-shift-assignment-status" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-shift-assignment-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Shift Request Details Modal -->
<div class="working-modal" id="view-shift-request-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-shift-request-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Shift Request Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-shift-request-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Employee:</label>
                            <div id="view-shift-request-employee" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Shift Type:</label>
                            <div id="view-shift-request-type" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Date:</label>
                            <div id="view-shift-request-date" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Time:</label>
                            <div id="view-shift-request-time" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Status:</label>
                            <div id="view-shift-request-status" class="detail-value">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">Submitted:</label>
                            <div id="view-shift-request-submitted" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="detail-item">
                            <label class="detail-label">Reason:</label>
                            <div id="view-shift-request-reason" class="detail-value">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-shift-request-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Shift Modal -->
<div class="working-modal" id="edit-shift-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('edit-shift-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title" id="edit-shift-modal-title">Edit Shift</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('edit-shift-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <form id="edit-shift-form">
                    <input type="hidden" id="edit-shift-id" name="shift_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label" for="edit-shift-date">Shift Date:</label>
                                <input type="date" class="form-control" id="edit-shift-date" name="shift_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label" for="edit-shift-status">Status:</label>
                                <select class="form-control" id="edit-shift-status" name="status" required>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label" for="edit-shift-start-time">Start Time:</label>
                                <input type="time" class="form-control" id="edit-shift-start-time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label" for="edit-shift-end-time">End Time:</label>
                                <input type="time" class="form-control" id="edit-shift-end-time" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="detail-item">
                                <label class="detail-label" for="edit-shift-location">Location:</label>
                                <input type="text" class="form-control" id="edit-shift-location" name="location" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="detail-item">
                                <label class="detail-label" for="edit-shift-notes">Notes:</label>
                                <textarea class="form-control" id="edit-shift-notes" name="notes" rows="3" placeholder="Optional notes"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('edit-shift-modal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveEditShift()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@endpush

<style>
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

/* Working Modal Centering */
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
// Function to scroll to calendar section (for View Schedule button)
function scrollToCalendarSection() {
  const calendarSection = document.getElementById('calendar-section');
  if (calendarSection) {
    calendarSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    // Add a subtle highlight effect
    calendarSection.style.boxShadow = '0 0 20px rgba(0, 123, 255, 0.3)';
    setTimeout(() => {
      calendarSection.style.boxShadow = '';
    }, 2000);
  }
}

// Function to scroll to shift requests section (for Review Requests and Approve/Reject buttons)
function scrollToShiftRequests() {
  const shiftRequestsSection = document.getElementById('shift-requests-section');
  if (shiftRequestsSection) {
    shiftRequestsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    // Add a subtle highlight effect
    shiftRequestsSection.style.boxShadow = '0 0 20px rgba(0, 123, 255, 0.3)';
    setTimeout(() => {
      shiftRequestsSection.style.boxShadow = '';
    }, 2000);
  }
}

// Check if we need to scroll to calendar when page loads (from external navigation)
if (sessionStorage.getItem('scrollToCalendar') === 'true') {
  sessionStorage.removeItem('scrollToCalendar');
  // Wait for page to load then scroll to calendar
  window.addEventListener('load', function() {
    setTimeout(function() {
      scrollToCalendarSection();
    }, 500);
  });
}
</script>

@endsection
