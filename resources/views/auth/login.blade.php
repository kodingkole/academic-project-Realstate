@extends('layouts.app')

@section('title', 'Portal Login | Intern Estate')

@section('content')
<section class="login-section">
    <div class="container login-grid login-grid-single">
        <div class="login-card">
            <div class="login-card-header">
                <a href="{{ route('landing') }}" class="brand login-brand">
                    <span class="brand-icon">IE</span>
                    <span class="brand-name">Intern<span>Estate</span></span>
                </a>
                <h2>Welcome back</h2>
                <p>Enter your account details to continue.</p>
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
            <a href="{{ route('landing') }}" class="back-home">← Back to homepage</a>
        </div>
    </div>
</section>
@endsection
