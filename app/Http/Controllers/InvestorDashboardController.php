<?php

namespace App\Http\Controllers;

use App\Models\InvestorBooking;
use App\Models\InvestorDocument;
use App\Models\InvestorNotification;
use App\Models\InvestorPayment;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Services\ProjectProgressService;

class InvestorDashboardController extends Controller
{
    // ─── MAIN DASHBOARD ────────────────────────────────────────────────────────
    // Loads everything the investor sees on their dashboard page.
    public function index(Request $request, ProjectProgressService $progressService): View
    {
        $user = $request->user();

        // Load all bookings by this investor, with latest payments eagerly loaded
        $bookings = InvestorBooking::with(['project', 'payments' => fn($q) => $q->latest()])
            ->where('user_id', $user->id)
            ->orderBy('created_at')
            ->get();

        // Refresh overdue/forfeiture status for all bookings before rendering
        $this->refreshMissedInstallments($bookings);

        $projectIds = $bookings->pluck('project_id');

        // Load only the projects this investor has bookings in (for progress display)
        $projects = Project::with(['milestones', 'tasks'])->whereIn('id', $projectIds)->get();

        // All active projects shown in the reservation portal (flat selector)
        $availableProjects = Project::where('status', 'active')->orderBy('title')->get();

        // Calculate construction completion % per project using ProjectProgressService
        // (averages all task progress values for each project)
        $projectProgress = $projects->mapWithKeys(fn (Project $project) => [$project->id => $progressService->percentage($project)]);

        // Fetch the single latest construction site update per project for display
        $latestConstructionUpdates = DB::table('daily_progress_reports')
            ->whereIn('project_id', $projectIds)
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->get()
            ->unique('project_id')
            ->keyBy('project_id');

        // Get all unit numbers already reserved in each available project
        // (used to mark cubes as "taken" in the floor plan grid)
        $reservedUnits = InvestorBooking::whereIn('project_id', $availableProjects->pluck('id'))
            ->whereNotIn('status', ['rejected', 'cancelled'])->get()->groupBy('project_id')->map->pluck('unit_no')->map->all();

        // 10 Floors x 4 Flats per floor (A, B, C, D) = 40 units per property
        // Builds the floor plan cube map for each available project
        $buildingUnitMaps = $availableProjects->mapWithKeys(function (Project $project) use ($reservedUnits) {
            $taken = $reservedUnits[$project->id] ?? [];
            $floors = collect(range(10, 1))->map(function ($floor) use ($taken) {
                return [
                    'floor' => $floor,
                    'units' => collect(['A', 'B', 'C', 'D'])->map(function ($flat) use ($floor, $taken) {
                        $unitNo = $floor . $flat;
                        return [
                            'unit_no'   => $unitNo,
                            'name'      => $unitNo,
                            // Mark as booked if unit_no matches any reserved format
                            'is_booked' => in_array($unitNo, $taken, true) || in_array('A-'.$unitNo, $taken, true) || in_array('U-'.$unitNo, $taken, true)
                        ];
                    })->all()
                ];
            });
            return [$project->id => $floors];
        });

        // Total amount already paid (across all bookings) by this investor
        $paid = (float) InvestorPayment::where('user_id', $user->id)->where('status', 'paid')->sum('amount');

        // Count of unread notifications shown in the bell icon badge
        $unreadNotifications = InvestorNotification::where('user_id', $user->id)->where('is_read', false)->count();

        // Per-booking summary: how much paid, how much remaining, and percentage done
        $bookingSummaries = $bookings->keyBy('id')->map(function (InvestorBooking $booking) {
            $paid = (float) InvestorPayment::where(function ($q) use ($booking) {
                $q->where('booking_id', $booking->id)
                  ->orWhere(function ($sub) use ($booking) {
                      // Also count payments that were made before booking_id was linked
                      $sub->where('user_id', $booking->user_id)
                          ->where('project_id', $booking->project_id)
                          ->whereNull('booking_id');
                  });
            })->where('status', 'paid')->sum('amount');
            $price = (float) $booking->investment_amount;
            return (object) ['paid' => $paid, 'remaining' => max(0, $price - $paid), 'percentage' => $price > 0 ? min(100, round(($paid / $price) * 100, 1)) : 0];
        });

        // Build due alerts array for the view (within 7 days or overdue)
        // Shows a colored alert bar at the top of the dashboard for upcoming/missed payments
        $dueAlerts = [];
        foreach ($bookings as $b) {
            // Skip bookings with no due date, or already forfeited/fully paid
            if (!$b->next_payment_date || in_array($b->status, ['forfeited', 'paid_in_full'])) continue;
            $due = Carbon::parse($b->next_payment_date)->startOfDay();
            // Negative = overdue, 0 = due today, positive = days remaining
            $daysLeft = (int) today()->startOfDay()->diffInDays($due, false);
            if ($daysLeft > 7) continue; // Only show alerts for within 7 days
            if ($daysLeft < 0) {
                // Red alert — payment is overdue
                $style = 'background:#fef2f2;border:1.5px solid #fca5a5;color:#991b1b;';
                $icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                $text = 'Payment Overdue — ' . abs($daysLeft) . ' day' . (abs($daysLeft) !== 1 ? 's' : '') . ' past due';
            } elseif ($daysLeft === 0) {
                // Orange alert — due today
                $style = 'background:#fff7ed;border:1.5px solid #fdba74;color:#9a3412;';
                $icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9a3412" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                $text = 'Payment Due Today!';
            } elseif ($daysLeft <= 3) {
                // Orange warning — due in 1-3 days
                $style = 'background:#fff7ed;border:1.5px solid #fdba74;color:#9a3412;';
                $icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9a3412" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                $text = 'Payment due in ' . $daysLeft . ' day' . ($daysLeft !== 1 ? 's' : '');
            } else {
                // Yellow reminder — due in 4-7 days
                $style = 'background:#fefce8;border:1.5px solid #fde047;color:#854d0e;';
                $icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#854d0e" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
                $text = 'Payment due in ' . $daysLeft . ' days';
            }
            $dueAlerts[] = [
                'style'         => $style,
                'icon'          => $icon,
                'text'          => $text,
                'project_title' => $b->project?->title ?? 'Unknown',
                'unit_no'       => $b->unit_no,
                'due_formatted' => $due->format('d M Y'),
                'booking_id'    => $b->id,
                'project_id'    => $b->project_id,
            ];
        }

        return view('investor.dashboard', compact('bookings', 'projects', 'availableProjects', 'buildingUnitMaps', 'paid', 'unreadNotifications', 'bookingSummaries', 'projectProgress', 'latestConstructionUpdates', 'dueAlerts'));
    }

