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
