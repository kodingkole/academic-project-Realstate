@extends('layouts.investor')

@section('title', 'Investor Dashboard | Intern Estate')
@section('page-heading', 'Investor Dashboard')

@section('content')
<!-- Validation Error Alert: Displays flash errors or form validation failures -->
@if($errors->any())
<div role="alert" style="margin:0 0 20px;padding:14px 18px;border:1px solid #fca5a5;border-radius:10px;background:#fef2f2;color:#991b1b;font-weight:600">
    {{ $errors->first() }}
</div>
@endif

<!-- Hero Header Section: Displays investor welcome greeting and portfolio summary introduction -->
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

<!-- Payment Confirmation Modal: Displays transaction receipt after returning from payment gateway -->
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

<!-- Top Summary Metrics Grid: Displays aggregated portfolio totals -->
<section class="stat-card-grid investor-stat-grid">
    <article class="stat-card investor-stat-card"><div><p>Total contracted value</p><h3>BDT {{ number_format($bookings->sum('investment_amount')) }}</h3></div></article>
    <article class="stat-card investor-stat-card"><div><p>Active projects</p><h3>{{ $projects->count() }}</h3></div></article>
    <article class="stat-card investor-stat-card"><div><p>Confirmed payments</p><h3>BDT {{ number_format($paid) }}</h3></div></article>
    <article class="stat-card investor-stat-card"><div><p>Unread updates</p><h3 id="liveNotificationCount">{{ $unreadNotifications }}</h3></div></article>
</section>

<!-- Overdue & Upcoming Due Date Alerts Banner: Warns investor about installments due within 7 days or overdue -->
@if(!empty($dueAlerts))
@foreach($dueAlerts as $alert)
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:14px 20px;border-radius:12px;margin-bottom:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);{{ $alert['style'] }}" role="alert">
    <div style="display:flex;align-items:center;gap:12px;">
        <span style="display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">{!! $alert['icon'] !!}</span>
        <div>
            <strong style="font-size:14px;display:block;">{{ $alert['text'] }}</strong>
            <span style="font-size:12px;opacity:0.85;">
                {{ $alert['project_title'] }} — Unit {{ $alert['unit_no'] }}
                &bull; Due: {{ $alert['due_formatted'] }}
            </span>
        </div>
    </div>
    <button type="button" class="btn-portfolio-pay"
            style="flex-shrink:0;font-size:13px;padding:7px 16px;"
            data-modal-open="paymentModal"
            data-booking-id="{{ $alert['booking_id'] }}"
            data-project-id="{{ $alert['project_id'] }}">
        Pay Now
    </button>
</div>
@endforeach
@endif

<!-- Quick Actions Section: Quick modal triggers for reserving a unit or making an installment payment -->
<section class="investor-actions">
    <div class="investor-actions-intro"><span>QUICK ACTIONS</span><h3>Manage your investment</h3><p>Reserve an available unit with a 15% deposit, then pay the scheduled installment or full balance.</p></div>
    <article class="investor-action-card"><h4>Reserve a unit</h4><p>Interactive 10-floor cube map. Pay 15% booking deposit to lock unit.</p><button type="button" class="investor-reserve-button" data-modal-open="bookingModal">Reserve property</button></article>
    <article class="investor-action-card payment"><h4>Make a payment</h4><p>Submit your scheduled installment or settle the complete remaining balance.</p><button type="button" class="investor-pay-button" data-modal-open="paymentModal">Make a payment</button></article>
</section>

<!-- Available Properties Showcase: Displays active projects with 15% initial booking fee calculation -->
<section class="dashboard-panel" style="margin-bottom:25px">
    <div class="panel-header"><div><h3>Available properties</h3><p>Select any property to view its 10-floor unit floorplan and 15% reservation fee.</p></div></div>
    <div style="display:grid;gap:15px;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));padding:10px 0">
        @forelse($availableProjects as $property)
        @php($propPrice = $property->total_budget ?: 5000000)
        @php(preg_match('/Dag #?(\d+)/i', $property->title, $_dagM))
        <div class="investor-property-card">
            <div style="display:flex;align-items:flex-start;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                <h4 style="font-size:16px;margin:0;color:#0f172a;flex:1;min-width:0">{{ $property->title }}</h4>
                @if(!empty($_dagM[1]))
                    <span style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:800;padding:2px 8px;border-radius:8px;border:1px solid #fde68a;white-space:nowrap;flex-shrink:0;">Dag {{ $_dagM[1] }}</span>
                @endif
            </div>
            <p style="color:#64748b;font-size:12px;margin-bottom:12px;display:flex;align-items:center;gap:4px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> {{ $property->location }}</p>
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

