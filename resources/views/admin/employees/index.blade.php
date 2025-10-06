@extends('layouts.hr')

@section('title', 'Employees - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Employee Management</h2>
        <p class="text-muted mb-0">Manage employee information and records</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Employees</li>
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

<!-- Employee Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card-modern" onclick="window.location.href='/employees'" style="cursor: pointer;">
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
    <div class="stat-card-modern" onclick="window.location.href='/employees?status=active'" style="cursor: pointer;">
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
    <div class="stat-card-modern" onclick="window.location.href='/employees?view=departments'" style="cursor: pointer;">
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
      <i class="fas fa-users me-2"></i>Employee Directory
    </h5>
    <div>
      <button class="btn btn-primary" onclick="openAddEmployeeModal()">
        <i class="fas fa-plus me-2"></i>Add Employee
      </button>
    </div>
  </div>
  <div class="card-body">
    <!-- Employee Search and Filters -->
    <form method="GET" action="{{ route('employees.index') }}" id="employee-filter-form">
      <div class="row mb-3">
        <div class="col-md-4">
          <input type="text" class="form-control" name="search" id="employee-search" 
                 placeholder="Search employees..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
          <select class="form-select" name="department" id="department-filter">
            <option value="">All Departments</option>
            @foreach($departments as $department)
              <option value="{{ $department }}" {{ request('department') == $department ? 'selected' : '' }}>
                {{ $department }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select" name="status" id="status-filter">
            <option value="">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary me-2">
            <i class="fas fa-search me-1"></i>Search
          </button>
          <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-times me-1"></i>Clear
          </a>
        </div>
      </div>
    </form>

    <!-- Filter Information Display -->
    @if(request()->has('status') || request()->has('department') || request()->has('search'))
    <div class="alert alert-info mb-3">
      <i class="fas fa-filter me-2"></i>
      <strong>Filtered View:</strong>
      @if(request()->get('status'))
        Status: {{ ucfirst(request()->get('status')) }}
      @endif
      @if(request()->get('department'))
        @if(request()->get('status')) | @endif
        Department: {{ request()->get('department') }}
      @endif
      @if(request()->get('search'))
        @if(request()->get('status') || request()->get('department')) | @endif
        Search: "{{ request()->get('search') }}"
      @endif
      <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-secondary ms-2">
        <i class="fas fa-times me-1"></i>Clear Filters
      </a>
    </div>
    @endif

    <!-- Employee Count Display -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="employee-count text-muted">
        {{ $employees->count() }} employee{{ $employees->count() !== 1 ? 's' : '' }} found
      </div>
      @if($employees->count() > 0)
        <small class="text-muted">
          <i class="fas fa-info-circle me-1"></i>
          Data loaded from API
        </small>
      @endif
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
              <div class="btn-group" role="group">
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
                <form method="POST" action="{{ route('employees.destroy', $employee->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this employee?')">
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
            <form id="add-employee-form" method="POST" action="{{ route('employees.store') }}">
                @csrf
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_first_name" name="first_name" required maxlength="100" value="{{ old('first_name') }}">
                                @error('first_name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_last_name" name="last_name" required maxlength="100" value="{{ old('last_name') }}">
                                @error('last_name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="add_email" name="email" required maxlength="255" value="{{ old('email') }}">
                                @error('email')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_salary" class="form-label">Salary</label>
                                <input type="number" class="form-control" id="add_salary" name="salary" step="0.01" min="0" max="9999999.99" value="{{ old('salary') }}">
                                @error('salary')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_position" class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_position" name="position" required maxlength="100" value="{{ old('position') }}">
                                @error('position')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_department" class="form-label">Department <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_department" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Human Resources" {{ old('department') == 'Human Resources' ? 'selected' : '' }}>Human Resources</option>
                                    <option value="Information Technology" {{ old('department') == 'Information Technology' ? 'selected' : '' }}>Information Technology</option>
                                    <option value="Finance" {{ old('department') == 'Finance' ? 'selected' : '' }}>Finance</option>
                                    <option value="Marketing" {{ old('department') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                                    <option value="Sales" {{ old('department') == 'Sales' ? 'selected' : '' }}>Sales</option>
                                    <option value="Operations" {{ old('department') == 'Operations' ? 'selected' : '' }}>Operations</option>
                                </select>
                                @error('department')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="add_phone" name="phone" maxlength="20" placeholder="+63 912 345 6789" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_hire_date" class="form-label">Hire Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="add_hire_date" name="hire_date" required value="{{ old('hire_date', date('Y-m-d')) }}">
                                @error('hire_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_status" name="status" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="terminated" {{ old('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                        @error('status')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
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
                                <input type="number" class="form-control" id="edit_salary" name="salary" step="0.01" min="0" max="9999999.99">
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

<!-- Timesheet Modal for Employee Integration -->
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
                <div class="working-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timesheet_employee_id" class="form-label">Employee</label>
                                <select class="form-select" id="timesheet_employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees ?? [] as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timesheet_work_date" class="form-label">Work Date</label>
                                <input type="date" class="form-control" id="timesheet_work_date" name="work_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timesheet_hours_worked" class="form-label">Hours Worked</label>
                                <input type="number" class="form-control" id="timesheet_hours_worked" name="hours_worked" min="0" max="24" step="0.5" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timesheet_overtime_hours" class="form-label">Overtime Hours</label>
                                <input type="number" class="form-control" id="timesheet_overtime_hours" name="overtime_hours" min="0" max="12" step="0.5">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timesheet_status" class="form-label">Status</label>
                                <select class="form-select" id="timesheet_status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timesheet_description" class="form-label">Description</label>
                                <textarea class="form-control" id="timesheet_description" name="description" rows="3"></textarea>
                            </div>
                        </div>
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

<script>
// Handle edit employee data from session on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(session('edit_employee'))
        const editData = @json(session('edit_employee'));
        
        // Populate edit form with session data
        document.getElementById('edit_employee_id').value = editData.id;
        document.getElementById('edit_first_name').value = editData.first_name || '';
        document.getElementById('edit_last_name').value = editData.last_name || '';
        document.getElementById('edit_email').value = editData.email || '';
        document.getElementById('edit_salary').value = editData.salary || '';
        document.getElementById('edit_position').value = editData.position || '';
        document.getElementById('edit_department').value = editData.department || '';
        document.getElementById('edit_phone').value = editData.phone || '';
        document.getElementById('edit_hire_date').value = editData.hire_date || '';
        document.getElementById('edit_status').value = editData.status || 'active';
        
        // Set form action
        document.getElementById('edit-employee-form').action = `/employees/${editData.id}`;
        
        // Open edit modal
        openWorkingModal('edit-employee-modal');
    @endif
});

// This function is no longer needed as we have separate modals
// Keeping for backward compatibility but it's not used
function populateEditForm(employee) {
    // Function kept for compatibility - not used with separate modals
}

// Handle form submission with validation for both forms
document.addEventListener('DOMContentLoaded', function() {
    // Add Employee Form Validation
    const addForm = document.getElementById('add-employee-form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            // Clear previous errors
            clearFormErrors('add-employee-modal');
            
            if (!validateEmployeeForm('add')) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('add-employee-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            }
        });
    }
    
    // Edit Employee Form Validation
    const editForm = document.getElementById('edit-employee-form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            // Clear previous errors
            clearFormErrors('edit-employee-modal');
            
            if (!validateEmployeeForm('edit')) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('edit-employee-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            }
        });
    }
    
    // Auto-open modal if there are validation errors
    @if($errors->any() && old('_token'))
        // Check if this was from add employee form
        @if(!session('edit_employee'))
            setTimeout(function() {
                openAddEmployeeModal();
                // Populate form with old values
                populateFormWithOldValues('add');
            }, 100);
        @endif
    @endif
});

// Validate employee form (works for both add and edit)
function validateEmployeeForm(type) {
    const prefix = type === 'add' ? 'add_' : 'edit_';
    
    const firstName = document.getElementById(prefix + 'first_name').value.trim();
    const lastName = document.getElementById(prefix + 'last_name').value.trim();
    const email = document.getElementById(prefix + 'email').value.trim();
    const position = document.getElementById(prefix + 'position').value.trim();
    const department = document.getElementById(prefix + 'department').value;
    const hireDate = document.getElementById(prefix + 'hire_date').value;
    const status = document.getElementById(prefix + 'status').value;
    
    let hasErrors = false;
    
    // Clear previous errors
    const modal = document.getElementById(type + '-employee-modal');
    modal.querySelectorAll('.text-danger.small').forEach(el => el.remove());
    modal.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Validate required fields
    if (!firstName) {
        showFieldError(prefix + 'first_name', 'First name is required');
        hasErrors = true;
    }
    
    if (!lastName) {
        showFieldError(prefix + 'last_name', 'Last name is required');
        hasErrors = true;
    }
    
    if (!email) {
        showFieldError(prefix + 'email', 'Email is required');
        hasErrors = true;
    } else if (!isValidEmail(email)) {
        showFieldError(prefix + 'email', 'Please enter a valid email address');
        hasErrors = true;
    }
    
    if (!position) {
        showFieldError(prefix + 'position', 'Position is required');
        hasErrors = true;
    }
    
    if (!department) {
        showFieldError(prefix + 'department', 'Department is required');
        hasErrors = true;
    }
    
    if (!hireDate) {
        showFieldError(prefix + 'hire_date', 'Hire date is required');
        hasErrors = true;
    }
    
    if (!status) {
        showFieldError(prefix + 'status', 'Status is required');
        hasErrors = true;
    }
    
    return !hasErrors;
}

// Modal functions for working-modal system
function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

// Open Add Employee Modal
function openAddEmployeeModal() {
    const modal = document.getElementById('add-employee-modal');
    if (modal) {
        // Reset form to ensure clean state
        const form = document.getElementById('add-employee-form');
        if (form) {
            form.reset();
            
            // Set default values
            const statusField = document.getElementById('add_status');
            const hireDateField = document.getElementById('add_hire_date');
            const submitBtn = document.getElementById('add-employee-btn');
            
            if (statusField) statusField.value = 'active';
            if (hireDateField) hireDateField.value = new Date().toISOString().split('T')[0];
            
            // Reset submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Save Employee';
            }
        }
        
        // Clear any validation errors
        clearFormErrors('add-employee-modal');
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        setTimeout(() => {
            const firstInput = document.getElementById('add_first_name');
            if (firstInput) firstInput.focus();
        }, 100);
    }
}

// Open Edit Employee Modal
function openEditEmployeeModal(employeeId) {
    // Fetch employee data via AJAX
    fetch(`/employees/${employeeId}/view`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const employee = data.employee;
                
                // Populate edit form
                document.getElementById('edit_employee_id').value = employee.id;
                document.getElementById('edit_first_name').value = employee.first_name || '';
                document.getElementById('edit_last_name').value = employee.last_name || '';
                document.getElementById('edit_email').value = employee.email || '';
                document.getElementById('edit_salary').value = employee.salary || '';
                document.getElementById('edit_position').value = employee.position || '';
                document.getElementById('edit_department').value = employee.department || '';
                document.getElementById('edit_phone').value = employee.phone || '';
                document.getElementById('edit_hire_date').value = employee.hire_date || '';
                document.getElementById('edit_status').value = employee.status || 'active';
                
                // Set form action
                document.getElementById('edit-employee-form').action = `/employees/${employee.id}`;
                
                // Open modal
                const modal = document.getElementById('edit-employee-modal');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                
                // Focus first input
                setTimeout(() => document.getElementById('edit_first_name').focus(), 100);
            } else {
                alert('Error loading employee data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading employee data');
        });
}

function closeWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// This function is no longer needed as we have separate modals
// Keeping for backward compatibility but it's not used
function resetEmployeeForm() {
    // Function kept for compatibility - not used with separate modals
}

// Employee filtering functions
function clearFilters() {
    document.getElementById('employee-search').value = '';
    document.getElementById('department-filter').value = '';
    document.getElementById('status-filter').value = '';
    filterEmployees();
}

function filterEmployees() {
    const searchTerm = document.getElementById('employee-search').value.toLowerCase();
    const departmentFilter = document.getElementById('department-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    
    const rows = document.querySelectorAll('#employees-table-body tr');
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return; // Skip "no employees" row
        
        const name = row.cells[1].textContent.toLowerCase();
        const department = row.cells[3].textContent;
        const status = row.cells[4].textContent.toLowerCase();
        
        const matchesSearch = name.includes(searchTerm);
        const matchesDepartment = !departmentFilter || department === departmentFilter;
        const matchesStatus = !statusFilter || status.includes(statusFilter);
        
        row.style.display = (matchesSearch && matchesDepartment && matchesStatus) ? '' : 'none';
    });
}

