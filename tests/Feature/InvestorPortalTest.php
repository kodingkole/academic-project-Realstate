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

        $response->assertRedirect(route('investor.otp.form'));
        $code = Cache::get('investor-login-otp:'.$investor->email);
        $this->post(route('investor.otp.verify'), ['otp' => $code])->assertRedirect(route('investor.dashboard'));
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

        $this->actingAs($investor)->post(route('investor.pay'), ['project_id' => $project->id, 'amount' => 25000, 'payment_method' => 'bKash', 'payer_reference' => 'BK123'])->assertRedirect(route('investor.ledger'));
        $payment = InvestorPayment::firstOrFail();
        $this->assertSame('pending', $payment->status);

        $this->actingAs($admin)->patch(route('admin.investor-payments.approve', $payment), ['gateway_transaction_id' => 'GW-123'])->assertSessionHas('success');
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertDatabaseHas('investor_notifications', ['user_id' => $investor->id, 'title' => 'Payment confirmed']);
        $this->actingAs($investor)->get(route('investor.ledger'))->assertOk()->assertSee('Paid');
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
}
