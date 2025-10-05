@extends('layouts.hr')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid">
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">System Settings</h2>
        <p class="text-muted mb-0">Configure and manage HR3 system settings</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">System Settings</li>
      </ol>
    </nav>
  </div>
</div>

    <!-- Settings Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs modern-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                <i class="fas fa-cog me-2"></i>General
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                                <i class="fas fa-bell me-2"></i>Notifications
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-shield-alt me-2"></i>Security
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="settingsTabContent">
                        <!-- General Settings -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <div class="settings-section">
                                <h5 class="settings-section-title">General System Settings</h5>
                                <div class="settings-form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">System Name</label>
                                                <input type="text" class="form-control" value="HR3 System" name="system_name">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Default Language</label>
                                                <select class="form-select" name="default_language">
                                                    <option value="en" selected>English</option>
                                                    <option value="es">Spanish</option>
                                                    <option value="fr">French</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Date Format</label>
                                                <select class="form-select" name="date_format">
                                                    <option value="Y-m-d" selected>YYYY-MM-DD</option>
                                                    <option value="m/d/Y">MM/DD/YYYY</option>
                                                    <option value="d/m/Y">DD/MM/YYYY</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Time Format</label>
                                                <select class="form-select" name="time_format">
                                                    <option value="24" selected>24 Hour (HH:MM)</option>
                                                    <option value="12">12 Hour (h:mm AM/PM)</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Timezone</label>
                                                <select class="form-select" name="timezone">
                                                    <option value="Asia/Manila" selected>Asia/Manila (GMT+8)</option>
                                                    <option value="UTC">UTC (GMT+0)</option>
                                                    <option value="America/New_York">America/New_York (GMT-5)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Notifications Settings -->
                        <div class="tab-pane fade" id="notifications" role="tabpanel">
                            <div class="settings-section">
                                <h5 class="settings-section-title">Notification Preferences</h5>
                                <div class="settings-form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="form-subsection-title">Email Notifications</h6>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="email_attendance" id="emailAttendance" checked>
                                                    <label class="form-check-label" for="emailAttendance">
                                                        Attendance Alerts
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="email_leave" id="emailLeave" checked>
                                                    <label class="form-check-label" for="emailLeave">
                                                        Leave Requests
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="email_payroll" id="emailPayroll">
                                                    <label class="form-check-label" for="emailPayroll">
                                                        Payroll Notifications
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="form-subsection-title">System Notifications</h6>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="system_alerts" id="systemAlerts" checked>
                                                    <label class="form-check-label" for="systemAlerts">
                                                        System Alerts
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="maintenance_notices" id="maintenanceNotices" checked>
                                                    <label class="form-check-label" for="maintenanceNotices">
                                                        Maintenance Notices
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Notification Frequency</label>
                                                <select class="form-select" name="notification_frequency">
                                                    <option value="immediate" selected>Immediate</option>
                                                    <option value="hourly">Hourly</option>
                                                    <option value="daily">Daily</option>
                                                    <option value="weekly">Weekly</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="settings-section">
                                <h5 class="settings-section-title">Security Configuration</h5>
                                <div class="settings-form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Password Minimum Length</label>
                                                <input type="number" class="form-control" name="password_min_length" value="8" min="6" max="20">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Session Timeout (minutes)</label>
                                                <input type="number" class="form-control" name="session_timeout" value="120" min="15" max="480">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Max Login Attempts</label>
                                                <input type="number" class="form-control" name="max_login_attempts" value="5" min="3" max="10">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="require_2fa" id="require2FA">
                                                    <label class="form-check-label" for="require2FA">
                                                        Require Two-Factor Authentication
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="password_complexity" id="passwordComplexity" checked>
                                                    <label class="form-check-label" for="passwordComplexity">
                                                        Require Complex Passwords
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="audit_logging" id="auditLogging" checked>
                                                    <label class="form-check-label" for="auditLogging">
                                                        Enable Audit Logging
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-secondary" onclick="resetSettings()">
                            <i class="fas fa-undo me-1"></i>Reset to Defaults
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="exportSettings()">
                                <i class="fas fa-download me-1"></i>Export Settings
                            </button>
                            <button type="button" class="btn btn-success" onclick="saveAllSettings()">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Settings JavaScript -->
