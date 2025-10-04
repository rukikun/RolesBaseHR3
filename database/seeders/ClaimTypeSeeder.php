<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClaimType;
use Illuminate\Support\Facades\DB;

class ClaimTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $claimTypes = [
            [
                'name' => 'Travel Expenses',
                'code' => 'TRAVEL',
                'description' => 'Business travel related expenses',
                'max_amount' => 5000.00,
                'requires_attachment' => 1,
                'auto_approve' => 0,
                'is_active' => 1,
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'OFFICE',
                'description' => 'Office supplies and equipment',
                'max_amount' => 1000.00,
                'requires_attachment' => 1,
                'auto_approve' => 0,
                'is_active' => 1,
            ],
            [
                'name' => 'Meal Allowance',
                'code' => 'MEAL',
                'description' => 'Business meal expenses',
                'max_amount' => 500.00,
                'requires_attachment' => 1,
                'auto_approve' => 0,
                'is_active' => 1,
            ],
            [
                'name' => 'Training Costs',
                'code' => 'TRAINING',
                'description' => 'Professional development and training',
                'max_amount' => 2000.00,
                'requires_attachment' => 1,
                'auto_approve' => 0,
                'is_active' => 1,
            ],
            [
                'name' => 'Medical Expenses',
                'code' => 'MEDICAL',
                'description' => 'Medical and health related expenses',
                'max_amount' => 3000.00,
                'requires_attachment' => 1,
                'auto_approve' => 0,
                'is_active' => 1,
            ],
        ];

        // Clear existing data first
        DB::table('claim_types')->truncate();
        
        // Insert all data
        foreach ($claimTypes as $claimType) {
            DB::table('claim_types')->insert(array_merge($claimType, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
