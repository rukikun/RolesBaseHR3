# HR3 System Views Organization

## New View Structure

### Organized Directories
```
resources/views/
├── admin/                  # Admin-specific views
│   ├── employees/         # Employee management
│   └── profile/           # Admin profiles
├── attendance/            # Attendance management
│   ├── management.blade.php
│   └── time_and_attendance.blade.php
├── auth/                  # Authentication views
├── claims/                # Claims & reimbursements
│   └── reimbursement.blade.php
├── components/            # Reusable components
│   └── comprehensive-hr-menu.blade.php
├── dashboard/             # Dashboard views
│   ├── index.blade.php    (HR Dashboard)
│   └── admin.blade.php    (Admin Dashboard)
├── employee_ess_modules/  # Employee self-service
├── landing/               # Landing pages
│   └── index.blade.php
├── layouts/               # Layout templates
├── leaves/                # Leave management
│   └── management.blade.php
├── portal/                # Portal selection
│   └── selection.blade.php
├── profile/               # User profiles
├── settings/              # System settings
│   └── index.blade.php
├── shifts/                # Shift management
│   └── schedule_management.blade.php
└── timesheets/           # Timesheet management
    └── management.blade.php
```

## View Mappings

### Old → New View References
- `dashboard` → `dashboard.index`
- `admin_dashboard` → `dashboard.admin`
- `attendance_management` → `attendance.management`
- `TimeAndAttendance` → `attendance.time_and_attendance`
- `timesheet_management` → `timesheets.management`
- `shift_schedule_management` → `shifts.schedule_management`
- `leave_management` → `leaves.management`
- `claims_reimbursement` → `claims.reimbursement`
- `employees` → `admin.employees.index`
- `portal_selection` → `portal.selection`
- `landing` → `landing.index`

## Benefits

### Organization Benefits
- ✅ **Logical Grouping**: Related views grouped by functionality
- ✅ **Easy Navigation**: Clear directory structure
- ✅ **Maintainability**: Easier to locate and update views
- ✅ **Scalability**: Room for additional views in each category

### Development Benefits
- ✅ **Team Collaboration**: Standardized structure for developers
- ✅ **Code Organization**: Clear separation of concerns
- ✅ **Professional Structure**: Industry-standard Laravel organization
- ✅ **Future-Proof**: Organized for continued development

## Usage

### Controller View References
```php
// Dashboard views
return view('dashboard.index');        // HR Dashboard
return view('dashboard.admin');        // Admin Dashboard

// Module-specific views
return view('attendance.management');   // Attendance Management
return view('timesheets.management');  // Timesheet Management
return view('shifts.schedule_management'); // Shift Management
return view('leaves.management');      // Leave Management
return view('claims.reimbursement');   // Claims & Reimbursement

// Settings
return view('settings.index');         // System Settings
```

### Route Definitions
```php
Route::get('/dashboard', [HRDashboardController::class, 'index'])->name('dashboard');
Route::get('/admin/dashboard', function() { return view('dashboard.admin'); })->name('admin.dashboard');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
```

Generated on: 2025-10-05 17:39:59
