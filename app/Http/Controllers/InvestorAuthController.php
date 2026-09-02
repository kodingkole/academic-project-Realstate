<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InvestorNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InvestorAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $dashboard = match (Auth::user()->role) {
                'investor' => route('investor.dashboard'),
                'landowner' => route('landowner.dashboard'),
                default => route('admin.dashboard'),
            };

            return redirect()->to($dashboard);
        }

        return view('auth.investor-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials + ['role' => 'investor'], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('investor.dashboard')->with('success', 'Welcome back to your investor portal.');
        }

        return back()->withErrors(['email' => 'Invalid email or password for investor account.'])->onlyInput('email');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('investor.dashboard');
        }

        return view('auth.investor-register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nid_number' => ['required', 'string', 'min:10', 'max:17', 'unique:users,nid_number'],
            'tin_number' => ['required', 'string', 'min:10', 'max:15', 'unique:users,tin_number'],
            'electricity_bill_no' => ['required', 'string', 'min:6', 'max:25', 'unique:users,electricity_bill_no'],
        ], [
            'nid_number.unique' => 'This NID Number is already registered in our legal registry database.',
            'tin_number.unique' => 'This TIN Certificate Number is already registered in our tax verification database.',
            'electricity_bill_no.unique' => 'This Electricity / Utility Bill Account Number is already registered.',
            'phone.unique' => 'This Phone Number is already registered.',
            'email.unique' => 'This Email Address is already registered.',
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => trim($data['phone']),
            'password' => Hash::make($data['password']),
            'role' => 'investor',
            'nid_number' => trim($data['nid_number']),
            'tin_number' => trim($data['tin_number']),
            'electricity_bill_no' => trim($data['electricity_bill_no']),
        ]);

        InvestorNotification::create([
            'user_id' => $user->id,
            'title' => 'Investor Account Verified & Registered',
            'message' => 'Welcome to Intern Estate. Your legal verification details (NID: ' . $user->nid_number . ', TIN: ' . $user->tin_number . ') have been registered.',
            'type' => 'security'
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('investor.dashboard')->with('success', 'Investor account created and verified successfully! Welcome to your dashboard.');
    }
}
