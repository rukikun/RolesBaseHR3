@extends('layouts.hr')

@section('title', 'Edit Profile - Jetlouge Travels')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
            <div class="dashboard-logo me-3">
                <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
            </div>
            <div>
                <h2 class="fw-bold mb-1">Edit Profile</h2>
                <p class="text-muted mb-0">Update your profile information and settings</p>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.profile.index') }}" class="text-decoration-none">Profile</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Alert Messages -->
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

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-edit me-2"></i>Edit Profile Information
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Profile Picture Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-bold">Profile Picture</label>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @if($user->profile_picture)
                                        <img src="{{ Storage::url($user->profile_picture) }}" alt="Current Profile Picture" 
                                             class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 80px; height: 80px; background-color: var(--jetlouge-primary); color: white; font-size: 32px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" class="form-control" id="profile_picture" name="profile_picture" 
                                           accept="image/jpeg,image/png,image/jpg,image/gif">
                                    <div class="form-text">Upload a new profile picture (JPEG, PNG, JPG, GIF - Max: 2MB)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label fw-bold">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                   id="username" name="username" value="{{ old('username', $user->username) }}">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-bold">Phone Number</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Additional Profile Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="role" class="form-label fw-bold">Role</label>
                            <input type="text" class="form-control" 
                                   id="role" name="role" 
                                   value="{{ ucwords(str_replace('_', ' ', $user->role ?? 'employee')) }}" 
                                   readonly 
                                   style="background-color: #f8f9fa; cursor: not-allowed;"
                                   title="Role cannot be changed. Contact administrator to modify your role.">
                            <small class="text-muted">Role is assigned by administrator and cannot be changed.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="department" class="form-label fw-bold">Department</label>
                            <select class="form-select @error('department') is-invalid @enderror" id="department" name="department">
                                <option value="">Select Department</option>
                                <option value="HR" {{ old('department', $user->department ?? '') == 'HR' ? 'selected' : '' }}>Human Resources</option>
                                <option value="IT" {{ old('department', $user->department ?? '') == 'IT' ? 'selected' : '' }}>Information Technology</option>
                                <option value="Finance" {{ old('department', $user->department ?? '') == 'Finance' ? 'selected' : '' }}>Finance</option>
                                <option value="Marketing" {{ old('department', $user->department ?? '') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                                <option value="Operations" {{ old('department', $user->department ?? '') == 'Operations' ? 'selected' : '' }}>Operations</option>
                                <option value="Sales" {{ old('department', $user->department ?? '') == 'Sales' ? 'selected' : '' }}>Sales</option>
                            </select>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="manager_id" class="form-label fw-bold">Direct Manager</label>
                            <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                                <option value="">Select Manager</option>
                                @if(isset($employees))
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" 
                                            {{ old('manager_id', $user->manager_id ?? '') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->first_name }} {{ $employee->last_name }} - {{ $employee->department }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('manager_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="work_location" class="form-label fw-bold">Work Location</label>
                            <select class="form-select @error('work_location') is-invalid @enderror" id="work_location" name="work_location">
                                <option value="">Select Location</option>
                                <option value="Main Office" {{ old('work_location', $user->work_location ?? '') == 'Main Office' ? 'selected' : '' }}>Main Office</option>
                                <option value="Branch Office" {{ old('work_location', $user->work_location ?? '') == 'Branch Office' ? 'selected' : '' }}>Branch Office</option>
                                <option value="Remote" {{ old('work_location', $user->work_location ?? '') == 'Remote' ? 'selected' : '' }}>Remote</option>
                                <option value="Hybrid" {{ old('work_location', $user->work_location ?? '') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                            </select>
                            @error('work_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-user me-2"></i>Personal Information</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label fw-bold">Date of Birth</label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth ?? '') }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label fw-bold">Gender</label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', $user->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $user->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender', $user->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                <option value="Prefer not to say" {{ old('gender', $user->gender ?? '') == 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="address" class="form-label fw-bold">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" 
                                      placeholder="Enter complete address">{{ old('address', $user->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-phone-alt me-2"></i>Emergency Contact</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="emergency_contact_name" class="form-label fw-bold">Emergency Contact Name</label>
                            <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                   id="emergency_contact_name" name="emergency_contact_name" 
                                   value="{{ old('emergency_contact_name', $user->emergency_contact_name ?? '') }}" 
                                   placeholder="Full name of emergency contact">
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="emergency_contact_phone" class="form-label fw-bold">Emergency Contact Phone</label>
                            <input type="text" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                   id="emergency_contact_phone" name="emergency_contact_phone" 
                                   value="{{ old('emergency_contact_phone', $user->emergency_contact_phone ?? '') }}" 
                                   placeholder="Phone number">
                            @error('emergency_contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="emergency_contact_relationship" class="form-label fw-bold">Relationship</label>
                            <select class="form-select @error('emergency_contact_relationship') is-invalid @enderror" 
                                    id="emergency_contact_relationship" name="emergency_contact_relationship">
                                <option value="">Select Relationship</option>
                                <option value="Spouse" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                                <option value="Parent" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Parent' ? 'selected' : '' }}>Parent</option>
                                <option value="Sibling" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Sibling' ? 'selected' : '' }}>Sibling</option>
                                <option value="Child" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Child' ? 'selected' : '' }}>Child</option>
                                <option value="Friend" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Friend' ? 'selected' : '' }}>Friend</option>
                                <option value="Other" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('emergency_contact_relationship')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.profile.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const permissionsDiv = document.getElementById('role-permissions');
    
    const rolePermissions = {
        'super_admin': {
            'description': 'Full system access with all permissions',
            'permissions': ['System Configuration', 'User Management', 'All HR Modules', 'Reports & Analytics', 'Audit Logs']
        },
        'admin': {
            'description': 'Administrative access to most system features',
            'permissions': ['User Management', 'All HR Modules', 'Reports & Analytics']
        },
        'hr_manager': {
            'description': 'Human Resources management capabilities',
            'permissions': ['Employee Management', 'Leave Management', 'Claims Management', 'Reports & Analytics']
        },
        'hr_scheduler': {
            'description': 'Scheduling and timesheet management',
            'permissions': ['Shift Management', 'Schedule Management', 'Timesheet Approval']
        },
        'attendance_admin': {
            'description': 'Time and attendance administration',
            'permissions': ['Time & Attendance', 'Leave Management', 'Timesheet Management']
        }
    };
    
    function updatePermissions() {
        const selectedRole = roleSelect.value;
        const roleData = rolePermissions[selectedRole];
        
        if (roleData) {
            let html = `<p class="mb-2"><strong>${roleData.description}</strong></p>`;
            html += '<div class="row">';
            roleData.permissions.forEach(permission => {
                html += `<div class="col-md-6 mb-1">
                    <i class="fas fa-check-circle text-success me-2"></i>${permission}
                </div>`;
            });
            html += '</div>';
            permissionsDiv.innerHTML = html;
        }
    }
    
    // Update permissions on page load
    updatePermissions();
    
    // Update permissions when role changes
    roleSelect.addEventListener('change', updatePermissions);
});
</script>

<style>
.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: 1px solid #e9ecef;
}

.form-control, .form-select {
    border-radius: 8px;
}

.btn {
    border-radius: 8px;
}

.alert {
    border-radius: 8px;
}
</style>
@endsection
