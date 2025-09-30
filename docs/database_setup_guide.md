# HR3 System Database Setup Guide

## Prerequisites
1. **XAMPP installed** with MySQL service running
2. **PHP and Composer** installed and accessible via command line
3. **Laravel framework** properly configured

## Step-by-Step Database Setup

### 1. Environment Configuration
```bash
# Copy environment file
copy .env.example .env

# Generate Laravel application key
php artisan key:generate
```

### 2. Database Creation & Installation
```bash
# Start XAMPP MySQL service (if not running)
# Open XAMPP Control Panel and start MySQL

# Create database and run installation script
mysql -u root -p < database\fresh_install.sql
```

### 3. Laravel Integration
```bash
# Run Laravel migrations (optional - schema already created)
php artisan migrate:status

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 4. Test Database Connectivity
```bash
# Test database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected!';"

# Test API endpoints
php artisan serve
# Visit: http://localhost:8000/api/dashboard/stats
```

## Database Schema Overview

Your `fresh_install.sql` includes:
- **Users table** - Authentication system
- **Employees table** - Employee management
- **Time entries** - Timesheet tracking
- **Shifts & Shift types** - Schedule management
- **Leave management** - Leave requests and balances
- **Claims system** - Expense claims and reimbursements
- **Laravel system tables** - Sessions, cache, jobs

## Current Configuration

**Database Settings (.env):**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr3systemdb
DB_USERNAME=root
DB_PASSWORD=
```

## API Integration Status

Based on memories, your system has:
- ✅ **Complete CRUD operations** for all modules
- ✅ **Modal functionality** with working-modal CSS
- ✅ **AJAX integration** across all HR modules
- ✅ **Clockify API integration** for time tracking
- ✅ **AI services integration** (OpenAI/Ollama)

## Troubleshooting

### Common Issues:
1. **"Database not found"** - Run the fresh_install.sql script
2. **"Connection refused"** - Start XAMPP MySQL service
3. **"Key not set"** - Run `php artisan key:generate`
4. **Modal not working** - Ensure working-modal CSS is included

### Test Commands:
```bash
# Check database connection
php artisan db:show

# List all tables
php artisan db:table --show

# Test specific API endpoint
curl http://localhost:8000/api/employees
```

## Next Steps After Setup

1. **Start development server**: `php artisan serve`
2. **Access admin dashboard**: http://localhost:8000/admin_dashboard
3. **Login credentials**: admin@jetlouge.com / password123
4. **Test all HR modules** through the dashboard interface

## Push/Pull Operations

Once database is set up:
- **Push**: All form submissions via AJAX to API endpoints
- **Pull**: Real-time data loading with auto-refresh (30 seconds)
- **Sync**: Clockify integration maintains dual time tracking
- **Backup**: Use `mysqldump` for database backups
