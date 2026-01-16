<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\BiometricCredential;

class SetupDefaultBiometrics extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'biometric:setup-defaults';

    /**
     * The console command description.
     */
    protected $description = 'Set up default biometric credentials for all employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up default biometric credentials for all employees...');

        $employees = Employee::all();
        $created = 0;
        $existing = 0;

        foreach ($employees as $employee) {
            // Check if employee already has biometric credentials
            $existingCredential = BiometricCredential::where('employee_id', $employee->id)->first();
            
            if ($existingCredential) {
                $existing++;
                $this->line("Employee {$employee->email} already has biometric credentials");
                continue;
            }

            // Create default biometric credential
            BiometricCredential::create([
                'employee_id' => $employee->id,
                'credential_id' => 'default_' . $employee->id . '_' . time(),
                'public_key' => 'default_fingerprint_key',
                'authenticator_type' => 'platform',
                'authenticator_data' => ['type' => 'default_fingerprint'],
                'device_name' => 'Default Fingerprint Device',
                'is_active' => true
            ]);

            $created++;
            $this->line("✓ Created default biometric for {$employee->email}");
        }

        $this->info("\nSummary:");
        $this->info("- Created: {$created} new biometric credentials");
        $this->info("- Existing: {$existing} employees already had credentials");
        $this->info("- Total employees: " . $employees->count());
        
        $this->info("\nAll employees now have default biometric authentication enabled!");
        $this->info("They can use fingerprint verification after OTP verification.");
    }
}
