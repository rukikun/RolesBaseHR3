<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Traits\DatabaseConnectionTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;


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
            $apiErrors = [];
            $apiUrls = [
                'https://hr4.jetlougetravels-ph.com/api/employees',
                'http://hr4.jetlougetravels-ph.com/api/employees'
            ];

            foreach ($apiUrls as $apiUrl) {
                try {
                    $response = Http::timeout(15)
                        ->acceptJson()
                        ->withoutVerifying()
                        ->get($apiUrl);

                    if ($response->successful()) {
                        $apiData = $response->json();
                        $apiPayload = $apiData;
                        if (is_array($apiData)) {
                            if (array_key_exists('data', $apiData) && is_array($apiData['data'])) {
                                $apiPayload = $apiData['data'];
                            } elseif (array_key_exists('employees', $apiData) && is_array($apiData['employees'])) {
                                $apiPayload = $apiData['employees'];
                            }
                        }

                        if (is_array($apiPayload)) {
                            \Log::info('API Response received from ' . $apiUrl . ' - Count: ' . count($apiPayload));
                        }

                        if (is_array($apiPayload) && !empty($apiPayload)) {
                            // Transform API data to objects that match the view expectations
                            $employees = collect($apiPayload)->map(function ($employee) {
                                $fullName = $employee['full_name']
                                    ?? $employee['name']
                                    ?? trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
                                $firstName = $employee['first_name'] ?? '';
                                $lastName = $employee['last_name'] ?? '';
                                $position = $employee['position']
                                    ?? $employee['job_title']
                                    ?? $employee['role']
                                    ?? $employee['title']
                                    ?? 'N/A';
                                $departmentRaw = '';
                                if (array_key_exists('department', $employee)) {
                                    if (is_array($employee['department'])) {
                                        $departmentRaw = $employee['department']['name'] ?? '';
                                    } else {
                                        $departmentRaw = $employee['department'] ?? '';
                                    }
                                }
                                if ($departmentRaw === '') {
                                    $departmentRaw = $employee['division']
                                        ?? $employee['team']
                                        ?? '';
                                }
                                $department = $departmentRaw !== ''
                                    ? $departmentRaw
                                    : $this->mapDepartment($employee['role'] ?? $position);

                                if ($firstName === '' && $lastName === '' && !empty($fullName)) {
                                    $nameParts = preg_split('/\s+/', trim($fullName));
                                    $firstName = $nameParts[0] ?? '';
                                    $lastName = trim(implode(' ', array_slice($nameParts, 1)));
                                }

                                return (object) [
                                    'id' => $employee['id'],
                                    'first_name' => $firstName,
                                    'last_name' => $lastName,
                                    'name' => $fullName,
                                    'email' => $employee['email'],
                                    'position' => $position,
                                    'department' => $department,
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
                            break;
                        }
                    } else {
                        $apiErrors[] = $apiUrl . ' responded with status ' . $response->status();
                    }
                } catch (\Exception $innerException) {
                    $apiErrors[] = $apiUrl . ' error: ' . $innerException->getMessage();
                }
            }

            if ($employees->isEmpty()) {
                if (!empty($apiErrors)) {
                    \Log::warning('HR4 API request failed: ' . implode(' | ', $apiErrors));
                }
                session()->flash('error', 'Unable to load employees from HR4 at the moment.');
            }

        } catch (\Exception $e) {
            \Log::error('Employee Management API Error: ' . $e->getMessage());
            $employees = collect();
            session()->flash('error', 'Unable to load employees from HR4 at the moment.');
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

            $apiErrors = [];
            $employeesPayload = $this->fetchApiPayload([
                'https://hr4.jetlougetravels-ph.com/api/employees',
                'http://hr4.jetlougetravels-ph.com/api/employees'
            ], $apiErrors);

            if (!$employeesPayload) {
                Log::error('Employees API request failed: ' . implode(' | ', $apiErrors));
                return response()->json(['error' => 'Failed to fetch employees from API.'], 500);
            }

            $employees = $this->extractEmployees($employeesPayload);
            Log::info('Employees API data received. Count: ' . count($employees));

            if (empty($employees)) {
                return response()->json(['error' => 'No employee data received from API'], 400);
            }

            $accountsPayload = $this->fetchApiPayload([
                'https://hr4.jetlougetravels-ph.com/api/accounts',
                'http://hr4.jetlougetravels-ph.com/api/accounts'
            ], $apiErrors);
            $accounts = $this->extractAccounts($accountsPayload);
            [$accountsByEmployeeId, $accountsByEmail] = $this->indexAccounts($accounts);

            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];

            foreach ($employees as $index => $employeeData) {
                try {
                    $email = $employeeData['email'] ?? null;
                    if (!$email) {
                        $skipped++;
                        $errors[] = "Employee at index {$index}: Missing email";
                        continue;
                    }

                    $account = $this->resolveAccount($employeeData, $accountsByEmployeeId, $accountsByEmail);
                    $updateData = $this->buildEmployeeSyncData($employeeData, $account);

                    $employee = Employee::updateOrCreate(['email' => $email], $updateData);
                    if ($employee->wasRecentlyCreated) {
                        $imported++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Employee at index {$index}: " . $e->getMessage();
                    Log::error("Error syncing employee at index {$index}: " . $e->getMessage());
                }
            }

            Log::info("Employee sync completed: {$imported} created, {$updated} updated, {$skipped} skipped, " . count($errors) . " errors");

            $message = "Sync completed: {$imported} created, {$updated} updated, {$skipped} skipped.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " errors occurred.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'updated' => $updated,
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
            $apiErrors = [];
            $employeesPayload = $this->fetchApiPayload([
                'https://hr4.jetlougetravels-ph.com/api/employees',
                'http://hr4.jetlougetravels-ph.com/api/employees'
            ], $apiErrors);

            if (!$employeesPayload) {
                return response()->json(['error' => 'Failed to fetch employees from API'], 500);
            }

            $employees = $this->extractEmployees($employeesPayload);
            $employeeData = collect($employees)->firstWhere('id', (int) $id);

            if (!$employeeData) {
                return response()->json(['error' => 'Employee not found in API data'], 404);
            }

            $accountsPayload = $this->fetchApiPayload([
                'https://hr4.jetlougetravels-ph.com/api/accounts',
                'http://hr4.jetlougetravels-ph.com/api/accounts'
            ], $apiErrors);
            $accounts = $this->extractAccounts($accountsPayload);
            [$accountsByEmployeeId, $accountsByEmail] = $this->indexAccounts($accounts);

            $account = $this->resolveAccount($employeeData, $accountsByEmployeeId, $accountsByEmail);
            $updateData = $this->buildEmployeeSyncData($employeeData, $account);

            $employee = Employee::updateOrCreate(['email' => $employeeData['email']], $updateData);
            $action = $employee->wasRecentlyCreated ? 'imported' : 'updated';

            Log::info("Individual employee synced: {$employee->first_name} {$employee->last_name}");

            return response()->json([
                'success' => true,
                'message' => "Successfully {$action} employee: {$employee->first_name} {$employee->last_name}",
                'employee' => $employee
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
        $normalized = strtolower(trim((string) $apiStatus));
        $statusMap = [
            'passed' => 'active',
            'active' => 'active',
            'regular' => 'active',
            'new_hire' => 'active',
            'inactive' => 'inactive',
            'terminated' => 'terminated',
            'resigned' => 'terminated',
            'separated' => 'terminated'
        ];

        return $statusMap[$normalized] ?? 'active';
    }

    /**
     * Fetch API payload with HTTP and stream/cURL fallback.
     */
    private function fetchApiPayload(array $urls, array &$apiErrors): ?array
    {
        foreach ($urls as $url) {
            try {
                $response = Http::timeout(15)
                    ->acceptJson()
                    ->withoutVerifying()
                    ->get($url);

                if ($response->successful()) {
                    $payload = $response->json();
                    if (is_array($payload)) {
                        return $payload;
                    }
                } else {
                    $apiErrors[] = $url . ' responded with status ' . $response->status();
                }
            } catch (\Exception $e) {
                $apiErrors[] = $url . ' error: ' . $e->getMessage();
            }

            $streamPayload = $this->fetchPayloadViaStream($url, $apiErrors);
            if ($streamPayload) {
                return $streamPayload;
            }
        }

        return null;
    }

    private function fetchPayloadViaStream(string $url, array &$apiErrors): ?array
    {
        $allowUrlFopen = filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN);
        if ($allowUrlFopen) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
                'http' => [
                    'timeout' => 15,
                ],
            ]);
            $response = @file_get_contents($url, false, $context);
            if ($response !== false) {
                $payload = json_decode($response, true);
                return is_array($payload) ? $payload : null;
            }

            $error = error_get_last();
            $apiErrors[] = $url . ' stream error: ' . ($error['message'] ?? 'unknown error');
        }

        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $statusCode >= 400) {
            $apiErrors[] = $url . ' curl error: ' . ($curlError ?: 'HTTP ' . $statusCode);
            return null;
        }

        $payload = json_decode($response, true);
        return is_array($payload) ? $payload : null;
    }

    private function extractEmployees($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        if (isset($payload['employees']) && is_array($payload['employees'])) {
            return $payload['employees'];
        }

        return array_values($payload);
    }

    private function extractAccounts($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            $payload = $payload['data'];
        }

        $systemAccounts = $payload['system_accounts'] ?? [];
        $essAccounts = $payload['ess_accounts'] ?? [];

        return array_merge(
            is_array($systemAccounts) ? $systemAccounts : [],
            is_array($essAccounts) ? $essAccounts : []
        );
    }

    private function indexAccounts(array $accounts): array
    {
        $byEmployeeId = [];
        $byEmail = [];

        foreach ($accounts as $account) {
            if (!is_array($account)) {
                continue;
            }

            $employee = $account['employee'] ?? [];
            $employeeId = $account['employee_id'] ?? $employee['id'] ?? null;
            $email = $employee['email'] ?? $account['email'] ?? null;

            if ($employeeId) {
                $this->storeAccountWithPriority($byEmployeeId, $employeeId, $account);
            }

            if ($email) {
                $this->storeAccountWithPriority($byEmail, strtolower(trim($email)), $account);
            }
        }

        return [$byEmployeeId, $byEmail];
    }

    private function storeAccountWithPriority(array &$bucket, $key, array $account): void
    {
        $priority = $this->accountPriority($account);
        if (!isset($bucket[$key]) || $priority > $this->accountPriority($bucket[$key])) {
            $bucket[$key] = $account;
        }
    }

    private function accountPriority(array $account): int
    {
        $accountType = strtolower(trim((string) ($account['account_type'] ?? '')));
        return $accountType === 'system' ? 2 : 1;
    }

    private function resolveAccount(array $employeeData, array $accountsByEmployeeId, array $accountsByEmail): ?array
    {
        $employeeId = $employeeData['id'] ?? null;
        if ($employeeId && isset($accountsByEmployeeId[$employeeId])) {
            return $accountsByEmployeeId[$employeeId];
        }

        $email = $employeeData['email'] ?? null;
        if ($email) {
            $key = strtolower(trim($email));
            return $accountsByEmail[$key] ?? null;
        }

        return null;
    }

    private function buildEmployeeSyncData(array $employeeData, ?array $account): array
    {
        $firstName = $employeeData['first_name'] ?? '';
        $lastName = $employeeData['last_name'] ?? '';
        if ($firstName === '' && $lastName === '' && !empty($employeeData['full_name'])) {
            $nameParts = preg_split('/\s+/', trim($employeeData['full_name']));
            $firstName = $nameParts[0] ?? 'Unknown';
            $lastName = trim(implode(' ', array_slice($nameParts, 1)));
        }

        $position = $employeeData['position']
            ?? $employeeData['role']
            ?? $employeeData['job_title']
            ?? 'Employee';

        $department = null;
        if (isset($employeeData['department'])) {
            if (is_array($employeeData['department'])) {
                $department = $employeeData['department']['name'] ?? null;
            } else {
                $department = $employeeData['department'];
            }
        }
        if (!$department) {
            $department = $this->mapDepartment($position);
        }

        $hireDate = $this->normalizeDate($employeeData['date_hired'] ?? $employeeData['start_date'] ?? null)
            ?? now()->toDateString();

        $status = $this->mapStatus($employeeData['status'] ?? $employeeData['employee_status'] ?? null);
        $role = $this->mapRoleFromPosition($position);

        $data = [
            'first_name' => $firstName ?: 'Unknown',
            'last_name' => $lastName ?: 'Employee',
            'email' => $employeeData['email'],
            'phone' => $employeeData['phone'] ?? null,
            'position' => $position,
            'department' => $department ?: 'General',
            'hire_date' => $hireDate,
            'status' => $status,
            'role' => $role,
            'online_status' => 'offline',
            'salary' => $employeeData['salary'] ?? 0.00,
        ];

        if (Schema::hasColumn('employees', 'address')) {
            $data['address'] = $employeeData['address'] ?? null;
        }

        if (Schema::hasColumn('employees', 'gender')) {
            $data['gender'] = $employeeData['gender'] ?? null;
        }

        if (Schema::hasColumn('employees', 'date_of_birth')) {
            $data['date_of_birth'] = $this->normalizeDate($employeeData['birth_date'] ?? null);
        }

        if ($account && !empty($account['password'])) {
            $data['password'] = Hash::make(trim((string) $account['password']));
        }

        if ($account) {
            if (Schema::hasColumn('employees', 'profile_picture_url')) {
                $data['profile_picture_url'] = $account['profile_picture'] ?? null;
            } elseif (Schema::hasColumn('employees', 'profile_picture')) {
                $data['profile_picture'] = $account['profile_picture'] ?? null;
            }
        }

        return $data;
    }

    private function normalizeDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function mapRoleFromPosition(?string $position): string
    {
        $position = strtolower(trim((string) $position));

        if (str_contains($position, 'super admin') || str_contains($position, 'system administrator')) {
            return 'super_admin';
        }

        if (str_contains($position, 'hr manager')) {
            return 'hr_manager';
        }

        if (str_contains($position, 'hr scheduler')) {
            return 'hr_scheduler';
        }

        if (str_contains($position, 'admin')) {
            return 'admin';
        }

        return 'employee';
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
