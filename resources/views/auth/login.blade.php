@extends('layouts.app')

@section('title', 'Sign In & Portal Registration | Intern Estate')

@section('content')
<section class="login-section" style="padding: 40px 0;">
    <div class="container login-grid login-grid-single">
        <div class="login-card" style="max-width: 680px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 20px 40px rgba(15,23,42,0.08); padding: 36px;">
            
            <!-- Card Header -->
            <div class="login-card-header" style="text-align: center; margin-bottom: 28px;">
                <a href="{{ route('landing') }}" class="brand login-brand" style="justify-content: center; margin-bottom: 12px;">
                    <span class="brand-icon">IE</span>
                    <span class="brand-name">Intern<span>Estate</span></span>
                </a>
                <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Portal Access & Registration Hub</h2>
                <p style="color: #64748b; font-size: 14px;">Register a new verified account or sign in to your portal.</p>
            </div>

            <!-- New Account Registration Choice Section -->
            <div class="portal-role-register-section">
                <div class="role-section-header">
                    <span class="role-badge">NEW ACCOUNT</span>
                    <h3>Select Account Type to Register</h3>
                </div>

                <div class="role-cards-grid">
                    <!-- Investor Register Card -->
                    <div class="role-register-card investor">
                        <div class="role-card-top">
                            <span class="role-pill investor">Investor Portal</span>
                            <h4>Investor Registration</h4>
                            <p>Reserve property units, pay 15% booking deposit, and manage monthly installment plans.</p>
                        </div>
                        <a href="{{ route('investor.register') }}" class="btn-role-register investor">
                            Register as Investor →
                        </a>
                    </div>

                    <!-- Landowner Register Card -->
                    <div class="role-register-card landowner">
                        <div class="role-card-top">
                            <span class="role-pill landowner">Landowner Portal</span>
                            <h4>Landowner Registration</h4>
                            <p>Submit land for joint venture development and track legal deed vetting progress.</p>
                        </div>
                        <a href="{{ route('landowner.register') }}" class="btn-role-register landowner">
                            Register as Landowner →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div style="border-top: 1px solid #e2e8f0; margin: 32px 0 24px; position: relative; text-align: center;">
                <span style="background: #ffffff; padding: 0 16px; color: #64748b; font-size: 12px; font-weight: 800; position: relative; top: -10px; text-transform: uppercase; letter-spacing: 0.5px;">
                    OR SIGN IN TO EXISTING ACCOUNT
                </span>
            </div>

            <!-- Unified Sign In Form -->
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

                <button type="submit" class="login-submit" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    Sign In to Portal
                </button>
            </form>

            <!-- Role Specific Direct Login Links -->
            <div class="auth-link-footer" style="margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                <a href="{{ route('investor.login') }}" style="color: #059669;">Investor Login</a>
                <span style="color: #cbd5e1;">•</span>
                <a href="{{ route('landowner.login') }}" style="color: #0284c7;">Landowner Login</a>
                <span style="color: #cbd5e1;">•</span>
                <a href="{{ route('login') }}" style="color: #64748b;">Admin Command Center</a>
            </div>

            <a href="{{ route('landing') }}" class="back-home" style="margin-top: 16px; display: block; text-align: center;">← Back to homepage</a>
        </div>
    </div>
</section>
@endsection
