@extends('layouts.investor')

@section('title', 'Investor Dashboard | Intern Estate')
@section('page-heading', 'Investor Dashboard')

@section('content')
<section class="investor-welcome-card">
    <div>
        <span class="investor-eyebrow">INVESTMENT OVERVIEW</span>
        <h2>Welcome back, {{ auth()->user()->name }}.</h2>
        <p>View your property price, 15% booking deposit, installment plan, paid amount and remaining balance in one place.</p>
    </div>
    <div class="investor-welcome-mark">
        <span>IE</span>
        <small>INVESTOR</small>
    </div>
</section>

<!-- Success Payment Confirmation Notification Banner / Dialog -->
@if(session('payment_success_details'))
@php($details = session('payment_success_details'))
<div class="payment-success-modal-overlay" id="paymentSuccessNotice">
    <div class="payment-success-modal-card">
        <h3>Payment Submitted Successfully!</h3>
        <p class="success-subtitle">Status: <span class="status-badge-pending">Waiting for Admin Verification</span></p>
        
        <div class="success-receipt-box">
            <div class="receipt-row">
                <span>Transaction ID:</span>
                <strong><code>{{ $details['transaction_id'] }}</code></strong>
            </div>
            <div class="receipt-row">
                <span>Property / Unit:</span>
                <strong>{{ $details['project_title'] }} — {{ $details['unit_no'] }}</strong>
            </div>
            <div class="receipt-row">
                <span>Amount Paid:</span>
                <strong class="receipt-amount">BDT {{ number_format($details['amount']) }}</strong>
            </div>
            <div class="receipt-row">
                <span>Payment Method:</span>
                <strong>{{ $details['payment_method'] }}</strong>
            </div>
            <div class="receipt-row">
                <span>Payment Type:</span>
                <strong>{{ $details['payment_type'] }}</strong>
            </div>
        </div>

        <p class="receipt-notice-text">
            Your payment request has been recorded. Once the finance administrator verifies the transaction reference against bank logs, your remaining balance and portfolio status will be updated immediately.
        </p>

        <button type="button" class="btn-close-success" onclick="document.getElementById('paymentSuccessNotice').remove()">Acknowledge & Close</button>
    </div>
</div>
@endif

<section class="stat-card-grid investor-stat-grid">
    <article class="stat-card investor-stat-card"><div><p>Total contracted value</p><h3>BDT {{ number_format($bookings->sum('investment_amount')) }}</h3></div></article>
    <article class="stat-card investor-stat-card"><div><p>Active projects</p><h3>{{ $projects->count() }}</h3></div></article>
    <article class="stat-card investor-stat-card"><div><p>Confirmed payments</p><h3>BDT {{ number_format($paid) }}</h3></div></article>
    <article class="stat-card investor-stat-card"><div><p>Unread updates</p><h3 id="liveNotificationCount">{{ $unreadNotifications }}</h3></div></article>
</section>

<section class="investor-actions">
    <div class="investor-actions-intro"><span>QUICK ACTIONS</span><h3>Manage your investment</h3><p>Reserve an available unit with a 15% deposit, then pay the scheduled installment or full balance.</p></div>
    <article class="investor-action-card"><h4>Reserve a unit</h4><p>Interactive 10-floor cube map. Pay 15% booking deposit to lock unit.</p><button type="button" class="investor-reserve-button" data-modal-open="bookingModal">Reserve property</button></article>
    <article class="investor-action-card payment"><h4>Make a payment</h4><p>Submit your scheduled installment or settle the complete remaining balance.</p><button type="button" class="investor-pay-button" data-modal-open="paymentModal">Make a payment</button></article>
</section>

