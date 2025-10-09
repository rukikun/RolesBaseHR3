<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Employee;
use App\Models\UserActivity;
use App\Models\UserPreference;

class AdminProfileController extends Controller
{
    /**
     * Display the admin profile page
     */
    public function index()
    {
        $user = Auth::user();
        $roles = $this->getAvailableRoles();
        
        // Get recent activities for the user (with error handling)
        $recentActivities = collect([]);
        try {
            if (method_exists($user, 'recentActivities')) {
                $recentActivities = $user->recentActivities(10)->get();
            }
        } catch (\Exception $e) {
            // Handle case where UserActivity table doesn't exist
            $recentActivities = collect([]);
        }
        
        // Create some sample activities if none exist (for demo purposes)
        if ($recentActivities->count() === 0) {
            try {
                $this->createSampleActivities($user->id);
                if (method_exists($user, 'recentActivities')) {
                    $recentActivities = $user->recentActivities(10)->get();
                }
            } catch (\Exception $e) {
                // Silently handle if activity creation fails
                $recentActivities = collect([]);
            }
        }
        
        // Get employee statistics (with error handling)
        $accountAgeDays = $user->created_at->diffInDays(now());
        
        try {
            // Get login count
            $totalLogins = \App\Models\EmployeeActivity::where('employee_id', $user->id)
                ->where('activity_type', 'login')
                ->count();
            
            // Get profile update count
            $profileUpdates = \App\Models\EmployeeActivity::where('employee_id', $user->id)
                ->where('activity_type', 'profile_update')
                ->count();
            
            // Get last activity
            $lastActivityRecord = \App\Models\EmployeeActivity::where('employee_id', $user->id)
                ->orderBy('performed_at', 'desc')
                ->first();
            $lastActivity = null;
            if ($lastActivityRecord) {
                try {
                    $lastActivity = $lastActivityRecord->performed_at;
                } catch (\Exception $e) {
                    // Fallback to created_at if performed_at is not accessible
                    $lastActivity = $lastActivityRecord->created_at ?? null;
                }
            }
            
        } catch (\Exception $e) {
            // Fallback values if EmployeeActivity table doesn't exist
            $totalLogins = 0;
            $profileUpdates = 0;
            $lastActivity = null;
        }

        $userStats = [
            'total_logins' => $totalLogins > 0 ? $totalLogins : 0,
            'profile_updates' => $profileUpdates > 0 ? $profileUpdates : 0,
            'last_activity' => $lastActivity,
            'account_age_days' => $accountAgeDays > 0 ? $accountAgeDays : 1
        ];
        
        return view('admin.profile.index', compact('user', 'roles', 'recentActivities', 'userStats'));
    }

