<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LandownerAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('landowner.dashboard');
        }

        return view('auth.landowner-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials + ['role' => 'landowner'], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('landowner.dashboard')->with('success', 'Welcome back to Landowner Portal.');
        }

        return back()->withErrors(['email' => 'Invalid email or password for landowner account.'])->onlyInput('email');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('landowner.dashboard');
        }

        return view('auth.landowner-register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nid_number' => ['required', 'string', 'min:10', 'max:17', 'unique:users,nid_number'],
            'deed_khatian_no' => ['required', 'string', 'min:4', 'max:30', 'unique:users,deed_khatian_no'],
            'electricity_bill_no' => ['required', 'string', 'min:6', 'max:25', 'unique:users,electricity_bill_no'],
        ], [
            'nid_number.unique' => 'This NID Number is already registered in our legal land registry.',
            'deed_khatian_no.unique' => 'This Land Deed / Khatian Number is already registered under another submission.',
            'electricity_bill_no.unique' => 'This Electricity / Utility Bill Account Number is already registered.',
            'phone.unique' => 'This Phone Number is already registered.',
            'email.unique' => 'This Email Address is already registered.',
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => trim($data['phone']),
            'password' => Hash::make($data['password']),
            'role' => 'landowner',
            'nid_number' => trim($data['nid_number']),
            'deed_khatian_no' => trim($data['deed_khatian_no']),
            'electricity_bill_no' => trim($data['electricity_bill_no']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('landowner.dashboard')->with('success', 'Landowner account registered successfully! Welcome to your portal.');
    }
}
