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
use Illuminate\View\View;

class InvestorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $bookings = InvestorBooking::with(['project', 'payments' => fn($q) => $q->latest()])
            ->where('user_id', $user->id)
            ->get();
        $this->refreshMissedInstallments($bookings);
        $projectIds = $bookings->pluck('project_id');
        $projects = Project::with('milestones')->whereIn('id', $projectIds)->get();
        $availableProjects = Project::whereIn('status', ['active', 'planned'])->orderBy('title')->get();
        
        $reservedUnits = InvestorBooking::whereIn('project_id', $availableProjects->pluck('id'))
            ->whereNotIn('status', ['rejected', 'cancelled'])->get()->groupBy('project_id')->map->pluck('unit_no')->map->all();

        // 10 Floors x 4 Flats per floor (A, B, C, D) = 40 units per property
        $buildingUnitMaps = $availableProjects->mapWithKeys(function (Project $project) use ($reservedUnits) {
            $taken = $reservedUnits[$project->id] ?? [];
            $floors = collect(range(10, 1))->map(function ($floor) use ($taken) {
                return [
                    'floor' => $floor,
                    'units' => collect(['A', 'B', 'C', 'D'])->map(function ($flat) use ($floor, $taken) {
                        $unitNo = $floor . $flat;
                        return [
                            'unit_no' => $unitNo,
                            'name' => $unitNo,
                            'is_booked' => in_array($unitNo, $taken, true) || in_array('A-'.$unitNo, $taken, true) || in_array('U-'.$unitNo, $taken, true)
                        ];
                    })->all()
                ];
            });
            return [$project->id => $floors];
        });

        $paid = (float) InvestorPayment::where('user_id', $user->id)->where('status', 'paid')->sum('amount');
        $unreadNotifications = InvestorNotification::where('user_id', $user->id)->where('is_read', false)->count();
        $bookingSummaries = $bookings->keyBy('project_id')->map(function (InvestorBooking $booking) {
            $paid = (float) InvestorPayment::where('user_id', $booking->user_id)->where('project_id', $booking->project_id)->where('status', 'paid')->sum('amount');
            $price = (float) $booking->investment_amount;
            return (object) ['paid' => $paid, 'remaining' => max(0, $price - $paid), 'percentage' => $price > 0 ? min(100, round(($paid / $price) * 100, 1)) : 0];
        });

        return view('investor.dashboard', compact('bookings', 'projects', 'availableProjects', 'buildingUnitMaps', 'paid', 'unreadNotifications', 'bookingSummaries'));
    }

    public function ledger(Request $request): View { $payments=InvestorPayment::where('user_id',$request->user()->id)->latest()->get(); $total=(float)$payments->sum('amount'); $paid=(float)$payments->where('status','paid')->sum('amount'); return view('investor.ledger',compact('payments','total','paid')); }
    public function documents(Request $request): View { $documents=InvestorDocument::where('user_id',$request->user()->id)->latest('issued_at')->get(); return view('investor.documents',compact('documents')); }
    public function notifications(Request $request): View { $notifications=InvestorNotification::where('user_id',$request->user()->id)->latest()->get(); return view('investor.notifications',compact('notifications')); }
    public function markNotificationRead(Request $request, InvestorNotification $notification): RedirectResponse { abort_unless($notification->user_id === $request->user()->id, 403); $notification->update(['is_read' => true]); return back(); }
    public function markAllNotificationsRead(Request $request): RedirectResponse { InvestorNotification::where('user_id', $request->user()->id)->where('is_read', false)->update(['is_read' => true]); return back()->with('success', 'All notifications marked as read.'); }

    public function reserve(Request $request): RedirectResponse
    {
        $data = $request->validate(['project_id'=>'required|exists:projects,id', 'unit_no'=>'required|string|max:50', 'installment_months'=>'required|in:12,24,36', 'installment_day'=>'required|integer|between:1,28']);
        $data['unit_no'] = strtoupper(trim($data['unit_no']));
        $project = Project::findOrFail($data['project_id']);
        
        $reservedByAnotherInvestor = InvestorBooking::where('project_id', $data['project_id'])->where('unit_no', $data['unit_no'])->where('user_id', '!=', $request->user()->id)->exists();
        if ($reservedByAnotherInvestor) return back()->withErrors(['unit_no' => 'This unit ('.$data['unit_no'].') is already booked by another investor. Please choose a white available unit box.'])->withInput();

        $data['investment_amount'] = (float) ($project->total_budget ?: 5000000);
        $months = (int) $data['installment_months'];
        $day = (int) $data['installment_day'];
        
        // Monthly installment calculates from remaining balance after 15% booking deposit
        $data['monthly_installment_amount'] = round(($data['investment_amount'] * 0.85) / $months, 2);
        $data['next_payment_date'] = now()->day(min($day, now()->daysInMonth))->toDateString();

        InvestorBooking::updateOrCreate(
            ['user_id'=>$request->user()->id, 'project_id'=>$data['project_id']],
            $data + ['status'=>'reserved', 'missed_installments'=>0, 'forfeited_at'=>null]
        );

        InvestorNotification::create([
            'user_id' => $request->user()->id,
            'title' => 'Unit reservation confirmed',
            'message' => 'Unit ' . $data['unit_no'] . ' in ' . $project->title . ' reserved successfully. Initial 15% booking fee: ৳' . number_format($data['investment_amount'] * 0.15) . '.',
            'type' => 'booking'
        ]);

        return back()->with('success', 'Unit ' . $data['unit_no'] . ' reserved successfully! 15% Initial Booking Fee is set for BDT ' . number_format($data['investment_amount'] * 0.15) . '.');
    }

    public function pay(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'booking_id' => 'nullable|exists:investor_bookings,id',
            'project_id' => 'required_without:booking_id|exists:projects,id',
            'amount' => 'nullable|numeric|min:1',
            'installment_months' => 'nullable|in:12,24,36',
            'payment_type' => 'nullable|in:installment,full_settlement,custom',
            'payment_method' => 'required|in:SSLCommerz,bKash,Nagad,Bank Transfer',
            'payer_reference' => 'nullable|string|max:100'
        ]);

        $booking = InvestorBooking::where('user_id', $request->user()->id)
            ->when($data['booking_id'] ?? null, fn ($query, $id) => $query->whereKey($id), fn ($query) => $query->where('project_id', $data['project_id']))
            ->firstOrFail();

        $this->refreshMissedInstallments(collect([$booking]));
        abort_if($booking->status === 'forfeited', 422, 'This booking was forfeited after three missed installment due dates.');
        
        $alreadyPaid = (float) InvestorPayment::where('user_id', $booking->user_id)->where('project_id', $booking->project_id)->where('status', 'paid')->sum('amount');
        $remaining = max(0, (float) $booking->investment_amount - $alreadyPaid);
        abort_if($remaining <= 0, 422, 'This booking has already been paid in full.');

        if (!empty($data['installment_months']) && in_array((int)$data['installment_months'], [12, 24, 36])) {
            $months = (int)$data['installment_months'];
            $booking->update([
                'installment_months' => $months,
                'monthly_installment_amount' => round($booking->investment_amount / $months, 2)
            ]);
        }

        $paymentType = $data['payment_type'] ?? 'installment';
        if ($paymentType === 'full_settlement') {
            $amount = $remaining;
        } elseif (!empty($data['amount'])) {
            $amount = min((float)$data['amount'], $remaining);
        } else {
            $amount = min((float) ($booking->monthly_installment_amount ?: ($remaining / ($booking->installment_months ?: 12))), $remaining);
        }

        unset($data['project_id'], $data['amount'], $data['installment_months']);
        
        $payment = InvestorPayment::create($data + [
            'booking_id' => $booking->id,
            'payment_type' => $paymentType,
            'user_id' => $request->user()->id,
            'project_id' => $booking->project_id,
            'amount' => $amount,
            'status' => 'pending',
            'transaction_id' => 'IE-' . strtoupper(str()->random(10))
        ]);

        InvestorNotification::create([
            'user_id' => $request->user()->id,
            'title' => 'Payment awaiting verification',
            'message' => 'Your payment request for ৳' . number_format($amount) . ' (' . $booking->project->title . ' - Unit ' . $booking->unit_no . ') has been submitted.',
            'type' => 'payment'
        ]);

        return redirect()->route('investor.dashboard')
            ->with('success', 'Payment submitted successfully! Waiting to be verified by admin.')
            ->with('payment_success_details', [
                'transaction_id' => $payment->transaction_id,
                'amount' => $amount,
                'project_title' => $booking->project->title,
                'unit_no' => $booking->unit_no,
                'payment_method' => $payment->payment_method,
                'payment_type' => $paymentType === 'full_settlement' ? 'Full Balance Settlement' : ($booking->installment_months ? $booking->installment_months . ' Months Installment' : 'Installment')
            ]);
    }

    public function invoice(Request $request, InvestorPayment $payment): View { abort_unless($payment->user_id === $request->user()->id, 403); return view('investor.invoice', compact('payment')); }
    private function bookings(int $userId) { return InvestorBooking::where('user_id',$userId)->get(); }

    private function refreshMissedInstallments($bookings): void
    {
        foreach ($bookings as $booking) {
            if (!$booking->next_payment_date || $booking->status === 'forfeited') continue;
            $due = Carbon::parse($booking->next_payment_date);
            if (!$due->lt(today()->startOfDay())) continue;
            $missed = min(3, $booking->missed_installments + $due->diffInMonths(now()) + 1);
            $booking->update(['missed_installments' => $missed, 'status' => $missed >= 3 ? 'forfeited' : $booking->status, 'forfeited_at' => $missed >= 3 ? now() : null]);
        }
    }
}