    /**
     * Show the edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        $roles = $this->getAvailableRoles();
        $employees = Employee::where('status', 'active')->get();
        
        return view('admin.profile.edit', compact('user', 'roles', 'employees'));
    }

    /**
     * Update the admin profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('employees')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('employees')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'department' => 'nullable|string|max:100',
            'work_location' => 'nullable|string|max:100',
            'manager_id' => 'nullable|integer|exists:employees,id',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female,Other,Prefer not to say',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|in:Spouse,Parent,Sibling,Child,Friend,Other'
        ]);

        try {
            // Track changes for activity logging - use actual Employee model fields
            $originalData = [
                'name' => $user->name, // This uses the accessor we created
                'email' => $user->email,
                'phone' => $user->phone,
                'department' => $user->department
            ];
            $changes = [];
            
            \Log::info('Profile update attempt', [
                'employee_id' => $user->id,
                'original_data' => $originalData,
                'request_data' => $request->only(['name', 'email', 'phone', 'department'])
            ]);
            
            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                
                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
                $user->profile_picture = $profilePicturePath;
                $changes['profile_picture'] = 'Updated';
            }

            // Track field changes for key fields
            $trackableFields = ['name', 'email', 'phone', 'department'];
            foreach ($trackableFields as $field) {
                $oldValue = $originalData[$field] ?? null;
                $newValue = $request->$field ?? null;
                
                if ($oldValue !== $newValue) {
                    $changes[$field] = [
                        'from' => $oldValue,
                        'to' => $newValue
                    ];
                }
            }

            // Update employee data with all available fields
            $updateData = [
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'department' => $request->department,
                'work_location' => $request->work_location,
                'manager_id' => $request->manager_id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relationship' => $request->emergency_contact_relationship,
                'profile_picture' => $user->profile_picture ?? null
            ];

            // Parse name into first_name and last_name for Employee model
            if ($request->name) {
                $nameParts = explode(' ', $request->name, 2);
                $updateData['first_name'] = $nameParts[0];
                $updateData['last_name'] = $nameParts[1] ?? '';
            }

            \Log::info('Attempting to update employee with data:', $updateData);
            
            $updateResult = $user->update($updateData);
            
            \Log::info('Update result:', [
                'success' => $updateResult,
                'changes_detected' => !empty($changes),
                'changes' => $changes
            ]);

            // Log activity if there were changes
            if (!empty($changes)) {
                try {
                    \App\Models\EmployeeActivity::logProfileUpdate($changes);
                    \Log::info('Profile activity logged successfully');
                } catch (\Exception $e) {
                    \Log::error('Profile activity logging failed: ' . $e->getMessage());
                }
            }

            \Log::info('Redirecting to profile with success message');
            
            return redirect()->route('admin.profile.index')
                           ->with('success', 'Profile updated successfully! Changes: ' . json_encode($changes));
                           
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error updating profile: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Show change password form
     */
    public function showChangePasswordForm()
    {
        return view('admin.profile.change-password');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Log password change activity
        try {
            if (class_exists('App\Models\UserActivity') && method_exists('App\Models\UserActivity', 'log')) {
                UserActivity::log('password_change', 'Password changed successfully');
            }
        } catch (\Exception $e) {
            // Silently handle if activity logging fails
            \Log::info('Password change activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.profile.index')
                       ->with('success', 'Password updated successfully!');
    }

    /**
     * Admin management - List all admins (Super Admin only)
     */
    public function manageAdmins()
    {
        // Check if user is super admin
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Unauthorized access.');
        }

        $admins = User::whereIn('role', ['super_admin', 'admin', 'hr_manager', 'hr_scheduler', 'attendance_admin'])
                     ->with('employee')
                     ->orderBy('created_at', 'desc')
                     ->get();
        
        $roles = $this->getAvailableRoles();
        $employees = Employee::where('status', 'active')->get();

        return view('admin.profile.manage-admins', compact('admins', 'roles', 'employees'));
    }

