@extends('layouts.investor')
@section('title', 'Payment Ledger | Intern Estate')
@section('page-heading', 'Payment Ledger')
@section('content')

<section class="stat-card-grid investor-stat-grid">
    <article class="stat-card investor-stat-card">
        <div><p>Total requested</p><h3>BDT {{ number_format($total) }}</h3></div>
    </article>
    <article class="stat-card investor-stat-card">
        <div><p>Paid</p><h3>BDT {{ number_format($paid) }}</h3></div>
    </article>
    <article class="stat-card investor-stat-card">
        <div><p>Awaiting / outstanding</p><h3>BDT {{ number_format($total - $paid) }}</h3></div>
    </article>
</section>

<section class="dashboard-panel">
    <div class="panel-header">
        <div>
            <h3>Payment history</h3>
            <p>Every request is verified by finance before it is marked paid. <strong>#SL 1 = oldest</strong> (chronological order).</p>
        </div>
    </div>
    <table class="erp-table">
        <thead>
            <tr>
                <th style="width:48px; text-align:center;">#SL</th>
                <th>Property / Unit</th>
                <th>Transaction ID</th>
                <th>Amount (BDT)</th>
                <th>Method / reference</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td style="text-align:center; font-weight:800; color:#6366f1; font-size:13px;">
                    #{{ $loop->iteration }}
                </td>
                <td>
                    @if($payment->booking)
                        <div style="font-weight:700; font-size:13px; color:#0f172a;">{{ $payment->booking->project?->title ?? '—' }}</div>
                        <div style="display:flex;gap:6px;align-items:center;margin-top:3px;flex-wrap:wrap;">
                            <span style="background:#e0f2fe;color:#0369a1;font-size:10px;font-weight:800;padding:1px 7px;border-radius:8px;">Unit {{ $payment->booking->unit_no }}</span>
                            <span class="type-pill" style="font-size:10px;padding:1px 7px;">{{ ucfirst(str_replace('_', ' ', $payment->payment_type ?? 'installment')) }}</span>
                        </div>
                    @elseif($payment->project)
                        <div style="font-weight:700; font-size:13px; color:#0f172a;">{{ $payment->project->title }}</div>
                        <span class="type-pill" style="font-size:10px;padding:1px 7px;margin-top:3px;display:inline-block;">{{ ucfirst(str_replace('_', ' ', $payment->payment_type ?? 'installment')) }}</span>
                    @else
                        <span style="color:#94a3b8;">—</span>
                    @endif
                </td>
                <td><code style="font-size:11px;">{{ $payment->transaction_id }}</code></td>
                <td><strong>BDT {{ number_format($payment->amount) }}</strong></td>
                <td>
                    {{ $payment->payment_method }}
                    <small>{{ $payment->payer_reference ?: 'Reference not provided' }}</small>
                </td>
                <td>
                    @if($payment->status === 'paid')
                        <span class="status-badge-verified">Verified</span>
                    @elseif($payment->status === 'pending')
                        <span class="status-badge-pending">Pending</span>
                    @else
                        <span class="status-badge-rejected">{{ ucfirst($payment->status) }}</span>
                    @endif
                    @if($payment->review_note)
                        <small>{{ $payment->review_note }}</small>
                    @endif
                </td>
                <td>{{ $payment->created_at->format('d M Y') }}</td>
                <td><a class="erp-secondary" href="{{ route('investor.invoice', $payment) }}">Invoice</a></td>
            </tr>
            @empty
            <tr><td colspan="8" class="erp-empty">No payments recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

@endsection
