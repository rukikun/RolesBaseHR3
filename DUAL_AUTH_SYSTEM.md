# HR3 System Dual Authentication Implementation

## Overview
Successfully implemented a dual authentication system for the HR3 System where:
- **Admin Portal** uses the `users` table for administrators, HR staff, and managers
- **Employee Portal** uses the `employees` table for regular employees

## System Architecture

### Admin Portal Authentication
- **Table**: `users`
- **Guard**: `web` (default)
- **Provider**: `users`
- **Model**: `App\Models\User`
- **Login URL**: `/admin/login`
- **Registration URL**: `/register`
- **Target Users**: Administrators, HR Managers, System Admins

### Employee Portal Authentication  
- **Table**: `employees`
- **Guard**: `employee`
- **Provider**: `employees`
- **Model**: `App\Models\Employee`
- **Login URL**: `/employee/login`
- **Dashboard URL**: `/employee/dashboard`
- **Target Users**: Regular employees, supervisors, team leads

## Authentication Configuration

### config/auth.php
```php
'defaults' => [
    'guard' => 'web',           // Default guard uses users table
    'passwords' => 'users',     // Password resets for users table
],

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',   // Admin portal uses users table
    ],
    'employee' => [
        'driver' => 'session',
        'provider' => 'employees', // Employee portal uses employees table
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    'employees' => [
        'driver' => 'eloquent',
        'model' => App\Models\Employee::class,
    ],
],
```

## Controllers

### AdminAuthController (AuthController.php)
- **Purpose**: Handles admin portal authentication
- **Guard**: `web` (uses users table)
- **Features**:
  - Login with role-based redirects
  - User activity logging
  - Session management
  - Password validation

### EmployeeAuthController.php
- **Purpose**: Handles employee portal authentication
- **Guard**: `employee` (uses employees table)
- **Features**:
  - Employee login with status updates
  - Online status tracking
  - ESS portal redirects
  - Employee-specific session handling

### RegisterController.php
- **Purpose**: Handles admin user registration
- **Target**: Creates users in `users` table
- **Features**:
  - Role-based registration
  - Admin account creation
  - Input validation

## Database Structure

### Users Table (Admin Portal)
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role VARCHAR(255) DEFAULT 'admin',
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    last_login TIMESTAMP NULL,
    profile_picture VARCHAR(255),
    job_title VARCHAR(255),
    department VARCHAR(255),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Employees Table (Employee Portal)