<section class="dashboard-panel" style="margin-bottom:25px">
    <div class="panel-header"><div><h3>Available properties</h3><p>Select any property to view its 10-floor unit floorplan and 15% reservation fee.</p></div></div>
    <div style="display:grid;gap:15px;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));padding:10px 0">
        @forelse($availableProjects as $property)
        @php($propPrice = $property->total_budget ?: 5000000)
        <div class="investor-property-card">
            <h4 style="font-size:16px;margin-bottom:6px;color:#0f172a">{{ $property->title }}</h4>
            <p style="color:#64748b;font-size:12px;margin-bottom:12px">{{ $property->location }}</p>
            <div style="display:flex;justify-content:space-between;align-items:center">
                <div>
                    <span style="color:#0f172a;font-weight:800;font-size:15px;display:block">BDT {{ number_format($propPrice) }}</span>
                    <small style="color:#059669;font-weight:700">15% Deposit: BDT {{ number_format($propPrice * 0.15) }}</small>
                </div>
                <button type="button" class="erp-button" data-modal-open="bookingModal" onclick="selectPropertyForReservation('{{ $property->id }}')">Reserve</button>
            </div>
        </div>
        @empty 
        <div class="erp-empty">No properties available.</div>
        @endforelse
    </div>
</section>

<section class="investor-dashboard-grid">
    <article class="dashboard-panel">
        <div class="panel-header">
            <div>
                <h3>My portfolio</h3>
                <p>Live payment progress for every reserved unit.</p>
            </div>
            <a class="erp-secondary" href="{{ route('investor.ledger') }}">View full ledger</a>
        </div>

        @forelse($projects as $project)
            @php($booking = $bookings->firstWhere('project_id', $project->id))
            @php($summary = $bookingSummaries->get($project->id))
            @php($isFullyPaid = ($summary?->remaining ?? 0) <= 0 || $booking?->status === 'paid_in_full')

            <div class="portfolio-item-card">
                <div class="portfolio-item-header">
                    <div class="portfolio-title-block">
                        <span class="portfolio-unit-badge">{{ $booking?->unit_no }}</span>
                        <div>
                            <h4 class="portfolio-project-title">{{ $project->title }}</h4>
                            <div class="portfolio-project-meta">
                                <span>Location: {{ $project->location }}</span>
                                <span class="meta-separator">•</span>
                                <span class="construction-pill">{{ $project->progress_percentage }}% Construction Complete</span>
                            </div>
                        </div>
                    </div>

                    <div class="portfolio-action-group">
                        @if($isFullyPaid)
                            <div class="paid-full-badge">
                                Paid in Full (100%)
                            </div>
                        @else
                            <button type="button" 
                                    class="btn-portfolio-pay" 
                                    data-modal-open="paymentModal"
                                    data-booking-id="{{ $booking->id }}"
                                    data-project-id="{{ $project->id }}">
                                Pay Now
                            </button>
                        @endif

                        <button type="button" class="btn-portfolio-history" onclick="togglePlotHistory('history-{{ $booking->id }}')">
                            Payment History ({{ $booking->payments->count() }})
                        </button>
                    </div>
                </div>

                <div class="portfolio-progress-container">
                    <div class="portfolio-progress-bar">
                        <span class="portfolio-progress-fill" style="width: {{ $summary?->percentage ?? 0 }}%"></span>
                    </div>
                    <div class="portfolio-progress-labels">
                        <span><strong>Plan:</strong> {{ $booking?->installment_months ? $booking->installment_months.' months ('.($booking->installment_months / 12).' year'.($booking->installment_months > 12 ? 's' : '').')' : 'Full payment' }}</span>
                        <span><strong>Paid:</strong> BDT {{ number_format($summary?->paid ?? 0) }} ({{ $summary?->percentage ?? 0 }}%)</span>
                        <span class="{{ $isFullyPaid ? 'text-green' : 'text-bold' }}"><strong>Remaining:</strong> BDT {{ number_format($summary?->remaining ?? 0) }}</span>
                        @if($booking?->next_payment_date && !$isFullyPaid)
                            <span class="next-due-tag"><strong>Next due:</strong> {{ $booking->next_payment_date->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>

                <!-- Toggleable Per-Plot Payment History Drawer -->
                <div id="history-{{ $booking->id }}" class="plot-history-container" style="display: none;">
                    <div class="plot-history-header">
                        <h5>Payment History — {{ $project->title }} ({{ $booking->unit_no }})</h5>
                        <small>{{ $booking->payments->count() }} transaction(s) recorded</small>
                    </div>
                    <table class="erp-table plot-history-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Method / Ref</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($booking->payments as $pmt)
                            <tr>
                                <td><code>{{ $pmt->transaction_id }}</code></td>
                                <td>{{ $pmt->payment_method }} @if($pmt->payer_reference)<small>({{ $pmt->payer_reference }})</small>@endif</td>
                                <td><strong>BDT {{ number_format($pmt->amount) }}</strong></td>
                                <td><span class="type-pill">{{ ucfirst(str_replace('_', ' ', $pmt->payment_type ?? 'installment')) }}</span></td>
                                <td>
                                    @if($pmt->status === 'paid')
                                        <span class="status-badge-verified">Verified</span>
                                    @elseif($pmt->status === 'pending')
                                        <span class="status-badge-pending">Pending Admin</span>
                                    @else
                                        <span class="status-badge-rejected">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $pmt->created_at->format('d M Y, h:i A') }}</td>
                                <td><a class="invoice-link" href="{{ route('investor.invoice', $pmt) }}" target="_blank">View Invoice</a></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="erp-empty">No payments submitted yet for this plot.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty 
            <div class="investor-empty-state">
                <h4>No investments yet</h4>
                <p>Reserve an available unit to begin.</p>
            </div>
        @endforelse
    </article>

    <aside class="dashboard-panel">
        <div class="panel-header">
            <div>
                <h3>Account & verification</h3>
                <p>Investor profile status</p>
            </div>
        </div>
        <div class="investor-profile-row"><span>Name</span><strong>{{ auth()->user()->name }}</strong></div>
        <div class="investor-profile-row"><span>Email</span><strong>{{ auth()->user()->email }}</strong></div>
        <div class="investor-profile-row"><span>Identity documents</span><strong class="investor-status">Verified</strong></div>
    </aside>
