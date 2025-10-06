<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Employee;

class AdminProfileController extends Controller
{
    /**
     * Display the admin profile page
     */
    public function index()
    {
        $user = Auth::user();
        $roles = $this->getAvailableRoles();
        
        // Get recent activities for the user
        $recentActivities = $user->recentActivities(10)->get();
        
        // Create some sample activities if none exist (for demo purposes)
        if ($recentActivities->count() === 0) {
            $this->createSampleActivities($user->id);
            $recentActivities = $user->recentActivities(10)->get();
        }
        
        // Get user statistics (using employee-based data)
        $accountAgeDays = $user->created_at->diffInDays(now());
        
        // Use employee activities instead of UserActivity
        $totalLogins = 11; // Sample data - could be tracked via login logs
        $profileUpdates = 5; // Sample data - could be tracked via profile update timestamps
        $lastActivity = (object)[
            'performed_at' => now()->subHour(),
            'description' => 'Recent profile activity'
        ];

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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('employees')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role' => 'required|in:admin,hr,manager,employee',
            'department' => 'nullable|in:Human Resource,Core Human,Logistics,Administration,Finance',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female,Other,Prefer not to say',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        try {
            // Track changes for activity logging
            $originalData = $user->only(['first_name', 'last_name', 'email', 'phone', 'role', 'department']);
            $changes = [];
            
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
            $trackableFields = ['first_name', 'last_name', 'email', 'phone', 'role', 'department'];
            foreach ($trackableFields as $field) {
                if (isset($originalData[$field]) && $originalData[$field] !== $request->$field) {
                    $changes[$field] = [
                        'from' => $originalData[$field],
                        'to' => $request->$field
                    ];
                }
            }

            // Update employee data with all fields
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'department' => $request->department,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'profile_picture' => $user->profile_picture ?? null
            ]);

            // Activity logging removed - now using employee activities instead

            return redirect()->route('admin.profile.index')
                           ->with('success', 'Profile updated successfully!');
                           
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

        // Activity logging removed - now using employee activities instead

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
            // Simplified preferences update (UserPreference model removed)
            $preferences = [
                'theme' => $request->theme ?? 'light',
                'language' => $request->language ?? 'en',
                'timezone' => $request->timezone ?? 'Asia/Manila',
                'date_format' => $request->date_format ?? 'Y-m-d',
                'time_format' => $request->time_format ?? '24',
                'notifications_email' => $request->boolean('notifications_email', true),
                'notifications_browser' => $request->boolean('notifications_browser', true),
                'records_per_page' => $request->records_per_page ?? 25
            ];

            // Store preferences in session for now (could be stored in user table later)
            session(['user_preferences' => $preferences]);

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
        // Get preferences from session or use defaults
        $defaults = [
            'theme' => 'light',
            'language' => 'en',
            'timezone' => 'Asia/Manila',
            'date_format' => 'Y-m-d',
            'time_format' => '24',
            'notifications_email' => true,
            'notifications_browser' => true,
            'records_per_page' => 25
        ];
        
        $preferences = session('user_preferences', $defaults);
        
        return response()->json([
            'success' => true,
            'preferences' => $preferences
        ]);
    }

    /**
     * Create sample activities for demo purposes
     * Note: Now using employee activities instead of separate UserActivity model
     */
    private function createSampleActivities($userId)
    {
        // Sample activities are now created through actual employee actions
        // like timesheet submissions, attendance records, etc.
        // No need for separate UserActivity records
        return true;
    }
}
