<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InvestorBooking;
use App\Models\InvestorPayment;
use App\Models\InvestorNotification;
use App\Models\Project;
use App\Models\LandSubmission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_investor_can_login_and_open_dashboard(): void
    {
        $investor = User::factory()->create([
            'role' => 'investor',
            'email' => 'investor@example.com',
            'password' => 'investor123',
        ]);

        Mail::fake();
        $response = $this->post(route('investor.login.attempt'), [
            'email' => $investor->email,
            'password' => 'investor123',
        ]);

        $response->assertRedirect(route('investor.dashboard'));
        $this->get(route('investor.dashboard'))->assertOk();
        $this->actingAs($investor)->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_cannot_open_investor_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('investor.dashboard'))->assertRedirect(route('investor.login'));
    }

    public function test_payment_is_verified_by_admin_and_visible_to_the_investor(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create(['title' => 'Payment Test Project', 'location' => 'Dhaka', 'status' => 'active', 'total_budget' => 1000000, 'progress_percentage' => 0]);
        InvestorBooking::create(['user_id' => $investor->id, 'project_id' => $project->id, 'unit_no' => 'A-1', 'investment_amount' => 100000, 'status' => 'reserved']);

        $this->actingAs($investor)->post(route('investor.pay'), ['project_id' => $project->id, 'amount' => 25000, 'payment_method' => 'bKash', 'payer_reference' => 'BK123'])->assertRedirect(route('investor.dashboard'));
        $payment = InvestorPayment::firstOrFail();
        $this->assertSame('pending', $payment->status);

        $this->actingAs($admin)->patch(route('admin.investor-payments.approve', $payment), ['gateway_transaction_id' => 'GW-123'])->assertSessionHas('success');
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertDatabaseHas('investor_notifications', ['user_id' => $investor->id, 'title' => 'Payment confirmed']);
        $this->actingAs($investor)->get(route('investor.ledger'))->assertOk()->assertSee('Paid');
    }

    public function test_payment_request_does_not_forfeit_a_booking_after_only_days_of_overdue_checks(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $project = Project::create(['title' => 'Overdue Payment Project', 'location' => 'Dhaka', 'status' => 'active', 'total_budget' => 1000000, 'progress_percentage' => 0]);
        $booking = InvestorBooking::create([
            'user_id' => $investor->id,
            'project_id' => $project->id,
            'unit_no' => 'A-2',
            'investment_amount' => 100000,
            'status' => 'reserved',
            'next_payment_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->actingAs($investor)->post(route('investor.pay'), [
            'booking_id' => $booking->id,
            'payment_method' => 'bKash',
            'payer_reference' => 'BK456',
        ])->assertRedirect(route('investor.dashboard'));

        $this->assertSame('reserved', $booking->fresh()->status);
        $this->assertSame(1, $booking->fresh()->missed_installments);
        $this->assertDatabaseHas('investor_payments', ['booking_id' => $booking->id, 'status' => 'pending']);
    }

    public function test_admin_project_progress_syncs_to_landowner_and_investor_portals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $landowner = User::factory()->create(['role' => 'landowner']);
        $investor = User::factory()->create(['role' => 'investor']);
        $submission = LandSubmission::create(['code' => 'LND-2026-SYNC1', 'user_id' => $landowner->id, 'title' => 'Connected Project', 'location' => 'Dhaka', 'division' => 'Dhaka', 'area_sqft' => 7200, 'asking_price' => 50000000, 'status' => 'under_review', 'stage' => 'Lawyer Assigned']);

        $this->actingAs($admin)->post(route('admin.submissions.approve', $submission), ['allocated_flats' => 'A-1'])->assertRedirect();
        $project = $submission->fresh()->project;
        InvestorBooking::create(['user_id' => $investor->id, 'project_id' => $project->id, 'unit_no' => 'B-1', 'investment_amount' => 100000, 'status' => 'reserved']);

        $this->actingAs($admin)->patch(route('erp.projects.update', $project), ['status' => 'active', 'progress_percentage' => 55, 'end_date' => now()->addYear()->toDateString()])->assertSessionHas('success');

        $this->actingAs($landowner)->get(route('landowner.dashboard'))->assertOk()->assertSee('55%');
        $this->actingAs($investor)->get(route('investor.dashboard'))->assertOk()->assertSee('55%');
        $this->assertDatabaseHas('investor_notifications', ['user_id' => $investor->id, 'title' => 'Project progress updated']);
    }

    public function test_overdue_booking_shows_blocked_red_alert_and_warning(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $project = Project::create(['title' => 'Blocked Unit Project', 'location' => 'Dhaka', 'status' => 'active', 'total_budget' => 2000000]);
        $booking = InvestorBooking::create([
            'user_id' => $investor->id,
            'project_id' => $project->id,
            'unit_no' => '4A',
            'investment_amount' => 2000000,
            'status' => 'reserved',
            'next_payment_date' => now()->subDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($investor)->get(route('investor.dashboard'));
        $response->assertOk();
        $response->assertSee('Plot / Flat Blocked');
        $response->assertSee('Admin er kache request pathan');
        $response->assertSee('3 month por flat');
    }

    public function test_investor_can_request_admin_to_unblock(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create(['title' => 'Unblock Request Project', 'location' => 'Dhaka', 'status' => 'active', 'total_budget' => 3000000]);
        $booking = InvestorBooking::create([
            'user_id' => $investor->id,
            'project_id' => $project->id,
            'unit_no' => '5B',
            'investment_amount' => 3000000,
            'status' => 'reserved',
            'next_payment_date' => now()->subDays(5)->toDateString(),
        ]);

        $this->actingAs($investor)
            ->post(route('investor.bookings.request-unblock', $booking))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('investor_notifications', [
            'user_id' => $investor->id,
            'title' => 'Unblock Request Submitted',
        ]);

        // Admin can unblock and extend due date
        $this->actingAs($admin)
            ->post(route('admin.investor-bookings.unblock', $booking))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('reserved', $booking->fresh()->status);
        $this->assertTrue(\Carbon\Carbon::parse($booking->fresh()->next_payment_date)->isFuture());
    }

    public function test_investor_can_pay_via_rocket(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $project = Project::create(['title' => 'Rocket Gateway Project', 'location' => 'Dhaka', 'status' => 'active', 'total_budget' => 1500000]);
        $booking = InvestorBooking::create([
            'user_id' => $investor->id,
            'project_id' => $project->id,
            'unit_no' => '6C',
            'investment_amount' => 1500000,
            'status' => 'reserved',
            'installment_months' => 24,
            'monthly_installment_amount' => 53125,
            'next_payment_date' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($investor)->post(route('investor.pay'), [
            'booking_id' => $booking->id,
            'payment_method' => 'Rocket',
            'payer_reference' => '017112233441',
        ])->assertRedirect(route('investor.dashboard'));

        $this->assertDatabaseHas('investor_payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'Rocket',
            'status' => 'pending',
        ]);
    }

    public function test_reservation_calculates_realtime_future_due_date(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $project = Project::create(['title' => 'Realtime Due Project', 'location' => 'Dhaka', 'status' => 'active', 'total_budget' => 5000000]);

        $this->actingAs($investor)->post(route('investor.reserve'), [
            'project_id' => $project->id,
            'unit_no' => '8A',
            'installment_months' => 24,
            'installment_day' => 10,
        ])->assertRedirect();

        $booking = InvestorBooking::where('user_id', $investor->id)->where('project_id', $project->id)->first();
        $this->assertNotNull($booking);
        $this->assertNotNull($booking->next_payment_date);
        $this->assertTrue(\Carbon\Carbon::parse($booking->next_payment_date)->isFuture());
    }
}
