@extends('layouts.app')

@section('title', 'Secure Payment Gateway | Intern Estate')

@section('content')
<div class="pg-gateway-wrapper">
    <div class="pg-gateway-container">
        
        <!-- Top Security Header -->
        <header class="pg-top-bar">
            <div class="pg-brand-lockup">
                <a href="{{ route('investor.dashboard') }}" class="pg-back-link" title="Cancel and return to dashboard">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    <span>Back to Dashboard</span>
                </a>
                <div class="pg-brand-separator"></div>
                <div class="pg-brand-info">
                    <h2>INTERN ESTATE</h2>
                    <span>SECURE CHECKOUT &bull; SSLCOMMERZ INTEGRATED</span>
                </div>
            </div>
            <div class="pg-trust-badges">
                <span class="pg-trust-pill"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> 256-Bit SSL Encrypted</span>
                <span class="pg-trust-pill"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Bangladesh Bank NPSB Verified</span>
            </div>
        </header>

        <!-- Main Gateway Layout -->
        <div class="pg-main-grid">
            
            <!-- Left Column: Order & Plot Summary -->
            <aside class="pg-order-summary">
                <div class="pg-summary-card">
                    <div class="pg-summary-badge-row">
                        <span class="pg-serial-pill">SL #{{ $plotSerial }}</span>
                        <span class="pg-unit-pill">Unit {{ $booking->unit_no }}</span>
                        @if($intent === 'reservation')
                            <span class="pg-type-pill deposit">15% Booking Deposit</span>
                        @elseif($paymentType === 'full_settlement')
                            <span class="pg-type-pill full">Full Settlement (100%)</span>
                        @else
                            <span class="pg-type-pill installment">Monthly Installment</span>
                        @endif
                    </div>

                    <h3 class="pg-project-name">{{ $project->title }}</h3>
                    <p class="pg-project-loc">{{ $project->location }}</p>

                    <div class="pg-amount-box">
                        <span class="pg-amount-label">Payable Amount</span>
                        <div class="pg-amount-value">
                            <small>BDT</small>
                            <strong>{{ number_format($amount) }}</strong>
                        </div>
                        <p class="pg-amount-words">Zero gateway surcharge &bull; Instant confirmation</p>
                    </div>

                    <div class="pg-breakdown-list">
                        <div class="pg-breakdown-item">
                            <span>Total Plot Price</span>
                            <strong>BDT {{ number_format($booking->investment_amount) }}</strong>
                        </div>
                        <div class="pg-breakdown-item">
                            <span>Already Paid</span>
                            <strong class="text-green">BDT {{ number_format($alreadyPaid) }}</strong>
                        </div>
                        <div class="pg-breakdown-item highlight">
                            <span>Remaining Balance</span>
                            <strong class="text-primary">BDT {{ number_format($remaining) }}</strong>
                        </div>
                        @if($intent === 'reservation')
                        <div class="pg-breakdown-item due-info">
                            <span>Next Installment Due</span>
                            <strong>{{ now()->addDays(30)->format('d M Y') }} (in 30 days)</strong>
                        </div>
                        @endif
                    </div>

                    <div class="pg-customer-meta">
                        <div class="pg-meta-row"><span>Investor:</span><strong>{{ auth()->user()->name }}</strong></div>
                        <div class="pg-meta-row"><span>Email:</span><strong>{{ auth()->user()->email }}</strong></div>
                        <div class="pg-meta-row"><span>Order Ref:</span><code>IE-ORD-{{ $booking->id }}-{{ time() }}</code></div>
                    </div>

                    <div class="pg-guarantee-note">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Official Intern Estate Escrow Protection. All transactions are logged and verifiable in your ledger instantly.</span>
                    </div>
                </div>
            </aside>

            <!-- Right Column: Interactive Multi-Channel Gateway -->
            <main class="pg-payment-channels">
                
                <!-- Channel Selector Tabs -->
                <div class="pg-channel-tabs" role="tablist">
                    <button type="button" class="pg-tab-btn active" data-channel="bkash" onclick="switchChannel('bkash')">
                        <span class="pg-tab-dot bkash-dot"></span>
                        <div class="pg-tab-text">
                            <strong>bKash</strong>
                            <small>Instant MFS</small>
                        </div>
                    </button>
                    <button type="button" class="pg-tab-btn" data-channel="nagad" onclick="switchChannel('nagad')">
                        <span class="pg-tab-dot nagad-dot"></span>
                        <div class="pg-tab-text">
                            <strong>Nagad</strong>
                            <small>Instant MFS</small>
                        </div>
                    </button>
                    <button type="button" class="pg-tab-btn" data-channel="rocket" onclick="switchChannel('rocket')">
                        <span class="pg-tab-dot rocket-dot"></span>
                        <div class="pg-tab-text">
                            <strong>Rocket</strong>
                            <small>DBBL MFS</small>
                        </div>
                    </button>
                    <button type="button" class="pg-tab-btn" data-channel="sslcommerz" onclick="switchChannel('sslcommerz')">
                        <span class="pg-tab-dot ssl-dot"></span>
                        <div class="pg-tab-text">
                            <strong>Cards &amp; Bank</strong>
                            <small>Visa / Master / Nexus</small>
                        </div>
                    </button>
                </div>

                <!-- Hidden Master Form for Submitting Verified Payment -->
                <form id="masterGatewayForm" method="POST" action="{{ route('investor.payment-gateway.process') }}">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <input type="hidden" name="intent" value="{{ $intent }}">
                    <input type="hidden" name="payment_type" value="{{ $paymentType }}">
                    <input type="hidden" name="amount" value="{{ $amount }}">
                    <input type="hidden" name="payment_id" value="{{ $paymentId }}">
                    <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="bKash">
                    <input type="hidden" name="payer_account" id="finalPayerAccount" value="">
                    <input type="hidden" name="otp" id="finalOtp" value="123456">
                    <input type="hidden" name="pin" id="finalPin" value="">
                </form>

                <!-- ========================================== -->
                <!-- CHANNEL 1: bKash Authentic Gateway Flow   -->
                <!-- ========================================== -->
                <div id="channel-bkash" class="pg-channel-screen active bkash-theme">
                    <div class="bkash-header">
                        <div class="bkash-logo-wrap">
                            <svg class="bkash-origami-svg" width="28" height="28" viewBox="0 0 100 100" fill="none" style="flex-shrink:0;">
                                <polygon points="50,12 16,38 52,48" fill="#ffffff" opacity="0.95"/>
                                <polygon points="16,38 42,88 52,48" fill="#ffffff" opacity="0.8"/>
                                <polygon points="52,48 88,58 50,12" fill="#ffffff"/>
                                <polygon points="52,48 42,88 92,58" fill="#ffffff" opacity="0.85"/>
                            </svg>
                            <span class="bkash-logo-text">bKash</span>
                        </div>
                        <div class="bkash-amount-badge">
                            <span>Amount:</span>
                            <strong>৳ {{ number_format($amount) }}</strong>
                        </div>
                    </div>

                    <!-- Step 1: Wallet Number -->
                    <div id="bkash-step-1" class="mfs-step-box active">
                        <div class="mfs-prompt-banner">
                            <p>Your bKash Account Number</p>
                            <small>Enter your 11-digit bKash wallet number</small>
                        </div>
                        <div class="mfs-input-group">
                            <span class="mfs-prefix">+88</span>
                            <input type="tel" id="bkash_phone" class="mfs-input" placeholder="01XXXXXXXXX" maxlength="11" value="01712345678" autofocus>
                        </div>
                        <label class="mfs-terms-check">
                            <input type="checkbox" id="bkash_terms" checked>
                            <span>I agree to the <a href="javascript:void(0)">terms and conditions</a> of bKash payment</span>
                        </label>
                        <div class="mfs-actions">
                            <button type="button" class="btn-mfs-next bkash-btn" onclick="bkashGoToStep2()">PROCEED</button>
                            <a href="{{ route('investor.dashboard') }}" class="btn-mfs-close">CLOSE</a>
                        </div>
                    </div>

                    <!-- Step 2: Verification Code (OTP) -->
                    <div id="bkash-step-2" class="mfs-step-box">
                        <div class="mfs-prompt-banner">
                            <p>Verification Code (OTP)</p>
                            <small>Enter the 6-digit code sent to <strong id="bkash-sent-phone">017XXXXXXXX</strong></small>
                        </div>
                        <div class="demo-otp-helper">
                            <span>Demo OTP Code: <strong>123456</strong> (or enter any 6 digits)</span>
                        </div>
                        <div class="otp-boxes-wrapper">
                            <input type="text" id="bkash_otp" class="mfs-otp-single" placeholder="Enter 6-digit OTP" maxlength="6" value="123456">
                        </div>
                        <div class="mfs-resend-timer">
                            <span>Resend code in <b id="bkash-timer">01:59</b></span>
                            <a href="javascript:void(0)" onclick="restartTimer('bkash-timer')">Resend Code</a>
                        </div>
                        <div class="mfs-actions">
                            <button type="button" class="btn-mfs-next bkash-btn" onclick="bkashGoToStep3()">CONFIRM</button>
                            <button type="button" class="btn-mfs-back" onclick="goToStep('bkash', 1)">BACK</button>
                        </div>
                    </div>

                    <!-- Step 3: bKash PIN -->
                    <div id="bkash-step-3" class="mfs-step-box">
                        <div class="mfs-prompt-banner">
                            <p>Enter bKash PIN</p>
                            <small>Enter the 5-digit PIN of your bKash account</small>
                        </div>
                        <div class="mfs-input-group pin-group">
                            <input type="password" id="bkash_pin" class="mfs-input pin-input" placeholder="•••••" maxlength="5" value="12345">
                        </div>
                        <div class="mfs-security-warning">
                            <span style="display:inline-flex;align-items:center;gap:6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> bKash will never ask you for your PIN or OTP. Keep it strictly confidential.</span>
                        </div>
                        <div class="mfs-actions">
                            <button type="button" class="btn-mfs-next bkash-btn" onclick="submitMfsPayment('bKash', 'bkash_phone', 'bkash_otp', 'bkash_pin')">CONFIRM PAYMENT</button>
                            <button type="button" class="btn-mfs-back" onclick="goToStep('bkash', 2)">BACK</button>
                        </div>
                    </div>

                    <!-- Step 4: Realistic Processing Overlay -->
                    <div id="bkash-processing" class="mfs-processing-box" style="display:none;">
                        <div class="mfs-spinner bkash-spinner"></div>
                        <h4>Connecting to bKash Gateway...</h4>
                        <p>Authorizing transaction of BDT {{ number_format($amount) }}</p>
                        <small>Please do not close or refresh this browser window</small>
                    </div>

                    <div class="bkash-footer">
                        <span style="display:inline-flex;align-items:center;gap:6px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> 16247 &bull; support@bkash.com</span>
                        <span>Official Payment Partner</span>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- CHANNEL 2: Nagad Authentic Gateway Flow   -->
                <!-- ========================================== -->
                <div id="channel-nagad" class="pg-channel-screen nagad-theme">
                    <div class="nagad-header">
                        <div class="nagad-logo-wrap">
                            <svg class="nagad-mark-svg" width="26" height="26" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                                <circle cx="12" cy="12" r="10" stroke="#ffffff" stroke-width="2.2" opacity="0.4"/>
                                <path d="M7 12a5 5 0 0 1 5-5c1.8 0 3.4.9 4.3 2.3-.6.1-1.3.3-1.8.8-1 1-1.3 2.5-.7 3.8-.8.7-1.8 1.1-2.8 1.1A5 5 0 0 1 7 12z" fill="#ffffff"/>
                                <circle cx="15.5" cy="14.5" r="2.5" fill="#ffffff"/>
                            </svg>
                            <span class="nagad-logo-text">নগদ (Nagad)</span>
                        </div>
                        <div class="nagad-amount-badge">
                            <span>Amount:</span>
                            <strong>৳ {{ number_format($amount) }}</strong>
                        </div>
                    </div>

                    <!-- Step 1: Wallet Number -->
                    <div id="nagad-step-1" class="mfs-step-box active">
                        <div class="mfs-prompt-banner">
                            <p>আপনার নগদ একাউন্ট নম্বর দিন</p>
                            <small>Enter 11-digit Nagad Account Number</small>
                        </div>
                        <div class="mfs-input-group">
                            <span class="mfs-prefix">+88</span>
                            <input type="tel" id="nagad_phone" class="mfs-input" placeholder="01XXXXXXXXX" maxlength="11" value="01812345678">
                        </div>
                        <label class="mfs-terms-check">
                            <input type="checkbox" id="nagad_terms" checked>
                            <span>আমি নগদের শর্তাবলীর সাথে একমত</span>
                        </label>
                        <div class="mfs-actions">
                            <button type="button" class="btn-mfs-next nagad-btn" onclick="nagadGoToStep2()">পরবর্তী (PROCEED)</button>
                            <a href="{{ route('investor.dashboard') }}" class="btn-mfs-close">বাতিল</a>
                        </div>
                    </div>

                    <!-- Step 2: OTP -->
                    <div id="nagad-step-2" class="mfs-step-box">
                        <div class="mfs-prompt-banner">
                            <p>যাচাইকরণ কোড (OTP) লিখুন</p>
                            <small>Enter 6-digit OTP sent to <strong id="nagad-sent-phone">018XXXXXXXX</strong></small>
                        </div>
                        <div class="demo-otp-helper">
                            <span>Demo OTP Code: <strong>123456</strong></span>
                        </div>
                        <div class="otp-boxes-wrapper">
                            <input type="text" id="nagad_otp" class="mfs-otp-single" placeholder="Enter 6-digit OTP" maxlength="6" value="123456">
                        </div>
                        <div class="mfs-resend-timer">
                            <span>Resend in <b id="nagad-timer">01:59</b></span>
                            <a href="javascript:void(0)" onclick="restartTimer('nagad-timer')">পুনরায় কোড পাঠান</a>
                        </div>
                        <div class="mfs-actions">
                            <button type="button" class="btn-mfs-next nagad-btn" onclick="nagadGoToStep3()">নিশ্চিত করুন</button>
                            <button type="button" class="btn-mfs-back" onclick="goToStep('nagad', 1)">পূর্ববর্তী</button>
                        </div>
                    </div>

                    <!-- Step 3: PIN -->
                    <div id="nagad-step-3" class="mfs-step-box">
                        <div class="mfs-prompt-banner">
                            <p>নগদ পিন (PIN) লিখুন</p>
                            <small>Enter your 4-digit secret Nagad PIN</small>
                        </div>
                        <div class="mfs-input-group pin-group">
                            <input type="password" id="nagad_pin" class="mfs-input pin-input" placeholder="••••" maxlength="4" value="1234">
                        </div>
                        <div class="mfs-security-warning">
                            <span style="display:inline-flex;align-items:center;gap:6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> নগদ কখনো আপনার পিন কাউকে জানাতে বলে না।</span>
                        </div>
                        <div class="mfs-actions">
                            <button type="button" class="btn-mfs-next nagad-btn" onclick="submitMfsPayment('Nagad', 'nagad_phone', 'nagad_otp', 'nagad_pin')">পেমেন্ট সম্পন্ন করুন</button>
                            <button type="button" class="btn-mfs-back" onclick="goToStep('nagad', 2)">পূর্ববর্তী</button>
                        </div>
                    </div>

                    <!-- Step 4: Realistic Processing Overlay -->
                    <div id="nagad-processing" class="mfs-processing-box" style="display:none;">
                        <div class="mfs-spinner nagad-spinner"></div>
                        <h4>Connecting to Nagad Direct Gateway...</h4>
                        <p>Processing ৳ {{ number_format($amount) }}</p>
                    </div>

                    <div class="nagad-footer">
                        <span style="display:inline-flex;align-items:center;gap:6px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> 16167 &bull; Bangladesh Post Office FinTech</span>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- CHANNEL 3: Rocket Authentic Gateway Flow  -->
                <!-- ========================================== -->
                <div id="channel-rocket" class="pg-channel-screen rocket-theme">
                    <div class="rocket-header">
                        <div class="rocket-logo-wrap">
                            <svg class="rocket-logo-svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                                <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/>
                                <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/>
                                <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/>
                                <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>
                            </svg>
                            <span class="rocket-logo-text">DBBL Rocket</span>
                        </div>
                        <div class="rocket-amount-badge">
                            <span>Amount:</span>
                            <strong>৳ {{ number_format($amount) }}</strong>
                        </div>
                    </div>

                    <!-- Step 1: Wallet Number -->
                    <div id="rocket-step-1" class="mfs-step-box active">
                        <div class="mfs-prompt-banner">
                            <p>Dutch-Bangla Bank Rocket Mobile Account</p>
                            <small>Enter 12-digit Account (Mobile No + 1 Check Digit)</small>
                        </div>
                        <div class="mfs-input-group">
                            <span class="mfs-prefix">+88</span>
                            <input type="tel" id="rocket_phone" class="mfs-input" placeholder="01XXXXXXXXXX" maxlength="12" value="019123456789">
                        </div>
                        <label class="mfs-terms-check">
                            <input type="checkbox" id="rocket_terms" checked>
                            <span>I accept DBBL Rocket payment terms</span>
                        </label>
                        <div class="mfs-actions">
                            <button type="button" class="btn-mfs-next rocket-btn" onclick="rocketGoToStep2()">SUBMIT</button>
                            <a href="{{ route('investor.dashboard') }}" class="btn-mfs-close">CANCEL</a>
                        </div>
                    </div>

                    <!-- Step 2: OTP -->
                    <div id="rocket-step-2" class="mfs-step-box">
                        <div class="mfs-prompt-banner">
                            <p>Rocket Security Code (OTP)</p>
                            <small>Enter 6-digit OTP sent to your registered DBBL device</small>
                        </div>
                        <div class="demo-otp-helper">
                            <span>Demo OTP Code: <strong>123456</strong></span>
                        </div>
                        <div class="otp-boxes-wrapper">
                            <input type="text" id="rocket_otp" class="mfs-otp-single" placeholder="Enter 6-digit OTP" maxlength="6" value="123456">
                        </div>
                        <div class="mfs-resend-timer">
                            <span>Resend in <b id="rocket-timer">01:59</b></span>
                            <a href="javascript:void(0)" onclick="restartTimer('rocket-timer')">Resend OTP</a>
                        </div>
                        <div class="mfs-actions">
                            <button type="button" class="btn-mfs-next rocket-btn" onclick="rocketGoToStep3()">VERIFY OTP</button>
                            <button type="button" class="btn-mfs-back" onclick="goToStep('rocket', 1)">BACK</button>
                        </div>
                    </div>

                    <!-- Step 3: PIN -->
                    <div id="rocket-step-3" class="mfs-step-box">
                        <div class="mfs-prompt-banner">
                            <p>Rocket 4-Digit PIN</p>
                            <small>Enter your confidential Rocket PIN</small>
                        </div>
                        <div class="mfs-input-group pin-group">
                            <input type="password" id="rocket_pin" class="mfs-input pin-input" placeholder="••••" maxlength="4" value="1234">
                        </div>
                        <div class="mfs-actions">
                            <button type="button" class="btn-mfs-next rocket-btn" onclick="submitMfsPayment('Rocket', 'rocket_phone', 'rocket_otp', 'rocket_pin')">PAY NOW</button>
                            <button type="button" class="btn-mfs-back" onclick="goToStep('rocket', 2)">BACK</button>
                        </div>
                    </div>

                    <!-- Step 4: Realistic Processing Overlay -->
                    <div id="rocket-processing" class="mfs-processing-box" style="display:none;">
                        <div class="mfs-spinner rocket-spinner"></div>
                        <h4>Connecting to DBBL Central Switch...</h4>
                        <p>Processing ৳ {{ number_format($amount) }}</p>
                    </div>

                    <div class="rocket-footer">
                        <span style="display:inline-flex;align-items:center;gap:6px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> 16216 &bull; Dutch-Bangla Bank PLC</span>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- CHANNEL 4: SSLCommerz / Cards Gateway     -->
                <!-- ========================================== -->
                <div id="channel-sslcommerz" class="pg-channel-screen ssl-theme">
                    <div class="ssl-header">
                        <div class="ssl-logo-wrap">
                            <svg class="ssl-shield-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <polyline points="9 12 11 14 15 10"/>
                            </svg>
                            <span class="ssl-logo-text">SSLCOMMERZ</span>
                        </div>
                        <div class="ssl-cards-icons">
                            <span class="card-chip">VISA</span>
                            <span class="card-chip">Mastercard</span>
                            <span class="card-chip">Amex</span>
                            <span class="card-chip">DBBL Nexus</span>
                        </div>
                    </div>

                    <!-- Step 1: Card Details -->
                    <div id="ssl-step-1" class="mfs-step-box active">
                        <div class="mfs-prompt-banner">
                            <p>Debit / Credit Card Payment</p>
                            <small>Pay securely via local or international cards</small>
                        </div>

                        <div class="ssl-card-fields">
                            <div class="ssl-field">
                                <label>Cardholder Name</label>
                                <input type="text" id="ssl_name" class="ssl-input" value="{{ auth()->user()->name }}" placeholder="Name on card">
                            </div>
                            <div class="ssl-field">
                                <label>Card Number</label>
                                <input type="text" id="ssl_card" class="ssl-input" placeholder="4242 &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; 4242" value="4242 5555 6666 4242" maxlength="19">
                            </div>
                            <div class="ssl-row">
                                <div class="ssl-field">
                                    <label>Expiry (MM/YY)</label>
                                    <input type="text" id="ssl_exp" class="ssl-input" placeholder="MM/YY" value="12/28" maxlength="5">
                                </div>
                                <div class="ssl-field">
                                    <label>CVV / CVC</label>
                                    <input type="password" id="ssl_cvv" class="ssl-input" placeholder="•••" value="123" maxlength="4">
                                </div>
                            </div>
                        </div>

                        <div class="mfs-actions" style="margin-top:20px;">
                            <button type="button" class="btn-mfs-next ssl-btn" onclick="sslGoToStep2()">PROCEED TO 3D-SECURE</button>
                            <a href="{{ route('investor.dashboard') }}" class="btn-mfs-close">CANCEL</a>
                        </div>
                    </div>

                    <!-- Step 2: 3D-Secure Bank OTP -->
                    <div id="ssl-step-2" class="mfs-step-box">
                        <div class="mfs-prompt-banner">
                            <p>Bank 3D-Secure OTP Verification</p>
                            <small>Enter the One-Time-Password sent by your issuing bank</small>
                        </div>
                        <div class="demo-otp-helper">
                            <span>Demo Bank OTP: <strong>123456</strong></span>
                        </div>
                        <div class="otp-boxes-wrapper">
                            <input type="text" id="ssl_otp" class="mfs-otp-single" placeholder="Enter 6-digit Bank OTP" maxlength="6" value="123456">
                        </div>
                        <div class="mfs-actions">
                            <button type="button" class="btn-mfs-next ssl-btn" onclick="submitCardPayment()">AUTHORIZE BDT {{ number_format($amount) }}</button>
                            <button type="button" class="btn-mfs-back" onclick="goToStep('ssl', 1)">BACK</button>
                        </div>
                    </div>

                    <!-- Step 3: Realistic Processing Overlay -->
                    <div id="ssl-processing" class="mfs-processing-box" style="display:none;">
                        <div class="mfs-spinner ssl-spinner"></div>
                        <h4>Connecting to Visa / Mastercard 3DS Switch...</h4>
                        <p>Settling BDT {{ number_format($amount) }} via SSLCommerz</p>
                    </div>

                    <div class="ssl-footer">
                        <span>PCI-DSS Level 1 Certified &bull; Bangladesh Bank Approved</span>
                    </div>
                </div>

            </main>
        </div>
    </div>
