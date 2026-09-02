@extends('layouts.app')

@section('title', 'Portal Sign In & Account Registration | Intern Estate')

@section('content')
<section class="login-section">
    <div class="container login-grid login-grid-single">
        <div class="login-card" style="max-width: 540px;">
            <div class="login-card-header">
                <a href="{{ route('landing') }}" class="brand login-brand">
                    <span class="brand-icon">IE</span>
                    <span class="brand-name">Intern<span>Estate</span></span>
                </a>
                <h2>Intern Estate Portal Access</h2>
                <p>Sign in to your account or register a new verified account below.</p>
            </div>

            <!-- Prominent Quick Registration Options Box -->
            <div class="register-promo-box" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 14px; padding: 16px; margin-bottom: 24px; text-align: center;">
                <h4 style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Don't have an account yet?</h4>
                <p style="font-size: 12px; color: #64748b; margin-bottom: 14px;">Register a new account with verified NID, TIN, or Land Deed details:</p>
                <div style="display: grid; gap: 10px; grid-template-columns: 1fr 1fr;">
                    <a href="{{ route('investor.register') }}" style="background: #10b981; color: #ffffff; padding: 12px; border-radius: 10px; font-weight: 800; font-size: 13px; text-decoration: none; display: block; text-align: center;">
                        Register as Investor
                    </a>
                    <a href="{{ route('landowner.register') }}" style="background: #0284c7; color: #ffffff; padding: 12px; border-radius: 10px; font-weight: 800; font-size: 13px; text-decoration: none; display: block; text-align: center;">
                        Register as Landowner
                    </a>
                </div>
            </div>

            <div style="border-top: 1px solid #e2e8f0; margin-bottom: 24px; position: relative; text-align: center;">
                <span style="background: #ffffff; padding: 0 12px; color: #64748b; font-size: 12px; font-weight: 700; position: relative; top: -10px;">OR SIGN IN TO EXISTING ACCOUNT</span>
            </div>

            <form method="POST" action="{{ route('login.attempt') }}" class="login-form">
                @csrf
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address" autocomplete="email" required autofocus class="@error('email') input-error @enderror">
                    @error('email')<p class="error-message">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <div class="label-row"><label for="password">Password</label><span>Minimum 8 characters</span></div>
                    <div class="password-wrapper">
                        <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required class="@error('password') input-error @enderror">
                        <button type="button" id="togglePassword" class="password-toggle">Show</button>
                    </div>
                    @error('password')<p class="error-message">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="login-submit">Sign In to Portal</button>
            </form>

            <div style="margin-top: 20px; text-align: center; display: flex; justify-content: center; gap: 15px; font-size: 13px;">
                <a href="{{ route('investor.login') }}" style="color: #10b981; font-weight: 700; text-decoration: none;">Investor Login</a>
                <span style="color: #cbd5e1;">•</span>
                <a href="{{ route('landowner.login') }}" style="color: #0284c7; font-weight: 700; text-decoration: none;">Landowner Login</a>
            </div>

            <a href="{{ route('landing') }}" class="back-home" style="margin-top: 16px; display: block; text-align: center;">← Back to homepage</a>
        </div>
    </div>
</section>
@endsection