// Add event listeners for filtering
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('employee-search').addEventListener('input', filterEmployees);
    document.getElementById('department-filter').addEventListener('change', filterEmployees);
    document.getElementById('status-filter').addEventListener('change', filterEmployees);
});

// Integration functions for timesheet and shift management
function addTimesheetForEmployee(employeeId, employeeName) {
    // Pre-select employee in timesheet modal
    const employeeSelect = document.getElementById('timesheet_employee_id');
    if (employeeSelect) {
        employeeSelect.value = employeeId;
    }
    
    // Set today's date
    const dateInput = document.getElementById('timesheet_work_date');
    if (dateInput) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    
    openWorkingModal('timesheet-modal');
}


// Helper functions for validation
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'text-danger small';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
    field.classList.add('is-invalid');
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Clear form validation errors
function clearFormErrors(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.querySelectorAll('.text-danger.small').forEach(el => el.remove());
        modal.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }
}

// Populate form with old values after validation error
function populateFormWithOldValues(type) {
    const prefix = type === 'add' ? 'add_' : 'edit_';
    
    @if(old('first_name'))
        const firstNameField = document.getElementById(prefix + 'first_name');
        if (firstNameField) firstNameField.value = '{{ old('first_name') }}';
    @endif
    
    @if(old('last_name'))
        const lastNameField = document.getElementById(prefix + 'last_name');
        if (lastNameField) lastNameField.value = '{{ old('last_name') }}';
    @endif
    
    @if(old('email'))
        const emailField = document.getElementById(prefix + 'email');
        if (emailField) emailField.value = '{{ old('email') }}';
    @endif
    
    @if(old('phone'))
        const phoneField = document.getElementById(prefix + 'phone');
        if (phoneField) phoneField.value = '{{ old('phone') }}';
    @endif
    
    @if(old('position'))
        const positionField = document.getElementById(prefix + 'position');
        if (positionField) positionField.value = '{{ old('position') }}';
    @endif
    
    @if(old('department'))
        const departmentField = document.getElementById(prefix + 'department');
        if (departmentField) departmentField.value = '{{ old('department') }}';
    @endif
    
    @if(old('hire_date'))
        const hireDateField = document.getElementById(prefix + 'hire_date');
        if (hireDateField) hireDateField.value = '{{ old('hire_date') }}';
    @endif
    
    @if(old('salary'))
        const salaryField = document.getElementById(prefix + 'salary');
        if (salaryField) salaryField.value = '{{ old('salary') }}';
    @endif
    
    @if(old('status'))
        const statusField = document.getElementById(prefix + 'status');
        if (statusField) statusField.value = '{{ old('status') }}';
    @endif
}
</script>

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

