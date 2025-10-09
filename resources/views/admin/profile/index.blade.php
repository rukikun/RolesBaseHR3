@extends('layouts.hr')

@section('title', 'Admin Profile - Jetlouge Travels')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-3">
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
            <div class="card-body text-center py-3">
                <!-- Profile Picture -->
                <div class="profile-picture-container mb-2">
                    @if($user->profile_picture)
                        <img src="{{ Storage::url($user->profile_picture) }}" alt="Profile Picture" 
                             class="rounded-circle profile-picture" width="80" height="80">
                    @else
                        <div class="profile-picture-placeholder rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; background-color: var(--jetlouge-primary); color: white; font-size: 32px; margin: 0 auto;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <!-- User Info -->
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-3 small">{{ $user->email }}</p>

                <!-- Action Buttons -->
                <div class="d-grid gap-1">
                    <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit Profile
                    </a>
                    <a href="{{ route('admin.profile.change-password') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-key me-1"></i>Change Password
                    </a>
                    @if($user->role === 'super_admin')
                        <a href="{{ route('admin.profile.manage-admins') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-users-cog me-1"></i>Manage Admins
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <!-- Profile Details Card -->
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-light py-2">
                <h6 class="card-title mb-0">
                    <i class="fas fa-user-circle me-2"></i>Profile Details
                </h6>
            </div>
            <div class="card-body py-3">
                @php
                    $profileFields = [
                        ['label' => 'Full Name', 'value' => $user->name, 'icon' => 'fas fa-user'],
                        ['label' => 'Email Address', 'value' => $user->email, 'icon' => 'fas fa-envelope'],
                        ['label' => 'Username', 'value' => $user->username ?? 'Not set', 'icon' => 'fas fa-at'],
                        ['label' => 'Phone Number', 'value' => $user->phone ?? 'Not set', 'icon' => 'fas fa-phone'],
                        ['label' => 'Role', 'value' => ucwords(str_replace('_', ' ', $user->role ?? 'employee')), 'icon' => 'fas fa-user-tag'],
                        ['label' => 'Department', 'value' => $user->department ?? 'Not set', 'icon' => 'fas fa-building'],
                        ['label' => 'Work Location', 'value' => $user->work_location ?? 'Not set', 'icon' => 'fas fa-map-marker-alt'],
                        ['label' => 'Last Login', 'value' => $user->last_login ? $user->last_login->format('M d, Y h:i A') : 'Never', 'icon' => 'fas fa-clock'],
                        ['label' => 'Account Created', 'value' => $user->created_at->format('M d, Y h:i A'), 'icon' => 'fas fa-calendar-plus']
                    ];
                @endphp

                @foreach($profileFields as $field)
                    <div class="mb-2">
                        <label class="form-label fw-bold text-muted small">
                            <i class="{{ $field['icon'] }} me-1"></i>{{ $field['label'] }}
                        </label>
                        <p class="mb-0 small">
                            @if(isset($field['badge']) && $field['badge'])
                                <span class="badge" style="background-color: var(--jetlouge-primary);">
                                    {{ $field['value'] }}
                                </span>
                            @else
                                {{ $field['value'] }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-8">

            <!-- Account Statistics Card -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Account Statistics
                    </h6>
                </div>
                <div class="card-body py-3">
                    @php
                        // Format account age properly
                        $accountAge = $userStats['account_age_days'];
                        if ($accountAge < 1) {
                            $accountAgeText = 'Today';
                        } elseif ($accountAge == 1) {
                            $accountAgeText = '1 day';
                        } else {
                            $accountAgeText = floor($accountAge) . ' days';
                        }

                        $stats = [
                            ['label' => 'Total Logins', 'value' => $userStats['total_logins'], 'icon' => 'fas fa-sign-in-alt', 'color' => 'success'],
                            ['label' => 'Profile Updates', 'value' => $userStats['profile_updates'], 'icon' => 'fas fa-edit', 'color' => 'info'],
                            ['label' => 'Account Age', 'value' => $accountAgeText, 'icon' => 'fas fa-birthday-cake', 'color' => 'warning'],
                            ['label' => 'Last Activity', 'value' => $userStats['last_activity'] ? $userStats['last_activity']->diffForHumans() : 'No activity', 'icon' => 'fas fa-clock', 'color' => 'primary']
                        ];
                    @endphp

                    <div class="row">
                        @foreach($stats as $stat)
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon me-2">
                                        <i class="{{ $stat['icon'] }} text-{{ $stat['color'] }}" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 small">{{ $stat['value'] }}</h6>
                                        <small class="text-muted">{{ $stat['label'] }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Activity Card -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Recent Activity
                    </h6>
                </div>
                <div class="card-body py-3">
                    @if($recentActivities->count() > 0)
                        <div class="activity-timeline">
                            @foreach($recentActivities as $activity)
                                <div class="activity-item d-flex align-items-start mb-2">
                                    <div class="activity-icon me-2">
                                        <i class="{{ $activity->getActivityIcon() }}" style="font-size: 1rem;"></i>
                                    </div>
                                    <div class="activity-content flex-grow-1">
                                        <p class="mb-1 small">{{ $activity->description }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ $activity->performed_at ? $activity->performed_at->diffForHumans() : $activity->created_at->diffForHumans() }}
                                            @if($activity->ip_address)
                                                <span class="ms-2">
                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $activity->ip_address }}
                                                </span>
                                            @endif
                                        </small>
                                        @if($activity->metadata && count($activity->metadata) > 0)
                                            <div class="activity-metadata mt-1">
                                                <small class="text-info">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Changes: {{ implode(', ', array_keys($activity->metadata)) }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No recent activity found</p>
                        </div>
                    @endif
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

.activity-timeline {
    position: relative;
}

.activity-item {
    position: relative;
    padding-left: 0;
    border-left: 2px solid #e9ecef;
    margin-left: 1rem;
    padding-left: 2rem;
}

.activity-item:last-child {
    border-left: none;
}

.activity-icon {
    position: absolute;
    left: -2rem;
    top: 0;
    background: white;
    padding: 0.25rem;
    border-radius: 50%;
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e9ecef;
}

.activity-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-left: 0.5rem;
}

.activity-metadata {
    background: rgba(13, 110, 253, 0.1);
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    margin-top: 0.5rem;
}

.stat-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(13, 110, 253, 0.1);
    border-radius: 50%;
}

/* Compact spacing adjustments */
.card-body {
    padding: 1rem !important;
}

.card-header {
    padding: 0.75rem 1rem !important;
}

.activity-content {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 0.75rem;
    margin-left: 0.25rem;
}

.activity-item {
    border-left: 2px solid #e9ecef;
    margin-left: 0.75rem;
    padding-left: 1.5rem;
}

.activity-icon {
    position: absolute;
    left: -1.5rem;
    top: 0;
    background: white;
    padding: 0.2rem;
    border-radius: 50%;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e9ecef;
}
</style>
@endsection
