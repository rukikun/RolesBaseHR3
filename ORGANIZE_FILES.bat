@echo off
echo HR3 System File Organization Script
echo ===================================

REM Create organized directory structure
echo Creating directory structure...
if not exist "database-backups" mkdir "database-backups"
if not exist "scripts" mkdir "scripts"

REM Move essential database files to organized location
echo Moving database files...
if exist "integrated_hr3system_complete.sql" move "integrated_hr3system_complete.sql" "database-backups\"
if exist "setup_hr3systemdb_complete.sql" move "setup_hr3systemdb_complete.sql" "database-backups\"
if exist "add_sample_shift_data.sql" move "add_sample_shift_data.sql" "database-backups\"

REM Move utility scripts
echo Moving utility scripts...
if exist "cleanup_unused_files.bat" move "cleanup_unused_files.bat" "scripts\"

REM Remove debug and test files
echo Removing debug files...
del /f /q check_*.php 2>nul
del /f /q debug_*.php 2>nul
del /f /q test_*.php 2>nul
del /f /q verify_*.php 2>nul

REM Remove temporary database setup files
echo Removing temporary database files...
del /f /q apply_shift_database.php 2>nul
del /f /q create_shift_system_final.php 2>nul
del /f /q create_shift_tables.php 2>nul
del /f /q create_shift_types_controller.php 2>nul
del /f /q create_test_employee.php 2>nul
del /f /q create_test_user.php 2>nul
del /f /q fix_authentication_complete.php 2>nul
del /f /q fix_ess_modules_errors.php 2>nul
del /f /q fix_foreach_issues.php 2>nul
del /f /q fix_route_errors_complete.php 2>nul
del /f /q fix_shift_calendar_display.php 2>nul
del /f /q fix_shift_data_complete.php 2>nul
del /f /q fix_shift_tables_and_modals.php 2>nul
del /f /q fix_time_entries_database.php 2>nul
del /f /q quick_restore_database.php 2>nul
del /f /q restore_database_complete.php 2>nul
del /f /q run_migrations_and_seed.php 2>nul
del /f /q run_shift_migration.php 2>nul
del /f /q run_shift_requests_migration.php 2>nul
del /f /q setup_complete_shift_system.php 2>nul
del /f /q setup_employee_ess_passwords.php 2>nul
del /f /q setup_shift_database_final.php 2>nul
del /f /q setup_shift_requests_data.php 2>nul

REM Remove duplicate and temporary SQL files
echo Removing temporary SQL files...
del /f /q check_*.sql 2>nul
del /f /q clean_*.sql 2>nul
del /f /q complete_database_recreation.sql 2>nul
del /f /q create_default_employee.sql 2>nul
del /f /q create_shift_tables_direct.sql 2>nul
del /f /q direct_database_setup.sql 2>nul
del /f /q fix_*.sql 2>nul
del /f /q force_drop_tables.sql 2>nul
del /f /q insert_sample_claims_data.sql 2>nul
del /f /q manual_shift_setup.sql 2>nul
del /f /q manual_shift_setup_complete.sql 2>nul
del /f /q quick_database_fix.sql 2>nul
del /f /q recreate_database_complete.sql 2>nul
del /f /q safe_database_cleanup.sql 2>nul
del /f /q setup_claims_system_complete.sql 2>nul
del /f /q setup_complete_hr_database.sql 2>nul
del /f /q setup_employee_passwords.sql 2>nul
del /f /q setup_shift_calendar_complete.sql 2>nul
del /f /q setup_shift_management_complete.sql 2>nul
del /f /q setup_shift_requests_direct.sql 2>nul
del /f /q setup_shift_system_complete.sql 2>nul
del /f /q simple_table_drop.sql 2>nul

echo.
echo ===================================
echo File Organization Complete!
echo ===================================
echo.
echo Organized Structure:
echo - database-backups/    (Essential database files)
echo - scripts/            (Utility scripts)
echo - app/               (Laravel application)
echo - config/            (Configuration)
echo - resources/         (Views and assets)
echo - routes/            (Route definitions)
echo - docs/              (Documentation)
echo.
echo Essential files preserved:
echo - Laravel framework files
echo - Database backups in database-backups/
echo - Documentation in docs/
echo - Configuration files (.env, composer.json, etc.)
echo.
echo Removed files:
echo - 50+ debug and test scripts
echo - 30+ temporary database setup files
echo - Duplicate SQL files
echo.
pause
