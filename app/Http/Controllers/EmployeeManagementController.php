<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    public function index(Request $request)
    {
        try {
            // Build API URL with query parameters
            $apiUrl = 'http://hr4.jetlougetravels-ph.com/api/employees';
            $queryParams = [];
            
            // Add filters if present
            if ($request->has('status') && $request->status != '') {
                $queryParams['status'] = $request->status;
            }
            
            if ($request->has('department') && $request->department != '') {
                $queryParams['department'] = $request->department;
            }
            
            if ($request->has('search') && $request->search != '') {
                $queryParams['search'] = $request->search;
            }
            
            // Add query parameters to URL if any
            if (!empty($queryParams)) {
                $apiUrl .= '?' . http_build_query($queryParams);
            }
            
            $response = Http::get($apiUrl);
            
            if ($response->successful()) {
                $apiData = $response->json();
                // Extract employees from the 'data' field of API response
                $employees = collect($apiData['data'] ?? []);
                
                // Convert to objects for blade compatibility
                $employees = $employees->map(function($employee) {
                    return (object) $employee;
                });
            } else {
                $employees = collect([]);
                Log::error('API request failed: ' . $response->body());
            }
            
            // Get departments for filter dropdown
            $departments = $employees->pluck('department')->filter()->unique()->values();
            
            return view('admin.employees.index', compact('employees', 'departments'));
            
        } catch (\Exception $e) {
            Log::error('Error fetching employees from API: ' . $e->getMessage());
            $employees = collect([]);
            $departments = collect([]);
            return view('admin.employees.index', compact('employees', 'departments'))
                ->with('error', 'Error loading employees: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        try {
            // Get departments from API
            $response = Http::get('http://127.0.0.1:8000/api/employees/departments/list');
            
            if ($response->successful()) {
                $apiData = $response->json();
                $departments = collect($apiData['data'] ?? []);
            } else {
                $departments = collect(['IT', 'HR', 'Finance', 'Marketing', 'Operations']); // Fallback
            }
            
            return view('admin.employees.create', compact('departments'));
            
        } catch (\Exception $e) {
            Log::error('Error fetching departments: ' . $e->getMessage());
            $departments = collect(['IT', 'HR', 'Finance', 'Marketing', 'Operations']); // Fallback
            return view('admin.employees.create', compact('departments'));
        }
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
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
            // Prepare data for API
            $employeeData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'department' => $request->department,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'password' => $request->password, // API will handle hashing
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'bank_account_number' => $request->bank_account_number,
                'tax_id' => $request->tax_id,
                'status' => 'active'
            ];

            // Send to API
            $response = Http::post('http://127.0.0.1:8000/api/employees', $employeeData);

            if ($response->successful()) {
                $apiData = $response->json();
                Log::info('Employee created successfully via API');
                return redirect()->route('employees.index')
                    ->with('success', 'Employee created successfully!');
            } else {
                $errorMessage = 'API Error: ' . $response->body();
                Log::error('API error creating employee: ' . $errorMessage);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error creating employee: ' . $errorMessage);
            }

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
    public function show($id)
    {
        try {
            // Get employee from API
            $response = Http::get("http://127.0.0.1:8000/api/employees/{$id}");
            
            if ($response->successful()) {
                $apiData = $response->json();
                $employee = (object) ($apiData['data'] ?? []);
                return view('admin.employees.show', compact('employee'));
            } else {
                Log::error('API error fetching employee: ' . $response->body());
                return redirect()->back()->with('error', 'Employee not found.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error showing employee: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading employee details.');
        }
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit($id)
    {
        try {
            // Get employee from API
            $response = Http::get("http://127.0.0.1:8000/api/employees/{$id}");
            
            if ($response->successful()) {
                $apiData = $response->json();
                $employee = (object) ($apiData['data'] ?? []);
                
                // Get departments from API
                $deptResponse = Http::get('http://127.0.0.1:8000/api/employees/departments/list');
                if ($deptResponse->successful()) {
                    $deptData = $deptResponse->json();
                    $departments = collect($deptData['data'] ?? []);
                } else {
                    $departments = collect(['IT', 'HR', 'Finance', 'Marketing', 'Operations']);
                }
                
                return view('admin.employees.edit', compact('employee', 'departments'));
            } else {
                Log::error('API error fetching employee: ' . $response->body());
                return redirect()->back()->with('error', 'Employee not found.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error loading employee for edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading employee details.');
        }
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
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
            // Prepare data for API
            $updateData = $request->except(['password', '_token', '_method']);
            
            // Only include password if provided
            if ($request->filled('password')) {
                $updateData['password'] = $request->password; // API will handle hashing
            }

            // Send to API
            $response = Http::put("http://127.0.0.1:8000/api/employees/{$id}", $updateData);

            if ($response->successful()) {
                Log::info('Employee updated successfully via API');
                return redirect()->route('employees.index')
                    ->with('success', 'Employee updated successfully!');
            } else {
                $errorMessage = 'API Error: ' . $response->body();
                Log::error('API error updating employee: ' . $errorMessage);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error updating employee: ' . $errorMessage);
            }

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
    public function destroy($id)
    {
        try {
            // Delete via API
            $response = Http::delete("http://127.0.0.1:8000/api/employees/{$id}");

            if ($response->successful()) {
                Log::info('Employee deleted successfully via API');
                return redirect()->route('employees.index')
                    ->with('success', 'Employee deleted successfully!');
            } else {
                $errorMessage = 'API Error: ' . $response->body();
                Log::error('API error deleting employee: ' . $errorMessage);
                return redirect()->back()
                    ->with('error', 'Error deleting employee: ' . $errorMessage);
            }

        } catch (\Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting employee: ' . $e->getMessage());
        }
    }

    /**
     * Get employee statistics via API
     */
    public function getStats()
    {
        try {
            $response = Http::get('http://127.0.0.1:8000/api/employees/stats/summary');
            
            if ($response->successful()) {
                $apiData = $response->json();
                return $apiData['data'] ?? [
                    'total' => 0,
                    'active' => 0,
                    'inactive' => 0,
                    'online' => 0,
                    'departments' => 0
                ];
            } else {
                Log::error('API error getting employee stats: ' . $response->body());
                return [
                    'total' => 0,
                    'active' => 0,
                    'inactive' => 0,
                    'online' => 0,
                    'departments' => 0
                ];
            }
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
