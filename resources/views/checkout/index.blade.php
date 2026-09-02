@extends('layouts.app')

@section('title', 'Property Investment & Installment Gateway | Intern Estate')

@section('content')
<div class="checkout-page-wrapper">
    <div class="container checkout-container">
        
        {{-- Header / Stepper --}}
        <div class="checkout-header">
            <div class="checkout-badge">
                <span>SECURE INVESTMENT & INSTALLMENT GATEWAY</span>
            </div>
            <h1>Complete Your Property Investment</h1>
            <p>Select your desired project, choose full payment or 1-3 years installment plan (Credit Card backed), and verify KYC documents.</p>
        </div>

        <div class="checkout-grid">
            
            {{-- Left Column: Project Selection, Installment Plan & Payment Details --}}
            <div class="checkout-main">
                
                {{-- Form for Verification Documents & Payment --}}
                <form id="checkoutForm" method="POST" action="{{ route('checkout.process') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="checkout_type" value="{{ $type }}">
                    <input type="hidden" id="selectedItemId" name="item_id" value="{{ $item ? $item->id : 1 }}">
                    <input type="hidden" id="payableAmountInput" name="amount" value="{{ $price }}">
                    <input type="hidden" id="selectedPaymentPlan" name="payment_plan" value="full">
                    <input type="hidden" id="selectedPaymentMethod" name="payment_method" value="bKash">

                    {{-- 1. Project Selection Step --}}
                    <div class="checkout-card property-summary-card">
                        <div class="card-header">
                            <h3>1. Select Project / Unit</h3>
                            <span class="status-badge live">Verified Real Estate</span>
                        </div>
                        
                        <div style="margin-bottom: 18px;">
                            <label style="color: #cbd5e1; font-size: 13px; font-weight: 700; display: block; margin-bottom: 8px;">Choose Investment Project:</label>
                            <select id="projectSelector" class="form-control" style="background: #0f172a; border: 1px solid rgba(148,163,184,0.3); border-radius: 10px; color: #fff; width: 100%; padding: 12px 14px; font-size: 14px; font-weight: 600;" onchange="onProjectChange(this)">
                                @foreach($availableProjects as $p)
                                    @php
                                        $unitCost = $p->total_budget > 0 ? (int) ($p->total_budget / 20) : 6000000;
                                    @endphp
                                    <option value="{{ $p->id }}" data-title="{{ $p->title }}" data-location="{{ $p->location }}" data-price="{{ $unitCost }}" {{ ($item && $item->id == $p->id) ? 'selected' : '' }}>
                                        {{ $p->title }} ({{ $p->location }}) — BDT {{ number_format($unitCost) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="property-summary-body" style="background: rgba(15, 23, 42, 0.6); padding: 16px; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.2);">
                            <div class="property-details">
                                <h2 id="displayProjectTitle">{{ $title }}</h2>
                                <p id="displayProjectLocation">Location: {{ $location }}</p>
                                <div class="property-price-tag">
                                    <span>Total Share Valuation:</span>
                                    <strong id="displayProjectPrice">BDT {{ number_format($price) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Installment Plan Selection (1 yr, 2 yr, 3 yr or Full) --}}
                    <div class="checkout-card">
                        <div class="card-header">
                            <h3>2. Choose Payment Plan (Full or 1-3 Yrs EMI)</h3>
                            <span class="required-pill">Flexible Financing</span>
                        </div>
                        <p class="verification-subtitle">Select full payment or split into convenient monthly installments. Note: Installment plans require a valid Credit Card for monthly auto-debit.</p>

                        <div style="display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); margin-bottom: 20px;">
                            {{-- Full Payment --}}
                            <div class="plan-card active" id="planCard_full" onclick="selectPlan('full')" style="background: rgba(15,23,42,0.8); border: 2px solid #10b981; border-radius: 12px; padding: 14px; cursor: pointer; text-align: center; transition: all 0.2s;">
                                <strong style="display: block; font-size: 14px; color: #fff;">Full Payment</strong>
                                <span style="font-size: 11px; color: #34d399; font-weight: 700;">100% Upfront</span>
                                <p style="font-size: 10px; color: #94a3b8; margin-top: 6px;">Single Payment</p>
                            </div>

                            {{-- 1 Year EMI --}}
                            <div class="plan-card" id="planCard_1_year" onclick="selectPlan('1_year')" style="background: rgba(15,23,42,0.8); border: 2px solid rgba(148,163,184,0.2); border-radius: 12px; padding: 14px; cursor: pointer; text-align: center; transition: all 0.2s;">
                                <strong style="display: block; font-size: 14px; color: #fff;">1 Year (12 Mo)</strong>
                                <span style="font-size: 11px; color: #38bdf8; font-weight: 700;" id="emiPrice_1_year">BDT {{ number_format((int)($price/12)) }}/mo</span>
                                <p style="font-size: 10px; color: #94a3b8; margin-top: 6px;">12 Installments</p>
                            </div>

                            {{-- 2 Years EMI --}}
                            <div class="plan-card" id="planCard_2_year" onclick="selectPlan('2_year')" style="background: rgba(15,23,42,0.8); border: 2px solid rgba(148,163,184,0.2); border-radius: 12px; padding: 14px; cursor: pointer; text-align: center; transition: all 0.2s;">
                                <strong style="display: block; font-size: 14px; color: #fff;">2 Years (24 Mo)</strong>
                                <span style="font-size: 11px; color: #38bdf8; font-weight: 700;" id="emiPrice_2_year">BDT {{ number_format((int)($price/24)) }}/mo</span>
                                <p style="font-size: 10px; color: #94a3b8; margin-top: 6px;">24 Installments</p>
                            </div>

                            {{-- 3 Years EMI --}}
                            <div class="plan-card" id="planCard_3_year" onclick="selectPlan('3_year')" style="background: rgba(15,23,42,0.8); border: 2px solid rgba(148,163,184,0.2); border-radius: 12px; padding: 14px; cursor: pointer; text-align: center; transition: all 0.2s;">
                                <strong style="display: block; font-size: 14px; color: #fff;">3 Years (36 Mo)</strong>
                                <span style="font-size: 11px; color: #38bdf8; font-weight: 700;" id="emiPrice_3_year">BDT {{ number_format((int)($price/36)) }}/mo</span>
                                <p style="font-size: 10px; color: #94a3b8; margin-top: 6px;">36 Installments</p>
                            </div>
                        </div>

                        {{-- Credit Card Section for Installment --}}
                        <div id="creditCardRequirementSection" style="display: none; background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 14px; padding: 20px; margin-top: 15px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <div>
                                    <h4 style="font-size: 15px; color: #a5b4fc; font-weight: 700;">Mandatory Credit Card Guarantee for Installments</h4>
                                </div>
                                <span class="validity-indicator valid">Good Credit History Checked</span>
                            </div>
                            <p style="color: #94a3b8; font-size: 12px; line-height: 1.5; margin-bottom: 16px;">
                                Installment payments are eligible only for investors holding a valid Credit Card from approved partner banks with healthy credit history.
                            </p>

                            <div style="display: grid; gap: 14px; grid-template-columns: 1fr 1fr;">
                                <div class="input-group">
                                    <label>Issuing Partner Bank *</label>
                                    <select name="card_bank" id="cardBankSelect" style="background: #0f172a; border: 1px solid rgba(148,163,184,0.3); border-radius: 8px; color: #fff; padding: 10px;">
                                        <option value="City Bank (American Express)">City Bank PLC (Amex / Visa / Mastercard)</option>
                                        <option value="BRAC Bank PLC">BRAC Bank PLC (Visa / Mastercard)</option>
                                        <option value="Eastern Bank Ltd (EBL)">Eastern Bank Ltd (EBL Titanium / Platinum)</option>
                                        <option value="Standard Chartered">Standard Chartered Bank Bangladesh</option>
                                        <option value="Dutch-Bangla Bank (DBBL)">Dutch-Bangla Bank (Nexus / Mastercard)</option>
                                        <option value="Dhaka Bank PLC">Dhaka Bank PLC (Credit Platinum)</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Credit Cardholder Name *</label>
                                    <input type="text" name="card_holder" id="cardHolder" placeholder="e.g. MOHAMMED RAHMAN" value="{{ auth()->user()->name }}">
                                </div>
                                <div class="input-group">
                                    <label>16-Digit Credit Card Number *</label>
                                    <input type="text" name="card_number" id="cardNumber" placeholder="4222 •••• •••• 9812" value="4222 8899 4411 9812">
                                </div>
                                <div style="display: grid; gap: 10px; grid-template-columns: 1fr 1fr;">
                                    <div class="input-group">
                                        <label>Expiry Date (MM/YY) *</label>
                                        <input type="text" name="card_expiry" id="cardExpiry" placeholder="12/28" value="08/29">
                                    </div>
                                    <div class="input-group">
                                        <label>CVV / CVC *</label>
                                        <input type="password" id="cardCvv" placeholder="•••" value="882">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- 3. Identity & Document Verification Step --}}
                    <div class="checkout-card verification-card">
                        <div class="card-header">
                            <h3>3. Buyer Identity & KYC Documents</h3>
                            <span class="required-pill">Mandatory Documents</span>
                        </div>
                        <p class="verification-subtitle">As per real estate regulatory standards, please verify your NID, Tax Certificate, and Electricity Bill below.</p>

                        <div class="verification-fields-grid">
                            
                            {{-- NID Verification --}}
                            <div class="doc-field-box">
                                <div class="doc-box-header">
                                    <h4>National ID (NID Card)</h4>
                                    <span class="validity-indicator valid" id="nidStatus">Valid Format</span>
                                </div>
                                <div class="doc-inputs">
                                    <div class="input-group">
                                        <label for="nid_number">NID Card Number *</label>
                                        <input type="text" id="nid_number" name="nid_number" required placeholder="e.g. 1992269123456789" value="{{ old('nid_number', '1992269123456789') }}" oninput="validateDoc('nid')">
                                    </div>
                                    <div class="input-group">
                                        <label for="nid_file">Upload NID Copy (PDF/JPG/PNG)</label>
                                        <input type="file" id="nid_file" name="nid_file" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                </div>
                            </div>

                            {{-- Tax Certificate Verification --}}
                            <div class="doc-field-box">
                                <div class="doc-box-header">
                                    <h4>Tax Clearance Certificate / TIN</h4>
                                    <span class="validity-indicator valid" id="taxStatus">Valid TIN</span>
                                </div>
                                <div class="doc-inputs">
                                    <div class="input-group">
                                        <label for="tax_cert_no">Tax Certificate / TIN Number *</label>
                                        <input type="text" id="tax_cert_no" name="tax_cert_no" required placeholder="e.g. TIN-8829401928" value="{{ old('tax_cert_no', 'TIN-8829401928') }}" oninput="validateDoc('tax')">
                                    </div>
                                    <div class="input-group">
                                        <label for="tax_cert_file">Upload Tax Certificate (PDF/JPG/PNG)</label>
                                        <input type="file" id="tax_cert_file" name="tax_cert_file" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                </div>
                            </div>

                            {{-- Electricity Bill Verification --}}
                            <div class="doc-field-box">
                                <div class="doc-box-header">
                                    <h4>Electricity Bill (Utility Proof)</h4>
                                    <span class="validity-indicator valid" id="elecStatus">Valid Utility</span>
                                </div>
                                <div class="doc-inputs">
                                    <div class="input-group">
                                        <label for="electricity_bill_no">Meter / Utility Account No *</label>
                                        <input type="text" id="electricity_bill_no" name="electricity_bill_no" required placeholder="e.g. ELEC-99304128" value="{{ old('electricity_bill_no', 'ELEC-99304128') }}" oninput="validateDoc('elec')">
                                    </div>
                                    <div class="input-group">
                                        <label for="electricity_bill_file">Upload Electricity Bill Copy</label>
                                        <input type="file" id="electricity_bill_file" name="electricity_bill_file" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- 4. Integrated Payment Channel --}}
                    <div class="checkout-card payment-gateway-card">
                        <div class="card-header">
                            <h3>4. Payment Channel (for Booking / 1st Installment)</h3>
                            <span class="secured-tag">SSL 256-bit Encrypted</span>
                        </div>

                        {{-- Payment Method Tabs --}}
                        <div class="payment-tabs">
                            <button type="button" class="payment-tab active bkash-tab" onclick="selectPaymentTab('bKash')">
                                <span>bKash Payment</span>
                            </button>
                            <button type="button" class="payment-tab nagad-tab" onclick="selectPaymentTab('Nagad')">
                                <span>Nagad Payment</span>
                            </button>
                            <button type="button" class="payment-tab bank-tab" onclick="selectPaymentTab('Bank')">
                                <span>Bank Transfer</span>
                            </button>
                        </div>

                        {{-- Tab 1: bKash Panel --}}
                        <div id="bkashPanel" class="payment-panel-box bkash-theme">
                            <div class="panel-header-brand">
                                <div class="brand-title">
                                    <span class="bkash-badge">bKash</span>
                                    <h4>bKash Merchant Payment</h4>
                                </div>
                                <span class="charge-free">0% Gateway Fee</span>
                            </div>
                            <div class="gateway-inputs">
                                <div class="input-group">
                                    <label>bKash Account Mobile Number *</label>
                                    <input type="text" id="bkashPhone" placeholder="01700000000" value="01711223344">
                                </div>
                                <div class="input-group">
                                    <label>bKash PIN / OTP Code *</label>
                                    <input type="password" id="bkashPin" placeholder="•••••" value="12345">
                                </div>
                            </div>
                            <div class="gateway-instructions">
                                Enter your 11-digit bKash account number and PIN.
                            </div>
                        </div>

                        {{-- Tab 2: Nagad Panel --}}
                        <div id="nagadPanel" class="payment-panel-box nagad-theme" style="display:none;">
                            <div class="panel-header-brand">
                                <div class="brand-title">
                                    <span class="nagad-badge">Nagad</span>
                                    <h4>Nagad Direct Checkout</h4>
                                </div>
                                <span class="charge-free">0% Gateway Fee</span>
                            </div>
                            <div class="gateway-inputs">
                                <div class="input-group">
                                    <label>Nagad Account Mobile Number *</label>
                                    <input type="text" id="nagadPhone" placeholder="01800000000" value="01819876543">
                                </div>
                                <div class="input-group">
                                    <label>Nagad PIN / OTP Code *</label>
                                    <input type="password" id="nagadPin" placeholder="••••" value="1234">
                                </div>
                            </div>
                            <div class="gateway-instructions">
                                Enter your Nagad registered mobile number and PIN.
                            </div>
                        </div>

                        {{-- Tab 3: Bank Transfer Panel --}}
                        <div id="bankPanel" class="payment-panel-box bank-theme" style="display:none;">
                            <div class="panel-header-brand">
                                <div class="brand-title">
                                    <span class="bank-badge">Bank</span>
                                    <h4>Direct Bank Transfer & Clearing</h4>
                                </div>
                                <span class="charge-free">Direct Clearance</span>
                            </div>
                            <div class="gateway-inputs bank-grid">
                                <div class="input-group">
                                    <label>Select Bank *</label>
                                    <select name="bank_name" id="bankSelect">
                                        <option value="City Bank Ltd">City Bank Ltd</option>
                                        <option value="Dutch-Bangla Bank (DBBL)">Dutch-Bangla Bank (DBBL)</option>
                                        <option value="BRAC Bank">BRAC Bank PLC</option>
                                        <option value="Islami Bank Bangladesh">Islami Bank Bangladesh</option>
                                        <option value="Eastern Bank Ltd (EBL)">Eastern Bank Ltd (EBL)</option>
                                        <option value="Sonali Bank PLC">Sonali Bank PLC</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Routing Number *</label>
                                    <input type="text" name="routing_number" id="bankRouting" placeholder="e.g. 225260831" value="225260831">
                                </div>
                                <div class="input-group wide-field">
                                    <label>Deposit Slip / Transaction Reference *</label>
                                    <input type="text" id="bankRef" placeholder="e.g. DEP-2026-981245" value="DEP-2026-981245">
                                </div>
                            </div>
                            <div class="gateway-instructions">
                                Bank Transfers will be recorded and audited against your reference number.
                            </div>
                        </div>

                        {{-- Hidden inputs actually sent in the POST form --}}
                        <input type="hidden" name="payer_reference" id="finalPayerReference" value="01711223344">
                        <input type="hidden" name="pin_or_ref" id="finalPinOrRef" value="12345">

                        {{-- Submit Button --}}
                        <div class="checkout-submit-area">
                            <button type="submit" class="checkout-submit-btn" id="submitPaymentBtn">
                                Submit Payment Request (BDT {{ number_format($price) }})
                            </button>
                            <p class="privacy-note" style="color: #94a3b8; margin-top: 12px; font-size: 13px;">
                                <strong>Note:</strong> Upon submission, your request will be recorded as <strong>Pending Admin Review</strong>. Our finance and compliance officers will verify the documents and approve your transaction.
                            </p>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Right Column: Order Summary Sidebar --}}
            <div class="checkout-sidebar">
                <div class="checkout-card order-breakdown-card">
                    <h3>Investment Breakdown</h3>
                    <div class="breakdown-row">
                        <span>Selected Project</span>
                        <strong id="sidebarProjectTitle">{{ $title }}</strong>
                    </div>
                    <div class="breakdown-row">
                        <span>Total Share Price</span>
                        <strong id="sidebarTotalValuation">BDT {{ number_format($price) }}</strong>
                    </div>
                    <div class="breakdown-row">
                        <span>Payment Plan</span>
                        <strong class="green-text" id="sidebarPlanName">Full Payment (100%)</strong>
                    </div>
                    <div class="breakdown-row" id="sidebarMonthlyRow" style="display: none;">
                        <span>Monthly Installment</span>
                        <strong class="green-text" id="sidebarMonthlyAmount">BDT 0 / month</strong>
                    </div>
                    <div class="breakdown-row">
                        <span>KYC & Document Audit</span>
                        <strong class="green-text">FREE</strong>
                    </div>
                    <hr class="breakdown-divider">
                    <div class="breakdown-row total-row">
                        <span>Today's Payable:</span>
                        <strong class="total-amount" id="sidebarTodayPayable">BDT {{ number_format($price) }}</strong>
                    </div>

                    <div class="guarantee-box">
                        <div>
                            <h4>Admin Vetted Guarantee</h4>
                            <p>All requests are verified by financial compliance & legal advisors before final deed issuance.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Inline Javascript for Gateway Tabs and Dynamic Validation --}}