</section>

<!-- FULL PAGE PROPERTY & UNIT RESERVATION PORTAL MODAL -->
<dialog id="bookingModal" class="fullpage-booking-portal">
    <div class="portal-booking-container">
        <!-- Portal Header -->
        <header class="portal-booking-header">
            <div class="portal-brand-title">
                <span class="security-badge-icon">15% Booking Deposit Required</span>
                <h2>Intern Estate Property & Unit Reservation Portal</h2>
                <p>Select property & 10-floor unit cube map (4 flats/floor) to reserve</p>
            </div>
            <button type="button" class="portal-close-btn" data-modal-close aria-label="Close Portal">Close Portal</button>
        </header>

        <form method="POST" action="{{ route('investor.reserve') }}" id="unitReservationForm" class="portal-booking-body">
            @csrf
            <input type="hidden" name="unit_no" id="reservationUnitInput" required>
            
            <div class="portal-booking-grid">
                <!-- Left Column: Property & Interactive Floor Plan Grid -->
                <div class="portal-section-main">
                    
                    <!-- Section 1: Property Selector & 15% Deposit Card -->
                    <div class="portal-card compact">
                        <div class="portal-card-head">
                            <span class="step-num">1</span>
                            <h3>Select Property & Deposit</h3>
                        </div>

                        <div class="form-group wide">
                            <select name="project_id" id="reservationProjectSelect" required class="portal-select">
                                @forelse($availableProjects as $property)
                                    @php($pPrice = (float) ($property->total_budget ?: 5000000))
                                    <option value="{{ $property->id }}" 
                                            data-price="{{ $pPrice }}"
                                            data-deposit="{{ $pPrice * 0.15 }}"
                                            data-remaining="{{ $pPrice * 0.85 }}">
                                        {{ $property->title }} — Fixed: BDT {{ number_format($pPrice) }} (15% Deposit: BDT {{ number_format($pPrice * 0.15) }})
                                    </option>
                                @empty
                                    <option value="">No property available</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- 15% Deposit Financial Breakdown Card -->
                        <div class="deposit-breakdown-card">
                            <div class="deposit-stat">
                                <span>Fixed Property Price:</span>
                                <strong id="resTotalPriceDisplay">BDT 0</strong>
                            </div>
                            <div class="deposit-stat highlight-deposit">
                                <span>15% Booking Deposit:</span>
                                <strong id="resDepositDisplay">BDT 0</strong>
                            </div>
                            <div class="deposit-stat">
                                <span>85% Installments:</span>
                                <strong id="resRemainingDisplay">BDT 0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Interactive 10-Floor Cube Box Floor Plan (Compact 2-Column Building Layout) -->
                    <div class="portal-card compact">
                        <div class="portal-card-head">
                            <span class="step-num">2</span>
                            <h3>Unit Map (Click white cube box to select)</h3>
                        </div>

                        <div class="cube-legend-bar">
                            <span class="legend-item"><span class="legend-box available"></span> White = Available (Clickable)</span>
                            <span class="legend-item"><span class="legend-box selected"></span> Green = Your Choice</span>
                            <span class="legend-item"><span class="legend-box booked"></span> Dark Gray = Booked</span>
                        </div>

                        <!-- Dynamic Building Maps per Property (Compact 2-Column Stack: Floors 10-6 left, 5-1 right) -->
                        @foreach($availableProjects as $property)
                        @php($allFloors = $buildingUnitMaps[$property->id] ?? [])
                        @php($leftFloors = collect($allFloors)->slice(0, 5))
                        @php($rightFloors = collect($allFloors)->slice(5, 5))

                        <div class="building-floor-plan" id="buildingMap-{{ $property->id }}" style="{{ $loop->first ? '' : 'display:none;' }}">
                            <div class="building-columns-wrapper">
                                <!-- Column 1: Floors 10 to 6 -->
                                <div class="building-col">
                                    @foreach($leftFloors as $floorData)
                                    <div class="floor-row-compact">
                                        <div class="floor-badge-sm">FL {{ $floorData['floor'] }}</div>
                                        <div class="floor-cubes-grid-sm">
                                            @foreach($floorData['units'] as $unit)
                                                @if($unit['is_booked'])
                                                    <button type="button" class="cube-box booked" disabled title="Unit {{ $unit['unit_no'] }} is already booked">
                                                        <span class="cube-unit-code">{{ $unit['unit_no'] }}</span>
                                                    </button>
                                                @else
                                                    <button type="button" 
                                                            class="cube-box available" 
                                                            data-unit-no="{{ $unit['unit_no'] }}"
                                                            onclick="selectUnitCubeBox(this, '{{ $unit['unit_no'] }}')">
                                                        <span class="cube-unit-code">{{ $unit['unit_no'] }}</span>
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Column 2: Floors 5 to 1 -->
                                <div class="building-col">
                                    @foreach($rightFloors as $floorData)
                                    <div class="floor-row-compact">
                                        <div class="floor-badge-sm">FL {{ $floorData['floor'] }}</div>
                                        <div class="floor-cubes-grid-sm">
                                            @foreach($floorData['units'] as $unit)
                                                @if($unit['is_booked'])
                                                    <button type="button" class="cube-box booked" disabled title="Unit {{ $unit['unit_no'] }} is already booked">
                                                        <span class="cube-unit-code">{{ $unit['unit_no'] }}</span>
                                                    </button>
                                                @else
                                                    <button type="button" 
                                                            class="cube-box available" 
                                                            data-unit-no="{{ $unit['unit_no'] }}"
                                                            onclick="selectUnitCubeBox(this, '{{ $unit['unit_no'] }}')">
                                                        <span class="cube-unit-code">{{ $unit['unit_no'] }}</span>
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Right Column: Installment Plan & Reservation Confirmation -->
                <div class="portal-section-side">
                    
                    <!-- Section 3: Tenure & Due Day -->
                    <div class="portal-card compact">
                        <div class="portal-card-head">
                            <span class="step-num">3</span>
                            <h3>Installment Tenure & Due Day</h3>
                        </div>

                        <div class="form-group wide">
                            <label for="reservationInstallmentMonths">Select Installment Plan:</label>
                            <select name="installment_months" id="reservationInstallmentMonths" required class="portal-select">
                                <option value="12">12 Months (1 Year Plan)</option>
                                <option value="24" selected>24 Months (2 Years Plan)</option>
                                <option value="36">36 Months (3 Years Plan)</option>
                            </select>
                        </div>

                        <div class="form-group wide">
                            <label for="reservationInstallmentDay">Monthly Auto-Due Day (1 - 28):</label>
                            <input type="number" min="1" max="28" name="installment_day" id="reservationInstallmentDay" value="5" required class="portal-input">
                        </div>
                    </div>

                    <!-- Reservation Summary Card & Submit -->
                    <div class="portal-card reservation-summary-box compact">
                        <h4>Reservation Summary</h4>
                        <div class="summary-line">
                            <span>Selected Unit:</span>
                            <strong id="selectedUnitDisplay" class="text-primary">None selected (Click a white box)</strong>
                        </div>
                        <div class="summary-line">
                            <span>15% Booking Fee:</span>
                            <strong id="summaryDepositFeeDisplay" class="text-green">BDT 0</strong>
                        </div>
                        <div class="summary-line">
                            <span>Monthly Installment:</span>
                            <strong id="summaryMonthlyFeeDisplay">BDT 0 / month</strong>
                        </div>

                        <button type="submit" class="btn-confirm-reservation" id="btnConfirmReservation" disabled>
                            Confirm Unit Reservation
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</dialog>

