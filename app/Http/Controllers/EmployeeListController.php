<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Traits\DatabaseConnectionTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class EmployeeListController extends Controller
{
    use DatabaseConnectionTrait;

    /**
     * Display a listing of employees from database
     */
    public function index()
    {
        Log::info('EmployeeListController@index called at ' . now());
        
        try {
            // Get employees from local database
            $employees = Employee::orderBy('created_at', 'desc')->get();
            
            // Calculate statistics
            $stats = [
                'total_employees' => $employees->count(),
                'active_employees' => $employees->where('status', 'active')->count(),
                'employees_with_timesheets' => 0, // Can be calculated if needed
            ];

            Log::info('Returning Employee List view with ' . $employees->count() . ' employees');
            
            return view('admin.employees.list', compact('employees', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Employee List Error: ' . $e->getMessage());
            
            return view('admin.employees.list', [
                'employees' => collect(),
                'stats' => [
                    'total_employees' => 0,
                    'active_employees' => 0,
                    'employees_with_timesheets' => 0,
                ]
            ])->with('error', 'Error loading employee list: ' . $e->getMessage());
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
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            $employee = Employee::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'department' => $request->department,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'status' => $request->status,
            ]);

            Log::info('Employee created successfully: ' . $employee->full_name);
            return redirect()->route('employees.list')
                ->with('success', 'Employee created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating employee: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating employee: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            $employee->update($request->only([
                'first_name', 'last_name', 'email', 'phone', 'position', 
                'department', 'hire_date', 'salary', 'status'
            ]));

            Log::info('Employee updated successfully: ' . $employee->full_name);
            return redirect()->route('employees.list')
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
    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employeeName = $employee->full_name;
            $employee->delete();

            Log::info('Employee deleted successfully: ' . $employeeName);
            return redirect()->route('employees.list')
                ->with('success', 'Employee deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting employee: ' . $e->getMessage());
        }
    }

    /**
     * Get employee details for viewing
     */
    public function show($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            return response()->json($employee);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
    }
}
