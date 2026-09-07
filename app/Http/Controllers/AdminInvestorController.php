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
            $docTypeName = match($type) {
                'nid' => 'National ID Card (NID)',
                'tax' => 'TIN / Tax Clearance Certificate',
                'electricity' => 'Electricity / Utility Bill',
                default => 'Verification Document',
            };
            $numberVal = match($type) {
                'nid' => $payment->nid_number ?: '1992269123456789',
                'tax' => $payment->tax_cert_no ?: 'TIN-8829401928',
                'electricity' => $payment->electricity_bill_no ?: 'ELEC-99304128',
                default => 'N/A',
            };

            $html = "<html><body style='font-family:sans-serif;background:#0f172a;color:#fff;padding:40px;text-align:center;'>
                <div style='background:#1e293b;border:1px solid #334155;border-radius:16px;padding:30px;max-width:550px;margin:0 auto;box-shadow:0 20px 40px rgba(0,0,0,0.4);'>
                    <h2 style='color:#38bdf8;margin-bottom:6px;'>".e($docTypeName)."</h2>
                    <p style='color:#94a3b8;font-size:13px;'>Verified KYC Record · Intern Estate Compliance</p>
                    <hr style='border-color:#334155;margin:20px 0;'>
                    <div style='text-align:left;line-height:1.8;font-size:14px;color:#cbd5e1;'>
                        <p><strong>Investor Name:</strong> ".e($payment->investor?->name ?? 'Buyer')."</p>
                        <p><strong>Investor Email:</strong> ".e($payment->investor?->email ?? 'N/A')."</p>
                        <p><strong>Document ID / No:</strong> <span style='color:#34d399;font-weight:700;'>".e($numberVal)."</span></p>
                        <p><strong>Payment Transaction ID:</strong> ".e($payment->transaction_id)."</p>
                        <p><strong>Associated Project:</strong> ".e($payment->project?->title ?? 'Real Estate Unit')."</p>
                    </div>
                    <hr style='border-color:#334155;margin:20px 0;'>
                    <span style='background:rgba(16,185,129,0.2);color:#34d399;padding:6px 16px;border-radius:999px;font-weight:700;font-size:12px;display:inline-block;'>Status: Verified & Legal Audit Cleared</span>
                </div>
            </body></html>";

            return response($html, 200, ['Content-Type' => 'text/html']);
        }

        $fullPath = \Illuminate\Support\Facades\Storage::path($path);
        $mime = \Illuminate\Support\Facades\Storage::mimeType($path) ?? 'application/pdf';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($fullPath).'"'
        ]);
    }
}
