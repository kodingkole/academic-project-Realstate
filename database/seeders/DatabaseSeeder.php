<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Material;
use App\Models\Lawyer;
use App\Models\LandSubmission;
use App\Models\JvAgreement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@internestate.com',
            ],
            [
                'name' => 'Administrator',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        $project = Project::updateOrCreate(['title' => 'Skyline Residence'], [
            'location' => 'Bashundhara R/A, Dhaka', 'division' => 'Dhaka', 'total_budget' => 85000000,
            'land_area' => 14400, 'progress_percentage' => 72, 'status' => 'active',
            'start_date' => now()->subMonths(7)->toDateString(), 'end_date' => now()->addMonths(9)->toDateString(),
        ]);
        $project->milestones()->updateOrCreate(['title'=>'Structural frame'], ['due_date'=>now()->subDays(8)->toDateString(),'status'=>'in_progress','completion_percentage'=>78,'delay_risk_level'=>'High']);
        $project->milestones()->updateOrCreate(['title'=>'MEP rough-in'], ['due_date'=>now()->addMonth()->toDateString(),'status'=>'pending','completion_percentage'=>18,'delay_risk_level'=>'Medium']);
        $project->tasks()->updateOrCreate(['title'=>'Complete level 8 slab'], ['priority'=>'high','start_date'=>now()->subDays(10)->toDateString(),'end_date'=>now()->addDays(4)->toDateString(),'progress'=>72,'status'=>'in_progress']);
        $project->expenses()->updateOrCreate(['description'=>'Reinforcement steel delivery'], ['category'=>'Materials','amount'=>6450000,'payment_method'=>'Bank Transfer','date'=>now()->subDays(3)->toDateString()]);

        $p2 = Project::updateOrCreate(['title' => 'Green Valley Heights'], [
            'location' => 'Uttara Sector 12, Dhaka', 'division' => 'Dhaka', 'total_budget' => 120000000,
            'land_area' => 18000, 'progress_percentage' => 48, 'status' => 'active',
            'start_date' => now()->subMonths(4)->toDateString(), 'end_date' => now()->addMonths(14)->toDateString(),
        ]);
        $p2->milestones()->updateOrCreate(['title'=>'Foundation piling'], ['due_date'=>now()->subDays(20)->toDateString(),'status'=>'completed','completion_percentage'=>100,'delay_risk_level'=>'Low']);

        $p3 = Project::updateOrCreate(['title' => 'Urban Trade Center'], [
            'location' => 'Gazipur Commercial Hub', 'division' => 'Dhaka', 'total_budget' => 45000000,
            'land_area' => 10800, 'progress_percentage' => 86, 'status' => 'active',
            'start_date' => now()->subMonths(10)->toDateString(), 'end_date' => now()->addMonths(4)->toDateString(),
        ]);

        $p4 = Project::updateOrCreate(['title' => 'Dhanmondi Royal Residency'], [
            'location' => 'Road 27, Dhanmondi, Dhaka', 'division' => 'Dhaka', 'total_budget' => 160000000,
            'land_area' => 21600, 'progress_percentage' => 30, 'status' => 'active',
            'start_date' => now()->subMonths(2)->toDateString(), 'end_date' => now()->addMonths(18)->toDateString(),
        ]);

        foreach ([
            ['Cement','Structural',320,'bags',500,570], ['Rebar Steel','Structural',18,'ton',25,92500], ['Ceramic Tiles','Finishing',840,'sqft',500,145],
        ] as [$name,$category,$stock,$unit,$reorder,$price]) Material::updateOrCreate(['name'=>$name], ['category'=>$category,'current_stock'=>$stock,'unit'=>$unit,'reorder_level'=>$reorder,'unit_price'=>$price]);

        $lawyer = Lawyer::updateOrCreate(['email'=>'farhana@internestate.test'], ['name'=>'Adv. Farhana Islam','phone'=>'+8801711000000','specialization'=>'Land & Property Law','active_cases_count'=>3]);
        $landowner = User::updateOrCreate(['email'=>'landowner@internestate.com'], ['name'=>'Nusrat Jahan','phone'=>'01711000000','role'=>'landowner','password'=>Hash::make('landowner123')]);
        $submission = LandSubmission::updateOrCreate(['title'=>'Uttara Sector 16 Plot'], ['code'=>'LND-2026-8912','user_id'=>$landowner->id,'landowner_id'=>$landowner->id,'landowner_name'=>$landowner->name,'phone'=>$landowner->phone,'location'=>'Uttara, Dhaka','division'=>'Dhaka','district'=>'Dhaka','katha_size'=>10,'area_sqft'=>7200,'road_width'=>30,'landmark'=>'Near Diabari Metro Station','nid_number'=>'1987654321098','asking_price'=>46000000,'assigned_lawyer_id'=>$lawyer->id,'project_id'=>$project->id,'status'=>'approved','stage'=>'JV Agreement Drafted','submitted_at'=>now()->subMonths(2)]);
        JvAgreement::updateOrCreate(['land_submission_id'=>$submission->id],['landowner_share_pct'=>40,'developer_share_pct'=>60,'allocated_flats_json'=>[['code'=>'A4','size'=>1450],['code'=>'B6','size'=>1320]],'terms'=>'The landowner contributes the scheduled land and receives 40% of completed saleable area. The developer finances, constructs and delivers the project subject to approved plans and statutory clearance.','status'=>'draft']);

        $investor = User::updateOrCreate(
            ['email' => 'investor@internestate.com'],
            [
                'name' => 'Investor',
                'role' => 'investor',
                'password' => Hash::make('investor123'),
            ]
        );

        \App\Models\InvestorBooking::updateOrCreate(
            ['user_id' => $investor->id, 'project_id' => $project->id],
            ['unit_no' => '7B', 'investment_amount' => 12500000, 'status' => 'reserved', 'installment_months' => 24, 'monthly_installment_amount' => 520833]
        );

        \App\Models\InvestorPayment::updateOrCreate(
            ['user_id' => $investor->id, 'transaction_id' => 'IE-20260824-01'],
            [
                'project_id' => $project->id,
                'amount' => 2500000,
                'payment_method' => 'Bank Transfer',
                'payer_reference' => 'TXN-BANK-998811',
                'status' => 'paid',
                'paid_at' => now()->subDays(5),
            ]
        );

    }
}
