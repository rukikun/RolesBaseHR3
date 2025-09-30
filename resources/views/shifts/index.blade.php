@extends('layouts.hr')

@section('title', 'Shift Management - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Shift Management</h2>
        <p class="text-muted mb-0">Create and manage shift types</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Shift Management</li>
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

<!-- Action Buttons -->
<div class="row mb-4">
  <div class="col-12">
    <div class="d-flex gap-2">
      <button class="btn btn-primary" id="add-shift-btn">
        <i class="fas fa-plus me-2"></i>Add New Shift
      </button>
      <a href="{{ url('/shifts/schedule') }}" class="btn btn-outline-primary">
        <i class="fas fa-calendar-alt me-2"></i>Manage Schedule
      </a>
      <a href="{{ url('/shifts/swap-requests') }}" class="btn btn-outline-info">
        <i class="fas fa-exchange-alt me-2"></i>Swap Requests
      </a>
    </div>
  </div>
</div>

<!-- Shifts Table -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-clock me-2"></i>Shift Types
    </h5>
    <div>
      <button class="btn btn-success btn-sm" id="refresh-shifts-btn">
        <i class="fas fa-sync-alt me-2"></i>Refresh
      </button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="shifts-table">
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
        <tbody id="shifts-tbody">
          <!-- Shifts will be loaded here -->
        </tbody>
      </table>
    </div>
    
    <!-- Loading indicator -->
    <div class="text-center py-4" id="shifts-loading" style="display: none;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading shifts...</p>
    </div>
    
    <!-- No data message -->
    <div class="text-center py-4" id="no-shifts-data" style="display: none;">
      <i class="fas fa-clock fa-3x text-muted mb-3"></i>
      <p class="text-muted">No shifts found. Create your first shift to get started.</p>
    </div>
  </div>
</div>

<!-- Add/Edit Shift Modal -->
<div class="modal fade" id="shift-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="shift-modal-title">Add New Shift</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="shift-form">
        <div class="modal-body">
          <input type="hidden" id="shift-id" name="shift_id">
          
          <div class="mb-3">
            <label for="shift-name" class="form-label">Shift Name *</label>
            <input type="text" class="form-control" id="shift-name" name="name" required>
          </div>
          
          <div class="mb-3">
            <label for="shift-type" class="form-label">Shift Type *</label>
            <select class="form-select" id="shift-type" name="shift_type" required>
              <option value="">Select Type</option>
              <option value="day">Day Shift</option>
              <option value="night">Night Shift</option>
              <option value="split">Split Shift</option>
            </select>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="start-time" class="form-label">Start Time *</label>
                <input type="time" class="form-control" id="start-time" name="start_time" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="end-time" class="form-label">End Time *</label>
                <input type="time" class="form-control" id="end-time" name="end_time" required>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="break-duration" class="form-label">Break Duration (minutes)</label>
            <input type="number" class="form-control" id="break-duration" name="break_duration" min="0" value="60">
          </div>
          
          <div class="mb-3">
            <label for="shift-description" class="form-label">Description</label>
            <textarea class="form-control" id="shift-description" name="description" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="save-shift-btn">Save Shift</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
  loadShifts();
});

// Load shifts
function loadShifts() {
  showShiftsLoading(true);
  
  fetch('/api/shifts', {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    showShiftsLoading(false);
    if (data.success) {
      populateShiftsTable(data.data);
    } else {
      showNoShiftsData(true);
    }
  })
  .catch(error => {
    showShiftsLoading(false);
    console.error('Error loading shifts:', error);
    showNotification('Error loading shifts', 'error');
  });
}

// Show/hide loading indicator
function showShiftsLoading(show) {
  document.getElementById('shifts-loading').style.display = show ? 'block' : 'none';
  document.getElementById('no-shifts-data').style.display = 'none';
}

// Show/hide no data message
function showNoShiftsData(show) {
  document.getElementById('no-shifts-data').style.display = show ? 'block' : 'none';
  document.getElementById('shifts-loading').style.display = 'none';
}

