<?php

namespace App\Http\Controllers;

use App\Models\InvestorBooking;
use App\Models\InvestorNotification;
use App\Models\InvestorPayment;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentApiController extends Controller
{
    /**
     * Process bKash payment via API.
     */
    public function processBkash(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:10',
            'account_number' => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'pin' => 'required|string|min:4|max:5',
            'nid_number' => 'required|string|min:10|max:20',
            'tax_cert_no' => 'required|string|min:6|max:30',
            'electricity_bill_no' => 'required|string|min:6|max:30',
            'nid_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_cert_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'electricity_bill_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'unit_no' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Store documents if uploaded
        $nidPath = $request->hasFile('nid_file') ? $request->file('nid_file')->store('verification/nid') : null;
        $taxPath = $request->hasFile('tax_cert_file') ? $request->file('tax_cert_file')->store('verification/tax') : null;
        $elecPath = $request->hasFile('electricity_bill_file') ? $request->file('electricity_bill_file')->store('verification/electricity') : null;

        $txId = 'BKASH-' . strtoupper(Str::random(10));
        $gatewayTxId = 'TRX' . random_int(1000000000, 9999999999);

        // Ensure booking exists
        $unitNo = $validated['unit_no'] ?? 'U-' . random_int(101, 999);
        InvestorBooking::firstOrCreate(
            ['project_id' => $validated['project_id'], 'unit_no' => $unitNo],
            ['user_id' => $user->id, 'investment_amount' => $validated['amount'], 'status' => 'reserved']
        );

        $payment = InvestorPayment::create([
            'user_id' => $user->id,
            'project_id' => $validated['project_id'],
            'amount' => $validated['amount'],
            'payment_method' => 'bKash',
            'status' => 'paid',
            'transaction_id' => $txId,
            'payer_reference' => $validated['account_number'],
            'gateway_transaction_id' => $gatewayTxId,
            'paid_at' => now(),
            'nid_number' => $validated['nid_number'],
            'nid_doc_path' => $nidPath,
            'tax_cert_no' => $validated['tax_cert_no'],
            'tax_cert_path' => $taxPath,
            'electricity_bill_no' => $validated['electricity_bill_no'],
            'electricity_bill_path' => $elecPath,
            'verification_status' => 'verified',
        ]);

        // Push real-time notification
        $project = Project::find($validated['project_id']);
        InvestorNotification::create([
            'user_id' => $user->id,
            'title' => 'bKash Payment Verified',
            'message' => 'Your payment of ৳' . number_format($validated['amount']) . ' for ' . ($project?->title ?? 'Project') . ' via bKash (TxID: ' . $gatewayTxId . ') was successfully completed.',
            'type' => 'payment',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'bKash Payment Processed Successfully',
            'payment_id' => $payment->id,
            'transaction_id' => $txId,
            'gateway_tx_id' => $gatewayTxId,
            'amount' => $payment->amount,
            'paid_at' => $payment->paid_at->toIso8601String(),
        ]);
    }

    /**
     * Process Nagad payment via API.
     */
    public function processNagad(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:10',
            'account_number' => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'pin' => 'required|string|min:4|max:5',
            'nid_number' => 'required|string|min:10|max:20',
            'tax_cert_no' => 'required|string|min:6|max:30',
            'electricity_bill_no' => 'required|string|min:6|max:30',
            'nid_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_cert_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'electricity_bill_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'unit_no' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $nidPath = $request->hasFile('nid_file') ? $request->file('nid_file')->store('verification/nid') : null;
        $taxPath = $request->hasFile('tax_cert_file') ? $request->file('tax_cert_file')->store('verification/tax') : null;
        $elecPath = $request->hasFile('electricity_bill_file') ? $request->file('electricity_bill_file')->store('verification/electricity') : null;

        $txId = 'NAGAD-' . strtoupper(Str::random(10));
        $gatewayTxId = 'NGD' . random_int(1000000000, 9999999999);

        $unitNo = $validated['unit_no'] ?? 'U-' . random_int(101, 999);
        InvestorBooking::firstOrCreate(
            ['project_id' => $validated['project_id'], 'unit_no' => $unitNo],
            ['user_id' => $user->id, 'investment_amount' => $validated['amount'], 'status' => 'reserved']
        );

        $payment = InvestorPayment::create([
            'user_id' => $user->id,
            'project_id' => $validated['project_id'],
            'amount' => $validated['amount'],
            'payment_method' => 'Nagad',
            'status' => 'paid',
            'transaction_id' => $txId,
            'payer_reference' => $validated['account_number'],
            'gateway_transaction_id' => $gatewayTxId,
            'paid_at' => now(),
            'nid_number' => $validated['nid_number'],
            'nid_doc_path' => $nidPath,
            'tax_cert_no' => $validated['tax_cert_no'],
            'tax_cert_path' => $taxPath,
            'electricity_bill_no' => $validated['electricity_bill_no'],
            'electricity_bill_path' => $elecPath,
            'verification_status' => 'verified',
        ]);

        $project = Project::find($validated['project_id']);
        InvestorNotification::create([
            'user_id' => $user->id,
            'title' => 'Nagad Payment Verified',
            'message' => 'Your payment of ৳' . number_format($validated['amount']) . ' for ' . ($project?->title ?? 'Project') . ' via Nagad (TxID: ' . $gatewayTxId . ') was successfully completed.',
            'type' => 'payment',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Nagad Payment Processed Successfully',
            'payment_id' => $payment->id,
            'transaction_id' => $txId,
            'gateway_tx_id' => $gatewayTxId,
            'amount' => $payment->amount,
            'paid_at' => $payment->paid_at->toIso8601String(),
        ]);
    }

    /**
     * Process Bank Transfer payment via API.
     */
    public function processBank(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:10',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'routing_number' => 'required|string|max:50',
            'deposit_ref' => 'required|string|max:100',
            'nid_number' => 'required|string|min:10|max:20',
            'tax_cert_no' => 'required|string|min:6|max:30',
            'electricity_bill_no' => 'required|string|min:6|max:30',
            'nid_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_cert_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'electricity_bill_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'unit_no' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $nidPath = $request->hasFile('nid_file') ? $request->file('nid_file')->store('verification/nid') : null;
        $taxPath = $request->hasFile('tax_cert_file') ? $request->file('tax_cert_file')->store('verification/tax') : null;
        $elecPath = $request->hasFile('electricity_bill_file') ? $request->file('electricity_bill_file')->store('verification/electricity') : null;

        $txId = 'BANK-' . strtoupper(Str::random(10));
        $gatewayTxId = 'BANK-' . strtoupper($validated['deposit_ref']);

        $unitNo = $validated['unit_no'] ?? 'U-' . random_int(101, 999);
        InvestorBooking::firstOrCreate(
            ['project_id' => $validated['project_id'], 'unit_no' => $unitNo],
            ['user_id' => $user->id, 'investment_amount' => $validated['amount'], 'status' => 'reserved']
        );

        $payment = InvestorPayment::create([
            'user_id' => $user->id,
            'project_id' => $validated['project_id'],
            'amount' => $validated['amount'],
            'payment_method' => 'Bank Transfer (' . $validated['bank_name'] . ')',
            'status' => 'paid',
            'transaction_id' => $txId,
            'payer_reference' => 'Acc: ' . $validated['account_number'] . ' | Routing: ' . $validated['routing_number'],
            'gateway_transaction_id' => $gatewayTxId,
            'paid_at' => now(),
            'nid_number' => $validated['nid_number'],
            'nid_doc_path' => $nidPath,
            'tax_cert_no' => $validated['tax_cert_no'],
            'tax_cert_path' => $taxPath,
            'electricity_bill_no' => $validated['electricity_bill_no'],
            'electricity_bill_path' => $elecPath,
            'verification_status' => 'verified',
        ]);

        $project = Project::find($validated['project_id']);
        InvestorNotification::create([
            'user_id' => $user->id,
            'title' => 'Bank Transfer Submitted & Verified',
            'message' => 'Your Bank transfer payment of ৳' . number_format($validated['amount']) . ' via ' . $validated['bank_name'] . ' (Ref: ' . $validated['deposit_ref'] . ') has been verified and processed.',
            'type' => 'payment',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Bank Payment Processed Successfully',
            'payment_id' => $payment->id,
            'transaction_id' => $txId,
            'gateway_tx_id' => $gatewayTxId,
            'amount' => $payment->amount,
            'paid_at' => $payment->paid_at->toIso8601String(),
        ]);
    }

    /**
     * Check payment status by transaction ID.
     */
    public function verifyStatus(string $transactionId): JsonResponse
    {
        $payment = InvestorPayment::with('project', 'investor')
            ->where('transaction_id', $transactionId)
            ->orWhere('gateway_transaction_id', $transactionId)
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment transaction not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'payment' => [
                'id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'gateway_tx_id' => $payment->gateway_transaction_id,
                'amount' => (float) $payment->amount,
                'payment_method' => $payment->payment_method,
                'status' => $payment->status,
                'verification_status' => $payment->verification_status,
                'nid_number' => $payment->nid_number,
                'tax_cert_no' => $payment->tax_cert_no,
                'electricity_bill_no' => $payment->electricity_bill_no,
                'project' => $payment->project?->title,
                'buyer_name' => $payment->investor?->name,
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ],
        ]);
    }
}
