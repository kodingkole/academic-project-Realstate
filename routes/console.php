<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\InvestorBooking;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('installments:check-overdue', function () {
    $updated = 0;
    InvestorBooking::whereNotNull('next_payment_date')->where('status', '!=', 'forfeited')->each(function (InvestorBooking $booking) use (&$updated) {
        $due = Carbon::parse($booking->next_payment_date);
        if (!$due->lt(today()->startOfDay())) return;
        $missed = min(3, $booking->missed_installments + $due->diffInMonths(now()) + 1);
        $booking->update(['missed_installments' => $missed, 'status' => $missed >= 3 ? 'forfeited' : $booking->status, 'forfeited_at' => $missed >= 3 ? now() : null]);
        $updated++;
    });
    $this->info("Updated {$updated} overdue installment schedule(s).");
})->purpose('Record missed installment due dates and forfeit bookings after three misses');

Schedule::command('installments:check-overdue')->dailyAt('00:10');
