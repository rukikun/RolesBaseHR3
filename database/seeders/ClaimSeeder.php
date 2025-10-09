<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Employee;
use Carbon\Carbon;

class ClaimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get claim types and employees
        $claimTypes = ClaimType::all();
        $employees = Employee::take(5)->get();

        if ($claimTypes->isEmpty() || $employees->isEmpty()) {
            $this->command->warn('Please run ClaimTypeSeeder and ensure employees exist first');
            return;
        }

        // Get specific claim types safely
        $travelType = $claimTypes->where('code', 'TRAVEL')->first();
        $officeType = $claimTypes->where('code', 'OFFICE')->first();
        $mealType = $claimTypes->where('code', 'MEAL')->first();
        $transportType = $claimTypes->where('code', 'TRANSPORT')->first();
        $trainingType = $claimTypes->where('code', 'TRAINING')->first();
        $medicalType = $claimTypes->where('code', 'MEDICAL')->first();
        $telecomType = $claimTypes->where('code', 'TELECOM')->first();

        if (!$travelType || !$officeType || !$mealType || !$transportType || !$trainingType || !$medicalType || !$telecomType) {
            $this->command->warn('Some claim types are missing. Please run ClaimTypeSeeder first');
            return;
        }

        $claims = [
            [
                'employee_id' => $employees->random()->id,
                'claim_type_id' => $travelType->id,
                'amount' => 12500.00,
                'claim_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'description' => 'Business trip to client meeting in Singapore - flight and hotel expenses',
                'status' => 'pending',
            ],
            [
                'employee_id' => $employees->random()->id,
                'claim_type_id' => $officeType->id,
                'amount' => 3200.00,
                'claim_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'description' => 'Office supplies: printer paper, pens, and folders',
                'status' => 'approved',
                'approved_at' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
                'approved_by' => $employees->random()->id,
            ],
            [
                'employee_id' => $employees->random()->id,
                'claim_type_id' => $mealType->id,
                'amount' => 1800.00,
                'claim_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'description' => 'Lunch meeting with potential client',
                'status' => 'approved',
                'approved_at' => Carbon::now()->subHours(12)->format('Y-m-d H:i:s'),
                'approved_by' => $employees->random()->id,
            ],
            [
                'employee_id' => $employees->random()->id,
                'claim_type_id' => $transportType->id,
                'amount' => 850.00,
                'claim_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'description' => 'Taxi fare to client office and parking fees',
                'status' => 'paid',
                'approved_at' => Carbon::now()->subHours(6)->format('Y-m-d H:i:s'),
                'approved_by' => $employees->random()->id,
                'paid_at' => Carbon::now()->subHours(2)->format('Y-m-d H:i:s'),
            ],
            [
                'employee_id' => $employees->random()->id,
                'claim_type_id' => $trainingType->id,
                'amount' => 18000.00,
                'claim_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'description' => 'AWS Cloud Certification training course',
                'status' => 'pending',
            ],
            [
                'employee_id' => $employees->random()->id,
                'claim_type_id' => $medicalType->id,
                'amount' => 4500.00,
                'claim_date' => Carbon::now()->subDays(4)->format('Y-m-d'),
                'description' => 'Annual health checkup as required by company policy',
                'status' => 'approved',
                'approved_at' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
                'approved_by' => $employees->random()->id,
            ],
            [
                'employee_id' => $employees->random()->id,
                'claim_type_id' => $telecomType->id,
                'amount' => 2200.00,
                'claim_date' => Carbon::now()->subDays(6)->format('Y-m-d'),
                'description' => 'Monthly business phone bill',
                'status' => 'rejected',
                'approved_at' => Carbon::now()->subDays(3)->format('Y-m-d H:i:s'),
                'approved_by' => $employees->random()->id,
                'rejection_reason' => 'Personal usage detected in phone bill. Please submit only business-related charges.',
            ],
            [
                'employee_id' => $employees->random()->id,
                'claim_type_id' => $officeType->id,
                'amount' => 2800.00,
                'claim_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'description' => 'Wireless mouse and keyboard for home office setup',
                'status' => 'approved',
                'approved_at' => Carbon::now()->subHours(8)->format('Y-m-d H:i:s'),
                'approved_by' => $employees->random()->id,
            ],
        ];

        foreach ($claims as $claimData) {
            Claim::create($claimData);
        }
    }
}