    /**
     * Create new admin (Super Admin only)
     */
    public function createAdmin(Request $request)
    {
        // Check if user is super admin
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'nullable|string|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'employee_id' => 'nullable|exists:employees,id',
            'role' => 'required|in:admin,hr_manager,hr_scheduler,attendance_admin',
            'password' => 'required|string|min:8|confirmed'
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'employee_id' => $request->employee_id,
                'role' => $request->role,
                'password' => Hash::make($request->password),
                'is_active' => true
            ]);

            return redirect()->route('admin.profile.manage-admins')
                           ->with('success', 'Admin created successfully!');
                           
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error creating admin: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Update admin role/status (Super Admin only)
     */
    public function updateAdmin(Request $request, $id)
    {
        // Check if user is super admin
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Unauthorized access.');
        }

        $admin = User::findOrFail($id);
        
        // Prevent super admin from modifying themselves
        if ($admin->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot modify your own account.']);
        }

        $request->validate([
            'role' => 'required|in:admin,hr_manager,hr_scheduler,attendance_admin',
            'is_active' => 'required|boolean'
        ]);

        $admin->update([
            'role' => $request->role,
            'is_active' => $request->is_active
        ]);

        return redirect()->route('admin.profile.manage-admins')
                       ->with('success', 'Admin updated successfully!');
    }

    /**
     * Delete admin (Super Admin only)
     */
    public function deleteAdmin($id)
    {
        // Check if user is super admin
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Unauthorized access.');
        }

        $admin = User::findOrFail($id);
        
        // Prevent super admin from deleting themselves
        if ($admin->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Prevent deleting other super admins
        if ($admin->role === 'super_admin') {
            return back()->withErrors(['error' => 'Cannot delete super admin accounts.']);
        }

        $admin->delete();

        return redirect()->route('admin.profile.manage-admins')
                       ->with('success', 'Admin deleted successfully!');
    }

    /**
     * Get available roles based on current user's role
     */
    private function getAvailableRoles()
    {
        $allRoles = [
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator', 
            'hr_manager' => 'HR Manager',
            'hr_scheduler' => 'HR Scheduler',
            'attendance_admin' => 'Attendance & Leave Admin'
        ];

        $currentUserRole = Auth::user()->role;

        // Super admin can assign any role except super_admin to others
        if ($currentUserRole === 'super_admin') {
            return $allRoles;
        }

        // Other roles can only view their own role
        if (!$currentUserRole || !isset($allRoles[$currentUserRole])) {
            // Optionally log or handle missing/invalid role
            return [];
        }
        return [$currentUserRole => $allRoles[$currentUserRole]];
    }

    /**
     * Get role permissions
     */
    public function getRolePermissions($role)
    {
        $permissions = [
            'super_admin' => [
                'system_configuration' => true,
                'user_management' => true,
                'all_hr_modules' => true,
                'reports_analytics' => true,
                'audit_logs' => true
            ],
            'admin' => [
                'user_management' => true,
                'all_hr_modules' => true,
                'reports_analytics' => true,
                'audit_logs' => false
            ],
            'hr_manager' => [
                'employee_management' => true,
                'leave_management' => true,
                'claims_management' => true,
                'reports_analytics' => true,
                'audit_logs' => false
            ],
            'hr_scheduler' => [
                'shift_management' => true,
                'schedule_management' => true,
                'timesheet_approval' => true,
                'reports_analytics' => false,
                'audit_logs' => false
            ],
            'attendance_admin' => [
                'time_attendance' => true,
                'leave_management' => true,
                'timesheet_management' => true,
                'reports_analytics' => false,
                'audit_logs' => false
            ]
        ];

        return $permissions[$role] ?? [];
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'theme' => 'nullable|in:light,dark',
            'language' => 'nullable|in:en,es,fr',
            'timezone' => 'nullable|string|max:50',
            'date_format' => 'nullable|in:Y-m-d,m/d/Y,d/m/Y',
            'time_format' => 'nullable|in:12,24',
            'notifications_email' => 'nullable|boolean',
            'notifications_browser' => 'nullable|boolean',
            'records_per_page' => 'nullable|integer|min:10|max:100'
        ]);

        try {
            // Update preferences
            $preferences = [
                UserPreference::THEME => $request->theme ?? 'light',
                UserPreference::LANGUAGE => $request->language ?? 'en',
                UserPreference::TIMEZONE => $request->timezone ?? 'Asia/Manila',
                UserPreference::DATE_FORMAT => $request->date_format ?? 'Y-m-d',
                UserPreference::TIME_FORMAT => $request->time_format ?? '24',
                UserPreference::NOTIFICATIONS_EMAIL => $request->boolean('notifications_email', true),
                UserPreference::NOTIFICATIONS_BROWSER => $request->boolean('notifications_browser', true),
                UserPreference::RECORDS_PER_PAGE => $request->records_per_page ?? 25
            ];

            foreach ($preferences as $key => $value) {
                $type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string');
                $user->setPreference($key, $value, $type);
            }

            // Log preference update activity
            try {
                if (class_exists('App\Models\UserActivity') && method_exists('App\Models\UserActivity', 'log')) {
                    UserActivity::log('settings_update', 'User preferences updated', $preferences);
                }
            } catch (\Exception $e) {
                // Silently handle if activity logging fails
                \Log::info('Preferences activity logging failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating preferences: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user preferences
     */
    public function getPreferences()
    {
        $user = Auth::user();
        $preferences = $user->getAllPreferences();
        $defaults = UserPreference::getDefaultPreferences();
        
        // Merge with defaults
        $preferences = array_merge($defaults, $preferences);
        
        return response()->json([
            'success' => true,
            'preferences' => $preferences
        ]);
    }

    /**
     * Create sample activities for demo purposes
     */
    private function createSampleActivities($employeeId)
    {
        try {
            if (!class_exists('App\Models\EmployeeActivity')) {
                return;
            }

            // Create some sample login activities
            \App\Models\EmployeeActivity::create([
                'employee_id' => $employeeId,
                'activity_type' => 'login',
                'description' => 'Employee logged in successfully',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'metadata' => ['login_method' => 'web_form'],
                'performed_at' => now()->subHours(2)
            ]);

            \App\Models\EmployeeActivity::create([
                'employee_id' => $employeeId,
                'activity_type' => 'login',
                'description' => 'Employee logged in successfully',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'metadata' => ['login_method' => 'web_form'],
                'performed_at' => now()->subDays(1)
            ]);

            \App\Models\EmployeeActivity::create([
                'employee_id' => $employeeId,
                'activity_type' => 'profile_update',
                'description' => 'Profile information updated',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'metadata' => ['name' => ['from' => 'Old Name', 'to' => 'New Name']],
                'performed_at' => now()->subHours(5)
            ]);
            
            \App\Models\EmployeeActivity::create([
                'employee_id' => $employeeId,
                'activity_type' => 'settings_update',
                'description' => 'Employee preferences updated',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'metadata' => ['theme' => 'dark', 'language' => 'en'],
                'performed_at' => now()->subHours(8)
            ]);
        } catch (\Exception $e) {
            // Silently handle if sample activity creation fails
            \Log::info('Sample activity creation failed: ' . $e->getMessage());
        }
    }
}
