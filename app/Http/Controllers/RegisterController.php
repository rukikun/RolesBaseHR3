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
            'role' => 'required|in:superadmin,super_admin,admin,hr_manager,hr_scheduler,employee',
            'password' => 'required|string|min:8',
            'confirmPassword' => 'required|same:password',
            'agreeTerms' => 'accepted',
        ]);

        $role = $validated['role'] === 'superadmin' ? 'super_admin' : $validated['role'];

        Employee::create([
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $role,
            'position' => 'Employee',
            'department' => 'General',
            'hire_date' => now()->toDateString(),
            'status' => 'active',
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.login')->with('success', 'Admin account created successfully! Please log in with your credentials.');
    }
}