<!-- FULL PAGE MAKE PAYMENT PORTAL MODAL -->
<dialog id="paymentModal" class="fullpage-payment-portal">
    <div class="portal-payment-container">
        <!-- Portal Header -->
        <header class="portal-payment-header">
            <div class="portal-brand-title">
                <span class="security-badge-icon">256-Bit SSL Encrypted</span>
                <h2>Intern Estate Payment Gateway Portal</h2>
                <p>Complete your plot installment or full settlement with admin-verified security</p>
            </div>
            <button type="button" class="portal-close-btn" data-modal-close aria-label="Close Portal">Close Portal</button>
        </header>

        <form method="POST" action="{{ route('investor.pay') }}" id="securePaymentForm" class="portal-payment-body">
            @csrf
            
            <div class="portal-payment-grid">
                <!-- Left Column: Plot & Installment Breakdown -->
                <div class="portal-section-main">
                    
                    <!-- Section 1: Plot Selection & Real Price Summary -->
                    <div class="portal-card compact">
                        <div class="portal-card-head">
                            <span class="step-num">1</span>
                            <h3>Select Reserved Plot / Property</h3>
                        </div>
                        
                        <div class="form-group wide">
                            <label for="paymentBookingSelect">Select Your Reserved Property & Unit:</label>
                            <select name="booking_id" id="paymentBookingSelect" required class="portal-select">
                                @forelse($bookings->whereNotIn('status', ['forfeited', 'paid_in_full']) as $booking)
                                    @php($paymentProject = $booking->project)
                                    @php($paymentSummary = $bookingSummaries->get($booking->project_id))
                                    @if(($paymentSummary?->remaining ?? 0) > 0)
                                    <option value="{{ $booking->id }}" 
                                            data-project-title="{{ $paymentProject?->title }}"
                                            data-unit-no="{{ $booking->unit_no }}"
                                            data-investment-amount="{{ $booking->investment_amount }}"
                                            data-paid="{{ $paymentSummary?->paid ?? 0 }}"
                                            data-remaining="{{ $paymentSummary?->remaining ?? 0 }}"
                                            data-installment-months="{{ $booking->installment_months ?: 24 }}"
                                            data-monthly-installment="{{ $booking->monthly_installment_amount }}">
                                        {{ $paymentProject?->title ?? 'Project' }} — Unit {{ $booking->unit_no }} (Remaining: BDT {{ number_format($paymentSummary?->remaining ?? 0) }})
                                    </option>
                                    @endif
                                @empty
                                    <option value="">No active reservation available requiring payment</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- Real Price & Live Financial Summary Card -->
                        <div class="real-plot-summary-box" id="plotSummaryBox">
                            <div class="summary-stat-item">
                                <span>Total Fixed Plot Price</span>
                                <strong id="summaryTotalPrice">BDT 0</strong>
                            </div>
                            <div class="summary-stat-item">
                                <span>Total Amount Paid</span>
                                <strong id="summaryPaidPrice" class="text-green">BDT 0</strong>
                            </div>
                            <div class="summary-stat-item highlight">
                                <span>Current Remaining Balance</span>
                                <strong id="summaryRemainingPrice" class="text-primary">BDT 0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Payment Amount Options (Uses Saved Tenure from Reservation) -->
                    <div class="portal-card compact">
                        <div class="portal-card-head">
                            <span class="step-num">2</span>
                            <h3>Payment Amount Options</h3>
                        </div>

                        <div class="payment-plan-badge-row">
                            <span class="saved-tenure-tag">Selected Plan: <strong id="savedTenureDisplay">24 Months Installment Plan</strong></span>
                        </div>

                        <div class="payment-mode-block">
                            <div class="payment-mode-grid">
                                <label class="mode-card-option">
                                    <input type="radio" name="payment_type" value="installment" checked class="mode-radio" id="modeInstallment">
                                    <div class="mode-card-content">
                                        <div class="mode-title">Scheduled Monthly Installment</div>
                                        <div class="mode-amount" id="calcInstallmentDisplay">BDT 0 / month</div>
                                    </div>
                                </label>

                                <label class="mode-card-option">
                                    <input type="radio" name="payment_type" value="full_settlement" class="mode-radio" id="modeFull">
                                    <div class="mode-card-content">
                                        <div class="mode-title">Pay Full Remaining Balance</div>
                                        <div class="mode-amount" id="calcFullDisplay">BDT 0</div>
                                    </div>
                                </label>

                                <label class="mode-card-option">
                                    <input type="radio" name="payment_type" value="custom" class="mode-radio" id="modeCustom">
                                    <div class="mode-card-content">
                                        <div class="mode-title">Custom Amount</div>
                                        <div class="custom-amount-input-wrap">
                                            <input type="number" min="1000" name="amount" id="customAmountInput" placeholder="Enter custom amount (BDT)" disabled class="portal-input">
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Channel & Terms -->
                <div class="portal-section-side">
                    <!-- Section 3: Payment Channel & References -->
                    <div class="portal-card compact">
                        <div class="portal-card-head">
                            <span class="step-num">3</span>
                            <h3>Gateway & Reference</h3>
                        </div>

                        <div class="form-group wide">
                            <div class="channel-selector-grid">
                                <label class="channel-card">
                                    <input type="radio" name="payment_method" value="bKash" checked>
                                    <span>bKash Online</span>
                                </label>
                                <label class="channel-card">
                                    <input type="radio" name="payment_method" value="Nagad">
                                    <span>Nagad Pay</span>
                                </label>
                                <label class="channel-card">
                                    <input type="radio" name="payment_method" value="Bank Transfer">
                                    <span>Bank Wire</span>
                                </label>
                                <label class="channel-card">
                                    <input type="radio" name="payment_method" value="SSLCommerz">
                                    <span>Card</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group wide">
                            <label for="payerReferenceInput">Transaction / Reference No:</label>
                            <input type="text" name="payer_reference" id="payerReferenceInput" placeholder="e.g. TrxID 9A82B3C7" class="portal-input">
                        </div>
                    </div>

                    <!-- Mandatory Terms & Policy Conditions Box -->
                    <div class="payment-terms-box compact">
                        <h4>Payment Rules & Terms</h4>
                        <ul>
                            <li><strong>Monthly Due Date:</strong> Auto due on designated day.</li>
                            <li><strong>Grace Period:</strong> 15 days grace period allowed.</li>
                            <li><strong>Default Policy:</strong> 3 missed installments (60+ days) leads to cancellation & forfeiture.</li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <div class="portal-submit-area">
                        <button type="submit" class="btn-confirm-secure-payment">
                            Confirm Payment
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</dialog>

