<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConstructionErpTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_command_center_and_every_erp_workspace(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Admin Command Center');
        $this->actingAs($admin)->get(route('admin.erp'))->assertOk()->assertSee('Construction ERP Hub');
        foreach (['projects','tasks','inventory','procurement','workforce','finance','site-progress','inspections','documents','equipment'] as $module) {
            $this->actingAs($admin)->get(route('erp.'.$module))->assertOk();
        }
    }

    public function test_auto_reorder_creates_one_pending_request_for_low_stock_material(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $material = Material::create(['name'=>'Cement','category'=>'Structural','current_stock'=>10,'unit'=>'bags','reorder_level'=>50,'unit_price'=>550]);
        $this->actingAs($admin)->post(route('erp.inventory.reorder'))->assertRedirect();
        $this->actingAs($admin)->post(route('erp.inventory.reorder'))->assertRedirect();
        $this->assertSame(1, PurchaseRequest::where('material_id', $material->id)->where('status','pending')->count());
    }

    public function test_non_admin_cannot_open_erp(): void
    {
        $investor = User::factory()->create(['role' => 'investor']);
        $this->actingAs($investor)->get(route('erp.finance'))->assertRedirect(route('login'));
    }
}