</div>

<style>
/* ======================================================= */
/* ULTRA-CLEAN HOSTED PAYMENT GATEWAY STYLES              */
/* ======================================================= */
.pg-gateway-wrapper {
    min-height: 100vh;
    background: #f1f5f9;
    padding: 30px 16px 60px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #0f172a;
    box-sizing: border-box;
}
.pg-gateway-container {
    max-width: 1060px;
    margin: 0 auto;
}

/* Header */
.pg-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 24px;
    background: #ffffff;
    padding: 16px 24px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 6px rgba(15,23,42,0.04);
}
.pg-brand-lockup {
    display: flex;
    align-items: center;
    gap: 16px;
}
.pg-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #475569;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    transition: color 0.15s;
}
.pg-back-link:hover { color: #0f172a; }
.pg-brand-separator {
    width: 1px;
    height: 28px;
    background: #cbd5e1;
}
.pg-brand-info h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 900;
    letter-spacing: 0.5px;
    color: #0f172a;
}
.pg-brand-info span {
    font-size: 11px;
    color: #64748b;
    font-weight: 700;
    letter-spacing: 0.3px;
}
.pg-trust-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.pg-trust-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
}

/* Main Grid */
.pg-main-grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 24px;
    align-items: start;
}
@media (max-width: 900px) {
    .pg-main-grid { grid-template-columns: 1fr; }
}

