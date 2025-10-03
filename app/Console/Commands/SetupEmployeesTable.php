<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SetupEmployeesTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:setup {--seed : Also seed the table with sample data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the employees table with proper structure and optional sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up employees table...');

        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'employees'");
            
            if (empty($tableExists)) {
                $this->info('Creating employees table...');
                
                // Run the migration
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/2024_01_01_000001_create_employees_table.php',
                    '--force' => true
                ]);
                
                $this->info('✅ Employees table created successfully!');
            } else {
                $this->info('✅ Employees table already exists.');
            }

            // Check if we should seed data
            if ($this->option('seed')) {
                $existingCount = DB::table('employees')->count();
                
                if ($existingCount == 0) {
                    $this->info('Seeding employees table with sample data...');
                    
                    $seeder = new \Database\Seeders\EmployeesTableSeeder();
                    $seeder->run();
                    
                    $this->info('✅ Sample employees added successfully!');
                } else {
                    $this->info('✅ Employees table already has data (' . $existingCount . ' records).');
                }
            }

            // Verify table structure
            $this->info('Verifying table structure...');
            $columns = DB::select("SHOW COLUMNS FROM employees");
            
            $requiredColumns = [
                'id', 'first_name', 'last_name', 'email', 'phone', 
                'position', 'department', 'hire_date', 'salary', 
                'status', 'online_status', 'created_at', 'updated_at'
            ];
            
            $existingColumns = array_column($columns, 'Field');
            $missingColumns = array_diff($requiredColumns, $existingColumns);
            
            if (empty($missingColumns)) {
                $this->info('✅ All required columns are present.');
            } else {
                $this->warn('⚠️  Missing columns: ' . implode(', ', $missingColumns));
            }

            // Show final status
            $totalEmployees = DB::table('employees')->count();
            $activeEmployees = DB::table('employees')->where('status', 'active')->count();
            
            $this->info("\n📊 Final Status:");
            $this->info("   Total Employees: {$totalEmployees}");
            $this->info("   Active Employees: {$activeEmployees}");
            
            $this->info("\n🎉 Employees table setup completed successfully!");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error setting up employees table: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
