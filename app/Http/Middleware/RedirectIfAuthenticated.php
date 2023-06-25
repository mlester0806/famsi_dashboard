<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if (auth()->user()->user_type == User::APPLICANT) {
                    return redirect(RouteServiceProvider::APPLICANT_HOME);
                }

                if (auth()->user()->user_type == User::HR_STAFF) {
                    return redirect(RouteServiceProvider::HR_STAFF_HOME);
                }

                if (auth()->user()->user_type == User::HR_MANAGER) {
                    return redirect(RouteServiceProvider::HR_MANAGER_HOME);
                }

                if (auth()->user()->user_type == User::ADMIN) {
                    return redirect(RouteServiceProvider::ADMIN_HOME);
                }
            }
        }

        return $next($request);
    }
}
