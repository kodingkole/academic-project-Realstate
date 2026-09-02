<?php

namespace Tests\Feature;

use App\Models\JvAgreement;
use App\Models\LandSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandownerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landowner_can_complete_submission_with_private_documents(): void
    {
        Storage::fake('local');
        $response = $this->post(route('land.submit.store'), [
            'phone'=>'01712345678','division'=>'Dhaka','district'=>'Dhaka','location'=>'Uttara Sector 18',
            'katha'=>8.5,'road_width'=>30,'landmark'=>'Metro station','description'=>'Corner plot',
            'owner_name'=>'Public Landowner','nid_number'=>'1234567890123',
            'deed_path'=>UploadedFile::fake()->create('deed.pdf',250,'application/pdf'),
            'nid_path'=>UploadedFile::fake()->create('nid.jpg',120,'image/jpeg'),
        ]);
        $response->assertRedirect(route('land.submit'))->assertSessionHas('submission_success');
        $submission=LandSubmission::firstOrFail();
        $this->assertMatchesRegularExpression('/^LND-\d{4}-[A-Z0-9]{5}$/',$submission->code);
        $this->assertNull($submission->user_id);
        Storage::disk('local')->assertExists($submission->deed_path);
        Storage::disk('local')->assertExists($submission->nid_path);
    }

    public function test_admin_approval_creates_project_and_balanced_jv_draft(): void
    {
        $admin=User::factory()->create(['role'=>'admin']);
        $submission=LandSubmission::create(['code'=>'LND-2026-TEST1','title'=>'JV Plot','location'=>'Dhaka','division'=>'Dhaka','area_sqft'=>7200,'asking_price'=>50000000,'status'=>'under_review','stage'=>'Lawyer Assigned']);
        $this->actingAs($admin)->post(route('admin.submissions.approve',$submission),['landowner_share_pct'=>45,'allocated_flats'=>'A4, B6'])->assertRedirect();
        $submission->refresh();
        $this->assertNotNull($submission->project_id);
        $this->assertSame('JV Agreement Drafted',$submission->stage);
        $agreement=JvAgreement::where('land_submission_id',$submission->id)->firstOrFail();
        $this->assertSame('45.00',$agreement->landowner_share_pct);
        $this->assertSame('55.00',$agreement->developer_share_pct);
        $this->assertSame(['A4','B6'],$agreement->allocated_flats_json);
    }

    public function test_landowner_only_sees_their_linked_portfolio(): void
    {
        $owner=User::factory()->create(['role'=>'landowner']);
        $other=User::factory()->create(['role'=>'landowner']);
        LandSubmission::create(['code'=>'LND-2026-OWN01','user_id'=>$owner->id,'title'=>'Own Plot','location'=>'Dhaka','division'=>'Dhaka','area_sqft'=>3600,'asking_price'=>20000000,'status'=>'submitted','stage'=>'Submitted']);
        LandSubmission::create(['code'=>'LND-2026-OTH01','user_id'=>$other->id,'title'=>'Other Plot','location'=>'Khulna','division'=>'Khulna','area_sqft'=>3600,'asking_price'=>10000000,'status'=>'submitted','stage'=>'Submitted']);
        $this->actingAs($owner)->get(route('landowner.dashboard'))->assertOk()->assertSee('LND-2026-OWN01')->assertDontSee('LND-2026-OTH01');
        $this->actingAs($owner)->get(route('landowner.submissions'))->assertOk()->assertSee('LND-2026-OWN01')->assertDontSee('LND-2026-OTH01');
    }
}
