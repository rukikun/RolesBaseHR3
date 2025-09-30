# Employee Self-Service (ESS) System Setup Guide

## Overview
The Employee Self-Service (ESS) system has been successfully integrated into the HR3 system, providing employees with a dedicated portal to access their HR information, submit requests, and manage their profiles.

## Features Implemented

### 1. **Employee Dashboard**
- Real-time statistics (leave requests, attendance, payslips, trainings)
- Notifications system
- Upcoming trainings display
- Recent requests tracking
- Progress indicators for competencies and training completion

### 2. **Authentication System**
- Separate employee login portal
- Secure authentication using Laravel's guard system
- Session management with online/offline status tracking

### 3. **Quick Actions**
- Apply for leave
- Log attendance
- View payslips
- Update profile
- Submit request forms

### 4. **Module Integration**
- Leave applications and balance tracking
- Attendance and time logs
- Payslip access
- Claims and reimbursements
- Request forms management
- Training programs
- Competency profiles
- Profile updates

## Files Created/Modified

### Controllers
- `app/Http/Controllers/EmployeeESSController.php` - Main ESS controller with authentication and dashboard logic

### Models
- `app/Models/Employee.php` - Updated to extend Authenticatable for login functionality

### Views
- `resources/views/employee_ess_modules/employee_dashboard.blade.php` - Updated to use assets1 CSS
- `resources/views/employee_ess_modules/partials/employee_topbar.blade.php` - Navigation bar
- `resources/views/employee_ess_modules/partials/employee_sidebar.blade.php` - Sidebar menu

### Database
- `database/migrations/2024_09_03_001900_create_employee_ess_tables.php` - ESS database tables
- `database/setup_ess_sample_data.sql` - Sample data for testing

### Configuration
- `config/auth.php` - Added employee authentication guard
- `routes/web.php` - ESS routes with proper authentication

### Styling
- Uses `public/assets1/css/employee_dashboard-style.css` for consistent Jetlouge branding

## Database Tables Created

1. **employee_notifications** - Employee notifications and alerts
2. **employee_requests** - General employee requests (equipment, certificates, etc.)
3. **training_programs** - Available training programs
4. **employee_trainings** - Employee training assignments and progress
5. **competency_assessments** - Employee competency evaluations
6. **payslips** - Employee payroll information
7. **employee_documents** - Document storage for employees

## Test Accounts

The following test employee accounts have been created:

| Name | Email | Password | Position | Department |
|------|-------|----------|----------|------------|
| John Doe | john.doe@jetlouge.com | password123 | Software Developer | IT |
| Jane Smith | jane.smith@jetlouge.com | password123 | HR Specialist | Human Resources |
| Mike Johnson | mike.johnson@jetlouge.com | password123 | Marketing Manager | Marketing |
| Sarah Wilson | sarah.wilson@jetlouge.com | password123 | Accountant | Finance |
| David Brown | david.brown@jetlouge.com | password123 | Travel Consultant | Operations |

## Setup Instructions

### 1. Database Setup
```bash
# Run the migration to create ESS tables
php artisan migrate

# Import sample data (optional for testing)
# Execute the SQL file in your MySQL database:
# database/setup_ess_sample_data.sql
```

### 2. Access URLs
- **Employee Login**: `http://your-domain/employee/login`
- **Employee Dashboard**: `http://your-domain/employee/dashboard` (after login)
- **Admin Dashboard**: `http://your-domain/admin_dashboard` (existing admin system)

### 3. Authentication Guards
The system uses Laravel's multi-guard authentication:
- `web` guard for admin users
- `employee` guard for employee users

### 4. Styling Integration
The ESS system uses the `assets1` directory for styling:
- CSS: `public/assets1/css/employee_dashboard-style.css`
- Jetlouge brand colors and consistent UI/UX

## Key Features

### Dashboard Statistics
- **Pending Leave Requests**: Shows employee's pending leave applications
- **Attendance Rate**: Monthly attendance percentage
- **Latest Payslip**: Most recent salary information
- **Upcoming Trainings**: Assigned training programs

### Quick Actions
- **Apply for Leave**: Modal form for leave applications
- **Log Attendance**: Clock in/out functionality
- **View Payslip**: Download latest payslip
- **Update Profile**: Edit personal information and profile picture
- **Submit Request**: General request forms (equipment, certificates, etc.)

### Progress Tracking
- **Competency Goals**: Achievement percentage
- **Training Completion**: Progress on assigned trainings
- **Attendance Rate**: Monthly performance indicator

## Security Features

1. **CSRF Protection**: All forms include CSRF tokens
2. **Authentication Guards**: Separate authentication for employees and admins
3. **Session Management**: Automatic online/offline status tracking
4. **Password Hashing**: Secure password storage using Laravel's Hash facade
5. **Route Protection**: Middleware protection for authenticated routes

## Integration with Main HR System

The ESS system is fully integrated with the existing HR3 system:
- **Shared Database**: Uses the same employee records
- **Cross-Reference**: Employee data is consistent across admin and ESS portals
- **Real-time Updates**: Changes reflect immediately in both systems
- **Unified Styling**: Consistent Jetlouge branding throughout

## Troubleshooting

### Common Issues

1. **Login Issues**
   - Ensure the employee has a password set in the database
   - Check that the email is correct
   - Verify the employee guard is configured in `config/auth.php`

2. **CSS Not Loading**
   - Verify `assets1` directory exists in `public/`
   - Check file permissions
   - Clear browser cache

3. **Database Errors**
   - Run `php artisan migrate` to ensure all tables exist
   - Check database connection in `.env` file
   - Verify XAMPP MySQL service is running

### Support
For technical support or questions about the ESS system, refer to the HR3 system documentation or contact the development team.

## Future Enhancements

Potential areas for expansion:
1. Mobile-responsive design improvements
2. Push notifications for important updates
3. Document upload functionality
4. Advanced reporting and analytics
5. Integration with external HR systems
6. Multi-language support

---

**Note**: This ESS system provides a comprehensive self-service portal for employees while maintaining security and integration with the main HR3 system. All blocking scripts have been removed and the system uses the specified `assets1` styling directory for consistent branding.
