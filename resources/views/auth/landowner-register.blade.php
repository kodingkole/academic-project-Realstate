@extends('layouts.app')

@section('title', 'Landowner Registration & Property Verification | Intern Estate')

@section('content')
<section class="login-section">
    <div class="container login-grid login-grid-single">
        <div class="login-card" style="max-width: 680px;">
            <div class="login-card-header">
                <a href="{{ route('landing') }}" class="brand login-brand">
                    <span class="brand-icon">IE</span>
                    <span class="brand-name">Intern<span>Estate</span></span>
                </a>
                <h2>Landowner Registration Portal</h2>
                <p>Register a landowner account with verified NID, deed/khatian, and utility bill details.</p>
            </div>

            <!-- Mandatory Legal Anti-Fraud Warning Callout Box -->
            <div class="legal-warning-box">
                <h4>Legal Disclaimer & Anti-Fraud Warning</h4>
                <p>
                    Submitting false, counterfeit, or duplicate NID numbers, land ownership deed numbers, or electricity bill accounts is a criminal offense under land fraud prevention laws. All property records are vetted by legal counsel. Fraudulent registrations will face criminal prosecution.
                </p>
            </div>

            <form method="POST" action="{{ route('landowner.register.store') }}" class="login-form">
                @csrf

                <div class="form-row-2col">
                    <div class="form-group">
                        <label for="name">Full Name (Matching Deed / NID)</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Rafiqul Islam" required autofocus class="@error('name') input-error @enderror">
                        @error('name')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="name@domain.com" required class="@error('email') input-error @enderror">
                        @error('email')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="form-row-2col">
                    <div class="form-group">
                        <label for="phone">Mobile Phone Number</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. +8801700000000" required class="@error('phone') input-error @enderror">
                        @error('phone')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="nid_number">National ID (NID) Number</label>
                        <input id="nid_number" type="text" name="nid_number" value="{{ old('nid_number') }}" placeholder="10 to 17 digit NID No." required class="@error('nid_number') input-error @enderror">
                        @error('nid_number')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="form-row-2col">
                    <div class="form-group">
                        <label for="deed_khatian_no">Land Deed / Khatian Number</label>
                        <input id="deed_khatian_no" type="text" name="deed_khatian_no" value="{{ old('deed_khatian_no') }}" placeholder="Deed No / CS/RS/BS Khatian" required class="@error('deed_khatian_no') input-error @enderror">
                        @error('deed_khatian_no')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="electricity_bill_no">Electricity / Utility Bill Account No</label>
                        <input id="electricity_bill_no" type="text" name="electricity_bill_no" value="{{ old('electricity_bill_no') }}" placeholder="e.g. DESCO / DPDC Bill No." required class="@error('electricity_bill_no') input-error @enderror">
                        @error('electricity_bill_no')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="form-row-2col">
                    <div class="form-group">
                        <label for="password">Account Password</label>
                        <input id="password" type="password" name="password" placeholder="Minimum 8 characters" required class="@error('password') input-error @enderror">
                        @error('password')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Re-enter password" required>
                    </div>
                </div>

                <button type="submit" class="login-submit">Register Landowner Account</button>
            </form>

            <div class="auth-link-footer">
                <a href="{{ route('landowner.login') }}">Already registered? Login to Landowner Portal</a>
            </div>
        </div>
    </div>
</section>
@endsection