@push('scripts')
<script>
// Reservation Portal Script
const resProjectSelect = document.getElementById('reservationProjectSelect');
const resUnitInput = document.getElementById('reservationUnitInput');
const resMonthsSelect = document.getElementById('reservationInstallmentMonths');
const resTotalPriceDisplay = document.getElementById('resTotalPriceDisplay');
const resDepositDisplay = document.getElementById('resDepositDisplay');
const resRemainingDisplay = document.getElementById('resRemainingDisplay');
const selectedUnitDisplay = document.getElementById('selectedUnitDisplay');
const summaryDepositFeeDisplay = document.getElementById('summaryDepositFeeDisplay');
const summaryMonthlyFeeDisplay = document.getElementById('summaryMonthlyFeeDisplay');
const btnConfirmReservation = document.getElementById('btnConfirmReservation');

function updateReservationPortal() {
    if (!resProjectSelect || !resProjectSelect.options.length) return;
    const opt = resProjectSelect.options[resProjectSelect.selectedIndex];
    if (!opt) return;

    const price = parseFloat(opt.getAttribute('data-price') || opt.dataset.price || 0);
    const deposit = parseFloat(opt.getAttribute('data-deposit') || opt.dataset.deposit || (price * 0.15));
    const remaining = parseFloat(opt.getAttribute('data-remaining') || opt.dataset.remaining || (price * 0.85));
    const months = parseInt(resMonthsSelect ? resMonthsSelect.value : 24) || 24;
    const monthlyCalc = months > 0 ? Math.round(remaining / months) : 0;

    if (resTotalPriceDisplay) resTotalPriceDisplay.textContent = `BDT ${price.toLocaleString()}`;
    if (resDepositDisplay) resDepositDisplay.textContent = `BDT ${deposit.toLocaleString()}`;
    if (resRemainingDisplay) resRemainingDisplay.textContent = `BDT ${remaining.toLocaleString()}`;
    if (summaryDepositFeeDisplay) summaryDepositFeeDisplay.textContent = `BDT ${deposit.toLocaleString()}`;
    if (summaryMonthlyFeeDisplay) summaryMonthlyFeeDisplay.textContent = `BDT ${monthlyCalc.toLocaleString()} / month`;

    // Show correct building floor plan map
    document.querySelectorAll('.building-floor-plan').forEach(el => el.style.display = 'none');
    const targetMap = document.getElementById(`buildingMap-${resProjectSelect.value}`);
    if (targetMap) {
        targetMap.style.display = 'block';
    }

    if (resUnitInput && !resUnitInput.value) {
        if (selectedUnitDisplay) {
            selectedUnitDisplay.textContent = 'None selected (Click a white box)';
            selectedUnitDisplay.className = 'text-primary';
        }
        if (btnConfirmReservation) btnConfirmReservation.disabled = true;
    }
}

