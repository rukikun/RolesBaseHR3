<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Employee API Data Test ===\n\n";

try {
    // Test the API endpoint
    echo "1. Testing API endpoint: http://hr4.jetlougetravels-ph.com/api/employees\n";
    
    $response = Http::timeout(10)->get('http://hr4.jetlougetravels-ph.com/api/employees');
    
    if ($response->successful()) {
        $apiData = $response->json();
        echo "✅ API Response successful\n";
        echo "📊 Total records: " . count($apiData) . "\n\n";
        
        // Transform data like the controller does
        $employees = collect($apiData)->map(function ($employee) {
            // Map department
            $departmentMap = [
                'Accountant' => 'Finance',
                'Logistics Coordinator' => 'Operations',
                'HR Manager' => 'Human Resources',
                'Software Developer' => 'Information Technology',
                'Sales Representative' => 'Sales',
                'Marketing Specialist' => 'Marketing'
            ];
            
            $department = $departmentMap[$employee['role'] ?? ''] ?? 'General';
            
            // Map status
            $statusMap = [
                'Passed' => 'active',
                'Active' => 'active',
                'Inactive' => 'inactive',
                'Terminated' => 'terminated'
            ];
            
            $status = $statusMap[$employee['status'] ?? 'Active'] ?? 'active';
            
            return (object) [
                'id' => $employee['id'],
                'first_name' => $employee['first_name'] ?? '',
                'last_name' => $employee['last_name'] ?? '',
                'name' => $employee['name'],
                'email' => $employee['email'],
                'position' => $employee['role'] ?? $employee['job_title'] ?? 'N/A',
                'department' => $department,
                'status' => $status,
                'phone' => $employee['phone'] ?? null,
                'hire_date' => $employee['date_hired'] ?? $employee['start_date'] ?? null,
                'external_id' => $employee['external_employee_id'] ?? null
            ];
        });
        
        echo "2. Transformed Employee Data:\n";
        echo "=" . str_repeat("=", 80) . "\n";
        printf("%-4s %-25s %-20s %-15s %-10s\n", "ID", "Name", "Position", "Department", "Status");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($employees as $employee) {
            printf("%-4s %-25s %-20s %-15s %-10s\n", 
                "#" . str_pad($employee->id, 3, '0', STR_PAD_LEFT),
                substr($employee->name, 0, 24),
                substr($employee->position, 0, 19),
                substr($employee->department, 0, 14),
                ucfirst($employee->status)
            );
        }
        
        echo "\n3. Statistics:\n";
        echo "- Total Employees: " . $employees->count() . "\n";
        echo "- Active Employees: " . $employees->where('status', 'active')->count() . "\n";
        echo "- Departments: " . $employees->pluck('department')->unique()->implode(', ') . "\n";
        
        echo "\n4. Raw API Data Sample:\n";
        echo json_encode($apiData[0], JSON_PRETTY_PRINT) . "\n";
        
    } else {
        echo "❌ API request failed with status: " . $response->status() . "\n";
        echo "Response: " . $response->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n✅ Employee API test completed!\n";
