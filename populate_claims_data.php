<?php
/**
 * Populate Claims & Reimbursement Test Data
 * Run this script to add sample claim types and claims for testing
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "🔄 Populating Claims & Reimbursement test data...\n\n";

    // 1. Create Claim Types if they don't exist
    echo "📝 Creating Claim Types...\n";
    
    $claimTypes = [
        [
            'name' => 'Travel Expenses',
            'code' => 'TRAVEL',
            'description' => 'Business travel and transportation costs',
            'max_amount' => 1000.00,
            'requires_attachment' => true,
            'auto_approve' => false,
            'is_active' => true
        ],
        [
            'name' => 'Meal Allowance',
            'code' => 'MEAL',
            'description' => 'Business meal expenses',
            'max_amount' => 100.00,
            'requires_attachment' => true,
            'auto_approve' => true,
            'is_active' => true
        ],
        [
            'name' => 'Office Supplies',
            'code' => 'OFFICE',
            'description' => 'Office equipment and supplies',
            'max_amount' => 500.00,
            'requires_attachment' => true,
            'auto_approve' => false,
            'is_active' => true
        ],
        [
            'name' => 'Training Costs',
            'code' => 'TRAIN',
            'description' => 'Professional development and training',
            'max_amount' => 2000.00,
            'requires_attachment' => true,
            'auto_approve' => false,
            'is_active' => true
        ],
        [
            'name' => 'Medical Expenses',
            'code' => 'MEDICAL',
            'description' => 'Medical reimbursements',
            'max_amount' => 1500.00,
            'requires_attachment' => true,
            'auto_approve' => false,
            'is_active' => true
        ]
    ];

    foreach ($claimTypes as $type) {
        $existing = DB::table('claim_types')->where('code', $type['code'])->first();
        if (!$existing) {
            DB::table('claim_types')->insert(array_merge($type, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
            echo "  ✅ Created: {$type['name']} ({$type['code']})\n";
        } else {
            echo "  ⏭️  Exists: {$type['name']} ({$type['code']})\n";
        }
    }

    // 2. Get some employees for claims
    echo "\n👥 Getting employees for claims...\n";
    $employees = DB::table('employees')->where('status', 'active')->limit(5)->get();
    
    if ($employees->isEmpty()) {
        echo "  ⚠️  No active employees found. Creating sample employees...\n";
        
        $sampleEmployees = [
            [
                'employee_id' => 'EMP001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@jetlouge.com',
                'phone' => '+1234567890',
                'department' => 'IT',
                'position' => 'Software Developer',
                'hire_date' => '2023-01-15',
                'status' => 'active'
            ],
            [
                'employee_id' => 'EMP002',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@jetlouge.com',
                'phone' => '+1234567891',
                'department' => 'HR',
                'position' => 'HR Manager',
                'hire_date' => '2022-03-10',
                'status' => 'active'
            ],
            [
                'employee_id' => 'EMP003',
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@jetlouge.com',
                'phone' => '+1234567892',
                'department' => 'Finance',
                'position' => 'Accountant',
                'hire_date' => '2023-06-01',
                'status' => 'active'
            ]
        ];

        foreach ($sampleEmployees as $emp) {
            $existing = DB::table('employees')->where('email', $emp['email'])->first();
            if (!$existing) {
                DB::table('employees')->insert(array_merge($emp, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
                echo "    ✅ Created employee: {$emp['first_name']} {$emp['last_name']}\n";
            }
        }
        
        $employees = DB::table('employees')->where('status', 'active')->limit(5)->get();
    }

    echo "  📊 Found {$employees->count()} active employees\n";

    // 3. Create sample claims
    echo "\n💰 Creating sample claims...\n";
    
    $claimTypesFromDb = DB::table('claim_types')->where('is_active', true)->get();
    
    $sampleClaims = [
        [
            'employee_id' => $employees->first()->id,
            'claim_type_id' => $claimTypesFromDb->where('code', 'TRAVEL')->first()->id,
            'amount' => 250.75,
            'claim_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'description' => 'Business trip to client meeting - taxi and parking fees',
            'status' => 'pending'
        ],
        [
            'employee_id' => $employees->skip(1)->first()->id ?? $employees->first()->id,
            'claim_type_id' => $claimTypesFromDb->where('code', 'MEAL')->first()->id,
            'amount' => 45.50,
            'claim_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'description' => 'Lunch meeting with potential client',
            'status' => 'approved'
        ],
        [
            'employee_id' => $employees->skip(2)->first()->id ?? $employees->first()->id,
            'claim_type_id' => $claimTypesFromDb->where('code', 'OFFICE')->first()->id,
            'amount' => 89.99,
            'claim_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
            'description' => 'Wireless mouse and keyboard for home office setup',
            'status' => 'pending'
        ],
        [
            'employee_id' => $employees->first()->id,
            'claim_type_id' => $claimTypesFromDb->where('code', 'TRAIN')->first()->id,
            'amount' => 299.00,
            'claim_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'description' => 'Laravel certification course registration',
            'status' => 'approved'
        ],
        [
            'employee_id' => $employees->skip(1)->first()->id ?? $employees->first()->id,
            'claim_type_id' => $claimTypesFromDb->where('code', 'MEDICAL')->first()->id,
            'amount' => 125.00,
            'claim_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'description' => 'Annual health checkup - company insurance deductible',
            'status' => 'paid'
        ]
    ];

    foreach ($sampleClaims as $claim) {
        // Check if similar claim already exists
        $existing = DB::table('claims')
            ->where('employee_id', $claim['employee_id'])
            ->where('amount', $claim['amount'])
            ->where('claim_date', $claim['claim_date'])
            ->first();
            
        if (!$existing) {
            DB::table('claims')->insert(array_merge($claim, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
            
            $employee = $employees->where('id', $claim['employee_id'])->first();
            $claimType = $claimTypesFromDb->where('id', $claim['claim_type_id'])->first();
            echo "  ✅ Created claim: {$employee->first_name} {$employee->last_name} - {$claimType->name} (\${$claim['amount']})\n";
        }
    }

    // 4. Display summary
    echo "\n📊 Summary:\n";
    $totalClaimTypes = DB::table('claim_types')->where('is_active', true)->count();
    $totalEmployees = DB::table('employees')->where('status', 'active')->count();
    $totalClaims = DB::table('claims')->count();
    $pendingClaims = DB::table('claims')->where('status', 'pending')->count();
    $approvedClaims = DB::table('claims')->where('status', 'approved')->count();
    $paidClaims = DB::table('claims')->where('status', 'paid')->count();
    $totalAmount = DB::table('claims')->whereIn('status', ['approved', 'paid'])->sum('amount');

    echo "  📝 Claim Types: {$totalClaimTypes}\n";
    echo "  👥 Active Employees: {$totalEmployees}\n";
    echo "  💰 Total Claims: {$totalClaims}\n";
    echo "  ⏳ Pending Claims: {$pendingClaims}\n";
    echo "  ✅ Approved Claims: {$approvedClaims}\n";
    echo "  💵 Paid Claims: {$paidClaims}\n";
    echo "  💲 Total Approved/Paid Amount: \${$totalAmount}\n";

    echo "\n✅ Claims & Reimbursement data populated successfully!\n";
    echo "🌐 Visit: http://localhost:8000/claims-reimbursement\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n\n";
}
