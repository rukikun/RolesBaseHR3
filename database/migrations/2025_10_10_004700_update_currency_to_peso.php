<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update claim types with Philippine Peso amounts
        DB::table('claim_types')->where('name', 'Meal Allowance')->update(['max_amount' => 2000.00]);
        DB::table('claim_types')->where('name', 'Medical Expenses')->update(['max_amount' => 10000.00]);
        DB::table('claim_types')->where('name', 'Office Supplies')->update(['max_amount' => 5000.00]);
        DB::table('claim_types')->where('name', 'Training Costs')->update(['max_amount' => 25000.00]);
        DB::table('claim_types')->where('name', 'Travel Expenses')->update(['max_amount' => 15000.00]);
        
        // Update existing claims - convert small amounts (likely USD) to PHP
        // Only update amounts that seem to be in USD (less than 1000)
        DB::statement('UPDATE claims SET amount = CASE 
            WHEN amount <= 1000 THEN amount * 56 
            ELSE amount 
        END');
        
        // Update employee salaries if they exist and seem to be in USD
        DB::statement('UPDATE employees SET salary = CASE 
            WHEN salary <= 100000 THEN salary * 1.5 
            ELSE salary 
        END WHERE salary IS NOT NULL');
        
        // Update shift types hourly rates
        DB::table('shift_types')->where('name', 'Morning Shift')->update(['hourly_rate' => 350.00]);
        DB::table('shift_types')->where('name', 'Afternoon Shift')->update(['hourly_rate' => 385.00]);
        DB::table('shift_types')->where('name', 'Night Shift')->update(['hourly_rate' => 450.00]);
        DB::table('shift_types')->where('name', 'Split Shift')->update(['hourly_rate' => 335.00]);
        DB::table('shift_types')->where('name', 'Weekend Shift')->update(['hourly_rate' => 420.00]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original USD amounts (approximate)
        DB::table('claim_types')->where('name', 'Meal Allowance')->update(['max_amount' => 500.00]);
        DB::table('claim_types')->where('name', 'Medical Expenses')->update(['max_amount' => 3000.00]);
        DB::table('claim_types')->where('name', 'Office Supplies')->update(['max_amount' => 1000.00]);
        DB::table('claim_types')->where('name', 'Training Costs')->update(['max_amount' => 2000.00]);
        DB::table('claim_types')->where('name', 'Travel Expenses')->update(['max_amount' => 5000.00]);
    }
};
