<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthenticated. Please login.'
                ], 401);
            }
            return redirect()->route('login');
        }

        $routeName = $request->route()?->getName();

        if ($routeName) {
            // Check if user has permission for the current route
            if (!auth()->user()->hasPermissionTo($routeName)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Unauthorized Access. You do not have permission to access ' . $routeName
                    ], 403);
                }
                
                abort(403, 'Unauthorized Access. You do not have permission to access this resource.');
            }
        }

        return $next($request);
    }
}
