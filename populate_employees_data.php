<?php
/**
 * Populate Employee Management Test Data
 * Run this script to add sample employees for testing
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "🔄 Populating Employee Management test data...\n\n";

    // 1. Create Employees if they don't exist
    echo "👥 Creating Employees...\n";
    
    $employees = [
        [
            'employee_id' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@jetlouge.com',
            'phone' => '+1234567890',
            'position' => 'Software Developer',
            'department' => 'Information Technology',
            'hire_date' => '2023-01-15',
            'salary' => 75000.00,
            'status' => 'active',
            'password' => Hash::make('password123')
        ],
        [
            'employee_id' => 'EMP002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@jetlouge.com',
            'phone' => '+1234567891',
            'position' => 'HR Manager',
            'department' => 'Human Resources',
            'hire_date' => '2022-03-10',
            'salary' => 85000.00,
            'status' => 'active',
            'password' => Hash::make('password123')
        ],
        [
            'employee_id' => 'EMP003',
            'first_name' => 'Mike',
            'last_name' => 'Johnson',
            'email' => 'mike.johnson@jetlouge.com',
            'phone' => '+1234567892',
            'position' => 'Accountant',
            'department' => 'Finance',
            'hire_date' => '2023-06-01',
            'salary' => 65000.00,
            'status' => 'active',
            'password' => Hash::make('password123')
        ],
        [
            'employee_id' => 'EMP004',
            'first_name' => 'Sarah',
            'last_name' => 'Wilson',
            'email' => 'sarah.wilson@jetlouge.com',
            'phone' => '+1234567893',
            'position' => 'Marketing Specialist',
            'department' => 'Marketing',
            'hire_date' => '2023-08-20',
            'salary' => 60000.00,
            'status' => 'active',
            'password' => Hash::make('password123')
        ],
        [
            'employee_id' => 'EMP005',
            'first_name' => 'Tom',
            'last_name' => 'Brown',
            'email' => 'tom.brown@jetlouge.com',
            'phone' => '+1234567894',
            'position' => 'Operations Manager',
            'department' => 'Operations',
            'hire_date' => '2022-11-05',
            'salary' => 80000.00,
            'status' => 'active',
            'password' => Hash::make('password123')
        ],
        [
            'employee_id' => 'EMP006',
            'first_name' => 'Lisa',
            'last_name' => 'Anderson',
            'email' => 'lisa.anderson@jetlouge.com',
            'phone' => '+1234567895',
            'position' => 'Sales Representative',
            'department' => 'Sales',
            'hire_date' => '2023-04-12',
            'salary' => 55000.00,
            'status' => 'active',
            'password' => Hash::make('password123')
        ],
        [
            'employee_id' => 'EMP007',
            'first_name' => 'David',
            'last_name' => 'Martinez',
            'email' => 'david.martinez@jetlouge.com',
            'phone' => '+1234567896',
            'position' => 'Quality Assurance',
            'department' => 'Information Technology',
            'hire_date' => '2023-02-28',
            'salary' => 70000.00,
            'status' => 'active',
            'password' => Hash::make('password123')
        ],
        [
            'employee_id' => 'EMP008',
            'first_name' => 'Emma',
            'last_name' => 'Taylor',
            'email' => 'emma.taylor@jetlouge.com',
            'phone' => '+1234567897',
            'position' => 'HR Assistant',
            'department' => 'Human Resources',
            'hire_date' => '2023-09-15',
            'salary' => 45000.00,
            'status' => 'inactive',
            'password' => Hash::make('password123')
        ]
    ];

    foreach ($employees as $employee) {
        $existing = DB::table('employees')->where('email', $employee['email'])->first();
        if (!$existing) {
            DB::table('employees')->insert(array_merge($employee, [
                'online_status' => 'offline',
                'created_at' => now(),
                'updated_at' => now()
            ]));
            echo "  ✅ Created: {$employee['first_name']} {$employee['last_name']} ({$employee['employee_id']}) - {$employee['position']}\n";
        } else {
            echo "  ⏭️  Exists: {$employee['first_name']} {$employee['last_name']} ({$employee['employee_id']})\n";
        }
    }

    // 2. Create some time entries for employees
    echo "\n⏰ Creating sample time entries...\n";
    
    $employeesFromDb = DB::table('employees')->where('status', 'active')->get();
    
    if ($employeesFromDb->count() > 0) {
        $sampleTimeEntries = [];
        
        foreach ($employeesFromDb->take(5) as $employee) {
            // Create time entries for the last 5 days
            for ($i = 0; $i < 5; $i++) {
                $workDate = Carbon::now()->subDays($i)->format('Y-m-d');
                
                // Check if time entry already exists
                $existing = DB::table('time_entries')
                    ->where('employee_id', $employee->id)
                    ->where('work_date', $workDate)
                    ->first();
                
                if (!$existing) {
                    $clockIn = Carbon::parse($workDate . ' 08:' . rand(0, 30) . ':00');
                    $clockOut = $clockIn->copy()->addHours(8)->addMinutes(rand(0, 60));
                    $hoursWorked = $clockOut->diffInHours($clockIn);
                    $overtimeHours = max(0, $hoursWorked - 8);
                    
                    $sampleTimeEntries[] = [
                        'employee_id' => $employee->id,
                        'work_date' => $workDate,
                        'clock_in_time' => $clockIn->format('H:i:s'),
                        'clock_out_time' => $clockOut->format('H:i:s'),
                        'hours_worked' => $hoursWorked,
                        'overtime_hours' => $overtimeHours,
                        'status' => ['pending', 'approved', 'approved', 'approved'][rand(0, 3)],
                        'description' => 'Regular work day',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
        }
        
        if (!empty($sampleTimeEntries)) {
            DB::table('time_entries')->insert($sampleTimeEntries);
            echo "  ✅ Created " . count($sampleTimeEntries) . " time entries\n";
        } else {
            echo "  ⏭️  Time entries already exist\n";
        }
    }

    // 3. Display summary
    echo "\n📊 Summary:\n";
    $totalEmployees = DB::table('employees')->count();
    $activeEmployees = DB::table('employees')->where('status', 'active')->count();
    $inactiveEmployees = DB::table('employees')->where('status', 'inactive')->count();
    $departments = DB::table('employees')
        ->select('department')
        ->distinct()
        ->whereNotNull('department')
        ->where('department', '!=', '')
        ->count();
    $timeEntries = 0;
    try {
        $timeEntries = DB::table('time_entries')->count();
    } catch (\Exception $e) {
        // time_entries table may not exist
    }

    echo "  👥 Total Employees: {$totalEmployees}\n";
    echo "  ✅ Active Employees: {$activeEmployees}\n";
    echo "  ⏸️  Inactive Employees: {$inactiveEmployees}\n";
    echo "  🏢 Departments: {$departments}\n";
    echo "  ⏰ Time Entries: {$timeEntries}\n";

    echo "\n✅ Employee Management data populated successfully!\n";
    echo "🌐 Visit: http://localhost:8000/employees\n";
    echo "🔑 Employee Login Credentials: [email] / password123\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n\n";
}
