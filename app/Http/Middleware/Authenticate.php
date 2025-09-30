<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Check if this is an employee route
            if (str_contains($request->getPathInfo(), '/employee') || 
                str_contains($request->getPathInfo(), '/employee-dashboard') ||
                str_contains($request->getPathInfo(), '/employee-profile')) {
                return '/employee/login';
            }
            
            // Default to admin login
            return '/admin/login';
        }
        
        return null;
    }
}