    // ─── SIMPLE ONE-LINER PAGES ─────────────────────────────────────────────────
    // Payment history (ledger) page — shows all payment records for this investor
    public function ledger(Request $request): View { $payments=InvestorPayment::with(['booking.project', 'project'])->where('user_id',$request->user()->id)->oldest()->get(); $total=(float)$payments->sum('amount'); $paid=(float)$payments->where('status','paid')->sum('amount'); return view('investor.ledger',compact('payments','total','paid')); }

    // Legal documents page — shows documents issued to this investor
    public function documents(Request $request): View { $documents=InvestorDocument::where('user_id',$request->user()->id)->latest('issued_at')->get(); return view('investor.documents',compact('documents')); }

    // Notifications inbox page — shows all notifications for this investor
    public function notifications(Request $request): View { $notifications=InvestorNotification::where('user_id',$request->user()->id)->latest()->get(); return view('investor.notifications',compact('notifications')); }

    // Mark a single notification as read (investor can only read their own)
    public function markNotificationRead(Request $request, InvestorNotification $notification): RedirectResponse { abort_unless($notification->user_id === $request->user()->id, 403); $notification->update(['is_read' => true]); return back(); }

    // Mark all unread notifications as read in one click
    public function markAllNotificationsRead(Request $request): RedirectResponse { InvestorNotification::where('user_id', $request->user()->id)->where('is_read', false)->update(['is_read' => true]); return back()->with('success', 'All notifications marked as read.'); }

