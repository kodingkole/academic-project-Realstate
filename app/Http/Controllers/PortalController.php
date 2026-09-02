<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CmsPage;
use App\Models\LandSubmission;
use App\Models\Lawyer;
use App\Models\Project;
use App\Models\JvAgreement;
use App\Services\ErpIntelligenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalController extends Controller
{
    public function adminDashboard(ErpIntelligenceService $ai): View
    {
        $projects = Project::with(['milestones','tasks','expenses'])->latest()->get();
        $stats = ['active_projects'=>$projects->where('status','active')->count(), 'budget_spent'=>(float) DB::table('expenses')->sum('amount'), 'active_workers'=>DB::table('attendances')->whereDate('check_in', today())->distinct('worker_name')->count('worker_name'), 'pending_legal'=>LandSubmission::whereIn('status',['submitted','under_review'])->count()];
        $risks = $projects->map(fn ($project) => ['project'=>$project, 'risk'=>$ai->projectRisk($project), 'spent'=>(float)$project->expenses->sum('amount')]);
        $submissions = LandSubmission::latest()->take(6)->get();
        return view('admin.command-center', compact('stats','risks','submissions'));
    }

    public function adminErpHub(): View { return view('admin.erp-hub', ['modules'=>$this->modules()]); }
    public function adminProjects(ErpIntelligenceService $ai): View
    {
        $module = 'projects';
        $records = DB::table('projects')->latest()->limit(50)->get();
        $projects = Project::orderBy('title')->get();
        $materials = collect();
        $users = DB::table('users')->orderBy('name')->get();
        $insights = Project::with(['milestones','tasks'])->get()->mapWithKeys(fn ($project) => [$project->id => $ai->projectRisk($project)]);
        return view('erp.module', compact('module','records','projects','materials','users','insights'));
    }
    public function adminLawyers(): View { return view('admin.lawyers', ['lawyers'=>Lawyer::latest()->get(), 'submissions'=>LandSubmission::latest()->get()]); }
    public function assignLawyer(Request $request, LandSubmission $submission): RedirectResponse { $data=$request->validate(['lawyer_id'=>'required|exists:lawyers,id']); $submission->update(['assigned_lawyer_id'=>$data['lawyer_id'],'status'=>'under_review','stage'=>'Lawyer Assigned']); Lawyer::whereKey($data['lawyer_id'])->increment('active_cases_count'); $this->log('assign','legal','Assigned lawyer to '.$submission->title,$request); return back()->with('success','Lawyer assigned and legal vetting started.'); }
    public function approveSubmission(Request $request, LandSubmission $submission): RedirectResponse
    {
        $data=$request->validate(['landowner_share_pct'=>'nullable|numeric|min:10|max:90','allocated_flats'=>'nullable|string']);
        DB::transaction(function () use ($submission,$data): void {
            $ownerShare=(float)($data['landowner_share_pct']??40);
            $project=$submission->project ?: Project::create([
                'title'=>$submission->title,
                'location'=>$submission->location,
                'division'=>$submission->division,
                'total_budget'=>max(50000000,(float)$submission->asking_price*1.6),
                'land_area'=>$submission->area_sqft,
                'progress_percentage'=>0,
                'status'=>'active',
                'start_date'=>now()->addMonth(),
                'end_date'=>now()->addMonths(30)
            ]);

            if ($project->status !== 'active') {
                $project->update(['status' => 'active']);
            }

            $flats=collect(explode(',',$data['allocated_flats']??''))->map(fn($v)=>trim($v))->filter()->values()->all();
            JvAgreement::updateOrCreate(['land_submission_id'=>$submission->id],['landowner_share_pct'=>$ownerShare,'developer_share_pct'=>100-$ownerShare,'allocated_flats_json'=>$flats,'terms'=>'The landowner contributes the scheduled land and receives the agreed share of completed saleable area. The developer finances, constructs and delivers the project subject to approved plans and statutory clearance.','status'=>'draft']);
            $submission->update(['status'=>'approved','stage'=>'JV Agreement Drafted','project_id'=>$project->id,'rejection_reason'=>null]);

            // Notify investors about newly approved land project
            $investors = \App\Models\User::where('role', 'investor')->get();
            foreach ($investors as $inv) {
                \App\Models\InvestorNotification::create([
                    'user_id' => $inv->id,
                    'title' => 'New Property Available for Investment',
                    'message' => 'Land submission "' . $submission->title . '" (' . $submission->location . ') has been approved and is now active for investment.',
                    'type' => 'booking',
                ]);
            }
        });
        $this->log('approve','legal','Approved '.$submission->title.' and created active project',$request); return back()->with('success','Submission approved; active project created and added to dashboard.');
    }
    public function rejectSubmission(Request $request, LandSubmission $submission): RedirectResponse { $data=$request->validate(['rejection_reason'=>'nullable|string|max:1000']); $submission->update(['status'=>'rejected','stage'=>'Rejected','rejection_reason'=>$data['rejection_reason']??'Legal requirements were not satisfied.']); $this->log('reject','legal','Rejected '.$submission->title,$request); return back()->with('success','Submission rejected.'); }
    public function adminAudit(Request $request): View { $logs=ActivityLog::query()->when($request->module,fn($q,$v)=>$q->where('module',$v))->when($request->action,fn($q,$v)=>$q->where('action',$v))->latest('timestamp')->paginate(30)->withQueryString(); return view('admin.audit',compact('logs')); }
    public function exportAuditLog(): StreamedResponse { return response()->streamDownload(function(){ $out=fopen('php://output','w'); fputcsv($out,['Time','User','Action','Module','Description','IP']); ActivityLog::orderBy('timestamp')->chunk(500,fn($rows)=>$rows->each(fn($r)=>fputcsv($out,[$r->timestamp,$r->user_id,$r->action,$r->module,$r->description,$r->ip_address]))); fclose($out); },'audit-log.csv',['Content-Type'=>'text/csv']); }
    public function landownerDashboard(Request $request): View
    {
        $submissions=LandSubmission::with(['agreement','project'])->where('user_id',$request->user()->id)->latest('submitted_at')->get();
        $currentValue=(float)$submissions->sum('asking_price');
        $projectedValue=$submissions->sum(fn($s)=>$s->project ? ((float)$s->project->total_budget*((float)($s->agreement?->landowner_share_pct??40)/100)) : ((float)$s->asking_price*1.35));
        $growth=$currentValue>0?(($projectedValue-$currentValue)/$currentValue)*100:0;
        $units=$submissions->flatMap(function($s){ return collect($s->agreement?->allocated_flats_json??[])->map(fn($unit)=>['code'=>is_array($unit)?($unit['code']??'-'):$unit,'size'=>is_array($unit)?($unit['size']??'TBA'):'TBA','progress'=>$s->project?->progress_percentage??0,'project'=>$s->project?->title??$s->title]); });
        return view('landowner.dashboard',compact('submissions','currentValue','projectedValue','growth','units'));
    }
    public function landownerSubmissions(Request $request): View
    {
        $submissions=LandSubmission::with(['agreement','lawyer','project'])->where('user_id',$request->user()->id)->latest('submitted_at')->get();
        return view('landowner.submissions',compact('submissions'));
    }
    private function log(string $action,string $module,string $description,Request $request): void { ActivityLog::create(['user_id'=>$request->user()?->id,'action'=>$action,'module'=>$module,'description'=>$description,'ip_address'=>$request->ip(),'timestamp'=>now()]); }
    private function modules(): array { return ['projects'=>['title'=>'Projects & AI Schedule','desc'=>'Milestones, delivery velocity and delay risk'],'tasks'=>['title'=>'Task Breakdown','desc'=>'Assignments, priorities and progress'],'inventory'=>['title'=>'Inventory & Auto-Reorder','desc'=>'Stock ledger and material demand'],'procurement'=>['title'=>'Procurement','desc'=>'Requests, quotations and POs'],'workforce'=>['title'=>'Workforce & Payroll','desc'=>'QR attendance and monthly payroll'],'finance'=>['title'=>'Finance','desc'=>'Expense categories and budget overruns'],'site-progress'=>['title'=>'Site Progress','desc'=>'Daily reports and photo timeline'],'inspections'=>['title'=>'Quality & Safety','desc'=>'Inspection results and corrective actions'],'documents'=>['title'=>'Document Control','desc'=>'Versioned drawings and approvals'],'equipment'=>['title'=>'Equipment Fleet','desc'=>'Utilization and maintenance due dates']]; }
}
