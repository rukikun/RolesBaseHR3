@extends('layouts.hr')

@section('title', 'Change Password - Jetlouge Travels')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
            <div class="dashboard-logo me-3">
                <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
            </div>
            <div>
                <h2 class="fw-bold mb-1">Change Password</h2>
                <p class="text-muted mb-0">Update your account password for security</p>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.profile.index') }}" class="text-decoration-none">Profile</a></li>
                <li class="breadcrumb-item active" aria-current="page">Change Password</li>
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
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-key me-2"></i>Change Password
                </h5>
            </div>
            <div class="card-body">
                <!-- Security Notice -->
                <div class="alert alert-info mb-4">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>Security Guidelines:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Use at least 8 characters</li>
                        <li>Include uppercase and lowercase letters</li>
                        <li>Include numbers and special characters</li>
                        <li>Avoid using personal information</li>
                    </ul>
                </div>

                <form action="{{ route('admin.profile.update-password') }}" method="POST" id="changePasswordForm">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye" id="current_password_icon"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                <i class="fas fa-eye" id="password_icon"></i>
                            </button>
                        </div>
                        <div class="form-text">Minimum 8 characters required</div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        
                        <!-- Password Strength Indicator -->
                        <div class="mt-2">
                            <div class="password-strength-bar">
                                <div class="password-strength-fill" id="passwordStrengthFill"></div>
                            </div>
                            <small class="text-muted" id="passwordStrengthText">Password strength will appear here</small>
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-bold">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                <i class="fas fa-eye" id="password_confirmation_icon"></i>
                            </button>
                        </div>
                        <div class="form-text" id="passwordMatchText"></div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.profile.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    let feedback = [];
    
    if (password.length >= 8) strength += 1;
    else feedback.push('At least 8 characters');
    
    if (/[a-z]/.test(password)) strength += 1;
    else feedback.push('Lowercase letter');
    
    if (/[A-Z]/.test(password)) strength += 1;
    else feedback.push('Uppercase letter');
    
    if (/[0-9]/.test(password)) strength += 1;
    else feedback.push('Number');
    
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    else feedback.push('Special character');
    
    return { strength, feedback };
}

// Update password strength indicator
function updatePasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthFill = document.getElementById('passwordStrengthFill');
    const strengthText = document.getElementById('passwordStrengthText');
    
    if (password.length === 0) {
        strengthFill.style.width = '0%';
        strengthText.textContent = 'Password strength will appear here';
        strengthText.className = 'text-muted';
        return;
    }
    
    const { strength, feedback } = checkPasswordStrength(password);
    const percentage = (strength / 5) * 100;
    
    strengthFill.style.width = percentage + '%';
    
    if (strength <= 2) {
        strengthFill.className = 'password-strength-fill bg-danger';
        strengthText.textContent = 'Weak - Missing: ' + feedback.join(', ');
        strengthText.className = 'text-danger';
    } else if (strength <= 3) {
        strengthFill.className = 'password-strength-fill bg-warning';
        strengthText.textContent = 'Fair - Missing: ' + feedback.join(', ');
        strengthText.className = 'text-warning';
    } else if (strength <= 4) {
        strengthFill.className = 'password-strength-fill bg-info';
        strengthText.textContent = 'Good - Missing: ' + feedback.join(', ');
        strengthText.className = 'text-info';
    } else {
        strengthFill.className = 'password-strength-fill bg-success';
        strengthText.textContent = 'Strong password';
        strengthText.className = 'text-success';
    }
}

// Check password match
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const matchText = document.getElementById('passwordMatchText');
    
    if (confirmation.length === 0) {
        matchText.textContent = '';
        return;
    }
    
    if (password === confirmation) {
        matchText.textContent = 'Passwords match';
        matchText.className = 'form-text text-success';
    } else {
        matchText.textContent = 'Passwords do not match';
        matchText.className = 'form-text text-danger';
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const confirmationField = document.getElementById('password_confirmation');
    
    passwordField.addEventListener('input', function() {
        updatePasswordStrength();
        checkPasswordMatch();
    });
    
    confirmationField.addEventListener('input', checkPasswordMatch);
    
    // Form validation
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        
        if (password !== confirmation) {
            e.preventDefault();
            alert('Passwords do not match. Please check and try again.');
            return false;
        }
        
        const { strength } = checkPasswordStrength(password);
        if (strength < 3) {
            if (!confirm('Your password is weak. Are you sure you want to continue?')) {
                e.preventDefault();
                return false;
            }
        }
    });
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

.password-strength-bar {
    width: 100%;
    height: 6px;
    background-color: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.password-strength-fill {
    height: 100%;
    width: 0%;
    transition: width 0.3s ease, background-color 0.3s ease;
    border-radius: 3px;
}

.input-group .btn {
    border-radius: 0 8px 8px 0;
}

.input-group .form-control {
    border-radius: 8px 0 0 8px;
}
</style>
@endsection
