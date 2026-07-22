<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->requires_onboarding) {
            if (!$request->routeIs('profile.*') && !$request->routeIs('logout')) {
                return redirect()->route('profile.index')->with('warning', 'Please complete your profile and change your password to continue.');
            }
        }
        
        return $next($request);
    }
}