    // ─── RESERVE A UNIT ─────────────────────────────────────────────────────────
    // Called when investor submits the reservation form (selects a flat cube and confirms)
    public function reserve(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'project_id'         => 'required|exists:projects,id',
            'unit_no'            => 'required|string|max:50',
            'installment_months' => 'required|in:12,24,36',
            'installment_day'    => 'nullable|integer|between:1,31',
            'payment_method'     => 'nullable|string|max:50',
            'payer_reference'    => 'nullable|string|max:100',
        ]);
        $data['unit_no'] = strtoupper(trim($data['unit_no']));
        $project = Project::findOrFail($data['project_id']);

        // Prevent double-booking: check if another investor already holds this unit
        $reservedByAnotherInvestor = InvestorBooking::where('project_id', $data['project_id'])
            ->where('unit_no', $data['unit_no'])
            ->where('user_id', '!=', $request->user()->id)
            ->exists();
        if ($reservedByAnotherInvestor) {
            return back()->withErrors(['unit_no' => 'This unit ('.$data['unit_no'].') is already booked by another investor. Please choose a white available unit box.'])->withInput();
        }

        // Use project total_budget as the flat price (fallback BDT 50,00,000)
        $data['investment_amount'] = (float) ($project->total_budget ?: 5000000);
        $months = (int) $data['installment_months'];

        // 15% of flat price = booking deposit (paid immediately)
        $depositAmount = round($data['investment_amount'] * 0.15, 2);

        // Monthly installment calculates from remaining 85% balance after 15% booking deposit
        $data['monthly_installment_amount'] = round(($data['investment_amount'] * 0.85) / $months, 2);

        // Next installment is auto-calculated exactly 30 days after reservation date
        $reservationDate = now();
        $nextPaymentDate = $reservationDate->copy()->addDays(30);
        $data['next_payment_date'] = $nextPaymentDate->toDateString();
        $data['installment_day']   = $nextPaymentDate->day;

        $paymentMethod  = $data['payment_method'] ?? 'bKash';
        $payerReference = $data['payer_reference'] ?? ($paymentMethod . ' - 15% Initial Booking Fee');
        unset($data['payment_method'], $data['payer_reference']);

        // DB::transaction() — ACID Atomicity: if any one of the 3 queries below fails,
        // ALL of them are automatically rolled back. No partial/corrupt data is saved.
        [$booking, $depositPayment] = DB::transaction(function () use ($data, $project, $depositAmount, $paymentMethod, $payerReference, $request) {

            // Query 1: Create or update the booking record
            // (handles same investor re-reserving the same project — uses updateOrCreate, not create)
            $booking = InvestorBooking::updateOrCreate(
                ['user_id' => $request->user()->id, 'project_id' => $data['project_id']],
                $data + ['status' => 'reserved', 'missed_installments' => 0, 'forfeited_at' => null]
            );

            // Query 2: Record the 15% booking deposit payment as confirmed
            $depositPayment = InvestorPayment::create([
                'booking_id'          => $booking->id,
                'user_id'             => $request->user()->id,
                'project_id'          => $project->id,
                'amount'              => $depositAmount,
                'payment_method'      => $paymentMethod,
                'payment_type'        => 'booking_deposit',
                'status'              => 'paid',
                'transaction_id'      => 'DEP-' . strtoupper(str()->random(10)),
                'payer_reference'     => $payerReference,
                'paid_at'             => now(),
                'verification_status' => 'verified',
            ]);

            // Query 3: Send in-app notification to investor confirming reservation and next due date
            InvestorNotification::create([
                'user_id' => $request->user()->id,
                'title'   => 'Unit reservation confirmed & 15% deposit paid',
                'message' => 'Unit ' . $data['unit_no'] . ' in ' . $project->title . ' reserved successfully. 15% booking fee of BDT ' . number_format($depositAmount) . ' paid. Next installment due on ' . now()->addDays(30)->format('d M Y') . ' (30 days from reservation).',
                'type'    => 'booking'
            ]);

            // Return both models so they are accessible outside the transaction closure
            return [$booking, $depositPayment];
        });

        // Redirect to payment gateway so investor can select MFS channel (bKash, Nagad, etc.)
        return redirect()->route('investor.payment-gateway', [
            'booking_id' => $booking->id,
            'intent'     => 'reservation',
            'payment_id' => $depositPayment->id,
        ])->with('success', 'Unit ' . $data['unit_no'] . ' reservation initiated! 15% Initial Booking Fee is BDT ' . number_format($depositAmount) . '. Please select your payment method (bKash, Nagad, Rocket, or SSLCommerz).');
    }

    // ─── REQUEST UNBLOCK ────────────────────────────────────────────────────────
    // When a booking is blocked (overdue), investor clicks "Request Admin" to ask admin to unblock
    public function requestUnblock(Request $request, InvestorBooking $booking): RedirectResponse
    {
        // Security check: investor can only request unblock for their own bookings
        abort_unless($booking->user_id === $request->user()->id, 403);

        // Log the unblock request in the admin activity log (admin sees this in Command Center)
        \App\Models\ActivityLog::create([
            'user_id'     => $request->user()->id,
            'action'      => 'unblock_request',
            'module'      => 'investor',
            'description' => 'Investor '.$request->user()->name.' requested unblock for Unit '.$booking->unit_no.' ('.$booking->project?->title.').',
            'ip_address'  => $request->ip(),
            'timestamp'   => now(),
        ]);

        // Send in-app notification to investor confirming request was submitted
        InvestorNotification::create([
            'user_id' => $request->user()->id,
            'title'   => 'Unblock Request Submitted',
            'message' => 'Your unblock request for Unit '.$booking->unit_no.' ('.$booking->project?->title.') has been submitted to the Admin Desk. Administration will review and respond shortly.',
            'type'    => 'legal',
        ]);

        // Flash a session flag so the dashboard shows "Request Sent" instead of the button
        session()->flash('unblock_requested_'.$booking->id, true);

        return back()->with('success', 'Unblock request sent to admin. Administration team will review and unblock shortly.');
    }

    // ─── PAYMENT GATEWAY PAGE ───────────────────────────────────────────────────
    // Shows the investor the payment UI (MFS channel selector + OTP form)
    // intent = 'reservation' → paying the 15% deposit for a new booking
    // intent = 'payment'     → paying a regular monthly installment
    public function paymentGateway(Request $request): View|RedirectResponse
    {
        $user      = $request->user();
        $bookingId = $request->query('booking_id');
        $intent    = $request->query('intent', 'payment'); // 'reservation' or 'payment'

        if (!$bookingId) {
            return redirect()->route('investor.dashboard')->withErrors(['error' => 'No booking selected for payment gateway.']);
        }

        // Load booking with its project; only allow investor to see their own booking
        $booking   = InvestorBooking::with('project')->where('user_id', $user->id)->findOrFail($bookingId);
        $project   = $booking->project;

        // Calculate how much is already paid and how much is still owed
        $alreadyPaid = (float) InvestorPayment::where('booking_id', $booking->id)->where('status', 'paid')->sum('amount');
        $remaining   = max(0, (float) $booking->investment_amount - $alreadyPaid);

        $paymentId = $request->query('payment_id');

        if ($intent === 'reservation') {
            // For reservation intent: amount = 15% of flat price (booking deposit)
            $paymentType = 'booking_deposit';
            $amount      = round((float)$booking->investment_amount * 0.15, 2);
        } else {
            // For regular payment intent: determine amount based on payment_type query param
            $paymentType = $request->query('payment_type', 'installment');
            if ($paymentType === 'full_settlement') {
                // Pay off the entire remaining balance at once
                $amount = $remaining;
            } elseif ($request->filled('amount')) {
                // Custom amount provided (capped at remaining balance)
                $amount = min($remaining, (float)$request->query('amount'));
            } else {
                // Default: pay one monthly installment amount
                $monthly = (float) ($booking->monthly_installment_amount ?: round($booking->investment_amount / ($booking->installment_months ?: 24), 2));
                $amount  = min($remaining, $monthly);
            }
        }
        

        // Chronological serial number of this booking (e.g. SL #1 = investor's first ever booking)
        $plotSerial = InvestorBooking::where('user_id', $user->id)
            ->where('created_at', '<=', $booking->created_at)
            ->count();

        return view('investor.payment-gateway', compact(
            'booking', 'project', 'intent', 'paymentType', 'amount', 'alreadyPaid', 'remaining', 'plotSerial', 'paymentId'
        ));
    }

    // ─── PROCESS GATEWAY PAYMENT (OTP FORM SUBMIT) ──────────────────────────────
    // Called when investor submits the OTP + payer account form in the payment gateway page
    public function processGatewayPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'booking_id'     => 'required|exists:investor_bookings,id',
            'intent'         => 'required|in:reservation,payment',
            'payment_method' => 'required|in:bKash,Nagad,Rocket,SSLCommerz,Card',
            'payer_account'  => 'required|string|max:50',
            'otp'            => 'required|string|max:10',
            'pin'            => 'nullable|string|max:10',
            'amount'         => 'required|numeric|min:1',
            'payment_type'   => 'nullable|string|in:booking_deposit,installment,full_settlement,custom',
            'payment_id'     => 'nullable|exists:investor_payments,id',
        ]);

        $user    = $request->user();
        // Re-verify booking belongs to this investor before processing payment
        $booking = InvestorBooking::with('project')->where('user_id', $user->id)
            ->whereKey($validated['booking_id'])
            ->firstOrFail();

        // Normalize 'Card' to 'SSLCommerz' and generate a unique transaction prefix
        $method = $validated['payment_method'] === 'Card' ? 'SSLCommerz' : $validated['payment_method'];
        $prefix = match($method) {
            'bKash'  => 'BKASH-',
            'Nagad'  => 'NAGAD-',
            'Rocket' => 'RCKT-',
            default  => 'SSL-',
        };
        // Generate unique system transaction ID and a fake gateway reference number
        $txId        = $prefix . strtoupper(str()->random(10));
        $gatewayTxId = 'TRX' . random_int(1000000000, 9999999999);
        $amount      = (float) $validated['amount'];

        if ($validated['intent'] === 'reservation') {
            // ── RESERVATION DEPOSIT PATH ──
            // Try to find the existing placeholder booking_deposit payment row
            $payment = null;
            if (!empty($validated['payment_id'])) {
                $payment = InvestorPayment::where('id', $validated['payment_id'])
                    ->where('booking_id', $booking->id)
                    ->first();
            }
            if (!$payment) {
                // Fallback: find by payment type if payment_id was not provided
                $payment = InvestorPayment::where('booking_id', $booking->id)
                    ->where('payment_type', 'booking_deposit')
                    ->first();
            }

            if ($payment) {
                // Update the existing deposit row with the chosen MFS channel details
                $payment->update([
                    'amount'                  => $amount,
                    'payment_method'          => $method,
                    'payer_reference'         => $validated['payer_account'],
                    'transaction_id'          => $txId,
                    'gateway_transaction_id'  => $gatewayTxId,
                    'status'                  => 'paid',
                    'verification_status'     => 'verified',
                    'paid_at'                 => now(),
                ]);
            } else {
                // No existing row found — create a new deposit payment record
                $payment = InvestorPayment::create([
                    'booking_id'             => $booking->id,
                    'user_id'                => $user->id,
                    'project_id'             => $booking->project_id,
                    'amount'                 => $amount,
                    'payment_method'         => $method,
                    'payment_type'           => 'booking_deposit',
                    'status'                 => 'paid',
                    'verification_status'    => 'verified',
                    'transaction_id'         => $txId,
                    'gateway_transaction_id' => $gatewayTxId,
                    'payer_reference'        => $validated['payer_account'],
                    'paid_at'                => now(),
                ]);
            }

            // After deposit confirmed, reset booking status and set next installment 30 days out
            $booking->update([
                'status'              => 'reserved',
                'next_payment_date'   => now()->addDays(30)->toDateString(),
                'missed_installments' => 0,
            ]);

            // Notify investor that their 15% deposit was received
            InvestorNotification::create([
                'user_id' => $user->id,
                'title'   => '15% Booking Deposit Confirmed via ' . $method,
                'message' => 'Deposit of BDT ' . number_format($amount) . ' for Unit ' . $booking->unit_no . ' (' . $booking->project?->title . ') was successfully completed. Next installment due on ' . now()->addDays(30)->format('d M Y') . ' (30 days from reservation).',
                'type'    => 'payment',
            ]);
        } else {
            // ── INSTALLMENT / FULL SETTLEMENT PATH ──
            $paymentType = $validated['payment_type'] ?? 'installment';

            // Record the installment payment row
            $payment = InvestorPayment::create([
                'booking_id'             => $booking->id,
                'user_id'                => $user->id,
                'project_id'             => $booking->project_id,
                'amount'                 => $amount,
                'payment_method'         => $method,
                'payment_type'           => $paymentType,
                'status'                 => 'paid',
                'verification_status'    => 'verified',
                'transaction_id'         => $txId,
                'gateway_transaction_id' => $gatewayTxId,
                'payer_reference'        => $validated['payer_account'],
                'paid_at'                => now(),
            ]);

            // Recalculate how much is still owed after this payment
            $totalPaid = (float) InvestorPayment::where('booking_id', $booking->id)->where('status', 'paid')->sum('amount');
            $remaining = max(0, (float)$booking->investment_amount - $totalPaid);

            if ($remaining <= 0) {
                // Flat is now fully paid — mark booking as paid_in_full, clear due date
                $booking->update([
                    'status'              => 'paid_in_full',
                    'next_payment_date'   => null,
                    'missed_installments' => 0,
                ]);
            } else {
                // Still has balance — reset status to reserved and push next due date 30 days out
                $booking->update([
                    'status'              => 'reserved',
                    'next_payment_date'   => now()->addDays(30)->toDateString(),
                    'missed_installments' => 0,
                ]);
            }

            // Notify investor that this installment payment was successful
            InvestorNotification::create([
                'user_id' => $user->id,
                'title'   => 'Payment Completed via ' . $method,
                'message' => 'Payment of BDT ' . number_format($amount) . ' for Unit ' . $booking->unit_no . ' (' . $booking->project?->title . ') completed successfully with TxID: ' . $txId . '.',
                'type'    => 'payment',
            ]);
        }

        // Flash receipt details to session — shown as a payment success modal/banner on dashboard
        session()->flash('payment_success_details', [
            'transaction_id'         => $txId,
            'gateway_transaction_id' => $gatewayTxId,
            'project_title'          => $booking->project?->title ?? 'Intern Estate Project',
            'unit_no'                => 'Unit ' . $booking->unit_no,
            'amount'                 => $amount,
            'payment_method'         => $method,
            'payment_type'           => ucfirst(str_replace('_', ' ', $payment->payment_type ?? 'payment')),
            'account_number'         => $validated['payer_account'],
            'date'                   => now()->format('d M Y, h:i A'),
        ]);

        return redirect()->route('investor.dashboard')
            ->with('success', 'Payment of BDT ' . number_format($amount) . ' completed successfully via ' . $method . '! (TxID: ' . $txId . ')');
    }

    // ─── DIRECT PAY (non-gateway path) ─────────────────────────────────────────
    // Legacy direct payment form (without MFS gateway UI)
    // If gateway_checkout is true or no payment_method specified, redirects to gateway page instead
    public function pay(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'booking_id'         => 'nullable|exists:investor_bookings,id',
            'project_id'         => 'required_without:booking_id|exists:projects,id',
            'amount'             => 'nullable|numeric|min:1',
            'installment_months' => 'nullable|in:12,24,36',
            'payment_type'       => 'nullable|in:installment,full_settlement,custom',
            'payment_method'     => 'nullable|in:bKash,Nagad,Rocket,Bank Transfer,SSLCommerz',
            'payer_reference'    => 'nullable|string|max:100',
            'gateway_checkout'   => 'nullable|boolean',
        ]);

        // Find the investor's booking (by booking_id if given, otherwise by project_id)
        $booking = InvestorBooking::where('user_id', $request->user()->id)
            ->when($data['booking_id'] ?? null, fn ($query, $id) => $query->whereKey($id), fn ($query) => $query->where('project_id', $data['project_id']))
            ->firstOrFail();

        // Refresh overdue status before checking if booking is still active
        $this->refreshMissedInstallments(collect([$booking]));
        if ($booking->status === 'forfeited') {
            return back()->withErrors([
                'booking_id' => 'This booking was forfeited after three missed installment due dates. Please contact the administrator to review the booking.',
            ]);
        }

        // Calculate remaining balance for this booking
        $alreadyPaid = (float) InvestorPayment::where('user_id', $booking->user_id)->where('project_id', $booking->project_id)->where('status', 'paid')->sum('amount');
        $remaining   = max(0, (float) $booking->investment_amount - $alreadyPaid);
        if ($remaining <= 0) {
            return back()->withErrors([
                'booking_id' => 'This booking has already been paid in full.',
            ]);
        }

        // If investor changed installment plan (12/24/36 months), update it now
        if (!empty($data['installment_months']) && in_array((int)$data['installment_months'], [12, 24, 36])) {
            $months = (int)$data['installment_months'];
            $booking->update([
                'installment_months'         => $months,
                'monthly_installment_amount' => round($booking->investment_amount / $months, 2)
            ]);
        }

        $paymentType        = $data['payment_type'] ?? 'installment';
        $monthlyInstallment = (float) ($booking->monthly_installment_amount ?: round($booking->investment_amount / ($booking->installment_months ?: 12), 2));

        // Determine how much to charge for this payment
        if ($paymentType === 'full_settlement') {
            // Pay entire remaining balance
            $amount = $remaining;
        } elseif ($paymentType === 'custom' && !empty($data['amount'])) {
            // Custom amount: must be at least one installment, capped at remaining balance
            $customVal  = (float) $data['amount'];
            $minAmount  = min($monthlyInstallment, $remaining);
            $amount     = max($minAmount, min($customVal, $remaining));
        } else {
            // Default: pay exactly one monthly installment (capped at remaining)
            $amount = min($monthlyInstallment, $remaining);
        }

        // If gateway checkout requested or payment method is not specified, redirect to the dedicated payment gateway
        if ($request->boolean('gateway_checkout') || empty($data['payment_method'])) {
            return redirect()->route('investor.payment-gateway', [
                'booking_id'   => $booking->id,
                'intent'       => 'payment',
                'payment_type' => $paymentType,
                'amount'       => $amount,
            ]);
        }

        unset($data['project_id'], $data['amount'], $data['installment_months'], $data['gateway_checkout']);

        // Create a pending payment record (admin must verify before it's counted as paid)
        $payment = InvestorPayment::create($data + [
            'booking_id'     => $booking->id,
            'payment_type'   => $paymentType,
            'user_id'        => $request->user()->id,
            'project_id'     => $booking->project_id,
            'amount'         => $amount,
            'status'         => 'pending',
            'transaction_id' => 'IE-' . strtoupper(str()->random(10))
        ]);

        // Notify investor that their payment is pending admin verification
        InvestorNotification::create([
            'user_id' => $request->user()->id,
            'title'   => 'Payment awaiting verification',
            'message' => 'Your payment request for BDT ' . number_format($amount) . ' (' . $booking->project->title . ' - Unit ' . $booking->unit_no . ') has been submitted.',
            'type'    => 'payment'
        ]);

        return redirect()->route('investor.dashboard')
            ->with('success', 'Payment submitted successfully! Waiting to be verified by admin.')
            ->with('payment_success_details', [
                'transaction_id' => $payment->transaction_id,
                'amount'         => $amount,
                'project_title'  => $booking->project->title,
                'unit_no'        => $booking->unit_no,
                'payment_method' => $payment->payment_method,
                'payment_type'   => $paymentType === 'full_settlement' ? 'Full Balance Settlement' : ($booking->installment_months ? $booking->installment_months . ' Months Installment' : 'Installment')
            ]);
    }

    // ─── INVOICE VIEW ───────────────────────────────────────────────────────────
    // Shows the printable invoice for a single payment (investor can only view their own)
    public function invoice(Request $request, InvestorPayment $payment): View { abort_unless($payment->user_id === $request->user()->id, 403); return view('investor.invoice', compact('payment')); }

    // ─── PRIVATE HELPERS ────────────────────────────────────────────────────────
    private function bookings(int $userId) { return InvestorBooking::where('user_id',$userId)->get(); }

    // Checks every booking and updates missed_installments count and status
    // Called at the start of index() and pay() to keep data fresh
    private function refreshMissedInstallments($bookings): void
    {
        foreach ($bookings as $booking) {
            // Skip bookings with no due date or already permanently forfeited
            if (!$booking->next_payment_date || $booking->status === 'forfeited') continue;
            $due        = Carbon::parse($booking->next_payment_date)->startOfDay();
            $todayStart = today()->startOfDay();

            // Due date is in the future — no missed installment. Reset if previously set.
            if (!$due->lt($todayStart)) {
                if ($booking->missed_installments > 0) {
                    $booking->update(['missed_installments' => 0, 'status' => 'reserved']);
                    $booking->missed_installments = 0;
                }
                continue;
            }

            // Due date is in the past — count how many months overdue (minimum 1)
            $monthsOverdue = (int) $due->diffInMonths($todayStart);
            // Add 1 so that the first missed month counts from day 1 of being overdue
            $missed = min(3, max((int)$booking->missed_installments, $monthsOverdue + 1));

            // After 3 missed installments (3 months) the booking is forfeited (flat taken back)
            $newStatus = $missed >= 3 ? 'forfeited' : $booking->status;
            $booking->update([
                'missed_installments' => $missed,
                'status'              => $newStatus,
                'forfeited_at'        => $missed >= 3 ? now() : $booking->forfeited_at,
            ]);
        }
    }
}
