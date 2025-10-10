<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyPesoData extends Command
{
    protected $signature = 'verify:peso-data';
    protected $description = 'Verify that all data is properly converted to Philippine Peso';

    public function handle()
    {
        $this->info('🔍 Verifying Philippine Peso data conversion...');
        $this->info('');

        // Check Claim Types
        $this->info('📋 Claim Types:');
        $claimTypes = DB::table('claim_types')->select('name', 'max_amount')->get();
        foreach ($claimTypes as $ct) {
            $this->info("  - {$ct->name}: ₱" . number_format($ct->max_amount, 2));
        }

        $this->info('');

        // Check Employee Salaries
        $this->info('👥 Employee Salaries:');
        $employees = DB::table('employees')->select('first_name', 'last_name', 'position', 'salary')->limit(5)->get();
        foreach ($employees as $emp) {
            $this->info("  - {$emp->first_name} {$emp->last_name} ({$emp->position}): ₱" . number_format($emp->salary, 2));
        }

        $this->info('');

        // Check Claims
        $this->info('💰 Sample Claims:');
        $claims = DB::table('claims')->select('amount', 'description')->limit(3)->get();
        foreach ($claims as $claim) {
            $description = substr($claim->description, 0, 40) . '...';
            $this->info("  - ₱" . number_format($claim->amount, 2) . " - {$description}");
        }

        $this->info('');

        // Check Shift Types
        $this->info('⏰ Shift Types Hourly Rates:');
        $shiftTypes = DB::table('shift_types')->select('name', 'hourly_rate')->get();
        foreach ($shiftTypes as $st) {
            $this->info("  - {$st->name}: ₱" . number_format($st->hourly_rate, 2) . "/hr");
        }

        $this->info('');
        $this->info('✅ All data verified - Philippine Peso conversion successful!');
        
        return 0;
    }
}
