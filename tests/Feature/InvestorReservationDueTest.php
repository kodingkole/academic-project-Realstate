<?php

namespace Tests\Feature;

use App\Models\InvestorBooking;
use App\Models\InvestorPayment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorReservationDueTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_auto_calculates_30_day_due_date_and_records_15_percent_deposit(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $project = Project::create([
            'title' => 'Test Green Residence',
            'location' => 'Uttara, Dhaka',
            'status' => 'active',
            'total_budget' => 120000000,
        ]);

        $response = $this->actingAs($investor)->post(route('investor.reserve'), [
            'project_id' => $project->id,
            'unit_no' => '7C',
            'installment_months' => 24,
            'payment_method' => 'bKash',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $booking = InvestorBooking::where('user_id', $investor->id)->where('project_id', $project->id)->first();
        $this->assertNotNull($booking);

        // Next payment date must be exactly 30 days from reservation
        $expectedDue = now()->addDays(30)->toDateString();
        $this->assertSame($expectedDue, \Carbon\Carbon::parse($booking->next_payment_date)->toDateString());

        // 15% booking deposit must be recorded as paid
        $depositPayment = InvestorPayment::where('booking_id', $booking->id)
            ->where('payment_type', 'booking_deposit')
            ->first();
        $this->assertNotNull($depositPayment);
        $this->assertSame('paid', $depositPayment->status);
        $this->assertEquals(18000000.00, (float)$depositPayment->amount);

        // Check Dashboard view calculations
        $dashResponse = $this->actingAs($investor)->get(route('investor.dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('Paid:</strong> BDT 18,000,000 (15%)', false);
        $dashResponse->assertSee('Remaining:</strong> BDT 102,000,000', false);
        $dashResponse->assertSee('Next due:</strong> ' . now()->addDays(30)->format('d M Y'), false);
    }
}
