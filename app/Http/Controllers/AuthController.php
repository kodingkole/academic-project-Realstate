<?php

namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate the user.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, false)) {
            return back()
                ->withErrors([
                    'email' => 'Enter valid email or password।',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $dashboard = Auth::user()->role === 'investor'
            ? route('investor.dashboard')
            : route('admin.dashboard');

        return redirect()
            ->to($dashboard)
            ->with('success', 'Welcome back! Login successful.');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('landing')
            ->with('success', 'You have been logged out successfully.');
    }
}
