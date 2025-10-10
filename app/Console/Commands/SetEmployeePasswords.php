<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetEmployeePasswords extends Command
{
    protected $signature = 'employees:set-passwords {--password=password123 : Default password for all employees}';
    protected $description = 'Set passwords for employees that don\'t have passwords';

    public function handle()
    {
        $defaultPassword = $this->option('password');
        
        $this->info('🔐 Setting passwords for employees...');
        
        // Update employees that don't have passwords
        $updated = DB::table('employees')
            ->whereNull('password')
            ->orWhere('password', '')
            ->update([
                'password' => Hash::make($defaultPassword),
                'updated_at' => now()
            ]);
            
        $this->info("✅ Updated {$updated} employees with password: {$defaultPassword}");
        
        // Display employee login credentials
        $this->info('');
        $this->info('👥 Employee Login Credentials:');
        $this->info('Password for all employees: ' . $defaultPassword);
        $this->info('');
        
        $employees = DB::table('employees')
            ->select('first_name', 'last_name', 'email', 'position')
            ->get();
            
        foreach ($employees as $employee) {
            $this->info("📧 {$employee->email} | 🔑 {$defaultPassword} | 👤 {$employee->first_name} {$employee->last_name} ({$employee->position})");
        }
        
        $this->info('');
        $this->info('💡 All employees can now login with their email and the password: ' . $defaultPassword);
        
        return 0;
    }
}
