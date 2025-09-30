<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        return view('admin_login');
    }

    // Handle login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->has('rememberMe'))) {
            $request->session()->regenerate();
            
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    // Handle logout
    public function logout(Request $request)
    {
        // Set employee offline status before logout
        if (Auth::check() && Auth::user()->employee) {
            Auth::user()->employee->setOffline();
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }
}
