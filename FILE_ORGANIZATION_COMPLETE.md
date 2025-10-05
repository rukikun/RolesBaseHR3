# HR3 System File Organization

## Directory Structure

### Core Laravel Directories
- `app/` - Application logic (Controllers, Models, etc.)
- `config/` - Configuration files
- `database/` - Migrations, seeders, and database setup
- `public/` - Web accessible files
- `resources/` - Views, assets, and language files
- `routes/` - Route definitions
- `storage/` - File storage and logs
- `tests/` - Test files

### Organized Directories
- `docs/` - Project documentation
- `maintenance/` - Maintenance scripts and SQL fixes
- `archive/` - Legacy files and old scripts

### Database Organization
- `database/migrations/` - Laravel migrations
- `database/seeders/` - Database seeders
- `database/setup/` - Database setup and schema files
- `database/backups/` - Database backup files

### Maintenance Organization
- `maintenance/fixes/` - Issue resolution scripts
- `maintenance/diagnostics/` - Testing and diagnostic scripts
- `maintenance/setup/` - Initial setup scripts
- `maintenance/sql/` - SQL fix files

## Files Removed
- 20+ test/fix PHP scripts from root directory
- Duplicate SQL files
- Outdated documentation files
- Legacy script directories

## Benefits
- ✅ Clean root directory with only essential Laravel files
- ✅ Logical grouping of related functionality
- ✅ Professional development structure
- ✅ Easy maintenance and navigation
- ✅ Proper git ignore configuration

## Usage
- Main Laravel commands: `php artisan [command]`
- Maintenance tools: `php maintenance/[category]/[script].php`
- Documentation: `/docs/` directory
- Database setup: `/database/setup/` directory

Generated on: 2025-10-05 17:21:48
