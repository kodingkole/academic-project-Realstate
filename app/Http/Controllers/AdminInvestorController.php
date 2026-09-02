<?php

namespace App\Http\Controllers;

use App\Models\InvestorBooking;
use App\Models\InvestorNotification;
use App\Models\InvestorPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminInvestorController extends Controller
{
    public function index(): View
    {
        return view('admin.investor-management', [
            'bookings' => InvestorBooking::with(['investor', 'project'])->latest()->paginate(15, ['*'], 'bookings'),
            'payments' => InvestorPayment::with(['investor', 'project'])->latest()->paginate(15, ['*'], 'payments'),
        ]);
    }

    public function approvePayment(Request $request, InvestorPayment $payment): RedirectResponse
    {
        $data = $request->validate([
            'gateway_transaction_id' => ['nullable', 'string', 'max:100'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);
        abort_if($payment->status !== 'pending', 422, 'Only pending payments can be approved.');

        DB::transaction(function () use ($payment, $request, $data): void {
            $payment->update($data + [
                'status' => 'paid',
                'reviewed_by' => $request->user()->id,
                'paid_at' => now(),
            ]);
            $booking = $payment->booking;
            if ($booking) {
                $paid = (float) InvestorPayment::where('user_id', $booking->user_id)->where('project_id', $booking->project_id)->where('status', 'paid')->sum('amount');
                $remaining = max(0, (float) $booking->investment_amount - $paid);
                $updates = ['missed_installments' => 0];
                if ($remaining <= 0) {
                    $updates += ['status' => 'paid_in_full', 'next_payment_date' => null];
                } elseif ($payment->payment_type === 'installment' && $booking->installment_day) {
                    $nextMonth = now()->addMonthNoOverflow();
                    $day = (int) $booking->installment_day;
                    $updates['next_payment_date'] = $nextMonth->day(min($day, $nextMonth->daysInMonth))->toDateString();
                }
                $booking->update($updates);
            }
            InvestorNotification::create([
                'user_id' => $payment->user_id,
                'title' => 'Payment confirmed',
                'message' => 'Your payment of ৳'.number_format((float) $payment->amount).' for '.$payment->project->title.' has been confirmed.',
                'type' => 'payment',
            ]);
        });

        return back()->with('success', 'Payment confirmed and the investor has been notified.');
    }

    public function rejectPayment(Request $request, InvestorPayment $payment): RedirectResponse
    {
        $data = $request->validate(['review_note' => ['required', 'string', 'max:1000']]);
        abort_if($payment->status !== 'pending', 422, 'Only pending payments can be rejected.');

        DB::transaction(function () use ($payment, $request, $data): void {
            $payment->update($data + ['status' => 'rejected', 'reviewed_by' => $request->user()->id]);
            InvestorNotification::create([
                'user_id' => $payment->user_id,
                'title' => 'Payment needs attention',
                'message' => 'Your payment request was not approved. Note: '.$data['review_note'],
                'type' => 'payment',
            ]);
        });

        return back()->with('success', 'Payment rejected and the investor has been notified.');
    }

    public function downloadDocument(InvestorPayment $payment, string $type)
    {
        $path = match($type) {
            'nid' => $payment->nid_doc_path,
            'tax' => $payment->tax_cert_path,
            'electricity' => $payment->electricity_bill_path,
            default => null,
        };

        if (!$path || !\Illuminate\Support\Facades\Storage::exists($path)) {
            // Return sample document response if local placeholder was used
            return response("Intern Estate KYC Verification Document\nType: ".strtoupper($type)."\nInvestor: {$payment->investor?->name}\nNID: {$payment->nid_number}\nTIN: {$payment->tax_cert_no}\nUtility No: {$payment->electricity_bill_no}\nTransaction: {$payment->transaction_id}\nAmount: BDT ".number_format($payment->amount)."\nStatus: Submitted for Legal Audit", 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'inline; filename="KYC_'.strtoupper($type).'_'.$payment->transaction_id.'.txt"',
            ]);
        }

        return \Illuminate\Support\Facades\Storage::download($path);
    }
}
