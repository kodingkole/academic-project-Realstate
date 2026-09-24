<?php

namespace Tests\Feature;

use App\Models\InvestorBooking;
use App\Models\InvestorPayment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_redirects_to_dedicated_payment_gateway(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $project = Project::create([
            'title' => 'Gateway Luxury Tower',
            'location' => 'Gulshan 2, Dhaka',
            'status' => 'active',
            'total_budget' => 60000000,
        ]);

        $response = $this->actingAs($investor)->post(route('investor.reserve'), [
            'project_id' => $project->id,
            'unit_no' => '5B',
            'installment_months' => 24,
            'payment_method' => 'bKash',
        ]);

        $booking = InvestorBooking::where('user_id', $investor->id)->firstOrFail();

        $response->assertRedirect(route('investor.payment-gateway', [
            'booking_id' => $booking->id,
            'intent' => 'reservation',
            'payment_id' => $booking->payments()->first()?->id,
        ]));
    }

    public function test_payment_gateway_view_renders_successfully(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $project = Project::create([
            'title' => 'Gateway Luxury Tower',
            'location' => 'Gulshan 2, Dhaka',
            'status' => 'active',
            'total_budget' => 60000000,
        ]);

        $booking = InvestorBooking::create([
            'user_id' => $investor->id,
            'project_id' => $project->id,
            'unit_no' => '5B',
            'investment_amount' => 60000000,
            'monthly_installment_amount' => 2125000,
            'installment_months' => 24,
            'status' => 'reserved',
            'next_payment_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($investor)->get(route('investor.payment-gateway', [
            'booking_id' => $booking->id,
            'intent' => 'reservation',
        ]));

        $response->assertOk();
        $response->assertSee('SECURE CHECKOUT');
        $response->assertSee('Gateway Luxury Tower');
        $response->assertSee('bKash');
        $response->assertSee('Nagad');
        $response->assertSee('Rocket');
        $response->assertSee('SSLCOMMERZ');
        $response->assertSee('BDT');
    }

    public function test_process_gateway_payment_completes_flow_with_phone_otp_and_pin(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $project = Project::create([
            'title' => 'Gateway Luxury Tower',
            'location' => 'Gulshan 2, Dhaka',
            'status' => 'active',
            'total_budget' => 60000000,
        ]);

        $booking = InvestorBooking::create([
            'user_id' => $investor->id,
            'project_id' => $project->id,
            'unit_no' => '5B',
            'investment_amount' => 60000000,
            'monthly_installment_amount' => 2125000,
            'installment_months' => 24,
            'status' => 'reserved',
            'next_payment_date' => now()->addDays(30),
        ]);

        // Process a monthly installment payment via bKash through the gateway
        $response = $this->actingAs($investor)->post(route('investor.payment-gateway.process'), [
            'booking_id' => $booking->id,
            'intent' => 'payment',
            'payment_method' => 'bKash',
            'payer_account' => '01712345678',
            'otp' => '123456',
            'pin' => '12345',
            'amount' => 2125000,
            'payment_type' => 'installment',
        ]);

        $response->assertRedirect(route('investor.dashboard'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('payment_success_details');

        $payment = InvestorPayment::where('booking_id', $booking->id)
            ->where('payment_method', 'bKash')
            ->where('status', 'paid')
            ->first();

        $this->assertNotNull($payment);
        $this->assertEquals(2125000.00, (float)$payment->amount);
        $this->assertSame('01712345678', $payment->payer_reference);
        $this->assertStringStartsWith('BKASH-', $payment->transaction_id);

        // Next payment date must be refreshed
        $this->assertSame(now()->addDays(30)->toDateString(), \Carbon\Carbon::parse($booking->fresh()->next_payment_date)->toDateString());
    }

    public function test_make_payment_modal_with_gateway_checkout_redirects_to_gateway(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $project = Project::create([
            'title' => 'Gateway Luxury Tower',
            'location' => 'Gulshan 2, Dhaka',
            'status' => 'active',
            'total_budget' => 60000000,
        ]);

        $booking = InvestorBooking::create([
            'user_id' => $investor->id,
            'project_id' => $project->id,
            'unit_no' => '5B',
            'investment_amount' => 60000000,
            'monthly_installment_amount' => 2125000,
            'installment_months' => 24,
            'status' => 'reserved',
            'next_payment_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($investor)->post(route('investor.pay'), [
            'booking_id' => $booking->id,
            'payment_type' => 'installment',
            'gateway_checkout' => 1,
        ]);

        $response->assertRedirect(route('investor.payment-gateway', [
            'booking_id' => $booking->id,
            'intent' => 'payment',
            'payment_type' => 'installment',
            'amount' => 2125000,
        ]));
    }
}