function selectUnitCubeBox(btnEl, unitNo) {
    if (!btnEl || btnEl.classList.contains('booked') || btnEl.disabled) return;

    document.querySelectorAll('.cube-box').forEach(b => {
        if (!b.classList.contains('booked')) {
            b.classList.remove('selected');
            b.classList.add('available');
        }
    });

    btnEl.classList.remove('available');
    btnEl.classList.add('selected');

    if (resUnitInput) resUnitInput.value = unitNo;
    if (selectedUnitDisplay) {
        selectedUnitDisplay.textContent = `Unit ${unitNo} (Selected)`;
        selectedUnitDisplay.className = 'text-green text-bold';
    }
    if (btnConfirmReservation) btnConfirmReservation.disabled = false;
}

function selectPropertyForReservation(projectId) {
    if (!resProjectSelect) return;
    for (let i = 0; i < resProjectSelect.options.length; i++) {
        if (resProjectSelect.options[i].value == projectId) {
            resProjectSelect.selectedIndex = i;
            if (resUnitInput) resUnitInput.value = '';
            updateReservationPortal();
            break;
        }
    }
}

if (resProjectSelect) {
    resProjectSelect.addEventListener('change', () => {
        if (resUnitInput) resUnitInput.value = '';
        updateReservationPortal();
    });
}
if (resMonthsSelect) {
    resMonthsSelect.addEventListener('change', updateReservationPortal);
}

