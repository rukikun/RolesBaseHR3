<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DashboardDataSeeder;

class PopulateDashboardData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the dashboard with sample data for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Populating dashboard with sample data...');
        
        $seeder = new DashboardDataSeeder();
        $seeder->run();
        
        $this->info('Dashboard data populated successfully!');
        $this->info('You can now view the admin dashboard with real data.');
        
        return Command::SUCCESS;
    }
}
