@extends('layouts.hr')

@section('title', 'Admin Profile - Jetlouge Travels')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
            <div class="dashboard-logo me-3">
                <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
            </div>
            <div>
                <h2 class="fw-bold mb-1">Admin Profile</h2>
                <p class="text-muted mb-0">Manage your profile information and account settings</p>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profile</li>
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

<div class="row">
    <!-- Profile Information Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <!-- Profile Picture -->
                <div class="profile-picture-container mb-3">
                    @if($user->profile_picture)
                        <img src="{{ Storage::url($user->profile_picture) }}" alt="Profile Picture" 
                             class="rounded-circle profile-picture" width="120" height="120">
                    @else
                        <div class="profile-picture-placeholder rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px; background-color: var(--jetlouge-primary); color: white; font-size: 48px; margin: 0 auto;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <!-- User Info -->
                <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->email }}</p>
                
                <!-- Role Badge -->
                <span class="badge role-badge mb-3" style="background-color: var(--jetlouge-primary);">
                    {{ $roles[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role)) }}
                </span>

                <!-- Status Badge -->
                <div class="mb-3">
                    @if($user->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                    <a href="{{ route('admin.profile.change-password') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-key me-2"></i>Change Password
                    </a>
                    @if($user->role === 'super_admin')
                        <a href="{{ route('admin.profile.manage-admins') }}" class="btn btn-outline-info">
                            <i class="fas fa-users-cog me-2"></i>Manage Admins
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Details Card -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-circle me-2"></i>Profile Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">Full Name</label>
                        <p class="mb-0">{{ $user->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">Email Address</label>
                        <p class="mb-0">{{ $user->email }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">Username</label>
                        <p class="mb-0">{{ $user->username ?? 'Not set' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">Phone Number</label>
                        <p class="mb-0">{{ $user->phone ?? 'Not set' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">Role</label>
                        <p class="mb-0">
                            <span class="badge" style="background-color: var(--jetlouge-primary);">
                                {{ $roles[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">Last Login</label>
                        <p class="mb-0">
                            @if($user->last_login)
                                {{ $user->last_login->format('M d, Y h:i A') }}
                            @else
                                Never
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">Account Created</label>
                        <p class="mb-0">{{ $user->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.profile-picture {
    object-fit: cover;
    border: 4px solid var(--jetlouge-light);
}

.profile-picture-placeholder {
    border: 4px solid var(--jetlouge-light);
}

.role-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: 1px solid #e9ecef;
}

.btn {
    border-radius: 8px;
}

.alert {
    border-radius: 8px;
}
</style>
@endsection
