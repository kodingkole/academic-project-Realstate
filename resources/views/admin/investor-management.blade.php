@extends('layouts.portal')
@section('title', 'Investor Operations & KYC Verification | Intern Estate')
@section('page-heading', 'Investor Operations & KYC Desk')
@section('content')

{{-- Unit Reservations Panel --}}
<section class="dashboard-panel" style="margin-bottom: 28px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(15,23,42,0.03); padding: 28px;">
    <div class="panel-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 20px;">
        <div>
            <h3 style="color: #0f172a; font-size: 18px; font-weight: 800; margin-bottom: 4px;">Unit Reservations & Property Bookings</h3>
            <p style="color: #64748b; font-size: 13px;">Active project reservations and flat units allocated to investors.</p>
        </div>
    </div>
    <div class="erp-table-wrap">
        <table class="erp-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Investor</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Project</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Unit No</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Investment Amount</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Booking Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 14px 12px;">
                            <strong style="color: #0f172a; font-size: 13px;">{{ $booking->investor?->name }}</strong>
                            <small style="display: block; color: #64748b; font-size: 11px;">{{ $booking->investor?->email }}</small>
                        </td>
                        <td style="padding: 14px 12px; font-size: 13px; font-weight: 600; color: #334155;">{{ $booking->project?->title }}</td>
                        <td style="padding: 14px 12px;">
                            <span style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #0f172a; font-weight: 800; font-size: 11px; padding: 3px 8px; border-radius: 6px;">{{ $booking->unit_no }}</span>
                        </td>
                        <td style="padding: 14px 12px; color: #0f172a; font-weight: 800; font-size: 14px;">BDT {{ number_format($booking->investment_amount) }}</td>
                        <td style="padding: 14px 12px;">
                            <span style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; font-weight: 700; font-size: 11px; padding: 4px 10px; border-radius: 999px;">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="erp-empty" style="text-align: center; padding: 30px; color: #94a3b8;">No investor reservations recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $bookings->links() }}
</section>