@push('scripts')
<script>
let currentBasePrice = {{ $price }};

function onProjectChange(select) {
    let opt = select.options[select.selectedIndex];
    let id = opt.value;
    let title = opt.getAttribute('data-title');
    let location = opt.getAttribute('data-location');
    let price = parseInt(opt.getAttribute('data-price')) || 6000000;

    currentBasePrice = price;
    document.getElementById('selectedItemId').value = id;
    document.getElementById('displayProjectTitle').textContent = title;
    document.getElementById('displayProjectLocation').textContent = 'Location: ' + location;
    document.getElementById('displayProjectPrice').textContent = 'BDT ' + price.toLocaleString();
    
    document.getElementById('sidebarProjectTitle').textContent = title;
    document.getElementById('sidebarTotalValuation').textContent = 'BDT ' + price.toLocaleString();

    // Update EMI labels
    document.getElementById('emiPrice_1_year').textContent = 'BDT ' + Math.round(price/12).toLocaleString() + '/mo';
    document.getElementById('emiPrice_2_year').textContent = 'BDT ' + Math.round(price/24).toLocaleString() + '/mo';
    document.getElementById('emiPrice_3_year').textContent = 'BDT ' + Math.round(price/36).toLocaleString() + '/mo';

    // Recalculate based on currently active plan
    let activePlan = document.getElementById('selectedPaymentPlan').value;
    selectPlan(activePlan);
}

