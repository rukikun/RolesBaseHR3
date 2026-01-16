<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:employees,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $nameParts = preg_split('/\s+/', trim($request->name), 2);
        $firstName = $nameParts[0] ?? 'Employee';
        $lastName = $nameParts[1] ?? 'User';

        $employee = Employee::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'phone' => null,
            'role' => 'employee',
            'position' => 'Employee',
            'department' => 'General',
            'hire_date' => now()->toDateString(),
            'status' => 'active',
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($employee));

        Auth::guard('employee')->login($employee);

        return redirect(route('dashboard', absolute: false));
    }
}
