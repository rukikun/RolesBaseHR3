<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'confirmPassword' => 'required|same:password',
            'agreeTerms' => 'accepted',
        ]);

        $user = User::create([
            'name' => $validated['firstName'] . ' ' . $validated['lastName'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
        ]);

        // Optionally log the user in after registration
        // Auth::login($user);

        return redirect()->route('admin.login')->with('success', 'Account created! Please log in.');
    }
}
