@extends('layouts.app')

@section('title', 'Verify Investor Login | Intern Estate')

@section('content')
<section class="login-section"><div class="container login-grid login-grid-single"><div class="login-card"><div class="login-card-header"><a href="{{ route('landing') }}" class="brand login-brand"><span class="brand-icon">IE</span><span class="brand-name">Intern<span>Estate</span></span></a><h2>Verify your login</h2><p>Enter the six-digit code sent to {{ session('investor_otp_email') }}.</p></div>
<form method="POST" action="{{ route('investor.otp.verify') }}" class="login-form">@csrf<div class="form-group"><label for="otp">Verification code</label><input id="otp" name="otp" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus class="@error('otp') input-error @enderror">@error('otp')<p class="error-message">{{ $message }}</p>@enderror</div><button type="submit" class="login-submit">Verify and sign in</button></form>
<a href="{{ route('investor.login') }}" class="back-home">Use another account</a></div></div></section>
@endsection
