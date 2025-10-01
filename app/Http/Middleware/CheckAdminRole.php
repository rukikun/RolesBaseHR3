<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();
        
        // If no specific roles are required, just check if user is authenticated
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access. Required role: ' . implode(' or ', $roles));
        }

        return $next($request);
    }
}
