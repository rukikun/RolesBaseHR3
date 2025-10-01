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
        
        return view('admin.profile.index', compact('user', 'roles'));
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
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'employee_id' => 'nullable|exists:employees,id',
            'role' => 'required|in:super_admin,admin,hr_manager,hr_scheduler,attendance_admin',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                
                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
                $user->profile_picture = $profilePicturePath;
            }

            // Update user data
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'employee_id' => $request->employee_id,
                'role' => $request->role,
                'profile_picture' => $user->profile_picture ?? null
            ]);

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
}
