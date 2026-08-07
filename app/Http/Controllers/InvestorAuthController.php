<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvestorAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.investor-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['role' => 'investor'], false)) {
            return back()->withErrors(['email' => 'Enter a valid investor email or password.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('investor.dashboard')->with('success', 'Welcome to your investor portal.');
    }
}
