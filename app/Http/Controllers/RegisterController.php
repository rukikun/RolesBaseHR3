<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin,hr,manager,employee',
            'password' => 'required|string|min:8',
            'confirmPassword' => 'required|same:password',
            'agreeTerms' => 'accepted',
        ]);

        $employee = Employee::create([
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
            'hire_date' => now(),
            'position' => ucfirst($validated['role']),
            'department' => $this->getDepartmentByRole($validated['role']),
        ]);

        return redirect()->route('admin.login')->with('success', 'Account created successfully! Please log in with your credentials.');
    }

    private function getDepartmentByRole($role)
    {
        $departments = [
            'admin' => 'Administration',
            'hr' => 'Human Resources',
            'manager' => 'Management',
            'employee' => 'General',
        ];

        return $departments[$role] ?? 'General';
    }
}
