<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Request Submitted | {{ $payment->transaction_id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #f8fafc;
            color: #0f172a;
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            padding: 40px 20px 80px;
        }
        .container {
            margin: 0 auto;
            max-width: 680px;
        }
        /* Pending Review Hero Box */
        .pending-hero {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
            margin-bottom: 24px;
            padding: 36px 30px;
            text-align: center;
        }
        .pending-hero h1 {
            color: #0f172a;
            font-family: 'Manrope', sans-serif;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .pending-hero p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 18px;
            max-width: 540px;
            margin-left: auto;
            margin-right: auto;
        }
        .tx-badge {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            color: #0f172a;
            display: inline-block;
            font-family: monospace;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 14px;
        }
        /* Invoice Card */
        .invoice-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            padding: 36px;
        }
        .invoice-header {
            align-items: center;
            border-bottom: 2px solid #0f172a;
            display: flex;
            justify-content: space-between;
            padding-bottom: 20px;
        }
        .brand-title h2 {
            font-family: 'Manrope', sans-serif;
            font-size: 22px;
            font-weight: 800;
        }
        .brand-title p {
            color: #64748b;
            font-size: 12px;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-meta strong {
            display: block;
            font-size: 15px;
        }
        .invoice-meta span {
            color: #64748b;
            font-size: 12px;
        }
        .invoice-table {
            border-collapse: collapse;
            margin: 24px 0;
            width: 100%;
        }
        .invoice-table td {
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            padding: 14px 8px;
        }
        .invoice-table td:first-child {
            color: #64748b;
            font-weight: 500;
            width: 45%;
        }
        .invoice-table td:last-child {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }
        .total-row td {
            border-top: 2px solid #e2e8f0;
            border-bottom: 2px solid #0f172a;
            font-size: 18px;
            padding: 16px 8px;
        }
        .total-row td:last-child {
            color: #0f172a;
            font-size: 24px;
            font-weight: 800;
        }
        .status-pill-pending {
            background: #fffbeb;
            border: 1px solid #f59e0b;
            border-radius: 999px;
            color: #b45309;
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 12px;
        }
        .status-pill-approved {
            background: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 999px;
            color: #047857;
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 12px;
        }
        .status-pill-review {
            background: #eef2ff;
            border: 1px solid #6366f1;
            border-radius: 999px;
            color: #4338ca;
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 12px;
        }
        .invoice-footer {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
            margin-top: 20px;
            padding: 14px;
            text-align: center;
        }
        /* Action Buttons */
        .actions-group {
            display: grid;
            gap: 12px;
            grid-template-columns: 1fr 1fr 1fr;
            margin-top: 24px;
        }
        .action-btn {
            align-items: center;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            display: inline-flex;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            justify-content: center;
            padding: 14px 18px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: #0f172a;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #1e293b;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
        }
        .btn-secondary:hover {
            background: #f1f5f9;
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .pending-hero, .actions-group { display: none; }
            .invoice-card { border: none; box-shadow: none; padding: 0; }
        }
        @media (max-width: 600px) {
            .actions-group { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">

    {{-- Pending Admin Review Hero Banner --}}
    <div class="pending-hero">
        <h1>Payment Request Submitted</h1>
        <p>Your payment request and KYC documents have been securely received. Our finance and compliance team will review your application before final approval.</p>
        <span class="tx-badge">Reference: {{ $payment->gateway_transaction_id ?: $payment->transaction_id }}</span>
    </div>

    {{-- Printable Money Receipt Request --}}
    <div class="invoice-card">
        <div class="invoice-header">
            <div class="brand-title">
                <h2>Intern Estate</h2>
                <p>Real Estate & Construction ERP System</p>
            </div>
            <div class="invoice-meta">
                <strong>PAYMENT REQUEST SLIP</strong>
                <span>{{ $payment->transaction_id }}</span>
            </div>
        </div>

        <table class="invoice-table">
            <tr>
                <td>Investor Name</td>
                <td>{{ auth()->user()->name }}</td>
            </tr>
            <tr>
                <td>Project</td>
                <td>{{ $payment->project?->title ?? 'Real Estate Property' }}</td>
            </tr>
            <tr>
                <td>Payment Method & Plan</td>
                <td>{{ $payment->payment_method }}</td>
            </tr>
            <tr>
                <td>Payer Reference / Account</td>
                <td>{{ $payment->payer_reference ?: 'Direct Channel' }}</td>
            </tr>
            <tr>
                <td>Current Request Status</td>
                <td>
                    @if($payment->status === 'paid')
                        <span class="status-pill-approved">Approved & Verified</span>
                    @elseif($payment->status === 'rejected')
                        <span class="status-pill-pending" style="color:#be123c; border-color:#f43f5e; background:#fff1f2;">Rejected</span>
                    @else
                        <span class="status-pill-pending">Pending Admin Review</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>KYC & Legal Verification</td>
                <td>
                    <span class="status-pill-review">Documents Under Review</span>
                </td>
            </tr>
            <tr>
                <td>Submission Date</td>
                <td>{{ $payment->created_at->format('d F Y, h:i A') }}</td>
            </tr>
            <tr class="total-row">
                <td>Submitted Amount</td>
                <td>BDT {{ number_format($payment->amount, 2) }}</td>
            </tr>
        </table>

        <div class="invoice-footer">
            Next Step: An administrator will verify your payment reference and KYC documents at the Admin Desk. You will receive an instant notification once approved.
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="actions-group">
        <a href="{{ route('investor.dashboard') }}" class="action-btn btn-primary">
            Back to Dashboard
        </a>
        <a href="{{ route('investor.ledger') }}" class="action-btn btn-secondary">
            View Ledger
        </a>
        <button onclick="window.print()" class="action-btn btn-secondary">
            Print Request Slip
        </button>
    </div>

</div>
</body>
</html>