<script>
// Working Modal JavaScript Functions
function openWorkingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
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
        // Reset form if it exists
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Reset hidden fields
            const employeeId = form.querySelector('#employee-id');
            const formMethod = form.querySelector('#form-method');
            if (employeeId) employeeId.value = '';
            if (formMethod) formMethod.value = 'POST';
            // Reset modal title
            const title = modal.querySelector('.working-modal-title');
            if (title) title.textContent = 'Add Employee';
        }
    }
}

// Employee search and filter functionality
function filterEmployees() {
    const searchTerm = document.getElementById('employee-search').value.toLowerCase();
    const departmentFilter = document.getElementById('department-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    const rows = document.querySelectorAll('#employees-table-body tr');
    
    rows.forEach(row => {
        if (row.cells.length < 6) return; // Skip empty state row
        
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

function clearFilters() {
    document.getElementById('employee-search').value = '';
    document.getElementById('department-filter').value = '';
    document.getElementById('status-filter').value = '';
    filterEmployees();
}

// Cross-module integration functions
function addTimesheetForEmployee(employeeId, employeeName) {
    // Pre-populate the timesheet form with employee data
    document.getElementById('timesheet_employee_id').value = employeeId;
    document.getElementById('timesheet_work_date').value = new Date().toISOString().split('T')[0]; // Today's date
    
    // Update modal title to show employee name
    document.querySelector('#timesheet-modal .working-modal-title').textContent = `Add Timesheet for ${employeeName}`;
    
    // Open the timesheet modal
    openWorkingModal('timesheet-modal');
}

// Function to handle timesheet form submission with success notification
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for search and filters
    const searchInput = document.getElementById('employee-search');
    const departmentFilter = document.getElementById('department-filter');
    const statusFilter = document.getElementById('status-filter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterEmployees);
    }
    if (departmentFilter) {
        departmentFilter.addEventListener('change', filterEmployees);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterEmployees);
    }
    
    // Handle timesheet form submission
    const timesheetForm = document.getElementById('timesheetForm');
    if (timesheetForm) {
        timesheetForm.addEventListener('submit', function(e) {
            // Client-side validation
            const employeeId = document.getElementById('timesheet_employee_id').value;
            const workDate = document.getElementById('timesheet_work_date').value;
            const hoursWorked = document.getElementById('timesheet_hours_worked').value;
            
            if (!employeeId || !workDate || !hoursWorked) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('saveTimesheetBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            }
            const workDate = document.getElementById('timesheet_work_date').value;
            const hoursWorked = document.getElementById('timesheet_hours_worked').value;
            
            if (!employeeId || !workDate || !hoursWorked) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
        });
    }
});

// Function to update employee statistics (can be called after operations)
function updateEmployeeStats() {
    fetch('/api/employees/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.data;
                // Update statistics cards if they exist
                const totalElement = document.querySelector('.stat-number');
                if (totalElement) {
                    totalElement.textContent = stats.total_employees || 0;
                }
            }
        })
        .catch(error => {
            console.log('Stats update not available:', error);
        });
}

