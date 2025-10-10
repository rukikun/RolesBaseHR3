@extends('layouts.hr')

@section('title', 'Employee List - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Employee List</h2>
        <p class="text-muted mb-0">Manage local employee database records</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Employee List</li>
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

<!-- Employee Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern" onclick="window.location.href='/employees/list'" style="cursor: pointer;">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-primary">
          <i class="fas fa-users text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $stats['total_employees'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Total Employees</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern" onclick="window.location.href='/employees/list?status=active'" style="cursor: pointer;">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-success">
          <i class="fas fa-user-check text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $stats['active_employees'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">Active</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern" onclick="window.location.href='/timesheet-management?filter=with_timesheets'" style="cursor: pointer;">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-info">
          <i class="fas fa-clock text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">{{ $stats['employees_with_timesheets'] ?? 0 }}</h3>
          <p class="text-muted mb-0 small stat-label">With Timesheets</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card-modern" onclick="window.location.href='/employees/list?view=departments'" style="cursor: pointer;">
      <div class="d-flex align-items-center">
        <div class="stat-icon-circle bg-warning">
          <i class="fas fa-building text-white"></i>
        </div>
        <div class="ms-3">
          <h3 class="fw-bold mb-0 stat-number">6</h3>
          <p class="text-muted mb-0 small stat-label">Departments</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Employee Management -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-users me-2"></i>Employee List
    </h5>
    <div>
      <button class="btn btn-primary" onclick="openAddEmployeeModal()">
        <i class="fas fa-plus me-2"></i>Add Employee
      </button>
    </div>
  </div>
  <div class="card-body">
    <!-- Employee Search and Filters -->
    <div class="row mb-3">
      <div class="col-md-4">
        <input type="text" class="form-control" id="employee-search" placeholder="Search employees...">
      </div>
      <div class="col-md-3">
        <select class="form-select" id="department-filter">
          <option value="">All Departments</option>
          <option value="Human Resources">Human Resources</option>
          <option value="Information Technology">Information Technology</option>
          <option value="Finance">Finance</option>
          <option value="Marketing">Marketing</option>
          <option value="Sales">Sales</option>
          <option value="Operations">Operations</option>
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select" id="status-filter">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="terminated">Terminated</option>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-outline-secondary" onclick="clearFilters()">
          <i class="fas fa-times me-1"></i>Clear
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover" id="employees-table">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Position</th>
            <th>Department</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="employees-table-body">
          @forelse($employees ?? [] as $employee)
          <tr>
            <td>#{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</td>
            <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
            <td>{{ $employee->position ?? 'N/A' }}</td>
            <td>{{ $employee->department ?? 'N/A' }}</td>
            <td>
              @if($employee->status === 'active')
                <span class="badge bg-success">Active</span>
              @elseif($employee->status === 'inactive')
                <span class="badge bg-warning">Inactive</span>
              @else
                <span class="badge bg-danger">Unknown</span>
              @endif
            </td>
            <td>
              <div class="btn-group" role="group" aria-label="Employee Actions">
                <!-- View Action -->
                <button class="btn btn-sm btn-outline-primary" onclick="viewEmployeeDetails({{ $employee->id }})" title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
                
                <!-- Edit Action -->
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openEditEmployeeModal({{ $employee->id }})" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                
                <!-- View Timesheets Action -->
                <a href="/timesheet-management?employee={{ $employee->id }}" class="btn btn-sm btn-outline-info" title="View Timesheets">
                  <i class="fas fa-clock"></i>
                </a>
                
                <!-- Delete Action -->
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEmployee({{ $employee->id }})" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">
              <i class="fas fa-users fa-3x mb-3 text-muted"></i><br>
              No employees found. <a href="#" onclick="openAddEmployeeModal()" class="text-primary">Add your first employee</a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- Add Employee Modal -->
<div class="working-modal" id="add-employee-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('add-employee-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Add Employee</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('add-employee-modal')">&times;</button>
            </div>
            <form id="add-employee-form" method="POST" action="{{ route('employees.list.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_first_name" name="first_name" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_last_name" name="last_name" required maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="add_email" name="email" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_salary" class="form-label">Salary</label>
                                <input type="number" class="form-control" id="add_salary" name="salary" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_position" class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_position" name="position" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_department" class="form-label">Department <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_department" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Human Resources">Human Resources</option>
                                    <option value="Information Technology">Information Technology</option>
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
                                <label for="add_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="add_phone" name="phone" maxlength="20" placeholder="+63 912 345 6789">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_hire_date" class="form-label">Hire Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="add_hire_date" name="hire_date" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_status" name="status" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('add-employee-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="add-employee-btn">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="working-modal" id="edit-employee-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('edit-employee-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Edit Employee</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('edit-employee-modal')">&times;</button>
            </div>
            <form id="edit-employee-form" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_employee_id" name="employee_id">
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email" name="email" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_salary" class="form-label">Salary</label>
                                <input type="number" class="form-control" id="edit_salary" name="salary" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_position" class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_position" name="position" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_department" class="form-label">Department <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_department" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Human Resources">Human Resources</option>
                                    <option value="Information Technology">Information Technology</option>
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
                                <label for="edit_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="edit_phone" name="phone" maxlength="20" placeholder="+63 912 345 6789">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_hire_date" class="form-label">Hire Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_hire_date" name="hire_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                </div>
                <div class="working-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('edit-employee-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="edit-employee-btn">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Employee View Modal -->
<div class="working-modal" id="employee-view-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('employee-view-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Employee Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('employee-view-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="view-employee-avatar" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face" 
                             alt="Employee" class="rounded-circle mb-3" width="150" height="150">
                        <h5 id="view-employee-name"></h5>
                        <p class="text-muted" id="view-employee-position"></p>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Employee ID:</th>
                                <td id="view-employee-id"></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td id="view-employee-email"></td>
                            </tr>
                            <tr>
                                <th>Department:</th>
                                <td id="view-employee-department"></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td id="view-employee-phone"></td>
                            </tr>
                            <tr>
                                <th>Hire Date:</th>
                                <td id="view-employee-hire-date"></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td id="view-employee-status"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('employee-view-modal')">Close</button>
                <button type="button" class="btn btn-primary" onclick="editEmployeeFromView()">Edit Employee</button>
            </div>
        </div>
    </div>
</div>

@endsection

<style>
/* Modern Statistics Cards */
.stat-card-modern {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #e9ecef;
  transition: all 0.3s ease;
  cursor: pointer;
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
  background: linear-gradient(90deg, #007bff, #0056b3);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stat-card-modern:hover::before {
  opacity: 1;
}

.stat-icon-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2c3e50;
  line-height: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #6c757d;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Icon colors */
.bg-primary {
  background: linear-gradient(135deg, #007bff, #0056b3) !important;
}

.bg-success {
  background: linear-gradient(135deg, #28a745, #1e7e34) !important;
}

.bg-info {
  background: linear-gradient(135deg, #17a2b8, #117a8b) !important;
}

.bg-warning {
  background: linear-gradient(135deg, #ffc107, #e0a800) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .stat-card-modern {
    padding: 20px;
    border-radius: 12px;
  }
  
  .stat-number {
    font-size: 2rem;
  }
  
  .stat-icon-circle {
    width: 48px;
    height: 48px;
    font-size: 20px;
  }
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
  padding: 60px 20px 20px 20px !important;
  box-sizing: border-box !important;
  overflow-y: auto !important;
}

.working-modal[style*="display: block"] {
  display: block !important;
}

.working-modal-dialog {
  max-width: 600px !important;
  width: 90% !important;
  margin: 0 auto !important;
  position: relative !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
}

.working-modal-content {
  background: white !important;
  border-radius: 8px !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
}

.working-modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: space-between;
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

/* Button Group Styling - Match Employee Directory */
.btn-group .btn {
  border-radius: 0;
  margin-left: -1px;
}

.btn-group .btn:first-child {
  border-top-left-radius: 0.375rem;
  border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
  border-top-right-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
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
.btn-group .btn-outline-warning,
.btn-group .btn-outline-secondary {
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

@push('scripts')
<script src="{{ asset('assets/js/hr-database-integration.js') }}"></script>
<script>
// Modal functions
function openAddEmployeeModal() {
    document.getElementById('add-employee-modal').style.display = 'block';
}

function openEditEmployeeModal(employeeId) {
    // Fetch employee data and populate form
    fetch(`/employees/list/${employeeId}/view`)
        .then(response => response.json())
        .then(employee => {
            document.getElementById('edit_employee_id').value = employee.id;
            document.getElementById('edit_first_name').value = employee.first_name;
            document.getElementById('edit_last_name').value = employee.last_name;
            document.getElementById('edit_email').value = employee.email;
            document.getElementById('edit_salary').value = employee.salary || '';
            document.getElementById('edit_position').value = employee.position;
            document.getElementById('edit_department').value = employee.department;
            document.getElementById('edit_phone').value = employee.phone || '';
            document.getElementById('edit_hire_date').value = employee.hire_date;
            document.getElementById('edit_status').value = employee.status;
            
            document.getElementById('edit-employee-form').action = `/employees/list/${employee.id}`;
            document.getElementById('edit-employee-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching employee data:', error);
            alert('Error loading employee data');
        });
}

function viewEmployeeDetails(employeeId) {
    // Fetch employee data and populate view modal
    fetch(`/employees/list/${employeeId}/view`)
        .then(response => response.json())
        .then(employee => {
            document.getElementById('view-employee-name').textContent = `${employee.first_name} ${employee.last_name}`;
            document.getElementById('view-employee-position').textContent = employee.position;
            document.getElementById('view-employee-id').textContent = `#${String(employee.id).padStart(4, '0')}`;
            document.getElementById('view-employee-email').textContent = employee.email;
            document.getElementById('view-employee-department').textContent = employee.department || 'N/A';
            document.getElementById('view-employee-phone').textContent = employee.phone || 'N/A';
            document.getElementById('view-employee-hire-date').textContent = employee.hire_date || 'N/A';
            document.getElementById('view-employee-status').innerHTML = `<span class="badge bg-${employee.status === 'active' ? 'success' : employee.status === 'inactive' ? 'warning' : 'danger'}">${employee.status}</span>`;
            
            document.getElementById('employee-view-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching employee data:', error);
            alert('Error loading employee data');
        });
}

function closeWorkingModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editEmployeeFromView() {
    const employeeId = document.getElementById('view-employee-id').textContent.replace('#', '').replace(/^0+/, '');
    closeWorkingModal('employee-view-modal');
    openEditEmployeeModal(employeeId);
}

function clearFilters() {
    document.getElementById('employee-search').value = '';
    document.getElementById('department-filter').value = '';
    document.getElementById('status-filter').value = '';
}

// Delete employee function
function deleteEmployee(employeeId) {
    if (!confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
        return;
    }
    
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/employees/list/${employeeId}`;
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfInput);
    
    // Add method override
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
