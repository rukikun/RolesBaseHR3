<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClaimType;

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
                'description' => 'Business travel expenses including flights, hotels, and meals',
                'max_amount' => 2000.00,
                'requires_attachment' => true,
                'auto_approve' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'OFFICE',
                'description' => 'Office supplies and equipment purchases',
                'max_amount' => 500.00,
                'requires_attachment' => true,
                'auto_approve' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Meal Allowance',
                'code' => 'MEAL',
                'description' => 'Daily meal allowance for business activities',
                'max_amount' => 50.00,
                'requires_attachment' => false,
                'auto_approve' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Transportation',
                'code' => 'TRANSPORT',
                'description' => 'Local transportation costs including taxi, bus, and parking',
                'max_amount' => 100.00,
                'requires_attachment' => true,
                'auto_approve' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Training & Development',
                'code' => 'TRAINING',
                'description' => 'Professional development courses and certifications',
                'max_amount' => 1500.00,
                'requires_attachment' => true,
                'auto_approve' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Medical Expenses',
                'code' => 'MEDICAL',
                'description' => 'Work-related medical expenses and health checkups',
                'max_amount' => 1000.00,
                'requires_attachment' => true,
                'auto_approve' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Phone & Internet',
                'code' => 'TELECOM',
                'description' => 'Business phone and internet expenses',
                'max_amount' => 200.00,
                'requires_attachment' => true,
                'auto_approve' => true,
                'is_active' => true,
            ],
        ];

        foreach ($claimTypes as $claimType) {
            ClaimType::create($claimType);
        }
    }
}