{{-- Payment & KYC Verification Queue --}}
<section class="dashboard-panel" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(15,23,42,0.03); padding: 28px;">
    <div class="panel-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 20px;">
        <div>
            <h3 style="color: #0f172a; font-size: 18px; font-weight: 800; margin-bottom: 4px;">Payment & KYC Document Verification Desk</h3>
            <p style="color: #64748b; font-size: 13px;">Review and cross-verify investor-submitted NID, TIN / Tax Certificate, and Utility Bills before authorizing payments.</p>
        </div>
    </div>

    <div class="erp-table-wrap">
        <table class="erp-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Investor & Project</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Amount & Channel</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800; min-width: 280px;">Buyer KYC Verification (NID / TIN / Utility)</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Status</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800; min-width: 240px;">Finance & Legal Decision</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr style="border-bottom: 1px solid #f1f5f9; vertical-align: top;">
                        
                        {{-- Investor Info --}}
                        <td style="padding: 16px 12px;">
                            <strong style="color: #0f172a; font-size: 14px; display: block; margin-bottom: 2px;">{{ $payment->investor?->name }}</strong>
                            <small style="color: #64748b; font-size: 11px; display: block; margin-bottom: 6px;">{{ $payment->investor?->email }}</small>
                            <span style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px; display: inline-block;">
                                {{ $payment->project?->title ?? 'Real Estate Property' }}
                            </span>
                        </td>

                        {{-- Amount & Channel --}}
                        <td style="padding: 16px 12px;">
                            <strong style="font-size: 15px; color: #047857; display: block; margin-bottom: 3px;">BDT {{ number_format($payment->amount) }}</strong>
                            <small style="color: #0f172a; font-weight: 700; display: block;">{{ $payment->payment_method }}</small>
                            <small style="color: #64748b; font-size: 11px; display: block; margin-top: 3px;">Ref: {{ $payment->payer_reference ?: $payment->transaction_id }}</small>
                        </td>

                        {{-- KYC Documents Card Box --}}
                        <td style="padding: 16px 12px;">
                            <div style="display: grid; gap: 8px;">
                                
                                {{-- NID Item --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px;">
                                    <div>
                                        <span style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 800; display: block;">National ID</span>
                                        <strong style="color: #0f172a; font-size: 12px;">{{ $payment->nid_number ?: ($payment->investor?->nid_number ?: '1992269123456') }}</strong>
                                    </div>
                                    <button type="button" onclick="previewKycDoc('{{ route('admin.investor-payments.document', ['payment' => $payment->id, 'type' => 'nid']) }}', 'National ID (NID) Document')" style="background: #0d9488; color: #ffffff; border: none; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; box-shadow: 0 2px 6px rgba(13,148,136,0.2);">
                                        👁️ View NID
                                    </button>
                                </div>

                                {{-- Tax Cert Item --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px;">
                                    <div>
                                        <span style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 800; display: block;">TIN / Tax Certificate</span>
                                        <strong style="color: #0f172a; font-size: 12px;">{{ $payment->tax_cert_no ?: ($payment->investor?->tin_number ?: 'TIN-8829401928') }}</strong>
                                    </div>
                                    <button type="button" onclick="previewKycDoc('{{ route('admin.investor-payments.document', ['payment' => $payment->id, 'type' => 'tax']) }}', 'TIN / Tax Certificate Document')" style="background: #0d9488; color: #ffffff; border: none; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; box-shadow: 0 2px 6px rgba(13,148,136,0.2);">
                                        👁️ View Tax
                                    </button>
                                </div>

                                {{-- Electricity Bill Item --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px;">
                                    <div>
                                        <span style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 800; display: block;">Electricity / Utility Bill</span>
                                        <strong style="color: #0f172a; font-size: 12px;">{{ $payment->electricity_bill_no ?: ($payment->investor?->electricity_bill_no ?: 'ELEC-99304128') }}</strong>
                                    </div>
                                    <button type="button" onclick="previewKycDoc('{{ route('admin.investor-payments.document', ['payment' => $payment->id, 'type' => 'electricity']) }}', 'Electricity / Utility Bill Document')" style="background: #0d9488; color: #ffffff; border: none; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; box-shadow: 0 2px 6px rgba(13,148,136,0.2);">
                                        👁️ View Bill
                                    </button>
                                </div>

                            </div>
                        </td>

                        {{-- Status Pill --}}
                        <td style="padding: 16px 12px;">
                            @if($payment->status === 'paid')
                                <span style="background: #ecfdf5; border: 1px solid #10b981; color: #047857; font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 999px; display: inline-block;">
                                    Approved
                                </span>
                                <small style="display: block; color: #64748b; font-size: 11px; margin-top: 5px;">Paid: {{ $payment->paid_at?->format('d M Y') }}</small>
                            @elseif($payment->status === 'rejected')
                                <span style="background: #fff1f2; border: 1px solid #f43f5e; color: #be123c; font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 999px; display: inline-block;">
                                    Rejected
                                </span>
                                @if($payment->review_note)<small style="display: block; color: #e11d48; font-size: 11px; margin-top: 5px;">{{ $payment->review_note }}</small>@endif
                            @else
                                <span style="background: #fffbeb; border: 1px solid #f59e0b; color: #b45309; font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 999px; display: inline-block;">
                                    Pending Review
                                </span>
                                <small style="display: block; color: #64748b; font-size: 11px; margin-top: 5px;">{{ $payment->created_at->diffForHumans() }}</small>
                            @endif
                        </td>

                        {{-- Action Buttons --}}
                        <td style="padding: 16px 12px;">
                            @if($payment->status === 'pending')
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    {{-- Approve Form --}}
                                    <form method="POST" action="{{ route('admin.investor-payments.approve', $payment) }}" style="display: flex; flex-direction: column; gap: 5px;">
                                        @csrf @method('PATCH')
                                        <input name="gateway_transaction_id" placeholder="Bank / Gateway TrxID (optional)" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px; padding: 7px 10px; color: #0f172a; width: 100%;">
                                        <button type="submit" style="background: #047857; color: #ffffff; border: none; font-weight: 800; font-size: 11px; padding: 8px 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                            Approve & Confirm Payment
                                        </button>
                                    </form>

                                    {{-- Reject Form --}}
                                    <form method="POST" action="{{ route('admin.investor-payments.reject', $payment) }}" style="display: flex; gap: 5px; margin-top: 4px;">
                                        @csrf @method('PATCH')
                                        <input name="review_note" placeholder="Rejection reason..." required style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px; padding: 7px 10px; color: #0f172a; flex: 1;">
                                        <button type="submit" style="background: #fff1f2; border: 1px solid #f43f5e; color: #be123c; font-weight: 800; font-size: 11px; padding: 7px 12px; border-radius: 6px; cursor: pointer;">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @else
                                <small style="color: #64748b; font-size: 11px;">Audit Logged: {{ $payment->updated_at->format('d M Y, h:i A') }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="erp-empty" style="text-align: center; padding: 30px; color: #94a3b8;">No payment verification requests in the queue.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</section>

{{-- Inline KYC Document Preview Modal --}}
<dialog id="kycViewerModal" style="border: none; border-radius: 16px; padding: 0; width: 90vw; max-width: 900px; background: #0f172a; color: #fff; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
    <div style="padding: 16px 20px; background: #1e293b; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155;">
        <h4 id="kycModalTitle" style="margin: 0; font-size: 16px; font-weight: 800; color: #38bdf8;">KYC Document Preview</h4>
        <button type="button" onclick="document.getElementById('kycViewerModal').close()" style="background: rgba(255,255,255,0.1); border: none; color: #fff; border-radius: 6px; padding: 6px 12px; cursor: pointer; font-weight: 700;">
            ✕ Close
        </button>
    </div>
    <div style="padding: 0; height: 75vh; background: #0f172a; overflow: hidden;">
        <iframe id="kycFrame" style="width: 100%; height: 100%; border: none;"></iframe>
    </div>
</dialog>

@push('scripts')
<script>
function previewKycDoc(url, title) {
    let modal = document.getElementById('kycViewerModal');
    let frame = document.getElementById('kycFrame');
    let titleElem = document.getElementById('kycModalTitle');

    if (titleElem) titleElem.textContent = title || 'KYC Document Preview';
    if (frame) frame.src = url;
    if (modal) modal.showModal();
}
</script>
@endpush

@endsection