// Action button functions
function viewEmployee(id) {
    fetch(`/employees/${id}/view`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const employee = data.employee;
                document.getElementById('view-employee-id').textContent = employee.id;
                document.getElementById('view-employee-name').textContent = `${employee.first_name} ${employee.last_name}`;
                document.getElementById('view-employee-position').textContent = employee.position;
                document.getElementById('view-employee-email').textContent = employee.email;
                document.getElementById('view-employee-department').textContent = employee.department;
                document.getElementById('view-employee-phone').textContent = employee.phone || 'N/A';
                document.getElementById('view-employee-hire-date').textContent = new Date(employee.hire_date).toLocaleDateString();
                document.getElementById('view-employee-status').innerHTML = `<span class="badge bg-${employee.status === 'active' ? 'success' : employee.status === 'inactive' ? 'warning' : 'danger'}">${employee.status}</span>`;
                
                openWorkingModal('employee-view-modal');
            } else {
                alert('Error loading employee details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading employee details');
        });
}

function editEmployee(id) {
    window.location.href = `/employees/${id}/edit`;
}

function editEmployeeFromView() {
    const employeeId = document.getElementById('view-employee-id').textContent;
    closeWorkingModal('employee-view-modal');
    editEmployee(employeeId);
}

function addTimesheet(employeeId) {
    document.getElementById('timesheet_employee_id').value = employeeId;
    const employeeName = document.querySelector(`tr[data-employee-id="${employeeId}"] td:nth-child(2)`).textContent;
    document.getElementById('timesheet-employee-name').textContent = employeeName;
    openWorkingModal('timesheet-modal');
}