function selectPlan(plan) {
    document.getElementById('selectedPaymentPlan').value = plan;

    // Reset card borders
    ['full', '1_year', '2_year', '3_year'].forEach(p => {
        let el = document.getElementById('planCard_' + p);
        if (el) {
            el.style.borderColor = 'rgba(148,163,184,0.2)';
            el.classList.remove('active');
        }
    });

    let activeEl = document.getElementById('planCard_' + plan);
    if (activeEl) {
        activeEl.style.borderColor = '#10b981';
        activeEl.classList.add('active');
    }

    let ccSection = document.getElementById('creditCardRequirementSection');
    let sidebarMonthlyRow = document.getElementById('sidebarMonthlyRow');
    let sidebarPlanName = document.getElementById('sidebarPlanName');
    let sidebarMonthlyAmount = document.getElementById('sidebarMonthlyAmount');
    let sidebarTodayPayable = document.getElementById('sidebarTodayPayable');
    let submitBtn = document.getElementById('submitPaymentBtn');
    let payableInput = document.getElementById('payableAmountInput');

    if (plan === 'full') {
        ccSection.style.display = 'none';
        sidebarMonthlyRow.style.display = 'none';
        sidebarPlanName.textContent = 'Full Payment (100%)';
        sidebarTodayPayable.textContent = 'BDT ' + currentBasePrice.toLocaleString();
        submitBtn.textContent = 'Submit Full Payment Request (BDT ' + currentBasePrice.toLocaleString() + ')';
        payableInput.value = currentBasePrice;
    } else {
        ccSection.style.display = 'block';
        sidebarMonthlyRow.style.display = 'flex';
        
        let months = plan === '1_year' ? 12 : (plan === '2_year' ? 24 : 36);
        let monthlyAmount = Math.round(currentBasePrice / months);
        // Down payment / 1st installment payable today
        let todayAmount = monthlyAmount; 

        sidebarPlanName.textContent = (months / 12) + ' Year Installment (' + months + ' Mo EMI)';
        sidebarMonthlyAmount.textContent = 'BDT ' + monthlyAmount.toLocaleString() + ' / mo';
        sidebarTodayPayable.textContent = 'BDT ' + todayAmount.toLocaleString() + ' (1st Installment)';
        submitBtn.textContent = 'Submit Installment Request (1st EMI: BDT ' + todayAmount.toLocaleString() + ')';
        payableInput.value = todayAmount;
    }
}

