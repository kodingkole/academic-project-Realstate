@extends('layouts.app')

@section('title', 'Landowner Registration & Property Verification | Intern Estate')

@section('content')
<section class="login-section" style="padding: 40px 0;">
    <div class="container login-grid login-grid-single">
        <div class="login-card" style="max-width: 760px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 20px 40px rgba(15,23,42,0.08); padding: 36px;">
            
            <!-- Header Title -->
            <div class="login-card-header" style="text-align: center; margin-bottom: 24px;">
                <a href="{{ route('landing') }}" class="brand login-brand" style="justify-content: center; margin-bottom: 12px;">
                    <span class="brand-icon">IE</span>
                    <span class="brand-name">Intern<span>Estate</span></span>
                </a>
                <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Landowner Registration & Property Portal</h2>
                <p style="color: #64748b; font-size: 14px;">Register your landowner account to submit land for joint venture development.</p>
            </div>

            <!-- Mandatory Pre-Registration Checklist Banner for Landowners -->
            <div class="pre-registration-checklist-box landowner">
                <div class="checklist-header">
                    <span class="checklist-badge landowner">LANDOWNER REQUIRED CHECKLIST</span>
                    <h4>What documents are required to register?</h4>
                    <p>Please ensure you have the following 4 verified property details ready before filling out this form:</p>
                </div>
                <div class="checklist-items-grid">
                    <div class="checklist-item">
                        <span class="item-num landowner">1</span>
                        <div>
                            <strong>National ID (NID) Number</strong>
                            <small>10 to 17 digit Smart/Original NID</small>
                        </div>
                    </div>
                    <div class="checklist-item">
                        <span class="item-num landowner">2</span>
                        <div>
                            <strong>Land Deed / Khatian No.</strong>
                            <small>Deed No / CS/RS/BS Khatian Record</small>
                        </div>
                    </div>
                    <div class="checklist-item">
                        <span class="item-num landowner">3</span>
                        <div>
                            <strong>Electricity / Utility Bill No.</strong>
                            <small>DESCO / DPDC / NESCO Bill No.</small>
                        </div>
                    </div>
                    <div class="checklist-item">
                        <span class="item-num landowner">4</span>
                        <div>
                            <strong>Active Phone & Email</strong>
                            <small>For legal counsel & JV contract updates</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legal Disclaimer Warning Box -->
            <div class="legal-warning-box">
                <h4>Legal Disclaimer & Anti-Fraud Notice</h4>
                <p>
                    Submitting false, counterfeit, or duplicate NID numbers, land ownership deeds, or electricity bills is a punishable criminal offense under Property Fraud Prevention Laws. All land records are cross-checked with sub-registry offices and legal counsel. Fraudulent submissions will face immediate criminal prosecution.
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
                        <input id="deed_khatian_no" type="text" name="deed_khatian_no" value="{{ old('deed_khatian_no') }}" placeholder="Deed No / CS/RS/BS Khatian No." required class="@error('deed_khatian_no') input-error @enderror">
                        @error('deed_khatian_no')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="electricity_bill_no">Electricity / Utility Bill Account No</label>
                        <input id="electricity_bill_no" type="text" name="electricity_bill_no" value="{{ old('electricity_bill_no') }}" placeholder="e.g. DESCO / DPDC / NESCO Bill No." required class="@error('electricity_bill_no') input-error @enderror">
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

                <button type="submit" class="login-submit" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); margin-top: 10px;">
                    Register Landowner Account
                </button>
            </form>

            <div class="auth-link-footer" style="margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                <a href="{{ route('landowner.login') }}" style="color: #0284c7;">Already registered? Login to Landowner Portal</a>
                <span style="color: #cbd5e1;">•</span>
                <a href="{{ route('investor.register') }}" style="color: #059669;">Register as Investor instead</a>
            </div>
        </div>
    </div>
</section>
@endsection
