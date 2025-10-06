<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EmployeesController extends Controller
{
    /**
     * Display employees page with API data
     */
    public function index(Request $request)
    {
        Log::info('EmployeesController@index called - fetching from API');
        
        // Initialize empty employees collection
        $employees = collect();
        
        try {
            // Fetch data from external API
            $response = Http::timeout(10)->get('http://hr4.jetlougetravels-ph.com/api/employees');

            if ($response->successful()) {
                $apiData = $response->json();
                Log::info('API Response received - Count: ' . count($apiData));
                
                if (is_array($apiData) && !empty($apiData)) {
                    // Transform API data to objects that match the view expectations
                    $employees = collect($apiData)->map(function ($employee) {
                        return (object) [
                            'id' => $employee['id'],
                            'first_name' => $employee['first_name'] ?? '',
                            'last_name' => $employee['last_name'] ?? '',
                            'name' => $employee['name'],
                            'email' => $employee['email'],
                            'position' => $employee['role'] ?? $employee['job_title'] ?? 'N/A',
                            'department' => $this->mapDepartment($employee['role'] ?? ''),
                            'status' => $this->mapStatus($employee['status'] ?? 'Active'),
                            'phone' => $employee['phone'] ?? null,
                            'hire_date' => $employee['date_hired'] ?? $employee['start_date'] ?? null,
                            'salary' => null, // Not provided by API
                            'external_id' => $employee['external_employee_id'] ?? null,
                            'age' => $employee['age'] ?? null,
                            'gender' => $employee['gender'] ?? null,
                            'address' => $employee['address'] ?? null
                        ];
                    });
                    
                    // Apply filters if requested
                    if ($request->has('status')) {
                        $employees = $employees->where('status', $request->get('status'));
                    }
                    
                    if ($request->has('view') && $request->get('view') === 'departments') {
                        $employees = $employees->whereNotNull('department')->where('department', '!=', '');
                    }
                    
                    Log::info('Successfully transformed ' . $employees->count() . ' employees from API');
                }
            } else {
                Log::warning('API request failed with status: ' . $response->status());
            }
            
        } catch (\Exception $e) {
            Log::error('Employee API Error: ' . $e->getMessage());
        }

        // Calculate statistics
        $stats = [
            'total_employees' => $employees->count(),
            'active_employees' => $employees->where('status', 'active')->count(),
            'employees_with_timesheets' => 0, // Not available from API
        ];

        Log::info('Returning view with ' . $employees->count() . ' employees');
        
        return view('admin.employees.index', compact('employees', 'stats'));
    }
    
    /**
     * Map API role to department
     */
    private function mapDepartment($role)
    {
        $departmentMap = [
            'Accountant' => 'Finance',
            'Logistics Coordinator' => 'Operations',
            'HR Manager' => 'Human Resources',
            'Software Developer' => 'Information Technology',
            'Sales Representative' => 'Sales',
            'Marketing Specialist' => 'Marketing'
        ];
        
        return $departmentMap[$role] ?? 'General';
    }
    
    /**
     * Map API status to view status
     */
    private function mapStatus($apiStatus)
    {
        $statusMap = [
            'Passed' => 'active',
            'Active' => 'active',
            'Inactive' => 'inactive',
            'Terminated' => 'terminated'
        ];
        
        return $statusMap[$apiStatus] ?? 'active';
    }
    
    /**
     * Store a new employee
     */
    public function store(Request $request)
    {
        return response()->json(['message' => 'Store method not implemented for API-based system']);
    }
    
    /**
     * View employee details
     */
    public function view($id)
    {
        return response()->json(['message' => 'View method not implemented for API-based system']);
    }
    
    /**
     * Update employee
     */
    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Update method not implemented for API-based system']);
    }
    
    /**
     * Delete employee
     */
    public function destroy($id)
    {
        return response()->json(['message' => 'Delete method not implemented for API-based system']);
    }
}
