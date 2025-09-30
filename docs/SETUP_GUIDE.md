# HR3 System Setup Guide

Complete setup instructions for the HR3 System with full API functionality and CRUD operations.

## 📋 Prerequisites

- **XAMPP** (Apache, MySQL, PHP 8.1+)
- **Composer** (PHP dependency manager)
- **Node.js & NPM** (for frontend assets)
- **Git** (version control)

## 🗄️ Database Setup

### 1. Start XAMPP Services
```bash
# Start Apache and MySQL services in XAMPP Control Panel
```

### 2. Create Database
```bash
# Option 1: Using phpMyAdmin (http://localhost/phpmyadmin)
# - Create new database: hr3systemdb

# Option 2: Using MySQL command line
mysql -u root -p
CREATE DATABASE hr3systemdb;
exit
```

### 3. Import Complete Schema
```bash
# Navigate to project directory
cd c:\Users\johnk\Herd\hr3system

# Import the complete database schema with sample data
mysql -u root -p hr3systemdb < database/complete_hr_schema.sql
```

## ⚙️ Environment Configuration

### 1. Copy Environment File
```bash
# Copy the example environment file
copy .env.example .env
```

### 2. Update Database Configuration
Edit `.env` file and ensure these settings:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr3systemdb
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Generate Application Key
```bash
php artisan key:generate
```

## 🚀 Laravel Setup

### 1. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Run Database Migrations (if needed)
```bash
# Clear config cache
php artisan config:clear

# Run migrations (optional - schema already imported)
php artisan migrate
```

### 3. Create Storage Links
```bash
# Create symbolic link for file storage
php artisan storage:link
```

### 4. Build Frontend Assets
```bash
# Compile frontend assets
npm run build
```

## 🔧 System Configuration

### 1. Set Proper Permissions
```bash
# Set storage and cache permissions (Windows)
# Right-click on storage and bootstrap/cache folders
# Properties > Security > Edit > Add permissions for IIS_IUSRS
```

### 2. Configure Web Server
Ensure your web server document root points to the `public` directory.

## 🧪 Testing the System

### 1. Start Laravel Development Server
```bash
php artisan serve
```
The application will be available at: `http://localhost:8000`

### 2. Test API Endpoints
```bash
# Run comprehensive API tests
php test_api_endpoints.php
```

### 3. Login Credentials
- **Email**: admin@jetlouge.com
- **Password**: password123

## 📊 Database Structure

The system includes the following main tables:
- `users` - Authentication
- `employees` - Employee management
- `time_entries` - Timesheet records
- `shifts` & `shift_types` - Shift scheduling
- `leave_requests` & `leave_types` - Leave management
- `claims` & `claim_types` - Expense claims

## 🔌 API Endpoints

### Employee Management
```
GET    /api/employees              - List all employees
POST   /api/employees              - Create employee
GET    /api/employees/{id}         - Get single employee
PUT    /api/employees/{id}         - Update employee
DELETE /api/employees/{id}         - Delete employee
GET    /api/employees/stats/summary - Employee statistics
```

### Timesheet Management
```
GET    /api/timesheets             - List timesheets
POST   /api/timesheets             - Create timesheet
GET    /api/timesheets/{id}        - Get single timesheet
PUT    /api/timesheets/{id}        - Update timesheet
DELETE /api/timesheets/{id}        - Delete timesheet
GET    /api/timesheets/stats       - Timesheet statistics
```

### Shift Management
```
GET    /api/shifts/schedule        - Get shift schedule
POST   /api/shifts/create          - Create shift
GET    /api/shifts/types           - Get shift types
GET    /api/shifts/requests        - Get shift requests
POST   /api/shifts/requests        - Create shift request
```

### Leave Management
```
GET    /api/leave/requests         - List leave requests
POST   /api/leave/requests         - Create leave request
GET    /api/leave/requests/{id}    - Get single leave request
PUT    /api/leave/requests/{id}    - Update leave request
DELETE /api/leave/requests/{id}    - Delete leave request
POST   /api/leave/requests/{id}/approve - Approve leave
POST   /api/leave/requests/{id}/reject  - Reject leave
```

### Claims Management
```
GET    /api/claims                 - List claims
POST   /api/claims                 - Create claim
GET    /api/claims/{id}            - Get single claim
PUT    /api/claims/{id}            - Update claim
DELETE /api/claims/{id}            - Delete claim
POST   /api/claims/{id}/approve    - Approve claim
POST   /api/claims/{id}/reject     - Reject claim
POST   /api/claims/{id}/mark-paid  - Mark as paid
```

## 💻 Frontend Usage

### 1. JavaScript API Library
Include the API library in your views:
```html
<script src="{{ asset('assets/js/hr-api.js') }}"></script>
```

### 2. Module-Specific Scripts
```html
<!-- For timesheet management -->
<script src="{{ asset('assets/js/timesheet-management.js') }}"></script>
```

### 3. Basic API Usage
```javascript
// Get all employees
const employees = await hrApi.getEmployees();

// Create new timesheet
const timesheet = await hrApi.createTimesheet({
    employee_id: 1,
    work_date: '2024-01-15',
    hours_worked: 8.0,
    description: 'Regular work day'
});

// Update employee
const updated = await hrApi.updateEmployee(1, {
    first_name: 'John',
    last_name: 'Doe',
    email: 'john.doe@company.com'
});
```

## 🔍 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure XAMPP MySQL is running
   - Check database credentials in `.env`
   - Verify database `hr3systemdb` exists

2. **Permission Errors**
   - Set proper permissions on `storage` and `bootstrap/cache`
   - Run `php artisan config:clear`

3. **API Endpoints Not Working**
   - Clear route cache: `php artisan route:clear`
   - Check Laravel logs: `storage/logs/laravel.log`

4. **Frontend JavaScript Errors**
   - Check browser console for errors
   - Ensure CSRF token is included in meta tags
   - Verify API endpoints are accessible

### Debug Commands
```bash
# Clear all caches
php artisan optimize:clear

# Check routes
php artisan route:list

# Check database connection
php artisan tinker
DB::connection()->getPdo();

# View logs
tail -f storage/logs/laravel.log
```

## 📈 Features Available

### ✅ Complete CRUD Operations
- **Employees**: Add, view, edit, delete, search, filter
- **Timesheets**: Submit, approve, reject, bulk operations, export
- **Shifts**: Schedule, assign, request changes, approve/reject
- **Leave**: Request, approve, reject, balance tracking
- **Claims**: Submit, approve, reject, mark paid, file attachments

### ✅ Advanced Features
- **Real-time updates** with AJAX
- **Bulk operations** for efficiency
- **Export functionality** (CSV)
- **File upload** support for receipts
- **Search and filtering** across all modules
- **Statistics dashboards** for insights
- **Responsive design** for mobile/desktop

### ✅ API Features
- **RESTful endpoints** for all operations
- **JSON responses** with proper HTTP status codes
- **Error handling** with descriptive messages
- **Data validation** on all inputs
- **Authentication** middleware support

## 🎯 Next Steps

1. **Customize the system** to match your business requirements
2. **Add authentication** middleware to secure API endpoints
3. **Implement role-based permissions** for different user types
4. **Set up automated backups** for the database
5. **Configure email notifications** for approvals/rejections
6. **Add reporting features** for management insights

## 📞 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review Laravel logs in `storage/logs/`
3. Test API endpoints using the provided test script
4. Verify database schema and sample data

The HR3 System is now fully functional with complete API integration and database persistence!
