<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Traits\DatabaseConnectionTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class EmployeeManagementController extends Controller
{
    use DatabaseConnectionTrait;

    /**
     * Display a listing of employees
     */
    public function index()
    {
        \Log::info('EmployeeManagementController@index called at ' . now());
        
        // Initialize empty employees collection
        $employees = collect();
        
        try {
            // Fetch data from HR4 API
            $response = Http::timeout(10)->get('https://hr4.jetlougetravels-ph.com/api/employees');

            if ($response->successful()) {
                $apiData = $response->json();
                \Log::info('API Response received - Count: ' . count($apiData));
                
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
                            'external_id' => $employee['external_employee_id'] ?? null,
                            'salary' => null, // Not provided by API
                            'age' => $employee['age'] ?? null,
                            'gender' => $employee['gender'] ?? null,
                            'address' => $employee['address'] ?? null
                        ];
                    });
                    
                    \Log::info('Successfully transformed ' . $employees->count() . ' employees from HR4 API');
                }
            } else {
                \Log::warning('HR4 API request failed with status: ' . $response->status());
                // Fallback to local database if API fails
                $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
                $employees = $employees->map(function($employee) {
                    $employee->name = $employee->first_name . ' ' . $employee->last_name;
                    return $employee;
                });
                \Log::info('Fallback: Retrieved ' . $employees->count() . ' employees from local database');
            }
            
        } catch (\Exception $e) {
            \Log::error('Employee Management API Error: ' . $e->getMessage());
            // Fallback to local database
            try {
                $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
                $employees = $employees->map(function($employee) {
                    $employee->name = $employee->first_name . ' ' . $employee->last_name;
                    return $employee;
                });
                \Log::info('Fallback: Retrieved ' . $employees->count() . ' employees from local database');
            } catch (\Exception $e2) {
                \Log::error('Both API and local database failed: ' . $e2->getMessage());
                $employees = collect();
            }
        }

        // Calculate statistics from the fetched employees
        $stats = [
            'total_employees' => $employees->count(),
            'active_employees' => $employees->where('status', 'active')->count(),
            'departments' => $employees->pluck('department')->filter()->unique()->count(),
            'employees_with_timesheets' => 0, // Not available from API
            'online_employees' => 0, // Not available from API
            'inactive_employees' => $employees->where('status', 'inactive')->count(),
            'terminated_employees' => $employees->where('status', 'terminated')->count(),
            'recent_hires' => $employees->filter(function($employee) {
                return $employee->hire_date && \Carbon\Carbon::parse($employee->hire_date)->gte(now()->subDays(30));
            })->count()
        ];

        \Log::info('Returning view with ' . $employees->count() . ' employees');
        
        return view('admin.employees.index', compact('employees', 'stats'));
    }

    /**
     * Export API data to local database
     */
    public function exportData(Request $request)
    {
        try {
            Log::info('Starting export data process...');
            
            // Fetch data from API
            $response = Http::timeout(10)->get('https://hr4.jetlougetravels-ph.com/api/employees');

            if (!$response->successful()) {
                Log::error('API request failed with status: ' . $response->status());
                return response()->json(['error' => 'Failed to fetch data from API. Status: ' . $response->status()], 500);
            }

            $apiData = $response->json();
            Log::info('API data received. Count: ' . count($apiData));
            
            if (empty($apiData)) {
                return response()->json(['error' => 'No data received from API'], 400);
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($apiData as $index => $employeeData) {
                try {
                    // Validate required fields
                    if (empty($employeeData['email'])) {
                        $errors[] = "Employee at index {$index}: Missing email";
                        continue;
                    }

                    // Check if employee already exists by email
                    $existingEmployee = Employee::where('email', $employeeData['email'])->first();
                    
                    if ($existingEmployee) {
                        $skipped++;
                        continue;
                    }

                    // Create new employee record (only using columns that exist in the table)
                    $newEmployee = Employee::create([
                        'first_name' => $employeeData['first_name'] ?? '',
                        'last_name' => $employeeData['last_name'] ?? '',
                        'email' => $employeeData['email'],
                        'phone' => $employeeData['phone'] ?? null,
                        'position' => $employeeData['role'] ?? $employeeData['job_title'] ?? 'N/A',
                        'department' => $this->mapDepartment($employeeData['role'] ?? ''),
                        'status' => $this->mapStatus($employeeData['status'] ?? 'Active'),
                        'hire_date' => $employeeData['date_hired'] ?? $employeeData['start_date'] ?? null,
                        'salary' => 0.00, // Default salary
                        'role' => 'employee', // Default role for imported employees
                        'online_status' => 'offline',
                        'password' => \Hash::make('password123'), // Default password
                    ]);

                    $imported++;
                    Log::info("Imported employee: {$newEmployee->email}");

                } catch (\Exception $e) {
                    $errors[] = "Employee at index {$index}: " . $e->getMessage();
                    Log::error("Error importing employee at index {$index}: " . $e->getMessage());
                }
            }

            Log::info("Data export completed: {$imported} imported, {$skipped} skipped, " . count($errors) . " errors");
            
            $message = "Successfully imported {$imported} employees. {$skipped} employees were skipped (already exist).";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " errors occurred.";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Export Data Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export individual employee data to local database
     */
    public function exportSingleEmployee(Request $request, $id)
    {
        try {
            // Fetch data from API
            $response = Http::timeout(10)->get('https://hr4.jetlougetravels-ph.com/api/employees');

            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch data from API'], 500);
            }

            $apiData = $response->json();
            $employeeData = null;

            // Find the specific employee by ID
            foreach ($apiData as $employee) {
                if ($employee['id'] == $id) {
                    $employeeData = $employee;
                    break;
                }
            }

            if (!$employeeData) {
                return response()->json(['error' => 'Employee not found in API data'], 404);
            }

            // Check if employee already exists by email
            $existingEmployee = Employee::where('email', $employeeData['email'])->first();
            
            if ($existingEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee already exists in local database'
                ]);
            }

            // Create new employee record (only using columns that exist in the table)
            $newEmployee = Employee::create([
                'first_name' => $employeeData['first_name'] ?? '',
                'last_name' => $employeeData['last_name'] ?? '',
                'email' => $employeeData['email'],
                'phone' => $employeeData['phone'] ?? null,
                'position' => $employeeData['role'] ?? $employeeData['job_title'] ?? 'N/A',
                'department' => $this->mapDepartment($employeeData['role'] ?? ''),
                'status' => $this->mapStatus($employeeData['status'] ?? 'Active'),
                'hire_date' => $employeeData['date_hired'] ?? $employeeData['start_date'] ?? null,
                'salary' => 0.00, // Default salary
                'role' => 'employee', // Default role for imported employees
                'online_status' => 'offline',
                'password' => \Hash::make('password123'), // Default password
            ]);

            Log::info("Individual employee exported: {$newEmployee->first_name} {$newEmployee->last_name}");
            
            return response()->json([
                'success' => true,
                'message' => "Successfully imported employee: {$newEmployee->first_name} {$newEmployee->last_name}",
                'employee' => $newEmployee
            ]);

        } catch (\Exception $e) {
            Log::error('Export Single Employee Error: ' . $e->getMessage());
            return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
        }
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
     * Show the form for creating a new employee
     */
    public function create()
    {
        $departments = Employee::distinct()->pluck('department')->filter();
        return view('admin.employees.create', compact('departments'));
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'password' => 'required|string|min:6',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'bank_account_number' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Generate employee ID if not provided
            $employeeId = $this->generateEmployeeId();

            $employee = Employee::create([
                'employee_id' => $employeeId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'department' => $request->department,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'password' => Hash::make($request->password),
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'bank_account_number' => $request->bank_account_number,
                'tax_id' => $request->tax_id,
                'status' => 'active'
            ]);

            Log::info('Employee created successfully: ' . $employee->full_name);
            return redirect()->route('employees.index')
                ->with('success', 'Employee created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating employee: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating employee: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee)
    {
        try {
            $employee->load(['timeEntries', 'shifts', 'leaveRequests', 'claims']);
            return view('admin.employees.show', compact('employee'));
        } catch (\Exception $e) {
            Log::error('Error showing employee: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading employee details.');
        }
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(Employee $employee)
    {
        $departments = Employee::distinct()->pluck('department')->filter();
        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated',
            'password' => 'nullable|string|min:6',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'bank_account_number' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            $updateData = $request->except(['password']);
            
            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $employee->update($updateData);

            Log::info('Employee updated successfully: ' . $employee->full_name);
            return redirect()->route('employees.index')
                ->with('success', 'Employee updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating employee: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating employee: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee)
    {
        try {
            $employeeName = $employee->full_name;
            $employee->delete();

            Log::info('Employee deleted successfully: ' . $employeeName);
            return redirect()->route('employees.index')
                ->with('success', 'Employee deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting employee: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique employee ID
     */
    private function generateEmployeeId()
    {
        $lastEmployee = Employee::orderBy('id', 'desc')->first();
        $nextId = $lastEmployee ? $lastEmployee->id + 1 : 1;
        return 'EMP' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get employee statistics
     */
    public function getStats()
    {
        try {
            return [
                'total' => Employee::count(),
                'active' => Employee::where('status', 'active')->count(),
                'inactive' => Employee::where('status', 'inactive')->count(),
                'online' => Employee::where('online_status', 'online')->count(),
                'departments' => Employee::distinct()->count('department')
            ];
        } catch (\Exception $e) {
            Log::error('Error getting employee stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'online' => 0,
                'departments' => 0
            ];
        }
    }
}
