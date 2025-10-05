<?php
/**
 * Populate Leave Management Test Data
 * Run this script to add sample leave types and requests for testing
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "🔄 Populating Leave Management test data...\n\n";

    // 1. Create Leave Types if they don't exist
    echo "📝 Creating Leave Types...\n";
    
    $leaveTypes = [
        [
            'name' => 'Annual Leave',
            'code' => 'AL',
            'description' => 'Annual vacation leave',
            'max_days_per_year' => 21,
            'carry_forward' => true,
            'requires_approval' => true,
            'is_active' => true
        ],
        [
            'name' => 'Sick Leave',
            'code' => 'SL',
            'description' => 'Medical sick leave',
            'max_days_per_year' => 10,
            'carry_forward' => false,
            'requires_approval' => false,
            'is_active' => true
        ],
        [
            'name' => 'Emergency Leave',
            'code' => 'EL',
            'description' => 'Emergency family leave',
            'max_days_per_year' => 5,
            'carry_forward' => false,
            'requires_approval' => true,
            'is_active' => true
        ],
        [
            'name' => 'Maternity Leave',
            'code' => 'ML',
            'description' => 'Maternity leave',
            'max_days_per_year' => 90,
            'carry_forward' => false,
            'requires_approval' => true,
            'is_active' => true
        ],
        [
            'name' => 'Paternity Leave',
            'code' => 'PL',
            'description' => 'Paternity leave',
            'max_days_per_year' => 7,
            'carry_forward' => false,
            'requires_approval' => true,
            'is_active' => true
        ]
    ];

    foreach ($leaveTypes as $type) {
        $existing = DB::table('leave_types')->where('code', $type['code'])->first();
        if (!$existing) {
            DB::table('leave_types')->insert(array_merge($type, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
            echo "  ✅ Created: {$type['name']} ({$type['code']})\n";
        } else {
            echo "  ⏭️  Exists: {$type['name']} ({$type['code']})\n";
        }
    }

    // 2. Get some employees for leave requests
    echo "\n👥 Getting employees for leave requests...\n";
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

    // 3. Create sample leave requests
    echo "\n📅 Creating sample leave requests...\n";
    
    $leaveTypesFromDb = DB::table('leave_types')->where('is_active', true)->get();
    
    $sampleRequests = [
        [
            'employee_id' => $employees->first()->id,
            'leave_type_id' => $leaveTypesFromDb->where('code', 'AL')->first()->id,
            'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
            'days_requested' => 5,
            'reason' => 'Family vacation to the beach',
            'status' => 'pending'
        ],
        [
            'employee_id' => $employees->skip(1)->first()->id ?? $employees->first()->id,
            'leave_type_id' => $leaveTypesFromDb->where('code', 'SL')->first()->id,
            'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'days_requested' => 3,
            'reason' => 'Medical appointment and recovery',
            'status' => 'approved'
        ],
        [
            'employee_id' => $employees->skip(2)->first()->id ?? $employees->first()->id,
            'leave_type_id' => $leaveTypesFromDb->where('code', 'EL')->first()->id,
            'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(8)->format('Y-m-d'),
            'days_requested' => 2,
            'reason' => 'Family emergency',
            'status' => 'pending'
        ]
    ];

    foreach ($sampleRequests as $request) {
        // Check if similar request already exists
        $existing = DB::table('leave_requests')
            ->where('employee_id', $request['employee_id'])
            ->where('start_date', $request['start_date'])
            ->first();
            
        if (!$existing) {
            DB::table('leave_requests')->insert(array_merge($request, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
            
            $employee = $employees->where('id', $request['employee_id'])->first();
            $leaveType = $leaveTypesFromDb->where('id', $request['leave_type_id'])->first();
            echo "  ✅ Created leave request: {$employee->first_name} {$employee->last_name} - {$leaveType->name} ({$request['days_requested']} days)\n";
        }
    }

    // 4. Display summary
    echo "\n📊 Summary:\n";
    $totalLeaveTypes = DB::table('leave_types')->where('is_active', true)->count();
    $totalEmployees = DB::table('employees')->where('status', 'active')->count();
    $totalRequests = DB::table('leave_requests')->count();
    $pendingRequests = DB::table('leave_requests')->where('status', 'pending')->count();

    echo "  📝 Leave Types: {$totalLeaveTypes}\n";
    echo "  👥 Active Employees: {$totalEmployees}\n";
    echo "  📅 Total Leave Requests: {$totalRequests}\n";
    echo "  ⏳ Pending Requests: {$pendingRequests}\n";

    echo "\n✅ Leave Management data populated successfully!\n";
    echo "🌐 Visit: http://localhost:8000/leave-management\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n\n";
}
