<?php

namespace Tests\Feature;

use App\Models\User;
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

        $response = $this->post(route('investor.login.attempt'), [
            'email' => $investor->email,
            'password' => 'investor123',
        ]);

        $response->assertRedirect(route('investor.dashboard'));
        $this->actingAs($investor)->get(route('investor.dashboard'))->assertOk();
        $this->actingAs($investor)->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_cannot_open_investor_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('investor.dashboard'))->assertRedirect(route('investor.login'));
    }
}
