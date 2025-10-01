<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Database\Seeders\DashboardDataSeeder;

class SetupDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:setup {--force : Force setup even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete setup of the admin dashboard with migrations and sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up HR3 System Admin Dashboard...');
        $this->newLine();

        // Step 1: Run migrations
        $this->info('📋 Step 1: Running database migrations...');
        try {
            Artisan::call('migrate', ['--force' => true]);
            $this->info('✅ Migrations completed successfully');
        } catch (\Exception $e) {
            $this->error('❌ Migration failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Step 2: Check if data already exists
        $employeeCount = 0;
        try {
            $employeeCount = DB::table('employees')->where('status', 'active')->count();
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not check existing employee data');
        }

        if ($employeeCount > 0 && !$this->option('force')) {
            $this->info("📊 Found {$employeeCount} existing employees");
            if (!$this->confirm('Data already exists. Do you want to add sample data anyway?')) {
                $this->info('✅ Dashboard setup completed (using existing data)');
                $this->displayAccessInfo();
                return Command::SUCCESS;
            }
        }

        // Step 3: Populate sample data
        $this->info('📊 Step 2: Populating sample data...');
        try {
            $seeder = new DashboardDataSeeder();
            $seeder->run();
            $this->info('✅ Sample data populated successfully');
        } catch (\Exception $e) {
            $this->error('❌ Data seeding failed: ' . $e->getMessage());
            $this->warn('Dashboard will still work but may show empty statistics');
        }

        // Step 4: Verify setup
        $this->info('🔍 Step 3: Verifying dashboard setup...');
        $this->verifySetup();

        // Step 5: Display success message
        $this->newLine();
        $this->info('🎉 Dashboard setup completed successfully!');
        $this->displayAccessInfo();

        return Command::SUCCESS;
    }

    private function verifySetup()
    {
        $checks = [
            'employees' => 'Employee records',
            'time_entries' => 'Time entries',
            'shift_types' => 'Shift types',
            'leave_types' => 'Leave types'
        ];

        foreach ($checks as $table => $description) {
            try {
                $count = DB::table($table)->count();
                if ($count > 0) {
                    $this->info("  ✅ {$description}: {$count} records");
                } else {
                    $this->warn("  ⚠️  {$description}: No records found");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ {$description}: Table not accessible");
            }
        }
    }

    private function displayAccessInfo()
    {
        $this->newLine();
        $this->info('📱 Dashboard Access Information:');
        $this->info('  • Admin Dashboard: /admin_dashboard');
        $this->info('  • Populate Data: /populate-dashboard');
        $this->info('  • Test Database: /test-db');
        $this->newLine();
        $this->info('🔧 Available Commands:');
        $this->info('  • php artisan dashboard:populate - Add more sample data');
        $this->info('  • php artisan dashboard:setup --force - Force complete reset');
        $this->newLine();
    }
}
