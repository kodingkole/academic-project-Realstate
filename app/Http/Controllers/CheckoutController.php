<?php

namespace App\Http\Controllers;

use App\Models\InvestorBooking;
use App\Models\InvestorNotification;
use App\Models\InvestorPayment;
use App\Models\LandSubmission;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Display the full page checkout gateway.
     */
    public function showCheckout(Request $request, string $type, int $id): View|RedirectResponse
    {
        $item = null;
        $title = 'Property Checkout';
        $location = 'Dhaka, Bangladesh';
        $price = 6000000;

        $availableProjects = Project::where('status', 'active')->get();
        if ($availableProjects->isEmpty()) {
            $availableProjects = Project::all();
        }

        if ($type === 'project') {
            $item = Project::find($id) ?? $availableProjects->first();
            if ($item) {
                $title = $item->title;
                $location = $item->location;
                $price = $item->total_budget > 0 ? (int) ($item->total_budget / 20) : ($item->estimated_cost ?? 6000000);
            }
        } elseif ($type === 'land') {
            $item = LandSubmission::find($id) ?? LandSubmission::first();
            if ($item) {
                $title = $item->title ?? ($item->district . ' JV Land');
                $location = $item->location;
                $price = $item->asking_price > 0 ? $item->asking_price : ($item->katha_size * 1500000);
            }
        }

        if (!$item && $availableProjects->isNotEmpty()) {
            $item = $availableProjects->first();
            $title = $item->title;
            $location = $item->location;
            $price = 6000000;
        }

        return view('checkout.index', [
            'type' => $type,
            'id' => $id,
            'item' => $item,
            'title' => $title,
            'location' => $location,
            'price' => $price,
            'availableProjects' => $availableProjects,
            'user' => $request->user(),
        ]);
    }

    /**
     * Process full page checkout purchase with Document Verification, Installment plan & Pending Admin Review.
     */
    public function processCheckout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'checkout_type' => 'required|in:project,land',
            'item_id' => 'required|integer',
            'unit_no' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:10',
            'payment_plan' => 'required|in:full,1_year,2_year,3_year',
            'payment_method' => 'required|in:bKash,Nagad,Bank,CreditCard',
            'payer_reference' => 'required|string|max:100',
            'pin_or_ref' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'routing_number' => 'nullable|string|max:50',
            // Credit card fields for installment
            'card_bank' => 'nullable|string|max:100',
            'card_number' => 'nullable|string|max:30',
            'card_holder' => 'nullable|string|max:100',
            'card_expiry' => 'nullable|string|max:10',
            // Verification documents
            'nid_number' => 'required|string|min:10|max:20',
            'tax_cert_no' => 'required|string|min:6|max:30',
            'electricity_bill_no' => 'required|string|min:6|max:30',
            'nid_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_cert_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'electricity_bill_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $user = $request->user();

        // Handle document file uploads if provided
        $nidPath = $request->hasFile('nid_file') ? $request->file('nid_file')->store('verification/nid') : 'documents/nid_verified.pdf';
        $taxPath = $request->hasFile('tax_cert_file') ? $request->file('tax_cert_file')->store('verification/tax') : 'documents/tax_verified.pdf';
        $elecPath = $request->hasFile('electricity_bill_file') ? $request->file('electricity_bill_file')->store('verification/electricity') : 'documents/electricity_verified.pdf';

        $method = $validated['payment_method'];
        $plan = $validated['payment_plan'];
        $planLabel = match($plan) {
            '1_year' => '1 Year EMI (12 Months)',
            '2_year' => '2 Years EMI (24 Months)',
            '3_year' => '3 Years EMI (36 Months)',
            default => 'Full One-Time Payment',
        };

        $prefix = strtoupper($method);
        $txId = $prefix . '-' . strtoupper(Str::random(10));
        $gatewayTxId = ($method === 'bKash' ? 'TRX' : ($method === 'Nagad' ? 'NGD' : 'REQ-')) . random_int(1000000000, 9999999999);

        // Find project
        $projectId = $validated['item_id'];
        $project = Project::find($projectId) ?? Project::first();
        if ($project) {
            $projectId = $project->id;
        }

        $unitNo = $validated['unit_no'] ?? 'U-' . random_int(101, 999);
        InvestorBooking::firstOrCreate(
            ['project_id' => $projectId, 'unit_no' => $unitNo],
            ['user_id' => $user->id, 'investment_amount' => $validated['amount'], 'status' => 'reserved']
        );

        $methodLabel = $method;
        if ($method === 'Bank' && !empty($validated['bank_name'])) {
            $methodLabel = 'Bank Transfer (' . $validated['bank_name'] . ')';
        } elseif ($plan !== 'full' && !empty($validated['card_bank'])) {
            $methodLabel = 'EMI Credit Card (' . $validated['card_bank'] . ' - ' . $planLabel . ')';
        }

        // Create Payment as PENDING for Admin Review
        $payment = InvestorPayment::create([
            'user_id' => $user->id,
            'project_id' => $projectId,
            'amount' => $validated['amount'],
            'payment_method' => $methodLabel,
            'status' => 'pending', // Pending Admin Review
            'transaction_id' => $txId,
            'payer_reference' => $validated['payer_reference'] . ' [' . $planLabel . ']',
            'gateway_transaction_id' => $gatewayTxId,
            'paid_at' => null, // Will be set upon admin approval
            'nid_number' => $validated['nid_number'],
            'nid_doc_path' => $nidPath,
            'tax_cert_no' => $validated['tax_cert_no'],
            'tax_cert_path' => $taxPath,
            'electricity_bill_no' => $validated['electricity_bill_no'],
            'electricity_bill_path' => $elecPath,
            'verification_status' => 'under_review',
        ]);

        // Push real-time notification to investor
        InvestorNotification::create([
            'user_id' => $user->id,
            'title' => 'Payment Request Submitted',
            'message' => 'Your payment request of ৳' . number_format($validated['amount']) . ' (' . $planLabel . ') for ' . ($project?->title ?? 'Project') . ' has been received. Status: Under Admin Review.',
            'type' => 'payment',
        ]);

        return redirect()->route('investor.invoice', ['payment' => $payment->id])
            ->with('success', 'Payment request submitted successfully! Your documents and transaction are currently under admin review.');
    }
}