function selectPaymentTab(method) {
    document.getElementById('selectedPaymentMethod').value = method;
    
    // Update active tab buttons
    document.querySelectorAll('.payment-tab').forEach(tab => tab.classList.remove('active'));
    
    document.getElementById('bkashPanel').style.display = 'none';
    document.getElementById('nagadPanel').style.display = 'none';
    document.getElementById('bankPanel').style.display = 'none';

    if (method === 'bKash') {
        document.querySelector('.bkash-tab').classList.add('active');
        document.getElementById('bkashPanel').style.display = 'block';
    } else if (method === 'Nagad') {
        document.querySelector('.nagad-tab').classList.add('active');
        document.getElementById('nagadPanel').style.display = 'block';
    } else if (method === 'Bank') {
        document.querySelector('.bank-tab').classList.add('active');
        document.getElementById('bankPanel').style.display = 'block';
    }
}

function validateDoc(type) {
    let inputId = type === 'nid' ? 'nid_number' : (type === 'tax' ? 'tax_cert_no' : 'electricity_bill_no');
    let statusId = type === 'nid' ? 'nidStatus' : (type === 'tax' ? 'taxStatus' : 'elecStatus');
    let val = document.getElementById(inputId).value.trim();
    let statusElem = document.getElementById(statusId);

    if (val.length >= 6) {
        statusElem.textContent = 'Verified Format';
        statusElem.className = 'validity-indicator valid';
    } else {
        statusElem.textContent = 'Minimum 6 characters';
        statusElem.className = 'validity-indicator invalid';
    }
}