function deleteEmployee(id, name) {
    if (confirm(`Are you sure you want to delete employee "${name}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/employees/${id}`;
        
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

// Shift Schedule Integration Functions (removed assignShiftToEmployee function)

function addTimesheetForEmployee(employeeId, employeeName) {
    // Redirect to timesheet management with employee pre-selected
    window.location.href = `/timesheet-management?employee=${employeeId}&action=add&name=${encodeURIComponent(employeeName)}`;
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        emergencyCloseAllModals();
    }
});

// Emergency cleanup function
function emergencyCloseAllModals() {
    // Force close all modals
    const allModals = document.querySelectorAll('.working-modal, .modal, [id*="modal"]');
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

// Force close all modals on page load
document.addEventListener('DOMContentLoaded', function() {
    emergencyCloseAllModals();
});

// View Details Functions (following claims reimbursement pattern)
function viewEmployeeDetails(employeeId) {
    const employeeRow = document.querySelector(`button[onclick="viewEmployeeDetails(${employeeId})"]`)?.closest('tr');
    
    if (employeeRow && employeeRow.cells.length >= 7) {
        document.getElementById('view-employee-details-name').textContent = employeeRow.cells[0].textContent.trim();
        document.getElementById('view-employee-details-position').textContent = employeeRow.cells[1].textContent.trim();
        document.getElementById('view-employee-details-department').textContent = employeeRow.cells[2].textContent.trim();
        document.getElementById('view-employee-details-email').textContent = employeeRow.cells[3].textContent.trim();
        document.getElementById('view-employee-details-phone').textContent = employeeRow.cells[4].textContent.trim();
        document.getElementById('view-employee-details-hire-date').textContent = employeeRow.cells[5].textContent.trim();
        document.getElementById('view-employee-details-status').textContent = employeeRow.cells[6].querySelector('.badge')?.textContent.trim() || 'Unknown';
        openWorkingModal('view-employee-details-modal');
    }
}
</script>

<!-- View Employee Details Modal -->
<div class="working-modal" id="view-employee-details-modal" style="display: none;">
    <div class="working-modal-backdrop" onclick="closeWorkingModal('view-employee-details-modal')"></div>
    <div class="working-modal-dialog">
        <div class="working-modal-content">
            <div class="working-modal-header">
                <h5 class="working-modal-title">Employee Details</h5>
                <button type="button" class="working-modal-close" onclick="closeWorkingModal('view-employee-details-modal')">&times;</button>
            </div>
            <div class="working-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Name:</strong>
                        <p id="view-employee-details-name" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Position:</strong>
                        <p id="view-employee-details-position" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Department:</strong>
                        <p id="view-employee-details-department" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p id="view-employee-details-email" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Phone:</strong>
                        <p id="view-employee-details-phone" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Hire Date:</strong>
                        <p id="view-employee-details-hire-date" class="mb-2">-</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p id="view-employee-details-status" class="mb-2">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Employee ID:</strong>
                        <p id="view-employee-details-id" class="mb-2">-</p>
                    </div>
                </div>
            </div>
            <div class="working-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWorkingModal('view-employee-details-modal')">Close</button>
            </div>
        </div>
    </div>
</style>

<script>
// Enhanced search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('employee-search');
    const departmentFilter = document.getElementById('department-filter');
    const statusFilter = document.getElementById('status-filter');
    const form = document.getElementById('employee-filter-form');
    
    // Submit form on Enter key in search input
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.submit();
            }
        });
    }
    
    // Auto-submit on filter changes
    if (departmentFilter) {
        departmentFilter.addEventListener('change', function() {
            form.submit();
        });
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            form.submit();
        });
    }
    
    // Show loading state during form submission
    form.addEventListener('submit', function() {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Searching...';
            submitBtn.disabled = true;
        }
    });
});

// Employee count display
function updateEmployeeCount() {
    const tableBody = document.getElementById('employees-table-body');
    const rows = tableBody.querySelectorAll('tr');
    const count = rows.length;
    
    // Update count display if exists
    const countDisplay = document.querySelector('.employee-count');
    if (countDisplay) {
        countDisplay.textContent = `${count} employee${count !== 1 ? 's' : ''} found`;
    }
}

// Call on page load
document.addEventListener('DOMContentLoaded', updateEmployeeCount);
</script>

@endsection

<style>
/* Working Modal Centering */
{{ ... }}
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
