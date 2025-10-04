<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeTablesSeeder extends Seeder
{
    /**
     * Run the database seeds for all type tables.
     * This seeder runs ClaimTypeSeeder, LeaveTypeSeeder, and ShiftTypeSeeder
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Type Tables Seeding...');
        
        // Seed Claim Types
        $this->command->info('📋 Seeding Claim Types...');
        $this->call(ClaimTypeSeeder::class);
        
        // Seed Leave Types
        $this->command->info('🏖️ Seeding Leave Types...');
        $this->call(LeaveTypeSeeder::class);
        
        // Seed Shift Types
        $this->command->info('⏰ Seeding Shift Types...');
        $this->call(ShiftTypeSeeder::class);
        
        $this->command->info('✅ Type Tables Seeding Completed Successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   • 5 Claim Types created');
        $this->command->info('   • 5 Leave Types created');
        $this->command->info('   • 5 Shift Types created');
        $this->command->info('');
        $this->command->info('🎯 All reference data has been populated successfully!');
    }
}
