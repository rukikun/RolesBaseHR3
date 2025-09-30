<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jetlouge Travels Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
</head>
<body style="background-color: #f8f9fa !important;">

  @include('partials.admin_topbar')
  @include('partials.admin_sidebar')

  <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

  <main id="main-content">
    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Employee Management</h2>
            <p class="text-muted mb-0">
              Welcome back,
              @if(Auth::check())
                {{ Auth::user()->name }}
              @else
                Admin
              @endif
              ! Here's your employee directory.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Employee List</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Employee List Section -->
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">Employee Directory</h4>
        <div class="d-flex gap-2">
          <input type="text" id="employee-search" class="form-control form-control-sm" placeholder="Search employees..." style="width: 200px;">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="bi bi-plus-lg me-1"></i> Add Employee
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-primary">
              <tr>
                <th class="fw-bold">ID</th>
                <th class="fw-bold">Employee</th>
                <th class="fw-bold">Contact</th>
                <th class="fw-bold">Position</th>
                <th class="fw-bold">Department</th>
                <th class="fw-bold">Hire Date</th>
                <th class="fw-bold">Status</th>
                <th class="fw-bold text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="employee-table-body">
              @forelse($employees as $index => $employee)
                <tr class="employee-row">
                  <td>{{ $employee->employee_id }}</td>
                  <td class="employee-name">
                    <div class="d-flex align-items-center">
                      <img src="{{ $employee->profile_picture ? asset('storage/' . $employee->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->first_name . ' ' . $employee->last_name) }}" 
                           class="rounded-circle me-2" width="32" height="32" alt="Profile">
                      <div>
                        <div class="fw-semibold">{{ $employee->first_name }} {{ $employee->last_name }}</div>
                        <small class="text-muted">{{ $employee->employee_id }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div>
                      <i class="bi bi-envelope me-1"></i>{{ $employee->email }}
                    </div>
                    <div class="mt-1">
                      <i class="bi bi-telephone me-1"></i>{{ $employee->phone_number }}
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-info bg-opacity-10 text-info">{{ $employee->position }}</span>
                  </td>
                  <td>
                    @switch($employee->department_id)
                      @case(1) Human Resources @break
                      @case(2) Information Technology @break
                      @case(3) Finance @break
                      @case(4) Marketing @break
                      @case(5) Operations @break
                      @case(6) Customer Service @break
                      @default {{ $employee->department_id ?? 'Not Assigned' }}
                    @endswitch
                  </td>
                  <td>{{ \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') }}</td>
                  <td>
                    @if($employee->status == 'Active')
                      <span class="badge bg-success">Active</span>
                    @elseif($employee->status == 'Inactive')
                      <span class="badge bg-secondary">Inactive</span>
                    @else
                      <span class="badge bg-warning">{{ $employee->status }}</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <button class="btn btn-outline-primary btn-sm edit-employee-btn"
                              data-id="{{ $employee->employee_id }}"
                              data-first-name="{{ $employee->first_name }}"
                              data-last-name="{{ $employee->last_name }}"
                              data-email="{{ $employee->email }}"
                              data-phone="{{ $employee->phone_number }}"
                              data-position="{{ $employee->position }}"
                              data-department="{{ $employee->department_id }}"
                              data-status="{{ $employee->status }}"
                              title="Edit Employee">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <a href="{{ route('employees.show', $employee->employee_id) }}" 
                         class="btn btn-outline-info btn-sm view-employee-btn" 
                         title="View Profile"
                         onclick="showLoadingToast('Loading employee profile...')">
                        <i class="bi bi-eye"></i>
                      </a>
                      <button class="btn btn-outline-danger btn-sm delete-employee-btn" 
                              title="Delete Employee" 
                              data-employee-id="{{ $employee->employee_id }}"
                              data-employee-name="{{ $employee->first_name }} {{ $employee->last_name }}">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">No employees found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Employee Modal -->
  <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-content">
          <div class="card-header modal-header">
            <h5 class="modal-title">Add New Employee</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="employee_id" class="form-label">Employee ID*</label>
                <input type="text" name="employee_id" class="form-control" value="{{ $nextEmployeeId }}" required>
                <small class="form-text text-muted">Suggested ID: {{ $nextEmployeeId }}</small>
              </div>
              <div class="col-md-6">
                <label for="status" class="form-label">Status*</label>
                <select name="status" class="form-select" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="first_name" class="form-label">First Name*</label>
                <input type="text" name="first_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="last_name" class="form-label">Last Name*</label>
                <input type="text" name="last_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="email" class="form-label">Email*</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="password" class="form-label">Password*</label>
                <input type="password" name="password" class="form-control" required autocomplete="new-password">
                <small class="form-text text-muted">Password will be hidden as you type.</small>
              </div>
              <div class="col-md-6">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" name="phone_number" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="position" class="form-label">Position</label>
                <input type="text" name="position" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="department_id" class="form-label">Department</label>
                <select name="department_id" class="form-select">
                  <option value="">Select Department</option>
                  <option value="1">Human Resources</option>
                  <option value="2">Information Technology</option>
                  <option value="3">Finance</option>
                  <option value="4">Marketing</option>
                  <option value="5">Operations</option>
                  <option value="6">Customer Service</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="hire_date" class="form-label">Hire Date</label>
                <input type="date" name="hire_date" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="profile_picture" class="form-label">Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control" accept="image/*">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="saveEmployeeBtn">
              <i class="bi bi-save me-1"></i> Save Employee
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Employee Modal -->
  <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="editEmployeeForm" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Employee</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="edit-first-name" class="form-label">First Name*</label>
                <input id="edit-first-name" type="text" name="first_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="edit-last-name" class="form-label">Last Name*</label>
                <input id="edit-last-name" type="text" name="last_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="edit-email" class="form-label">Email*</label>
                <input id="edit-email" type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="edit-phone" class="form-label">Phone Number</label>
                <input id="edit-phone" type="text" name="phone_number" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="edit-position" class="form-label">Position</label>
                <input id="edit-position" type="text" name="position" class="form-control">
              </div>
              <div class="col-md-6">
                <label for="edit-department" class="form-label">Department</label>
                <select id="edit-department" name="department_id" class="form-select">
                  <option value="">Select Department</option>
                  <option value="1">Human Resources</option>
                  <option value="2">Information Technology</option>
                  <option value="3">Finance</option>
                  <option value="4">Marketing</option>
                  <option value="5">Operations</option>
                  <option value="6">Customer Service</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="edit-status" class="form-label">Status*</label>
                <select id="edit-status" name="status" class="form-select" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-arrow-repeat me-1"></i> Update Employee
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Enhanced Toast Notifications -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="successToast" class="toast align-items-center text-bg-success border-0 mb-2" role="alert">
      <div class="d-flex">
        <div class="toast-body" id="successToastBody"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0 mb-2" role="alert">
      <div class="d-flex">
        <div class="toast-body" id="errorToastBody"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Search functionality
    document.getElementById('employee-search').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const rows = document.querySelectorAll('.employee-row');
      
      rows.forEach(row => {
        const name = row.querySelector('.employee-name').textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const position = row.cells[3].textContent.toLowerCase();
        
        if (name.includes(searchTerm) || email.includes(searchTerm) || position.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    // Edit employee modal
    document.querySelectorAll('.edit-employee-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
        const form = document.getElementById('editEmployeeForm');
        
        form.action = `/employees/${this.dataset.id}`;
        document.getElementById('edit-first-name').value = this.dataset.firstName;
        document.getElementById('edit-last-name').value = this.dataset.lastName;
        document.getElementById('edit-email').value = this.dataset.email;
        document.getElementById('edit-phone').value = this.dataset.phone;
        document.getElementById('edit-position').value = this.dataset.position;
        document.getElementById('edit-department').value = this.dataset.department;
        document.getElementById('edit-status').value = this.dataset.status;
        
        modal.show();
      });
    });

    // Delete button event listeners
    document.querySelectorAll('.delete-employee-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const employeeId = this.dataset.employeeId;
        const employeeName = this.dataset.employeeName;
        
        if (confirm(`Are you sure you want to delete ${employeeName}? This action cannot be undone.`)) {
          // Show loading state
          this.disabled = true;
          this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
          
          // Create and submit delete form
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/employees/${employeeId}`;
          
          // Add CSRF token
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          const csrfInput = document.createElement('input');
          csrfInput.type = 'hidden';
          csrfInput.name = '_token';
          csrfInput.value = csrfToken;
          form.appendChild(csrfInput);
          
          // Add DELETE method
          const methodInput = document.createElement('input');
          methodInput.type = 'hidden';
          methodInput.name = '_method';
          methodInput.value = 'DELETE';
          form.appendChild(methodInput);
          
          document.body.appendChild(form);
          form.submit();
        }
      });
    });

    // Toast notifications
    function showSuccessToast(message) {
      document.getElementById('successToastBody').textContent = message;
      new bootstrap.Toast(document.getElementById('successToast')).show();
    }

    function showErrorToast(message) {
      document.getElementById('errorToastBody').textContent = message;
      new bootstrap.Toast(document.getElementById('errorToast')).show();
    }

    function showLoadingToast(message) {
      document.getElementById('successToastBody').textContent = message;
      new bootstrap.Toast(document.getElementById('successToast')).show();
    }

    // Handle add employee form submission
    document.querySelector('#addEmployeeModal form').addEventListener('submit', function(e) {
      const submitBtn = document.getElementById('saveEmployeeBtn');
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    });

    // Show validation errors if any
    @if($errors->any())
      document.addEventListener('DOMContentLoaded', function() {
        let errorMessage = 'Please fix the following errors:\n';
        @foreach($errors->all() as $error)
          errorMessage += '• {{ $error }}\n';
        @endforeach
        showErrorToast(errorMessage);
      });
    @endif

    // Show success message if any
    @if(session('success'))
      document.addEventListener('DOMContentLoaded', function() {
        showSuccessToast('{{ session('success') }}');
      });
    @endif
  </script>
</body>
</html>
