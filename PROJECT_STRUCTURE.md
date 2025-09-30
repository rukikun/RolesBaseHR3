# HR3 System - Project Structure

## Overview
The HR3 System is a comprehensive Human Resources management application built with Laravel, featuring both admin dashboard and employee self-service (ESS) portals.

## Directory Structure

### Core Application
```
├── app/                    # Laravel application logic
│   ├── Console/           # Artisan commands
│   ├── Http/              # Controllers, middleware, requests
│   ├── Models/            # Eloquent models
│   ├── Providers/         # Service providers
│   └── Services/          # Business logic services
├── config/                # Configuration files
├── routes/                # Route definitions
│   ├── web.php           # Web routes (admin)
│   ├── api.php           # API routes
│   ├── auth.php          # Authentication routes
│   ├── employee.php      # Employee ESS routes
│   └── employee_portal.php # Employee portal routes
├── resources/             # Views, assets, language files
│   └── views/            # Blade templates
│       ├── employee_ess_modules/  # ESS module views
│       ├── auth/         # Authentication views
│       ├── layouts/      # Layout templates
│       └── _archive/     # Archived test views
└── public/               # Web server document root
    ├── assets/           # CSS, JS, images
    └── api/              # Public API endpoints
```

### Database
```
├── database/
│   ├── migrations/       # Database schema migrations
│   ├── seeders/         # Database seeders
│   └── sql/             # SQL scripts and setup files
│       ├── setup/       # Database setup scripts
│       └── legacy/      # Legacy SQL files
```

### Development & Maintenance
```
├── scripts/             # Maintenance and utility scripts
│   ├── tools/          # Development tools and fixes
│   ├── maintenance/    # Database maintenance scripts
│   └── archive/        # Archived scripts
├── docs/               # Documentation
│   └── archived/       # Archived documentation
├── tests/              # PHPUnit tests
└── storage/            # Application storage (logs, cache, etc.)
```

## Key Features

### Admin Dashboard
- Employee Management
- Timesheet Management
- Shift Schedule Management
- Leave Management
- Claims & Reimbursement
- Time & Attendance System

### Employee Self-Service (ESS)
- Personal Dashboard
- Timesheet Submission
- Leave Requests
- Shift Requests
- Claims Submission
- Schedule Viewing

## Database Configuration
- **Database**: MySQL (hr3systemdb)
- **Connection**: Configured for XAMPP/local development
- **Tables**: Unified schema supporting both admin and ESS systems

## Authentication
- **Admin Portal**: `/admin/login` → Admin Dashboard
- **Employee Portal**: `/employee-portal` → ESS Dashboard
- **Dual Authentication**: Separate guard systems for admin and employee access

## Installation & Setup
1. Clone repository
2. Run `composer install`
3. Configure `.env` file with database credentials
4. Run database migrations: `php artisan migrate`
5. Seed database: `php artisan db:seed`
6. Start development server: `php artisan serve`

## Development Notes
- **Framework**: Laravel 10.x
- **Frontend**: Bootstrap 5, jQuery
- **Database**: MySQL with PDO fallback
- **Architecture**: MVC pattern with server-side form handling
- **Security**: CSRF protection, input validation, secure authentication

## File Organization
This project has been organized to separate:
- **Production code** in standard Laravel directories
- **Development tools** in `scripts/` directory
- **Database scripts** in `database/sql/` directory
- **Documentation** in `docs/` directory
- **Archived files** in respective `_archive/` subdirectories

## Support
For technical documentation and setup guides, see the `docs/archived/` directory.
