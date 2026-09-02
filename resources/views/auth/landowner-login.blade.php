@extends('layouts.app')

@section('title', 'Landowner Login | Intern Estate')

@section('content')
<section class="login-section">
    <div class="container login-grid login-grid-single">
        <div class="login-card">
            <div class="login-card-header">
                <a href="{{ route('landing') }}" class="brand login-brand">
                    <span class="brand-icon">IE</span>
                    <span class="brand-name">Intern<span>Estate</span></span>
                </a>
                <h2>Landowner Login</h2>
                <p>Enter your landowner credentials to access your joint venture dashboard.</p>
            </div>
            <form method="POST" action="{{ route('landowner.login.attempt') }}" class="login-form">
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
                </div>
                <button type="submit" class="login-submit">Sign In as Landowner</button>
            </form>
            <div class="auth-link-footer">
                <a href="{{ route('landowner.register') }}">New Landowner? Register your account</a>
                <span class="meta-separator">•</span>
                <a href="{{ route('login') }}">Admin Portal Login</a>
            </div>
        </div>
    </div>
</section>
@endsection
