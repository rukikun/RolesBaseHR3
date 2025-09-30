<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateEmployeeStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Update employee activity if authenticated
        if (Auth::check() && Auth::user()->employee) {
            Auth::user()->employee->updateActivity();
            
            // Set online status if not already online
            if (!Auth::user()->employee->isOnline()) {
                Auth::user()->employee->setOnline();
            }
        }

        return $next($request);
    }
}