/* Left: Order Summary */
.pg-summary-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(15,23,42,0.04);
}
.pg-summary-badge-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    margin-bottom: 12px;
}
.pg-serial-pill {
    background: #1e293b;
    color: #ffffff;
    font-weight: 800;
    font-size: 12px;
    padding: 3px 8px;
    border-radius: 6px;
}
.pg-unit-pill {
    background: #e0e7ff;
    color: #3730a3;
    font-weight: 800;
    font-size: 12px;
    padding: 3px 8px;
    border-radius: 6px;
}
.pg-type-pill {
    font-weight: 800;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 6px;
}
.pg-type-pill.deposit { background: #fef3c7; color: #92400e; }
.pg-type-pill.installment { background: #e0f2fe; color: #0369a1; }
.pg-type-pill.full { background: #dcfce7; color: #166534; }

.pg-project-name {
    margin: 0 0 4px;
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
}
.pg-project-loc {
    margin: 0 0 18px;
    font-size: 13px;
    color: #64748b;
}

.pg-amount-box {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #ffffff;
    padding: 18px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 20px;
}
.pg-amount-label {
    display: block;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    margin-bottom: 4px;
}
.pg-amount-value {
    font-size: 32px;
    font-weight: 900;
    line-height: 1.1;
    color: #38bdf8;
}
.pg-amount-value small {
    font-size: 16px;
    font-weight: 700;
    color: #94a3b8;
    margin-right: 4px;
}
.pg-amount-words {
    margin: 8px 0 0;
    font-size: 11px;
    color: #cbd5e1;
}

.pg-breakdown-list {
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    padding: 12px 0;
    margin-bottom: 16px;
}
.pg-breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 13px;
    color: #475569;
}
.pg-breakdown-item strong { color: #0f172a; }
.pg-breakdown-item.highlight strong { color: #2563eb; }
.pg-breakdown-item.due-info {
    background: #f0fdf4;
    padding: 8px 10px;
    border-radius: 6px;
    margin-top: 6px;
    color: #166534;
}
.pg-breakdown-item.due-info strong { color: #15803d; }

.pg-customer-meta {
    font-size: 12px;
    margin-bottom: 16px;
}
.pg-meta-row {
    display: flex;
    justify-content: space-between;
    padding: 3px 0;
    color: #64748b;
}
.pg-meta-row strong { color: #1e293b; }

.pg-guarantee-note {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    padding: 10px;
    border-radius: 8px;
    font-size: 11px;
    color: #166534;
    line-height: 1.4;
}

/* Right: Payment Channels */
.pg-payment-channels {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(15,23,42,0.04);
}

/* Channel Tabs */
.pg-channel-tabs {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 24px;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 14px;
}
@media (max-width: 600px) {
    .pg-channel-tabs { grid-template-columns: repeat(2, 1fr); }
}
.pg-tab-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 10px 14px;
    border-radius: 8px;
    cursor: pointer;
    text-align: left;
    transition: all 0.15s;
}
.pg-tab-btn:hover {
    border-color: #cbd5e1;
    background: #f1f5f9;
}
.pg-tab-btn.active {
    background: #0f172a;
    border-color: #0f172a;
    color: #ffffff;
    box-shadow: 0 3px 8px rgba(15,23,42,0.18);
}
.pg-tab-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}
.bkash-dot { background: #D12053; }
.nagad-dot { background: #F7941D; }
.rocket-dot { background: #8C3494; }
.ssl-dot { background: #0284c7; }

.pg-tab-text strong {
    display: block;
    font-size: 13px;
    line-height: 1.2;
}
.pg-tab-text small {
    display: block;
    font-size: 11px;
    opacity: 0.8;
}

/* Channel Screens */
.pg-channel-screen {
    display: none;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
.pg-channel-screen.active {
    display: block;
}

/* ==================================== */
/* bKash Theme                          */
/* ==================================== */
.bkash-header {
    background: #D12053;
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.bkash-logo-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.bkash-bird-icon { font-size: 24px; }
.bkash-logo-text { font-size: 22px; font-weight: 900; letter-spacing: 0.5px; }
.bkash-amount-badge { font-size: 14px; text-align: right; }
.bkash-amount-badge strong { font-size: 16px; margin-left: 4px; }
.bkash-btn {
    background: #D12053 !important;
    color: #ffffff !important;
}
.bkash-btn:hover { background: #b81545 !important; }
.bkash-spinner { border-top-color: #D12053 !important; }
.bkash-footer {
    background: #fdf2f4;
    border-top: 1px solid #fce7eb;
    padding: 10px 20px;
    font-size: 11px;
    color: #9f1239;
    display: flex;
    justify-content: space-between;
}

/* ==================================== */
/* Nagad Theme                          */
/* ==================================== */
.nagad-header {
    background: #F7941D;
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.nagad-logo-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.nagad-flame-icon { font-size: 24px; }
.nagad-logo-text { font-size: 20px; font-weight: 900; }
.nagad-amount-badge { font-size: 14px; }
.nagad-btn {
    background: #F7941D !important;
    color: #ffffff !important;
}
.nagad-btn:hover { background: #e07f0f !important; }
.nagad-spinner { border-top-color: #F7941D !important; }
.nagad-footer {
    background: #fff7ed;
    border-top: 1px solid #ffedd5;
    padding: 10px 20px;
    font-size: 11px;
    color: #c2410c;
}

/* ==================================== */
/* Rocket Theme                         */
/* ==================================== */
.rocket-header {
    background: #8C3494;
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.rocket-logo-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.rocket-icon { font-size: 22px; }
.rocket-logo-text { font-size: 20px; font-weight: 900; }
.rocket-amount-badge { font-size: 14px; }
.rocket-btn {
    background: #8C3494 !important;
    color: #ffffff !important;
}
.rocket-btn:hover { background: #75287c !important; }
.rocket-spinner { border-top-color: #8C3494 !important; }
.rocket-footer {
    background: #faf5ff;
    border-top: 1px solid #f3e8ff;
    padding: 10px 20px;
    font-size: 11px;
    color: #6b21a8;
}

/* ==================================== */
/* SSLCommerz Theme                     */
/* ==================================== */
.ssl-header {
    background: #0f172a;
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.ssl-logo-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.ssl-shield-icon { font-size: 22px; }
.ssl-logo-text { font-size: 18px; font-weight: 900; letter-spacing: 0.5px; }
.ssl-cards-icons {
    display: flex;
    gap: 6px;
}
.card-chip {
    background: rgba(255,255,255,0.15);
    font-size: 10px;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: 4px;
    letter-spacing: 0.5px;
}
.ssl-btn {
    background: #0284c7 !important;
    color: #ffffff !important;
}
.ssl-btn:hover { background: #0369a1 !important; }
.ssl-spinner { border-top-color: #0284c7 !important; }
.ssl-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 10px 20px;
    font-size: 11px;
    color: #475569;
}

/* Step Boxes */
.mfs-step-box {
    display: none;
    padding: 24px;
    background: #ffffff;
}
.mfs-step-box.active {
    display: block;
}
.mfs-prompt-banner {
    margin-bottom: 20px;
    text-align: center;
}
.mfs-prompt-banner p {
    margin: 0 0 4px;
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
}
.mfs-prompt-banner small {
    font-size: 13px;
    color: #64748b;
}

.mfs-input-group {
    display: flex;
    align-items: center;
    border: 2px solid #cbd5e1;
    border-radius: 8px;
    overflow: hidden;
    max-width: 360px;
    margin: 0 auto 16px;
    transition: border-color 0.15s;
}
.mfs-input-group:focus-within {
    border-color: #0f172a;
}
.mfs-prefix {
    background: #f1f5f9;
    padding: 12px 14px;
    font-size: 15px;
    font-weight: 700;
    color: #475569;
    border-right: 1px solid #cbd5e1;
}
.mfs-input {
    border: none;
    outline: none;
    padding: 12px 16px;
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    width: 100%;
    letter-spacing: 1px;
}
.mfs-input.pin-input {
    text-align: center;
    letter-spacing: 6px;
    font-size: 22px;
}

.mfs-terms-check {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 12px;
    color: #475569;
    margin-bottom: 20px;
    cursor: pointer;
}
.mfs-terms-check a { color: #0284c7; text-decoration: underline; }

.mfs-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    max-width: 360px;
    margin: 0 auto;
}
.btn-mfs-next {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    letter-spacing: 0.5px;
    transition: all 0.15s;
}
.btn-mfs-close, .btn-mfs-back {
    padding: 12px 18px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-mfs-close:hover, .btn-mfs-back:hover { background: #e2e8f0; }

.demo-otp-helper {
    background: #eff6ff;
    border: 1px dashed #93c5fd;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    color: #1e40af;
    text-align: center;
    max-width: 360px;
    margin: 0 auto 16px;
}
.otp-boxes-wrapper {
    max-width: 240px;
    margin: 0 auto 16px;
}
.mfs-otp-single {
    width: 100%;
    padding: 12px;
    font-size: 20px;
    font-weight: 800;
    letter-spacing: 6px;
    text-align: center;
    border: 2px solid #cbd5e1;
    border-radius: 8px;
    outline: none;
    box-sizing: border-box;
}
.mfs-otp-single:focus {
    border-color: #0f172a;
}
.mfs-resend-timer {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    max-width: 360px;
    margin: 0 auto 20px;
    color: #64748b;
}
.mfs-resend-timer a {
    color: #0284c7;
    font-weight: 700;
    text-decoration: none;
}
.mfs-security-warning {
    background: #fffbeb;
    border: 1px solid #fde68a;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 11px;
    color: #92400e;
    text-align: center;
    max-width: 360px;
    margin: 0 auto 20px;
}

/* SSL Card Fields */
.ssl-card-fields {
    max-width: 400px;
    margin: 0 auto;
}
.ssl-field {
    margin-bottom: 14px;
}
.ssl-field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 4px;
}
.ssl-input {
    width: 100%;
    padding: 10px 14px;
    font-size: 14px;
    font-weight: 600;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    outline: none;
    box-sizing: border-box;
}
.ssl-input:focus { border-color: #0284c7; }
.ssl-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

/* Realistic Processing Spinner */
.mfs-processing-box {
    padding: 48px 24px;
    text-align: center;
    background: #ffffff;
}
.mfs-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid #e2e8f0;
    border-top: 4px solid #0f172a;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 20px;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.mfs-processing-box h4 {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
}
.mfs-processing-box p {
    margin: 0 0 8px;
    font-size: 14px;
    color: #475569;
}
.mfs-processing-box small {
    font-size: 12px;
    color: #94a3b8;
}
</style>

<script>
// Channel switching
function switchChannel(channelId) {
    document.querySelectorAll('.pg-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.pg-channel-screen').forEach(screen => screen.classList.remove('active'));

    const activeBtn = document.querySelector(`.pg-tab-btn[data-channel="${channelId}"]`);
    const activeScreen = document.getElementById(`channel-${channelId}`);
    if (activeBtn) activeBtn.classList.add('active');
    if (activeScreen) activeScreen.classList.add('active');

    // Update hidden form payment method
    let methodName = 'bKash';
    if (channelId === 'nagad') methodName = 'Nagad';
    else if (channelId === 'rocket') methodName = 'Rocket';
    else if (channelId === 'sslcommerz') methodName = 'SSLCommerz';
    document.getElementById('selectedPaymentMethod').value = methodName;
}

// Step navigation
function goToStep(channel, stepNumber) {
    document.querySelectorAll(`#channel-${channel} .mfs-step-box`).forEach(box => box.classList.remove('active'));
    const targetStep = document.getElementById(`${channel}-step-${stepNumber}`);
    if (targetStep) targetStep.classList.add('active');
}

// bKash Flow
function bkashGoToStep2() {
    const phone = document.getElementById('bkash_phone').value.trim();
    if (!phone || phone.length < 11) {
        alert('Please enter a valid 11-digit bKash account number.');
        return;
    }
    if (!document.getElementById('bkash_terms').checked) {
        alert('Please agree to bKash terms and conditions.');
        return;
    }
    document.getElementById('bkash-sent-phone').innerText = phone;
    goToStep('bkash', 2);
}
function bkashGoToStep3() {
    const otp = document.getElementById('bkash_otp').value.trim();
    if (!otp || otp.length < 4) {
        alert('Please enter the 6-digit verification code.');
        return;
    }
    goToStep('bkash', 3);
}

// Nagad Flow
function nagadGoToStep2() {
    const phone = document.getElementById('nagad_phone').value.trim();
    if (!phone || phone.length < 11) {
        alert('সঠিক ১১-সংখ্যার নগদ নম্বর লিখুন।');
        return;
    }
    document.getElementById('nagad-sent-phone').innerText = phone;
    goToStep('nagad', 2);
}
function nagadGoToStep3() {
    const otp = document.getElementById('nagad_otp').value.trim();
    if (!otp || otp.length < 4) {
        alert('সঠিক ৬-সংখ্যার ওটিপি লিখুন।');
        return;
    }
    goToStep('nagad', 3);
}

// Rocket Flow
function rocketGoToStep2() {
    const phone = document.getElementById('rocket_phone').value.trim();
    if (!phone || phone.length < 11) {
        alert('Please enter a valid Rocket account number.');
        return;
    }
    goToStep('rocket', 2);
}
function rocketGoToStep3() {
    const otp = document.getElementById('rocket_otp').value.trim();
    if (!otp || otp.length < 4) {
        alert('Please enter OTP code.');
        return;
    }
    goToStep('rocket', 3);
}

// SSL Card Flow
function sslGoToStep2() {
    const card = document.getElementById('ssl_card').value.trim();
    if (!card || card.length < 12) {
        alert('Please enter a valid card number.');
        return;
    }
    goToStep('ssl', 2);
}
function submitCardPayment() {
    const otp = document.getElementById('ssl_otp').value.trim();
    if (!otp || otp.length < 4) {
        alert('Please enter the bank OTP.');
        return;
    }
    document.getElementById('selectedPaymentMethod').value = 'SSLCommerz';
    document.getElementById('finalPayerAccount').value = document.getElementById('ssl_card').value;
    document.getElementById('finalOtp').value = otp;
    document.getElementById('finalPin').value = '3DS-VERIFIED';

    document.querySelectorAll('#channel-sslcommerz .mfs-step-box').forEach(b => b.style.display = 'none');
    document.getElementById('ssl-processing').style.display = 'block';

    setTimeout(() => {
        document.getElementById('masterGatewayForm').submit();
    }, 1200);
}

// Generic MFS Submit with realistic animation
function submitMfsPayment(method, phoneId, otpId, pinId) {
    const phone = document.getElementById(phoneId).value.trim();
    const otp = document.getElementById(otpId).value.trim();
    const pin = document.getElementById(pinId).value.trim();

    if (!pin || pin.length < 4) {
        alert('Please enter your secret PIN.');
        return;
    }

    document.getElementById('selectedPaymentMethod').value = method;
    document.getElementById('finalPayerAccount').value = phone;
    document.getElementById('finalOtp').value = otp;
    document.getElementById('finalPin').value = pin;

    const channelKey = method.toLowerCase();
    const channelContainer = document.getElementById(`channel-${channelKey}`);
    if (channelContainer) {
        channelContainer.querySelectorAll('.mfs-step-box').forEach(b => b.style.display = 'none');
        const proc = document.getElementById(`${channelKey}-processing`);
        if (proc) proc.style.display = 'block';
    }

    setTimeout(() => {
        document.getElementById('masterGatewayForm').submit();
    }, 1400);
}

// Countdown timer helper
function restartTimer(elementId) {
    let seconds = 119;
    const elem = document.getElementById(elementId);
    if (!elem) return;
    const interval = setInterval(() => {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        elem.innerText = `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
        seconds--;
        if (seconds < 0) clearInterval(interval);
    }, 1000);
}
</script>
@endsection