<script>
function saveAllSettings() {
    // Show loading state
    const saveBtn = document.querySelector('button[onclick="saveAllSettings()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    saveBtn.disabled = true;

    // Collect all form data
    const formData = new FormData();
    
    // Get all input, select, and textarea elements
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        if (input.type === 'checkbox') {
            formData.append(input.name, input.checked ? '1' : '0');
        } else if (input.type === 'file') {
            if (input.files[0]) {
                formData.append(input.name, input.files[0]);
            }
        } else if (input.name) {
            formData.append(input.name, input.value);
        }
    });

    // Simulate API call (replace with actual endpoint)
    setTimeout(() => {
        // Reset button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        
        // Show success message
        showAlert('Settings saved successfully!', 'success');
    }, 2000);
}

function resetSettings() {
    if (confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
        // Reset form values to defaults
        location.reload();
    }
}

function exportSettings() {
    // Collect settings data
    const settings = {};
    const inputs = document.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        if (input.name) {
            if (input.type === 'checkbox') {
                settings[input.name] = input.checked;
            } else {
                settings[input.name] = input.value;
            }
        }
    });

    // Create and download JSON file
    const dataStr = JSON.stringify(settings, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = 'hr3_settings_' + new Date().toISOString().split('T')[0] + '.json';
    link.click();
    
    showAlert('Settings exported successfully!', 'info');
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Initialize tooltips and other Bootstrap components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
/* Page Header Styling - Standard Module Style */
.page-header-container {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 24px;
    border: 1px solid #e1e5e9;
}

.page-header {
    width: 100%;
}

.dashboard-logo .logo-img {
    height: 50px;
    width: auto;
}

.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}

.breadcrumb-item a {
    color: #1976d2;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #666;
}

/* Settings Form Styling */
.settings-section {
    padding: 0;
}

.settings-section-title {
    color: #333;
    font-weight: 600;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e9ecef;
}

.settings-form {
    padding: 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 8px;
}

.form-subsection-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
    font-size: 16px;
}

/* Modern Tab Styling - Timesheet Management Style */
.modern-tabs {
    border-bottom: none !important;
    background: transparent !important;
    padding: 0 !important;
    margin: 0 !important;
}

.modern-tabs .nav-item {
    margin-right: 8px;
}

.modern-tabs .nav-link {
    border: none !important;
    border-radius: 25px !important;
    padding: 12px 24px !important;
    font-weight: 500 !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
    background: #f8f9fa !important;
    color: #6c757d !important;
    margin-bottom: 0 !important;
    position: relative !important;
    overflow: hidden !important;
}

.modern-tabs .nav-link:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    background: #e9ecef !important;
    color: #495057 !important;
}

.modern-tabs .nav-link.active {
    background: linear-gradient(135deg, #007bff, #0056b3) !important;
    color: white !important;
    font-weight: 600 !important;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4) !important;
    transform: translateY(-2px) !important;
}

.modern-tabs .nav-link.active:hover {
    background: linear-gradient(135deg, #0056b3, #007bff) !important;
    color: white !important;
}

/* Individual tab colors */
.modern-tabs #general-tab.active {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4) !important;
}

.modern-tabs #notifications-tab.active {
    background: linear-gradient(135deg, #ffc107, #fd7e14) !important;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4) !important;
    color: #212529 !important;
}

.modern-tabs #security-tab.active {
    background: linear-gradient(135deg, #dc3545, #c82333) !important;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4) !important;
}

.modern-tabs .nav-link i {
    font-size: 16px !important;
}

/* Card Styling */
.card {
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0;
}

.card-body {
    padding: 30px;
}

.card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    border-radius: 0 0 8px 8px;
    padding: 20px 30px;
}

/* Form Controls */
.form-control, .form-select {
    border: 1px solid #d1d3e2;
    border-radius: 6px;
    padding: 10px 12px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #1976d2;
    box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
}

.form-check-input {
    margin-top: 0.25rem;
}

.form-check-label {
    font-weight: normal;
    color: #495057;
    margin-left: 8px;
}

/* Button Styling */
.btn-success {
    background: linear-gradient(135deg, #4caf50, #45a049);
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
    transition: all 0.2s ease;
}

.btn-success:hover {
    background: linear-gradient(135deg, #45a049, #4caf50);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4);
}

.btn-secondary {
    background-color: #6c757d;
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-weight: 500;
}

.btn-outline-primary {
    border-color: #1976d2;
    color: #1976d2;
    border-radius: 6px;
    padding: 10px 20px;
    font-weight: 500;
}

.btn-outline-primary:hover {
    background-color: #1976d2;
    border-color: #1976d2;
}

/* Alert Styling */
.alert {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 6px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .page-breadcrumb {
        margin-left: 0;
        margin-top: 10px;
    }
    
    .page-actions {
        margin-left: 0;
        margin-top: 10px;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .card-footer {
        padding: 15px 20px;
    }
}
</style>
@endsection
