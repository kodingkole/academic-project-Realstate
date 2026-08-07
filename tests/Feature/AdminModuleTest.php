<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminModuleController;
use App\Models\ModuleRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_open_every_module(): void
    {
        $user = User::factory()->create();

        foreach (array_keys(AdminModuleController::MODULES) as $module) {
            $this->actingAs($user)->get(route('admin.modules.index', $module))->assertOk();
        }
    }

    public function test_authenticated_admin_can_create_update_and_delete_records_in_every_module(): void
    {
        $user = User::factory()->create();

        foreach (array_keys(AdminModuleController::MODULES) as $module) {
            $this->actingAs($user)->post(route('admin.modules.store', $module), [
                'title' => "Test {$module}",
                'status' => 'Active',
                'quantity' => 25,
                'amount' => 1500,
                'due_date' => '2026-08-15',
                'details' => "Backend record for {$module}",
            ])->assertRedirect(route('admin.modules.index', $module));

            $record = ModuleRecord::where('module', $module)->firstOrFail();
            $this->actingAs($user)->put(route('admin.modules.update', [$module, $record]), [
                'title' => "Updated {$module}", 'status' => 'Completed',
            ])->assertRedirect(route('admin.modules.index', $module));
            $this->assertDatabaseHas('module_records', [
                'module' => $module, 'title' => "Updated {$module}", 'status' => 'Completed',
            ]);

            $this->actingAs($user)->delete(route('admin.modules.destroy', [$module, $record]))
                ->assertRedirect(route('admin.modules.index', $module));
        }

        $this->assertDatabaseCount('module_records', 0);
    }
}
