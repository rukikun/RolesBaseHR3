# HR3 System - File Organization

## Overview
This document outlines the organized file structure for the HR3 System project, ensuring better maintainability and clarity.

## New Directory Structure

### 📁 `/maintenance/`
**Purpose**: Contains all maintenance, diagnostic, and setup scripts

#### 📁 `/maintenance/fixes/`
**Purpose**: Scripts that fix specific issues
- `fix_employee_login.php` - Comprehensive employee authentication fix
- `fix_database_connection.php` - Database connection troubleshooting
- `fix_database_issues.php` - General database issue resolution
- `fix_attendance_timezone.php` - Timezone-related attendance fixes

#### 📁 `/maintenance/diagnostics/`
**Purpose**: Scripts for testing and diagnosing system issues
- `check_employees.php` - Employee record verification
- `check_attendance_data.php` - Attendance data validation
- `test_employee_login.php` - Authentication testing suite

#### 📁 `/maintenance/setup/`
**Purpose**: Initial setup and configuration scripts
- `setup_admin_profile_system.php` - Admin profile system setup

### 📁 `/docs/solutions/`
**Purpose**: Documentation for implemented solutions
- `EMPLOYEE_LOGIN_SOLUTION.md` - Complete employee login fix documentation
- `FIXES_APPLIED.md` - Summary of all applied fixes
- `VITE_BUILD_FIX.md` - Vite build configuration fixes

### 📁 `/temp/`
**Purpose**: Temporary files and diagnostics (excluded from git)
- `database_diagnostic.txt` - Database diagnostic output

## Root Directory Files

### Core Laravel Files
- `artisan` - Laravel command-line interface
- `composer.json` / `composer.lock` - PHP dependencies
- `package.json` / `package-lock.json` - Node.js dependencies
- `phpunit.xml` - PHPUnit testing configuration
- `vite.config.js` - Vite build configuration
- `tailwind.config.js` - Tailwind CSS configuration

### Configuration Files
- `.env.example` - Environment configuration template
- `.gitignore` - Git ignore rules
- `.gitattributes` - Git attributes
- `.editorconfig` - Editor configuration

### Documentation
- `README.md` - Main project documentation
- `PROJECT_STRUCTURE.md` - Project structure overview
- `DASHBOARD_README.md` - Dashboard-specific documentation
- `FILE_ORGANIZATION.md` - This file

## Standard Laravel Directories

### 📁 `/app/`
- **Controllers**: HTTP request handling
- **Models**: Database models and business logic
- **Middleware**: Request filtering
- **Providers**: Service providers

### 📁 `/config/`
- Application configuration files
- Database, authentication, and service configurations

### 📁 `/database/`
- **migrations**: Database schema changes
- **factories**: Model factories for testing
- **seeders**: Database seeding scripts

### 📁 `/resources/`
- **views**: Blade templates
- **css/js**: Frontend assets

### 📁 `/routes/`
- Route definitions for web, API, and console

### 📁 `/public/`
- Publicly accessible files
- Entry point (`index.php`)
- Assets (CSS, JS, images)

### 📁 `/storage/`
- Application storage (logs, cache, uploads)

### 📁 `/tests/`
- Unit and feature tests

## File Organization Benefits

### ✅ **Improved Maintainability**
- Related files grouped together
- Clear separation of concerns
- Easy to locate specific functionality

### ✅ **Better Version Control**
- Temporary files excluded from git
- Maintenance scripts properly tracked
- Clean repository structure

### ✅ **Enhanced Development Workflow**
- Quick access to diagnostic tools
- Organized documentation
- Clear file purposes

### ✅ **Professional Structure**
- Industry-standard organization
- Scalable architecture
- Team-friendly layout

## Usage Guidelines

### 🔧 **Maintenance Scripts**
```bash
# Run diagnostic checks
php maintenance/diagnostics/check_employees.php
php maintenance/diagnostics/test_employee_login.php

# Apply fixes
php maintenance/fixes/fix_employee_login.php
php maintenance/fixes/fix_database_connection.php

# Setup systems
php maintenance/setup/setup_admin_profile_system.php
```

### 📚 **Documentation**
- Check `/docs/solutions/` for implemented fixes
- Refer to root-level `.md` files for general documentation
- Use `DASHBOARD_README.md` for dashboard-specific info

### 🗂️ **Adding New Files**
- **Fix scripts** → `/maintenance/fixes/`
- **Diagnostic tools** → `/maintenance/diagnostics/`
- **Setup scripts** → `/maintenance/setup/`
- **Solution docs** → `/docs/solutions/`
- **Temporary files** → `/temp/` (auto-ignored by git)

## Git Ignore Updates

Added the following patterns to `.gitignore`:
```gitignore
# Temporary files and diagnostics
/temp/
*.tmp
*.bak
*_backup.*
database_diagnostic.txt

# Maintenance scripts (optional)
# /maintenance/
```

## Migration Notes

### Files Moved:
- ✅ All fix scripts → `/maintenance/fixes/`
- ✅ All diagnostic scripts → `/maintenance/diagnostics/`
- ✅ Setup scripts → `/maintenance/setup/`
- ✅ Solution documentation → `/docs/solutions/`
- ✅ Temporary files → `/temp/`

### Files Remaining in Root:
- Core Laravel files (artisan, composer.json, etc.)
- Main documentation (README.md, PROJECT_STRUCTURE.md)
- Configuration files (.env.example, .gitignore, etc.)

---

**Last Updated**: October 4, 2025  
**Status**: ✅ Organization Complete  
**Maintainer**: HR3 System Development Team
