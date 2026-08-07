<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if ($request->user()?->role !== $role) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $loginRoute = $role === 'investor' ? 'investor.login' : 'login';

            return redirect()->route($loginRoute)
                ->withErrors(['email' => 'Please sign in with the correct portal account.']);
        }

        return $next($request);
    }
}