// Sync phone and PIN inputs based on selected tab on form submission
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    let method = document.getElementById('selectedPaymentMethod').value;
    let refInput = document.getElementById('finalPayerReference');
    let pinInput = document.getElementById('finalPinOrRef');
    let plan = document.getElementById('selectedPaymentPlan').value;

    if (method === 'bKash') {
        let phone = document.getElementById('bkashPhone').value.trim();
        let pin = document.getElementById('bkashPin').value.trim();
        refInput.value = phone || '01711223344';
        pinInput.value = pin || '12345';
    } else if (method === 'Nagad') {
        let nagadPhone = document.getElementById('nagadPhone').value.trim();
        let nagadPin = document.getElementById('nagadPin').value.trim();
        refInput.value = nagadPhone || '01819876543';
        pinInput.value = nagadPin || '1234';
    } else if (method === 'Bank') {
        let bankSelect = document.getElementById('bankSelect').value;
        let bankRef = document.getElementById('bankRef').value.trim() || 'DEP-' + Math.floor(Math.random()*1000000);
        let routing = document.getElementById('bankRouting').value.trim() || '225260831';
        refInput.value = bankSelect + ' (Ref: ' + bankRef + ')';
        pinInput.value = bankRef;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    let sel = document.getElementById('projectSelector');
    if (sel) {
        sel.addEventListener('change', function() {
            onProjectChange(this);
        });
        onProjectChange(sel);
    }
});
</script>
@endpush
@endsection
