@echo off
echo Cleaning up unused files from HR3 system...

REM Remove debug and test files
del /f /q check_*.php 2>nul
del /f /q debug_*.php 2>nul
del /f /q test_*.php 2>nul
del /f /q verify_*.php 2>nul

REM Remove temporary database setup files (keeping only the main ones)
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

echo Cleanup completed!
echo.
echo Essential files kept:
echo - integrated_hr3system_complete.sql (main database)
echo - setup_hr3systemdb_complete.sql (backup database setup)
echo - add_sample_shift_data.sql (sample data)
echo - All Laravel framework files (app/, config/, routes/, etc.)
echo - Documentation files in docs/ folder
echo.
pause
