<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {

            $user = Auth::user();

            // check session mismatch
            if ($user->session_id !== Session::getId()) {
                Auth::logout();
                return redirect('/login')->withErrors([
                    'login' => 'You are logged in from another device.'
                ]);
            }

            // update activity
            $user->last_login_at = now();
            $user->save();
        }

        return $next($request);
    }
}