// Attach listener when opening reservation modal
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-modal-open="bookingModal"]');
    if (btn) {
        setTimeout(() => {
            updateReservationPortal();
        }, 50);
    }
});

// Run calculation immediately on page load
updateReservationPortal();

// Payment History Toggle Function
function togglePlotHistory(containerId) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

// Live Calculation in Full Page Secure Payment Portal
const bookingSelect = document.getElementById('paymentBookingSelect');
const modeRadios = document.querySelectorAll('input[name="payment_type"]');
const customInput = document.getElementById('customAmountInput');

const summaryTotalPrice = document.getElementById('summaryTotalPrice');
const summaryPaidPrice = document.getElementById('summaryPaidPrice');
const summaryRemainingPrice = document.getElementById('summaryRemainingPrice');
const calcInstallmentDisplay = document.getElementById('calcInstallmentDisplay');
const calcFullDisplay = document.getElementById('calcFullDisplay');
const savedTenureDisplay = document.getElementById('savedTenureDisplay');

function updatePaymentPortalCalculations() {
    if (!bookingSelect || !bookingSelect.options.length) return;
    const opt = bookingSelect.options[bookingSelect.selectedIndex];
    if (!opt || !opt.dataset.remaining) return;

    const total = parseFloat(opt.getAttribute('data-investment-amount') || opt.dataset.investmentAmount || 0);
    const paid = parseFloat(opt.getAttribute('data-paid') || opt.dataset.paid || 0);
    const remaining = parseFloat(opt.getAttribute('data-remaining') || opt.dataset.remaining || 0);
    const months = parseInt(opt.getAttribute('data-installment-months') || opt.dataset.installmentMonths || 24);
    const monthlyInstallment = parseFloat(opt.getAttribute('data-monthly-installment') || opt.dataset.monthlyInstallment || Math.round(remaining / months));

    if (summaryTotalPrice) summaryTotalPrice.textContent = `BDT ${total.toLocaleString()}`;
    if (summaryPaidPrice) summaryPaidPrice.textContent = `BDT ${paid.toLocaleString()}`;
    if (summaryRemainingPrice) summaryRemainingPrice.textContent = `BDT ${remaining.toLocaleString()}`;
    if (savedTenureDisplay) savedTenureDisplay.textContent = `${months} Months Installment Plan`;

    if (calcInstallmentDisplay) calcInstallmentDisplay.textContent = `BDT ${monthlyInstallment.toLocaleString()} / month`;
    if (calcFullDisplay) calcFullDisplay.textContent = `BDT ${remaining.toLocaleString()}`;

    // Enable/disable custom input based on mode
    const selectedMode = document.querySelector('input[name="payment_type"]:checked')?.value;
    if (customInput) {
        if (selectedMode === 'custom') {
            customInput.disabled = false;
            customInput.required = true;
            if (!customInput.value) customInput.value = monthlyInstallment;
        } else {
            customInput.disabled = true;
            customInput.required = false;
        }
    }
}

if (bookingSelect) {
    bookingSelect.addEventListener('change', updatePaymentPortalCalculations);
    modeRadios.forEach(r => r.addEventListener('change', updatePaymentPortalCalculations));
    updatePaymentPortalCalculations();
}

// When opening paymentModal from a specific plot's Pay Now button
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-modal-open="paymentModal"]');
    if (btn && btn.dataset.bookingId && bookingSelect) {
        for (let i = 0; i < bookingSelect.options.length; i++) {
            if (bookingSelect.options[i].value == btn.dataset.bookingId) {
                bookingSelect.selectedIndex = i;
                updatePaymentPortalCalculations();
                break;
            }
        }
    }
});

// Notifications Realtime Sync
setInterval(() => fetch('{{ route('api.notifications') }}', {headers:{Accept:'application/json'}}).then(r=>r.json()).then(data=>{const badge=document.getElementById('liveNotificationCount');if(badge&&data.status==='success')badge.textContent=data.unread_count;}).catch(()=>{}), 5000);
</script>
@endpush
@endsection