<!-- My Investment Portfolio: List of all units reserved by the logged-in investor -->
<section class="investor-dashboard-grid">
    <article class="dashboard-panel">
        <div class="panel-header">
            <div>
                <h3>My portfolio</h3>
                <p>Live payment progress for every reserved unit.</p>
            </div>
            <a class="erp-secondary" href="{{ route('investor.ledger') }}">View full ledger</a>
        </div>

        @forelse($bookings as $booking)
            @php($project = $booking->project)
            @php($summary = $bookingSummaries->get($booking->id) ?? $bookingSummaries->get($project?->id))
            @php($isFullyPaid = ($summary?->remaining ?? 0) <= 0 || $booking?->status === 'paid_in_full')
            @php($dueDate = $booking?->next_payment_date ? \Carbon\Carbon::parse($booking->next_payment_date) : null)
            @php($isOverdue = !$isFullyPaid && $dueDate && $dueDate->lt(today()->startOfDay()))
            @php($isForfeited = $booking?->status === 'forfeited')
            @php($isBlocked = $isOverdue || $isForfeited || in_array($booking?->status, ['blocked']))
            @php($daysOverdue = ($dueDate && $isOverdue) ? today()->diffInDays($dueDate) : 0)
            @php($unblockRequested = session('unblock_requested_'.$booking?->id))
            @if(!$project) @continue @endif

            <div class="portfolio-item-card {{ $isBlocked ? 'is-blocked-card' : '' }}">
                <div class="portfolio-item-header">
                    <div class="portfolio-title-block">
                        {{-- Serial number badge (Chronological order: #1 = earliest reserved) --}}
                        <span style="display:inline-flex; align-items:center; justify-content:center; padding: 4px 10px; background: linear-gradient(135deg, #1e293b, #334155); color: #f8fafc; font-weight: 800; font-size: 13px; border-radius: 6px; letter-spacing: 0.5px; box-shadow: 0 2px 5px rgba(0,0,0,0.12); flex-shrink: 0;" title="Reservation order: #{{ $loop->iteration }} means this is the {{ $loop->iteration == 1 ? '1st' : ($loop->iteration == 2 ? '2nd' : ($loop->iteration == 3 ? '3rd' : $loop->iteration.'th')) }} property you reserved">
                            SL #{{ $loop->iteration }}
                        </span>
                        <span class="portfolio-unit-badge {{ $isBlocked ? 'badge-blocked' : '' }}">Unit {{ $booking?->unit_no }}</span>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <h4 class="portfolio-project-title" style="margin: 0;">{{ $project->title }}</h4>
                                @if($isForfeited)
                                    <span style="background:#fef2f2; color:#991b1b; font-size:11px; font-weight:700; padding:3px 9px; border-radius:12px; border:1px solid #fca5a5; display:inline-flex; align-items:center; gap:4px;"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg> Forfeited</span>
                                @elseif($isBlocked)
                                    <span class="status-blocked-tag" style="display:inline-flex; align-items:center; gap:4px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Blocked</span>
                                @endif
                                @if($isFullyPaid)
                                    <span style="background:#dcfce7; color:#166534; font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; border:1px solid #bbf7d0; display:inline-flex; align-items:center; gap:4px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Paid in Full (100%)</span>
                                @endif
                            </div>
                            <div class="portfolio-project-meta" style="margin-top: 4px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                                <span><strong>Reserved on:</strong> {{ $booking->created_at ? $booking->created_at->format('d M Y, h:i A') : 'N/A' }}</span>
                                <span class="meta-separator">•</span>
                                <span style="display:inline-flex; align-items:center; gap:4px;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> {{ $project->location }}</span>
                                @if(str_contains($project->title, 'Dag #'))
                                    @php(preg_match('/Dag #?(\d+)/i', $project->title, $dagMatch))
                                    @if(!empty($dagMatch[1]))
                                        <span style="background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 800; padding: 2px 9px; border-radius: 10px; border: 1px solid #fde68a;">Dag: {{ $dagMatch[1] }}</span>
                                    @endif
                                @endif
                                <span class="meta-separator">•</span>
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
                                {{ $isBlocked ? 'Pay Overdue' : 'Pay Now' }}
                            </button>
                        @endif

                        <button type="button" class="btn-portfolio-history" onclick="togglePlotHistory('history-{{ $booking->id }}')">
                            Payment History ({{ $booking->payments->count() }})
                        </button>
                    </div>
                </div>

                <!-- Financial Progress Bar: Displays total paid vs remaining balance and payment plan tenure -->
                <div class="portfolio-progress-container">
                    <div class="portfolio-progress-bar">
                        <span class="portfolio-progress-fill" style="width: {{ $summary?->percentage ?? 0 }}%"></span>
                    </div>
                    <div class="portfolio-progress-labels">
                        <span><strong>Plan:</strong> {{ $booking?->installment_months ? $booking->installment_months.' months ('.($booking->installment_months / 12).' year'.($booking->installment_months > 12 ? 's' : '').')' : 'Full payment' }}</span>
                        <span><strong>Paid:</strong> BDT {{ number_format($summary?->paid ?? 0) }} ({{ $summary?->percentage ?? 0 }}%)</span>
                        <span class="{{ $isFullyPaid ? 'text-green' : 'text-bold' }}"><strong>Remaining:</strong> BDT {{ number_format($summary?->remaining ?? 0) }}</span>
                        @if($booking?->next_payment_date && !$isFullyPaid)
                            @php($daysUntilDue = (int) \Carbon\Carbon::parse($booking->next_payment_date)->startOfDay()->diffInDays(today()->startOfDay(), false) * -1)
                            @if($isOverdue)
                                <span class="next-due-tag tag-overdue">
                                    <strong>Overdue:</strong> {{ $booking->next_payment_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="next-due-tag" style="display: inline-flex; align-items: center; gap: 6px;">
                                    <strong>Next due:</strong> {{ $booking->next_payment_date->format('d M Y') }}
                                    <span style="background: #e0f2fe; color: #0369a1; font-weight: 800; font-size: 11px; padding: 1px 7px; border-radius: 4px;">
                                        {{ $daysUntilDue > 0 ? 'in ' . $daysUntilDue . ' days' : ($daysUntilDue == 0 ? 'Due Today' : 'Overdue') }}
                                    </span>
                                </span>
                            @endif
                        @endif
                    </div>
                </div>

                @php($activeTasks = $project->tasks->where('status', 'in_progress'))
                @php($latestUpdate = $latestConstructionUpdates->get($project->id))
                <!-- Live Construction Update: Shows published work phases without a percentage estimate -->
                <section class="flat-work-update" aria-label="Construction update for {{ $booking?->unit_no }}">
                    <div class="flat-work-update-head"><div><span>LIVE CONSTRUCTION UPDATE</span><h5>Your flat {{ $booking?->unit_no }}: building work in progress</h5></div></div>
                    @if($activeTasks->isNotEmpty())
                        <div class="flat-work-list">@foreach($activeTasks->take(3) as $task)<div><strong>{{ $task->title }}</strong></div>@endforeach</div>
                    @else
                        <p class="flat-work-empty">No active phase has been published yet. Check back for the next construction update.</p>
                    @endif
                    @if($latestUpdate)<p class="flat-work-summary"><strong>{{ \Carbon\Carbon::parse($latestUpdate->report_date)->format('d M Y') }} update:</strong> {{ $latestUpdate->work_summary }}</p>@endif
                </section>

                @if($isBlocked)
                <!-- Clean, real-world overdue alert bar -->
                <div class="overdue-blocked-banner">
                    <div class="blocked-banner-left">
                        <span class="blocked-bell-icon" style="display:inline-flex;align-items:center;justify-content:center;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></span>
                        <div>
                            <div class="blocked-title-line">
                                <strong>Plot / Flat Blocked</strong>
                                <span class="blocked-badge-small">Due: {{ $booking->next_payment_date?->format('d M Y') }}</span>
                            </div>
                            <p class="blocked-subtext">
                                Contact admin to unblock, or risk forfeiture after 3 months of non-payment. Admin er kache request pathan, nahole 3 month por flat cancel hote pare.
                            </p>
                        </div>
                    </div>
                    <div class="blocked-banner-actions">
                        @if($unblockRequested)
                            <!-- Unblock request already submitted -->
                            <span class="unblock-submitted-pill" style="display:inline-flex;align-items:center;gap:4px;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Request Sent</span>
                        @else
                            <!-- Send unblock request to admin -->
                            <form method="POST" action="{{ route('investor.bookings.request-unblock', $booking) }}" style="display: inline; margin: 0;">
                                @csrf
                                <button type="submit" class="btn-request-admin-unblock">
                                    Request Admin
                                </button>
                            </form>
                        @endif

                        <!-- Pay overdue installment -->
                        <button type="button" 
                                class="btn-blocked-pay-now" 
                                data-modal-open="paymentModal"
                                data-booking-id="{{ $booking->id }}"
                                data-project-id="{{ $project->id }}">
                            Pay Overdue
                        </button>
                    </div>
                </div>
                @endif

                <!-- Payment History Drawer: Expandable table displaying all past payments for this unit -->
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

    <!-- Sidebar: Investor Profile and KYC verification status -->
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

<!-- ========================================================================= -->
<!-- MODAL 1: UNIT RESERVATION PORTAL (10-Floor Cube Map + 15% Booking Deposit) -->
<!-- ========================================================================= -->
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
                                            data-remaining="{{ $pPrice * 0.85 }}"
                                            data-location="{{ $property->location }}">
                                        {{ $property->title }} — BDT {{ number_format($pPrice) }} (Deposit: BDT {{ number_format($pPrice * 0.15) }})
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
                    
                    <!-- Section 3: Installment Plan & 15% Booking Fee -->
                    <div class="portal-card compact">
                        <div class="portal-card-head">
                            <span class="step-num">3</span>
                            <h3>Installment Plan & Due Schedule</h3>
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
                            <label>Next Installment Due Date:</label>
                            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #0f172a;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="color: #0369a1; font-size: 14px;">{{ now()->addDays(30)->format('d M Y') }}</strong>
                                    <span style="background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">Auto-Calculated (30 Days)</span>
                                </div>
                                <small style="color: #64748b; font-size: 11px; display: block; margin-top: 4px;">
                                    15% booking deposit is paid today ({{ now()->format('d M Y') }}). Your next monthly installment is due exactly 30 days after reservation date.
                                </small>
                            </div>
                        </div>

                        <div class="form-group wide" style="margin-top: 6px;">
                            <label for="reservationPaymentMethod">15% Booking Fee Payment Method:</label>
                            <select name="payment_method" id="reservationPaymentMethod" class="portal-select">
                                <option value="bKash">bKash (01711223344)</option>
                                <option value="Nagad">Nagad (01819876543)</option>
                                <option value="Rocket">Rocket (017112233441)</option>
                                <option value="Bank Transfer">Bank Transfer (City Bank)</option>
                                <option value="SSLCommerz">SSLCommerz Gateway</option>
                            </select>
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
                            <span>15% Booking Fee (Paid Now):</span>
                            <strong id="summaryDepositFeeDisplay" class="text-green">BDT 0</strong>
                        </div>
                        <div class="summary-line">
                            <span>Monthly Installment (Due in 30 Days):</span>
                            <strong id="summaryMonthlyFeeDisplay">BDT 0 / month</strong>
                        </div>

                        <button type="submit" class="btn-confirm-reservation" id="btnConfirmReservation" disabled>
                            Proceed to Payment Gateway (15% Deposit) &rarr;
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</dialog>

<!-- ========================================================================= -->
<!-- MODAL 2: MAKE PAYMENT PORTAL (Installment, Full Settlement & Custom Dues) -->
<!-- ========================================================================= -->
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
            <input type="hidden" name="gateway_checkout" value="1">
            
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
                                    @php($paymentSummary = $bookingSummaries->get($booking->id) ?? $bookingSummaries->get($booking->project_id))
                                    @if(($paymentSummary?->remaining ?? 0) > 0)
                                    <option value="{{ $booking->id }}" 
                                            data-project-title="{{ $paymentProject?->title }}"
                                            data-unit-no="{{ $booking->unit_no }}"
                                            data-investment-amount="{{ $booking->investment_amount }}"
                                            data-paid="{{ $paymentSummary?->paid ?? 0 }}"
                                            data-remaining="{{ $paymentSummary?->remaining ?? 0 }}"
                                            data-installment-months="{{ $booking->installment_months ?: 24 }}"
                                            data-monthly-installment="{{ $booking->monthly_installment_amount }}">
                                        [SL #{{ $loop->iteration }}] {{ $paymentProject?->title ?? 'Project' }} — Unit {{ $booking->unit_no }} (Remaining: BDT {{ number_format($paymentSummary?->remaining ?? 0) }})
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

                                <label class="mode-card-option" for="modeCustom">
                                    <input type="radio" name="payment_type" value="custom" class="mode-radio" id="modeCustom" onchange="updatePaymentPortalCalculations()">
                                    <div class="mode-card-content">
                                        <div class="mode-title">Custom Amount</div>
                                        <div class="custom-amount-input-wrap" style="margin-top: 6px;" onclick="event.stopPropagation()">
                                            <input type="number" min="1000" name="amount" id="customAmountInput" placeholder="Enter custom amount (BDT)" class="portal-input" onfocus="document.getElementById('modeCustom').checked=true;updatePaymentPortalCalculations();" oninput="validateCustomAmountLimits()" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; color: #0f172a; font-size: 14px; font-weight: 700; padding: 10px 14px; width: 100%;">
                                            <small id="customLimitNotice" style="font-size: 11px; color: #059669; font-weight: 700; display: block; margin-top: 4px;">Min: Monthly Installment | Max: Remaining Balance</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Gateway Checkout Notice & Submit -->
                <div class="portal-section-side">
                    <!-- Section 3: Payment Gateway Checkout -->
                    <div class="portal-card compact">
                        <div class="portal-card-head">
                            <span class="step-num">3</span>
                            <h3>Payment Gateway Checkout</h3>
                        </div>

                        <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <span style="font-size: 13px; color: #475569; font-weight: 700;">Direct Payment Channels:</span>
                                <span style="font-size: 11px; background: #e0f2fe; color: #0369a1; font-weight: 800; padding: 2px 8px; border-radius: 4px;">SSL Secured</span>
                            </div>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                                <span style="background: #fdf2f4; color: #9f1239; border: 1px solid #fce7eb; font-weight: 800; font-size: 12px; padding: 4px 10px; border-radius: 6px;">bKash</span>
                                <span style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; font-weight: 800; font-size: 12px; padding: 4px 10px; border-radius: 6px;">Nagad</span>
                                <span style="background: #faf5ff; color: #6b21a8; border: 1px solid #f3e8ff; font-weight: 800; font-size: 12px; padding: 4px 10px; border-radius: 6px;">Rocket</span>
                                <span style="background: #f0f9ff; color: #0369a1; border: 1px solid #e0f2fe; font-weight: 800; font-size: 12px; padding: 4px 10px; border-radius: 6px;">Cards &amp; SSL</span>
                            </div>
                            <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.5;">
                                Click below to proceed to the dedicated hosted gateway. You can choose any payment method and verify with your Mobile Number &rarr; OTP &rarr; PIN.
                            </p>
                        </div>
                    </div>

                    <!-- Mandatory Terms & Policy Conditions Box -->
                    <div class="payment-terms-box compact">
                        <h4>Payment Rules & Terms</h4>
                        <ul>
                            <li><strong>Monthly Due Date:</strong> Auto calculated 30 days from reservation.</li>
                            <li><strong>Grace Period:</strong> 15 days grace period allowed.</li>
                            <li><strong>Default Policy:</strong> 3 missed installments (60+ days) leads to cancellation & forfeiture.</li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <div class="portal-submit-area">
                        <button type="submit" class="btn-confirm-secure-payment">
                            Proceed to Payment Gateway &rarr;
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

// Reservation Portal Live Calculation
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

// Toggle Payment History Drawer for a specific unit
function togglePlotHistory(containerId) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

// =========================================================================
// Payment Portal Script: Calculates Installment, Full Balance & Custom Dues
// =========================================================================
const bookingSelect = document.getElementById('paymentBookingSelect');
const modeRadios = document.querySelectorAll('input[name="payment_type"]');
const customInput = document.getElementById('customAmountInput');

const summaryTotalPrice = document.getElementById('summaryTotalPrice');
const summaryPaidPrice = document.getElementById('summaryPaidPrice');
const summaryRemainingPrice = document.getElementById('summaryRemainingPrice');
const calcInstallmentDisplay = document.getElementById('calcInstallmentDisplay');
const calcFullDisplay = document.getElementById('calcFullDisplay');
const savedTenureDisplay = document.getElementById('savedTenureDisplay');

window.activateCustomAmountMode = function(e) {
    if (e && e.stopPropagation) e.stopPropagation();
    let modeRadio = document.getElementById('modeCustom');
    if (modeRadio) modeRadio.checked = true;
    let customInput = document.getElementById('customAmountInput');
    if (customInput) {
        customInput.disabled = false;
        customInput.focus();
    }
    updatePaymentPortalCalculations();
};

let currentMinCustom = 1000;
let currentMaxCustom = 10000000;

function updatePaymentPortalCalculations() {
    if (!bookingSelect || !bookingSelect.options.length) return;
    const opt = bookingSelect.options[bookingSelect.selectedIndex];
    if (!opt || !opt.dataset.remaining) return;

    const total = parseFloat(opt.getAttribute('data-investment-amount') || opt.dataset.investmentAmount || 0);
    const paid = parseFloat(opt.getAttribute('data-paid') || opt.dataset.paid || 0);
    const remaining = parseFloat(opt.getAttribute('data-remaining') || opt.dataset.remaining || 0);
    const months = parseInt(opt.getAttribute('data-installment-months') || opt.dataset.installmentMonths || 24);
    const monthlyInstallment = parseFloat(opt.getAttribute('data-monthly-installment') || opt.dataset.monthlyInstallment || Math.round(remaining / months));

    currentMinCustom = Math.min(monthlyInstallment, remaining);
    currentMaxCustom = remaining;

    if (summaryTotalPrice) summaryTotalPrice.textContent = `BDT ${total.toLocaleString()}`;
    if (summaryPaidPrice) summaryPaidPrice.textContent = `BDT ${paid.toLocaleString()}`;
    if (summaryRemainingPrice) summaryRemainingPrice.textContent = `BDT ${remaining.toLocaleString()}`;
    if (savedTenureDisplay) savedTenureDisplay.textContent = `${months} Months Installment Plan`;

    if (calcInstallmentDisplay) calcInstallmentDisplay.textContent = `BDT ${monthlyInstallment.toLocaleString()} / month`;
    if (calcFullDisplay) calcFullDisplay.textContent = `BDT ${remaining.toLocaleString()}`;

    const limitNotice = document.getElementById('customLimitNotice');
    if (limitNotice) {
        limitNotice.textContent = `Min: BDT ${currentMinCustom.toLocaleString()} (Installment) | Max: BDT ${currentMaxCustom.toLocaleString()} (Remaining)`;
    }

    // Enable/disable custom input based on mode
    const selectedMode = document.querySelector('input[name="payment_type"]:checked')?.value;
    if (customInput) {
        customInput.min = currentMinCustom;
        customInput.max = currentMaxCustom;

        if (selectedMode === 'custom') {
            customInput.disabled = false;
            customInput.required = true;
            if (!customInput.value) customInput.value = currentMinCustom;
            validateCustomAmountLimits();
        } else {
            customInput.disabled = true;
            customInput.required = false;
        }
    }
}

function validateCustomAmountLimits() {
    if (!customInput) return;
    let val = parseFloat(customInput.value);
    let notice = document.getElementById('customLimitNotice');
    if (!notice) return;

    if (isNaN(val)) {
        notice.textContent = `Min: BDT ${currentMinCustom.toLocaleString()} | Max: BDT ${currentMaxCustom.toLocaleString()}`;
        notice.style.color = '#059669';
        return;
    }

    if (val > currentMaxCustom) {
        customInput.value = currentMaxCustom;
        notice.textContent = `Cannot exceed remaining balance: BDT ${currentMaxCustom.toLocaleString()}`;
        notice.style.color = '#dc2626';
    } else if (val < currentMinCustom) {
        notice.textContent = `Cannot be less than monthly installment: BDT ${currentMinCustom.toLocaleString()}`;
        notice.style.color = '#d97706';
    } else {
        notice.textContent = `Valid Custom Amount: BDT ${val.toLocaleString()} (Min: BDT ${currentMinCustom.toLocaleString()} | Max: BDT ${currentMaxCustom.toLocaleString()})`;
        notice.style.color = '#059669';
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
