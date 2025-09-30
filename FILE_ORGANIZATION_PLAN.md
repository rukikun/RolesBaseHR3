# HR3 System File Organization Plan

## Current Status
The HR3 system has accumulated many temporary files, debug scripts, and duplicate database setup files that need cleanup and organization.

## Files to Keep (Essential)

### Core Laravel Framework
- `app/` - Application logic (Controllers, Models, Middleware)
- `config/` - Configuration files
- `routes/` - Route definitions
- `resources/` - Views and assets
- `public/` - Public web assets
- `database/` - Migrations and seeders
- `storage/` - File storage
- `bootstrap/` - Framework bootstrap
- `tests/` - Test files
- `vendor/` - Composer dependencies

### Essential Database Files
- `integrated_hr3system_complete.sql` - **MAIN DATABASE** (currently open)
- `setup_hr3systemdb_complete.sql` - Backup database setup (currently open)
- `add_sample_shift_data.sql` - Sample shift data for testing

### Configuration & Dependencies
- `composer.json` & `composer.lock` - PHP dependencies
- `package.json` & `package-lock.json` - Node.js dependencies
- `.env` & `.env.example` - Environment configuration
- `artisan` - Laravel command line tool
- `phpunit.xml` - Testing configuration
- `vite.config.js` - Asset bundling

### Documentation (docs/ folder)
- `AI_SETUP_GUIDE.md`
- `DATABASE_EXTENSION_GUIDE.md`
- `DATABASE_SETUP_INSTRUCTIONS.md`
- `EMPLOYEE_SHIFT_INTEGRATION_GUIDE.md`
- And other documentation files

### Project Files
- `.gitignore`, `.gitattributes`, `.editorconfig`
- `DEPLOYMENT_INSTRUCTIONS.md`
- `EMPLOYEE_ESS_LOGIN_SETUP.md`
- `ESS_MODULES_INTEGRATION_COMPLETE.md`

## Files to Remove (Temporary/Debug)

### Debug & Test Scripts (50+ files)
- `check_*.php` - Database check scripts
- `debug_*.php` - Debug scripts
- `test_*.php` - Test scripts
- `verify_*.php` - Verification scripts

### Temporary Database Setup Files (30+ files)
- `apply_shift_database.php`
- `create_shift_*.php`
- `fix_*.php` and `fix_*.sql`
- `restore_*.php`
- `setup_*.php` (except main ones)
- `run_*.php`
- `complete_database_recreation.sql`
- `recreate_database_complete.sql`
- `quick_*.sql`
- `manual_*.sql`
- `safe_*.sql`
- `simple_*.sql`
- `force_*.sql`
- `clean_*.sql`

## Proposed Directory Structure

```
hr3system/
├── app/                          # Laravel application
├── config/                       # Configuration
├── database/                     # Migrations & seeders
├── docs/                         # Documentation
├── public/                       # Web assets
├── resources/                    # Views & resources
├── routes/                       # Route definitions
├── storage/                      # File storage
├── tests/                        # Test files
├── database-backups/             # NEW: Database files
│   ├── integrated_hr3system_complete.sql
│   ├── setup_hr3systemdb_complete.sql
│   └── add_sample_shift_data.sql
├── scripts/                      # NEW: Utility scripts
│   └── cleanup_unused_files.bat
├── .env                          # Environment config
├── composer.json                 # PHP dependencies
├── package.json                  # Node dependencies
├── artisan                       # Laravel CLI
└── README.md                     # Project overview
```

## Manual Cleanup Steps

1. **Create new directories:**
   ```
   mkdir database-backups
   mkdir scripts
   ```

2. **Move essential database files:**
   ```
   move integrated_hr3system_complete.sql database-backups/
   move setup_hr3systemdb_complete.sql database-backups/
   move add_sample_shift_data.sql database-backups/
   ```

3. **Move utility scripts:**
   ```
   move cleanup_unused_files.bat scripts/
   ```

4. **Run the cleanup script to remove temporary files**

## Benefits of Organization

1. **Cleaner root directory** - Only essential Laravel files
2. **Organized database files** - All SQL files in one location
3. **Better maintainability** - Clear separation of concerns
4. **Easier deployment** - No confusion about which files are needed
5. **Version control friendly** - Cleaner git status and diffs

## Next Steps

1. Create the new directory structure
2. Move essential files to organized locations
3. Remove temporary/debug files
4. Update documentation references
5. Test system functionality after cleanup