```sql
CREATE TABLE employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role ENUM('employee', 'supervisor', 'team_lead') DEFAULT 'employee',
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    position VARCHAR(255),
    department VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    hire_date DATE,
    online_status VARCHAR(20) DEFAULT 'offline',
    last_activity TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## User Roles

### Admin Portal Roles (users table)
- **super_admin**: Full system access
- **admin**: Administrative functions
- **hr_manager**: HR management functions
- **hr_scheduler**: Scheduling and timesheet management
- **attendance_admin**: Attendance and leave management

### Employee Portal Roles (employees table)
- **employee**: Regular employee access
- **supervisor**: Supervisory functions
- **team_lead**: Team leadership functions

## Authentication Flow

### Admin Portal Login Flow
1. User visits `/admin/login`
2. Enters credentials
3. System authenticates against `users` table using `web` guard
4. On success, redirects to HR dashboard (`/dashboard`)
5. Session established with admin privileges

### Employee Portal Login Flow
1. Employee visits `/employee/login`
2. Enters credentials
3. System authenticates against `employees` table using `employee` guard
4. On success, updates online status and redirects to ESS dashboard
5. Session established with employee privileges

## Route Configuration

### Admin Routes
```php
// Admin authentication routes
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin registration
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.store');
```

### Employee Routes
```php
// Employee authentication routes
Route::get('/employee/login', [EmployeeAuthController::class, 'showLoginForm'])->name('employee.login');
Route::post('/employee/login', [EmployeeAuthController::class, 'login'])->name('employee.login.submit');
Route::post('/employee/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');
```

## Test Accounts

### Admin Portal Test Accounts (users table)
| Email | Password | Role | Access |
|-------|----------|------|--------|
| testadmin@jetlouge.com | password123 | admin | Full admin access |

### Employee Portal Test Accounts (employees table)
| Email | Password | Role | Access |
|-------|----------|------|--------|
| testemployee@jetlouge.com | password123 | employee | Employee self-service |

## Security Features

### Password Security
- **Hashing**: All passwords hashed using Laravel Hash facade
- **Validation**: Minimum 8 characters required
- **Guards**: Separate authentication guards prevent cross-contamination

### Session Security
- **Separate Sessions**: Admin and employee sessions are isolated
- **Guard-Specific**: Each guard manages its own session state
- **Regeneration**: Session IDs regenerated on login for security

### Access Control
- **Guard-Based**: Routes protected by appropriate guards
- **Role-Based**: Additional role checking within each system
- **Middleware**: Custom middleware for fine-grained access control

## Usage Instructions

### Admin Portal Usage
1. **Registration**: Visit `/register` to create admin accounts
2. **Login**: Visit `/admin/login` with admin credentials
3. **Dashboard**: Access HR dashboard with full system features
4. **Logout**: Use admin logout to end session

### Employee Portal Usage
1. **Login**: Visit `/employee/login` with employee credentials
2. **Dashboard**: Access ESS dashboard with employee features
3. **Self-Service**: Manage personal information, timesheets, leave requests
4. **Logout**: Use employee logout to end session

## API Endpoints

### Admin API
- `GET /api/current-user` - Get current admin user (web guard)
- `POST /admin/login` - Admin login
- `POST /admin/logout` - Admin logout

### Employee API
- `GET /api/employee/current-user` - Get current employee (employee guard)
- `POST /employee/login` - Employee login
- `POST /employee/logout` - Employee logout

## Testing

### Automated Testing
Run the test script to verify dual authentication:
```bash
php test_dual_auth.php
```

### Manual Testing
1. **Admin Portal**: 
   - Visit `http://localhost:8000/admin/login`
   - Login with `testadmin@jetlouge.com / password123`
   - Verify redirect to HR dashboard

2. **Employee Portal**:
   - Visit `http://localhost:8000/employee/login`
   - Login with `testemployee@jetlouge.com / password123`
   - Verify redirect to employee dashboard

## Benefits of Dual Authentication

### Separation of Concerns
- **Admin Functions**: Isolated in users table with admin-specific features
- **Employee Functions**: Isolated in employees table with ESS features
- **Data Integrity**: Prevents mixing of admin and employee data

### Security
- **Guard Isolation**: Separate authentication guards prevent privilege escalation
- **Role Separation**: Clear distinction between admin and employee roles
- **Session Isolation**: Admin and employee sessions are completely separate

### Scalability
- **Independent Systems**: Admin and employee portals can be scaled independently
- **Flexible Roles**: Each system can have its own role structure
- **Modular Design**: Easy to extend either system without affecting the other

## Troubleshooting

### Common Issues
1. **Authentication Errors**: Check guard configuration in auth.php
2. **Session Issues**: Clear Laravel caches after configuration changes
3. **Route Conflicts**: Ensure routes use correct guards and middleware

### Debug Commands
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan route:list
```

## Conclusion

The dual authentication system provides:
- ✅ **Separate Admin Portal**: Uses users table for administrators
- ✅ **Separate Employee Portal**: Uses employees table for employees
- ✅ **Guard Isolation**: Proper authentication separation
- ✅ **Role-Based Access**: Appropriate roles for each system
- ✅ **Security**: Isolated sessions and proper access control
- ✅ **Scalability**: Independent systems that can grow separately

The system is production-ready and provides clear separation between administrative and employee functions while maintaining security and usability.