// Populate shifts table
function populateShiftsTable(shifts) {
  const tbody = document.getElementById('shifts-tbody');
  
  if (shifts.length === 0) {
    showNoShiftsData(true);
    tbody.innerHTML = '';
    return;
  }
  
  tbody.innerHTML = shifts.map(shift => `
    <tr>
      <td>${shift.name}</td>
      <td>
        <span class="badge bg-${getShiftTypeColor(shift.shift_type)}">${shift.shift_type}</span>
      </td>
      <td>${shift.start_time}</td>
      <td>${shift.end_time}</td>
      <td>${shift.break_duration || 0} min</td>
      <td>
        <span class="badge bg-${shift.is_active ? 'success' : 'secondary'}">
          ${shift.is_active ? 'Active' : 'Inactive'}
        </span>
      </td>
      <td>
        <div class="btn-group btn-group-sm">
          <button class="btn btn-outline-primary" onclick="editShift(${shift.id})">
            <i class="fas fa-edit"></i>
          </button>
          <button class="btn btn-outline-danger" onclick="deactivateShift(${shift.id})">
            <i class="fas fa-ban"></i>
          </button>
        </div>
      </td>
    </tr>
  `).join('');
}

// Get shift type color
function getShiftTypeColor(type) {
  switch (type) {
    case 'day': return 'primary';
    case 'night': return 'dark';
    case 'split': return 'warning';
    default: return 'secondary';
  }
}

// Add shift button
document.getElementById('add-shift-btn').addEventListener('click', function() {
  document.getElementById('shift-modal-title').textContent = 'Add New Shift';
  document.getElementById('shift-form').reset();
  document.getElementById('shift-id').value = '';
  new bootstrap.Modal(document.getElementById('shift-modal')).show();
});

// Edit shift
function editShift(id) {
  fetch(`/api/shifts/${id}`, {
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const shift = data.data;
      document.getElementById('shift-modal-title').textContent = 'Edit Shift';
      document.getElementById('shift-id').value = shift.id;
      document.getElementById('shift-name').value = shift.name;
      document.getElementById('shift-type').value = shift.shift_type;
      document.getElementById('start-time').value = shift.start_time;
      document.getElementById('end-time').value = shift.end_time;
      document.getElementById('break-duration').value = shift.break_duration || 60;
      document.getElementById('shift-description').value = shift.description || '';
      
      new bootstrap.Modal(document.getElementById('shift-modal')).show();
    }
  })
  .catch(error => {
    console.error('Error loading shift:', error);
    showNotification('Error loading shift details', 'error');
  });
}

// Save shift form
document.getElementById('shift-form').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const shiftId = document.getElementById('shift-id').value;
  const isEdit = shiftId !== '';
  
  const url = isEdit ? `/api/shifts/${shiftId}` : '/api/shifts';
  const method = isEdit ? 'PUT' : 'POST';
  
  const data = {};
  formData.forEach((value, key) => {
    if (key !== 'shift_id') data[key] = value;
  });
  
  fetch(url, {
    method: method,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message, 'success');
      bootstrap.Modal.getInstance(document.getElementById('shift-modal')).hide();
      loadShifts();
    } else {
      showNotification(data.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error saving shift:', error);
    showNotification('Error saving shift', 'error');
  });
});

// Deactivate shift
function deactivateShift(id) {
  if (confirm('Are you sure you want to deactivate this shift?')) {
    fetch(`/api/shifts/${id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification(data.message, 'success');
        loadShifts();
      } else {
        showNotification(data.message, 'error');
      }
    })
    .catch(error => {
      console.error('Error deactivating shift:', error);
      showNotification('Error deactivating shift', 'error');
    });
  }
}

// Refresh shifts
document.getElementById('refresh-shifts-btn').addEventListener('click', loadShifts);

// Notification function
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
  notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
  notification.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 5000);
}
</script>
@endpush
@endsection
