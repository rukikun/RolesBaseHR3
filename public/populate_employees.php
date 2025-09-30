<?php
// Simple script to populate employees table with sample data
require_once __DIR__ . '/../vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Check if employees table exists
    $tablesExist = DB::select("SHOW TABLES LIKE 'employees'");
    
    if (empty($tablesExist)) {
        echo "Error: employees table does not exist. Please run migrations first.\n";
        exit;
    }
    
    // Check if employees already exist
    $existingCount = DB::table('employees')->count();
    
    if ($existingCount > 0) {
        echo "Employees table already has $existingCount records.\n";
        echo "Current employees:\n";
        $employees = DB::table('employees')->select('id', 'first_name', 'last_name', 'email', 'status')->get();
        foreach ($employees as $emp) {
            echo "- ID: {$emp->id}, Name: {$emp->first_name} {$emp->last_name}, Email: {$emp->email}, Status: {$emp->status}\n";
        }
    } else {
        echo "Inserting sample employees...\n";
        
        // Insert sample employees
        DB::table('employees')->insert([
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@company.com',
                'phone' => '555-0123',
                'position' => 'Software Developer',
                'department' => 'IT',
                'hire_date' => '2024-01-15',
                'salary' => 75000.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@company.com',
                'phone' => '555-0124',
                'position' => 'HR Manager',
                'department' => 'Human Resources',
                'hire_date' => '2023-06-10',
                'salary' => 68000.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@company.com',
                'phone' => '555-0125',
                'position' => 'Marketing Specialist',
                'department' => 'Marketing',
                'hire_date' => '2024-03-20',
                'salary' => 55000.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Wilson',
                'email' => 'sarah.wilson@company.com',
                'phone' => '555-0126',
                'position' => 'Accountant',
                'department' => 'Finance',
                'hire_date' => '2023-11-05',
                'salary' => 62000.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@company.com',
                'phone' => '555-0127',
                'position' => 'Operations Manager',
                'department' => 'Operations',
                'hire_date' => '2024-02-01',
                'salary' => 72000.00,
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        echo "Sample employees inserted successfully!\n";
        
        // Check if time_entries table exists and add sample timesheet data
        $timesheetTableExists = DB::select("SHOW TABLES LIKE 'time_entries'");
        
        if (!empty($timesheetTableExists)) {
            echo "Adding sample timesheet entries...\n";
            
            DB::table('time_entries')->insert([
                [
                    'employee_id' => 1,
                    'work_date' => today(),
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.0,
                    'status' => 'approved',
                    'description' => 'Regular work day',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'employee_id' => 2,
                    'work_date' => today(),
                    'hours_worked' => 8.0,
                    'overtime_hours' => 1.5,
                    'status' => 'pending',
                    'description' => 'Overtime for project deadline',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'employee_id' => 3,
                    'work_date' => today(),
                    'hours_worked' => 7.5,
                    'overtime_hours' => 0.0,
                    'status' => 'approved',
                    'description' => 'Marketing campaign work',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            
            echo "Sample timesheet entries added successfully!\n";
        }
    }
    
    // Display final count
    $finalCount = DB::table('employees')->count();
    echo "\nTotal employees in database: $finalCount\n";
    echo "Active employees: " . DB::table('employees')->where('status', 'active')->count() . "\n";
    echo "Departments: " . DB::table('employees')->whereNotNull('department')->distinct('department')->count() . "\n";
    
    if (!empty($timesheetTableExists)) {
        $timesheetCount = DB::table('time_entries')->count();
        echo "Total timesheet entries: $timesheetCount\n";
        
        $todayTimesheets = DB::table('employees')
            ->join('time_entries', 'employees.id', '=', 'time_entries.employee_id')
            ->where('employees.status', 'active')
            ->whereDate('time_entries.work_date', today())
            ->distinct('employees.id')
            ->count();
        echo "Employees with timesheets today: $todayTimesheets\n";
    }
    
    echo "\nDatabase population completed successfully!\n";
    echo "You can now refresh the timesheet management page to see the employee data.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Please check your database connection and ensure the tables exist.\n";
}
?>
