# HR3 System - Jetlouge Travels

A comprehensive Human Resources Management System built with Laravel, featuring Employee Self-Service (ESS) portal, admin dashboard, and integrated HR modules.

## System Overview

The HR3 system provides complete HR functionality including:
- **Employee Management** - Employee records, profiles, and authentication
- **Time & Attendance** - Clock in/out, timesheet management, overtime tracking
- **Shift Scheduling** - Shift assignment, calendar view, schedule management
- **Leave Management** - Leave requests, approvals, balance tracking
- **Claims & Reimbursement** - Expense claims, receipt uploads, approval workflow
- **Employee Self-Service Portal** - Dedicated employee access to HR functions

## Quick Start

1. **Database Setup**: Import `database-backups/integrated_hr3system_complete.sql`
2. **Environment**: Copy `.env.example` to `.env` and configure database settings
3. **Dependencies**: Run `composer install` and `npm install`
4. **Launch**: Use `php artisan serve` to start the development server

## Login Credentials

**Admin Portal:**
- Email: `admin@jetlouge.com`
- Password: `password123`

**Employee Portal:**
- Email: `john.doe@jetlouge.com` (and other employees)
- Password: `password123`

## Directory Structure

```
hr3system/
├── app/                    # Laravel application logic
├── config/                 # Configuration files
├── database/              # Migrations and seeders
├── database-backups/      # Essential database files
│   ├── integrated_hr3system_complete.sql
│   ├── setup_hr3systemdb_complete.sql
│   └── add_sample_shift_data.sql
├── docs/                  # Documentation
├── public/                # Web assets and API endpoints
├── resources/             # Views and frontend assets
├── routes/                # Route definitions
├── scripts/               # Utility scripts
├── storage/               # File storage
└── tests/                 # Test files
```

## Key Features

### Admin Dashboard
- Real-time statistics and analytics
- Employee management with online status tracking
- Integrated timesheet, shift, leave, and claims management
- Cross-module navigation and data sharing

### Employee Self-Service (ESS)
- Personal dashboard with attendance tracking
- Timesheet submission and history
- Leave request management
- Claims submission with file uploads
- Shift schedule viewing

### API Integration
- Clockify time tracking integration
- OpenAI-powered insights and automation
- RESTful API endpoints for all modules

## Database

The system uses MySQL with the `hr3systemdb` database containing:
- Employee records and authentication
- Time entries and attendance data
- Shift schedules and assignments
- Leave types, requests, and balances
- Claims and reimbursement data
- Training and competency tracking

## Development

**Requirements:**
- PHP 8.1+
- MySQL 5.7+
- Composer
- Node.js & NPM

**Local Development:**
```bash
# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Start development server
php artisan serve
```

## Deployment

See `DEPLOYMENT_INSTRUCTIONS.md` for complete deployment guide.

## Documentation

- `docs/AI_SETUP_GUIDE.md` - AI integration setup
- `docs/DATABASE_SETUP_INSTRUCTIONS.md` - Database configuration
- `docs/EMPLOYEE_SHIFT_INTEGRATION_GUIDE.md` - Module integration
- `EMPLOYEE_ESS_LOGIN_SETUP.md` - ESS portal configuration

## Support

For technical support or feature requests, refer to the documentation in the `docs/` folder or contact the development team.

---

**Version:** 1.0  
**Last Updated:** September 2025  
**Framework:** Laravel 11.x
