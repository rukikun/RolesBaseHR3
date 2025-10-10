<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowEmployeeCredentials extends Command
{
    protected $signature = 'employees:show-credentials';
    protected $description = 'Display employee login credentials';

    public function handle()
    {
        $this->info('👥 Employee Login Credentials');
        $this->info('============================');
        $this->info('Default Password: password123');
        $this->info('');
        
        $employees = DB::table('employees')
            ->select('email', 'first_name', 'last_name', 'position')
            ->orderBy('first_name')
            ->get();
            
        foreach ($employees as $employee) {
            $this->info("📧 Email: {$employee->email}");
            $this->info("👤 Name: {$employee->first_name} {$employee->last_name}");
            $this->info("💼 Position: {$employee->position}");
            $this->info("🔑 Password: password123");
            $this->info("---");
        }
        
        $this->info('');
        $this->info('💡 All employees use the same password: password123');
        $this->info('🔐 You can login to the employee portal using any email above with password123');
        
        return 0;
    }
}
