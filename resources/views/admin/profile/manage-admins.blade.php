@extends('layouts.hr')

@section('title', 'Manage Admins - Jetlouge Travels')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
            <div class="dashboard-logo me-3">
                <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
            </div>
            <div>
                <h2 class="fw-bold mb-1">Manage Admins</h2>
                <p class="text-muted mb-0">Manage administrator accounts and permissions</p>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.profile.index') }}" class="text-decoration-none">Profile</a></li>
                <li class="breadcrumb-item active" aria-current="page">Manage Admins</li>
            </ol>
        </nav>
    </div>
</div>

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

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0">Administrator Accounts ({{ $admins->count() }})</h5>
        <small class="text-muted">Manage system administrators and their roles</small>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdminModal">
        <i class="fas fa-plus me-2"></i>Add New Admin
    </button>
</div>

<!-- Admins Table -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Admin</th>
                        <th>Role</th>
                        <th>Employee Link</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($admin->profile_picture)
                                        <img src="{{ Storage::url($admin->profile_picture) }}" alt="Profile" 
                                             class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px; background-color: var(--jetlouge-primary); color: white; font-size: 16px;">
                                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $admin->name }}</div>
                                        <small class="text-muted">{{ $admin->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge role-badge" style="background-color: var(--jetlouge-primary);">
                                    {{ $roles[$admin->role] ?? ucfirst(str_replace('_', ' ', $admin->role)) }}
                                </span>
                            </td>
                            <td>
                                @if($admin->employee)
                                    <div class="small">
                                        <strong>{{ $admin->employee->first_name }} {{ $admin->employee->last_name }}</strong><br>
                                        <span class="text-muted">{{ $admin->employee->department }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">Not linked</span>
                                @endif
                            </td>
                            <td>
                                @if($admin->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if($admin->last_login)
                                    <div class="small">
                                        {{ $admin->last_login->format('M d, Y') }}<br>
                                        <span class="text-muted">{{ $admin->last_login->format('h:i A') }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                @if($admin->id !== Auth::id())
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editAdmin({{ $admin->id }}, '{{ $admin->role }}', {{ $admin->is_active ? 'true' : 'false' }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($admin->role !== 'super_admin')
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteAdmin({{ $admin->id }}, '{{ $admin->name }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-info">Current User</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-users-slash text-muted mb-2" style="font-size: 48px;"></i>
                                <p class="text-muted mb-0">No administrators found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Admin Modal -->
<div class="modal fade" id="createAdminModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Create New Admin
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.profile.create-admin') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="create_email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_username" class="form-label fw-bold">Username</label>
                            <input type="text" class="form-control" id="create_username" name="username">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_phone" class="form-label fw-bold">Phone Number</label>
                            <input type="text" class="form-control" id="create_phone" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_role" class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_role" name="role" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $roleKey => $roleName)
                                    @if($roleKey !== 'super_admin')
                                        <option value="{{ $roleKey }}">{{ $roleName }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_employee_id" class="form-label fw-bold">Link to Employee</label>
                            <select class="form-select" id="create_employee_id" name="employee_id">
                                <option value="">-- Not Linked --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->department }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_password" class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="create_password" name="password" required minlength="8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_password_confirmation" class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="create_password_confirmation" name="password_confirmation" required minlength="8">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit me-2"></i>Edit Admin
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAdminForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_role" class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_role" name="role" required>
                            @foreach($roles as $roleKey => $roleName)
                                @if($roleKey !== 'super_admin')
                                    <option value="{{ $roleKey }}">{{ $roleName }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_is_active" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_is_active" name="is_active" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Admin Modal -->
<div class="modal fade" id="deleteAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Admin
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the admin account for <strong id="deleteAdminName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-warning me-2"></i>
                    This action cannot be undone. The admin will lose access to the system immediately.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteAdminForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete Admin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editAdmin(adminId, currentRole, isActive) {
    document.getElementById('editAdminForm').action = `/admin/profile/admins/${adminId}`;
    document.getElementById('edit_role').value = currentRole;
    document.getElementById('edit_is_active').value = isActive ? '1' : '0';
    
    const modal = new bootstrap.Modal(document.getElementById('editAdminModal'));
    modal.show();
}

function deleteAdmin(adminId, adminName) {
    document.getElementById('deleteAdminName').textContent = adminName;
    document.getElementById('deleteAdminForm').action = `/admin/profile/admins/${adminId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteAdminModal'));
    modal.show();
}
</script>

<style>
.card {
    border: none;
    border-radius: 12px;
}

.btn {
    border-radius: 8px;
}

.alert {
    border-radius: 8px;
}

.role-badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.modal-content {
    border-radius: 12px;
}

.modal-header {
    border-bottom: 1px solid #e9ecef;
}

.form-control, .form-select {
    border-radius: 8px;
}
</style>
@endsection
