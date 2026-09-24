<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\InventoryTransaction;
use App\Models\InvestorBooking;
use App\Models\InvestorNotification;
use App\Models\Material;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\Task;
use App\Services\ErpIntelligenceService;
use App\Services\ProjectProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ErpController extends Controller
{
    private const TABLES=['projects'=>'projects','tasks'=>'tasks','inventory'=>'materials','procurement'=>'purchase_requests','workforce'=>'attendances','finance'=>'expenses','site-progress'=>'daily_progress_reports','inspections'=>'quality_inspections','documents'=>'documents','equipment'=>'equipment'];
    public function index(string $module, ErpIntelligenceService $ai): View { abort_unless(isset(self::TABLES[$module]),404); $records=DB::table(self::TABLES[$module])->latest()->limit(50)->get(); if ($module === 'projects') { $progressService=app(ProjectProgressService::class); $projectsForProgress=Project::with('tasks')->whereIn('id',$records->pluck('id'))->get()->keyBy('id'); $records=$records->map(function ($record) use ($progressService, $projectsForProgress) { $record->progress_percentage=$progressService->percentage($projectsForProgress->get($record->id)); return $record; }); } $projects=Project::orderBy('title')->get(); $materials=Material::orderBy('name')->get(); $users=DB::table('users')->whereNotIn('role',['investor','landowner'])->orderBy('name')->get(); $taskSummary=$module==='tasks' ? ['total'=>$records->count(),'pending'=>$records->where('status','pending')->count(),'in_progress'=>$records->where('status','in_progress')->count(),'completed'=>$records->where('status','completed')->count(),'average_progress'=>$records->count() ? round($records->avg('progress')) : 0] : []; $insights=$module==='projects' ? Project::with(['milestones','tasks'])->get()->mapWithKeys(fn($p)=>[$p->id=>$ai->projectRisk($p)]) : collect(); $requirements=DB::table('project_material_requirements')->join('projects','projects.id','=','project_material_requirements.project_id')->join('materials','materials.id','=','project_material_requirements.material_id')->select('project_material_requirements.*','projects.title as project_title','materials.name as material_name','materials.unit','materials.current_stock')->latest('project_material_requirements.updated_at')->get(); $fundedProjects=$projects->mapWithKeys(fn($p)=>[$p->id=>(float)DB::table('investor_payments')->where('project_id',$p->id)->where('status','paid')->sum('amount')-(float)DB::table('expenses')->where('project_id',$p->id)->sum('amount')]); $purchaseRequests=DB::table('purchase_requests')->join('materials','materials.id','=','purchase_requests.material_id')->leftJoin('projects','projects.id','=','purchase_requests.project_id')->select('purchase_requests.*','materials.name as material_name','projects.title as project_title')->latest('purchase_requests.created_at')->get();
        if ($module === 'projects') {
            $records = $records->map(function ($record) {
                unset($record->progress_percentage);
                return $record;
            });
        }

        if ($module === 'tasks') {
            $records = Task::latest()->limit(50)->get();
            $taskSummary = [
                'total' => $records->count(),
                'pending' => $records->where('status', 'pending')->count(),
                'in_progress' => $records->where('status', 'in_progress')->count(),
                'completed' => $records->where('status', 'completed')->count(),
                'average_progress' => $records->isNotEmpty() ? round($records->avg('progress')) : 0,
            ];
        }

        if ($module === 'procurement') {
            $records = DB::table('purchase_requests')
                ->leftJoin('projects', 'projects.id', '=', 'purchase_requests.project_id')
                ->join('materials', 'materials.id', '=', 'purchase_requests.material_id')
                ->select(
                    'purchase_requests.id',
                    DB::raw("COALESCE(projects.title, 'General stock') as project"),
                    'materials.name as material',
                    'purchase_requests.quantity',
                    'purchase_requests.estimated_cost',
                    'purchase_requests.status'
                )
                ->where('purchase_requests.status', '!=', 'received')
                ->latest('purchase_requests.created_at')
                ->limit(50)
                ->get();
        }

        // Document Control: sold flat buyer info
        $soldUnits = $module === 'documents' ? DB::table('investor_bookings')
            ->join('users', 'users.id', '=', 'investor_bookings.user_id')
            ->join('projects', 'projects.id', '=', 'investor_bookings.project_id')
            ->leftJoin(DB::raw('(SELECT booking_id, SUM(amount) as total_paid FROM investor_payments WHERE status="paid" GROUP BY booking_id) as paid_summary'), 'paid_summary.booking_id', '=', 'investor_bookings.id')
            ->select(
                'investor_bookings.id',
                'investor_bookings.unit_no',
                'investor_bookings.investment_amount',
                'investor_bookings.status as booking_status',
                'users.name as buyer_name',
                'users.email as buyer_email',
                'users.phone as buyer_phone',
                'projects.title as project_title',
                DB::raw('COALESCE(paid_summary.total_paid, 0) as total_paid')
            )
            ->whereNotIn('investor_bookings.status', ['rejected', 'cancelled'])
            ->orderBy('projects.title')
            ->orderBy('investor_bookings.unit_no')
            ->get()
            ->map(function ($row) {
                $row->due = max(0, (float)$row->investment_amount - (float)$row->total_paid);
                return $row;
            }) : collect();
        return view('erp.module',compact('module','records','projects','materials','users','taskSummary','insights','requirements','fundedProjects','purchaseRequests','soldUnits')); }
    public const STANDARD_CONSTRUCTION_TASKS = [
        ['title'=>'Site preparation', 'priority'=>'high', 'progress'=>0, 'status'=>'pending'],
        ['title'=>'Foundation', 'priority'=>'critical', 'progress'=>0, 'status'=>'pending'],
        ['title'=>'Structure and columns', 'priority'=>'critical', 'progress'=>0, 'status'=>'pending'],
        ['title'=>'Brickwork', 'priority'=>'high', 'progress'=>0, 'status'=>'pending'],
        ['title'=>'Electrical', 'priority'=>'medium', 'progress'=>0, 'status'=>'pending'],
        ['title'=>'Plumbing', 'priority'=>'medium', 'progress'=>0, 'status'=>'pending'],
        ['title'=>'Plastering', 'priority'=>'medium', 'progress'=>0, 'status'=>'pending'],
        ['title'=>'Flooring', 'priority'=>'low', 'progress'=>0, 'status'=>'pending'],
        ['title'=>'Painting', 'priority'=>'low', 'progress'=>0, 'status'=>'pending'],
        ['title'=>'Final inspection', 'priority'=>'critical', 'progress'=>0, 'status'=>'pending'],
    ];
    public function storeProject(Request $r): RedirectResponse { $data=$r->validate(['title'=>'required|string|max:255','location'=>'required|string|max:255','division'=>'required|in:Dhaka,Chittagong,Rajshahi,Sylhet,Khulna,Barisal,Rangpur,Mymensingh','total_budget'=>'required|numeric|min:10000000','land_area'=>'required|numeric|min:1440']); $project=Project::create($data+['status'=>'active','start_date'=>now()->toDateString()]); $this->seedStandardTasksForProject($project->id); return $this->done($r,'projects','Project created with standard construction tasks.'); }
    public function generateStandardTasks(Request $r): RedirectResponse { $projectId=$r->input('project_id'); if($projectId){ $this->seedStandardTasksForProject((int)$projectId); } else { foreach(Project::pluck('id') as $pid){ $this->seedStandardTasksForProject((int)$pid); } } return $this->done($r,'tasks','Standard construction task breakdown generated successfully.'); }
    private function seedStandardTasksForProject(int $projectId): void { foreach(self::STANDARD_CONSTRUCTION_TASKS as $taskData){ Task::firstOrCreate(['project_id'=>$projectId,'title'=>$taskData['title']],$taskData+['start_date'=>now()->subMonths(3)->toDateString(),'end_date'=>now()->addMonths(6)->toDateString()]); } }
    public function updateProject(Request $r, Project $project): RedirectResponse
    {
        $data = $r->validate([
            'status' => 'required|in:active,inactive',
            'end_date' => 'nullable|date',
            'progress_percentage' => 'nullable|integer|between:0,100',
        ]);
        $project->update($data);

        if (isset($data['progress_percentage'])) {
            $bookingUserIds = \App\Models\InvestorBooking::where('project_id', $project->id)->pluck('user_id')->unique();
            foreach ($bookingUserIds as $uId) {
                \App\Models\InvestorNotification::create([
                    'user_id' => $uId,
                    'title' => 'Project progress updated',
                    'message' => "Project {$project->title} progress updated to {$data['progress_percentage']}%.",
                    'type' => 'project',
                ]);
            }
        }

        return $this->done($r, 'projects', 'Project status updated.', 'update');
    }
    public function storeMilestone(Request $r, Project $project): RedirectResponse { $project->milestones()->create($r->validate(['title'=>'required|max:255','due_date'=>'required|date','status'=>'required|max:30','completion_percentage'=>'required|integer|between:0,100'])); return $this->done($r,'projects','Milestone added.'); }
    public function storeTask(Request $r): RedirectResponse { $data=$r->validate(['project_id'=>'required|exists:projects,id','title'=>'required|in:Site preparation,Foundation,Structure and columns,Brickwork,Electrical,Plumbing,Plastering,Flooring,Painting,Final inspection','assigned_to'=>'nullable|exists:users,id','priority'=>'required|in:low,medium,high,critical','start_date'=>'nullable|date','end_date'=>'nullable|date','progress'=>'nullable|integer|between:0,100','status'=>'required|max:30']); if (!empty($data['assigned_to'])) abort_unless(DB::table('users')->where('id',$data['assigned_to'])->whereNotIn('role',['investor','landowner'])->exists(),422,'Investors and landowners cannot be assigned construction tasks.'); Task::create($data); return $this->done($r,'tasks','Task created.'); }
    public function updateTaskProgress(Request $r, Task $task): RedirectResponse { $data=$r->validate(['progress'=>'required|integer|between:0,100']); $task->update($data+['status'=>$data['progress']===100?'completed':'in_progress']); return $this->done($r,'tasks','Progress updated.'); }
    public function simulateTaskProgress(Request $r, Task $task): RedirectResponse { $progress=min(100,(int)$task->progress+10); $task->update(['progress'=>$progress,'status'=>$progress===100?'completed':'in_progress']); return $this->done($r,'tasks','One workday recorded. Progress is now '.$progress.'%.','update'); }
    public function updateTask(Request $r, Task $task): RedirectResponse { $data=$r->validate(['project_id'=>'required|exists:projects,id','title'=>'required|in:Site preparation,Foundation,Structure and columns,Brickwork,Electrical,Plumbing,Plastering,Flooring,Painting,Final inspection','assigned_to'=>'nullable|exists:users,id','priority'=>'required|in:low,medium,high,critical','start_date'=>'nullable|date','end_date'=>'nullable|date','progress'=>'nullable|integer|between:0,100','status'=>'required|max:30']); if (!empty($data['assigned_to'])) abort_unless(DB::table('users')->where('id',$data['assigned_to'])->whereNotIn('role',['investor','landowner'])->exists(),422,'Investors and landowners cannot be assigned construction tasks.'); $task->update($data); return $this->done($r,'tasks','Task updated.','update'); }
    public function destroyTask(Request $r, Task $task): RedirectResponse { $title=$task->title; $task->delete(); return $this->done($r,'tasks',"Task '{$title}' deleted.",'delete'); }
    public function storeMaterial(Request $r): RedirectResponse { $material=Material::create($r->validate(['name'=>'required|max:255','category'=>'required|max:100','current_stock'=>'required|numeric|min:0','unit'=>'required|max:30','reorder_level'=>'required|numeric|min:0','unit_price'=>'required|numeric|min:0'])); $this->raiseLowStockRequest($material,null,$r->user()?->id); return $this->done($r,'inventory','Material created.'); }
    public function updateMaterial(Request $r, Material $material): RedirectResponse { $material->update($r->validate(['name'=>'required|max:255','category'=>'required|max:100','current_stock'=>'required|numeric|min:0','unit'=>'required|max:30','reorder_level'=>'required|numeric|min:0','unit_price'=>'required|numeric|min:0'])); $this->raiseLowStockRequest($material,null,$r->user()?->id); return $this->done($r,'inventory','Material updated.','update'); }
    public function destroyMaterial(Request $r, Material $material): RedirectResponse { $name=$material->name; $material->delete(); return $this->done($r,'inventory',"Material '{$name}' deleted.",'delete'); }
    public function stockTransaction(Request $r): RedirectResponse { $data=$r->validate(['material_id'=>'required|exists:materials,id','project_id'=>'nullable|exists:projects,id','type'=>'required|in:IN,OUT,TRANSFER','quantity'=>'required|numeric|min:0.01','date'=>'required|date','notes'=>'nullable|string']); DB::transaction(function()use($data,$r){ $m=Material::lockForUpdate()->findOrFail($data['material_id']); if($data['type']==='OUT'&&$m->current_stock<$data['quantity']) abort(422,'Insufficient stock.'); $m->increment('current_stock',$data['type']==='IN'?$data['quantity']:-$data['quantity']); InventoryTransaction::create($data); $m->refresh(); $this->raiseLowStockRequest($m, $data['project_id'] ?? null, $r->user()?->id); }); return $this->done($r,'inventory','Stock ledger updated; low stock is sent to Procurement automatically.'); }
    public function autoReorder(Request $r): RedirectResponse { Material::whereColumn('current_stock','<=','reorder_level')->get()->each(fn(Material $material) => $this->raiseLowStockRequest($material,null,$r->user()?->id)); return $this->done($r,'inventory','Low-stock procurement queue synchronized.'); }
    public function saveMaterialRequirement(Request $r): RedirectResponse { $data=$r->validate(['project_id'=>'required|exists:projects,id','material_id'=>'required|exists:materials,id','planned_quantity'=>'required|numeric|min:0','daily_quantity'=>'required|numeric|min:0']); DB::table('project_material_requirements')->updateOrInsert(['project_id'=>$data['project_id'],'material_id'=>$data['material_id']],$data+['updated_at'=>now(),'created_at'=>now()]); return $this->done($r,'inventory','Project material plan saved.'); }
    public function approvePurchaseRequest(Request $r, int $requestId): RedirectResponse { $request=DB::table('purchase_requests')->where('id',$requestId)->first(); abort_unless($request,404); abort_if($request->finance_status==='approved',422,'This request is already approved.'); $projectId=$request->project_id ?? DB::table('projects')->value('id'); $paid=(float) DB::table('investor_payments')->where('project_id',$projectId)->where('status','paid')->sum('amount'); $committed=(float) DB::table('expenses')->where('project_id',$projectId)->sum('amount')+(float) DB::table('purchase_requests')->where('project_id',$projectId)->where('finance_status','approved')->sum('estimated_cost'); $available=$paid-$committed; if($available < (float)$request->estimated_cost){ DB::table('purchase_requests')->where('id',$requestId)->update(['finance_status'=>'held','finance_note'=>'Held: approved investor payments do not cover this order yet.','updated_at'=>now()]); return $this->done($r,'finance','Order held: investor payment balance is insufficient.','update'); } DB::transaction(function()use($request,$r,$projectId){ DB::table('purchase_requests')->where('id',$request->id)->update(['status'=>'ordered','finance_status'=>'approved','finance_approved_by'=>$r->user()?->id,'finance_note'=>'Approved against available investor funds.','updated_at'=>now()]); if($projectId){ Expense::create(['project_id'=>$projectId,'category'=>'Materials','amount'=>$request->estimated_cost,'payment_method'=>'Procurement commitment','date'=>now()->toDateString(),'description'=>'Approved procurement request #'.$request->id]); } }); return $this->done($r,'finance','Procurement funded and added to project budget.','update'); }
    public function useEquipment(Request $r): RedirectResponse { $data=$r->validate(['project_id'=>'required|exists:projects,id','equipment_id'=>'required|exists:equipment,id','hours_used'=>'required|numeric|min:.1','usage_date'=>'required|date','notes'=>'nullable|string']); $equipment=DB::table('equipment')->where('id',$data['equipment_id'])->first(); abort_if($equipment->operational_status!=='Active',422,'Only active equipment can be assigned.'); $cost=(float)$equipment->hourly_rate*(float)$data['hours_used']; DB::transaction(function()use($data,$equipment,$cost){ DB::table('equipment_usages')->insert($data+['hourly_rate'=>$equipment->hourly_rate,'created_at'=>now(),'updated_at'=>now()]); DB::table('equipment')->where('id',$equipment->id)->increment('total_hours_logged',$data['hours_used']); Expense::create(['project_id'=>$data['project_id'],'category'=>'Equipment','amount'=>$cost,'payment_method'=>'Equipment usage','date'=>$data['usage_date'],'description'=>$equipment->name.' · '.$data['hours_used'].' hours']); }); return $this->done($r,'equipment','Equipment usage recorded and charged to the project budget.'); }
    public function storeExpense(Request $r): RedirectResponse { Expense::create($r->validate(['project_id'=>'required|exists:projects,id','category'=>'required|in:Materials,Labor,Equipment,Legal,Overhead','amount'=>'required|numeric|min:0','payment_method'=>'required|max:50','date'=>'required|date','description'=>'nullable|string'])); return $this->done($r,'finance','Expense recorded.'); }
    public function storeSupplier(Request $r): RedirectResponse { DB::table('suppliers')->insert($r->validate(['name'=>'required|max:255','contact_person'=>'nullable|max:255','phone'=>'nullable|max:50','email'=>'nullable|email','rating'=>'nullable|numeric|between:0,5','active_status'=>'nullable|boolean'])+['created_at'=>now(),'updated_at'=>now()]); return $this->done($r,'procurement','Supplier added.'); }
    public function storePurchaseRequest(Request $r): RedirectResponse { return $this->genericStore($r,'procurement'); }
    public function updatePurchaseRequestStatus(Request $r, int $requestId): RedirectResponse
    {
        $data = $r->validate(['status' => 'required|in:pending,ordered,received,cancelled']);

        DB::transaction(function () use ($data, $requestId): void {
            $purchaseRequest = DB::table('purchase_requests')->where('id', $requestId)->lockForUpdate()->first();
            abort_unless($purchaseRequest, 404);

            if ($purchaseRequest->status === 'received') {
                abort(422, 'Received requests cannot be changed because their quantity is already in inventory.');
            }

            if ($data['status'] === 'received') {
                $material = Material::lockForUpdate()->findOrFail($purchaseRequest->material_id);
                $material->increment('current_stock', $purchaseRequest->quantity);

                InventoryTransaction::create([
                    'material_id' => $material->id,
                    'project_id' => $purchaseRequest->project_id,
                    'type' => 'IN',
                    'quantity' => $purchaseRequest->quantity,
                    'date' => now()->toDateString(),
                    'notes' => 'Received through purchase request #' . $purchaseRequest->id,
                ]);
            }

            DB::table('purchase_requests')->where('id', $purchaseRequest->id)->update([
                'status' => $data['status'],
                'updated_at' => now(),
            ]);
        });

        return $this->done($r, 'procurement', $data['status'] === 'received'
            ? 'Product received and added to inventory.'
            : 'Purchase request status updated.', 'update');
    }
    public function createPurchaseOrder(Request $r): RedirectResponse { $data=$r->validate(['project_id'=>'nullable|exists:projects,id','supplier_id'=>'required|exists:suppliers,id','material_id'=>'required|exists:materials,id','quantity'=>'required|numeric|min:.01','total_cost'=>'required|numeric|min:0','status'=>'required|in:issued,delivered,cancelled']); DB::table('purchase_orders')->insert($data+['created_at'=>now(),'updated_at'=>now()]); return $this->done($r,'procurement','Purchase order issued.'); }
    public function storeReport(Request $r): RedirectResponse { return $this->genericStore($r,'site-progress'); }
    public function storeInspection(Request $r): RedirectResponse { return $this->genericStore($r,'inspections'); }
    public function storeDocument(Request $r): RedirectResponse { return $this->genericStore($r,'documents'); }
    public function storeEquipment(Request $r): RedirectResponse { return $this->genericStore($r,'equipment'); }
    public function genericStore(Request $r, string $module): RedirectResponse { abort_unless(isset(self::TABLES[$module]),404); $data=$r->validate($this->rules($module)); if($module==='workforce') $data += ['check_in'=>now(), 'qr_code_hash'=>(string) str()->uuid()]; if($module==='site-progress') $data['labor_count']=DB::table('attendances')->where('project_id',$data['project_id'])->whereDate('check_in',$data['report_date'])->where('status','present')->distinct('worker_name')->count('worker_name'); DB::table(self::TABLES[$module])->insert($data+['created_at'=>now(),'updated_at'=>now()]); if($module==='workforce'){ $cost=(float)$data['hourly_rate']*(float)$data['work_hours']; Expense::create(['project_id'=>$data['project_id'],'category'=>'Labor','amount'=>$cost,'payment_method'=>'Work log','date'=>now()->toDateString(),'description'=>$data['worker_name'].' · '.$data['work_hours'].' hours']); } return $this->done($r,$module,$module==='site-progress'?'Report saved; labor count was calculated from attendance.':'Record saved.'); }
    public function generatePayroll(Request $r): RedirectResponse { $data=$r->validate(['month_year'=>'required|date_format:Y-m','base_salary'=>'required|numeric|min:0']); $rows=DB::table('attendances')->where('check_in','like',$data['month_year'].'%')->select('worker_name')->selectRaw('COUNT(DISTINCT DATE(check_in)) days')->groupBy('worker_name')->get(); foreach($rows as $row) DB::table('payrolls')->updateOrInsert(['worker_name'=>$row->worker_name,'month_year'=>$data['month_year']],['role'=>'Worker','total_days_worked'=>$row->days,'base_salary'=>$data['base_salary'],'overtime_pay'=>0,'net_pay'=>round(($data['base_salary']/30)*$row->days,2),'payment_status'=>'pending','created_at'=>now(),'updated_at'=>now()]); return $this->done($r,'workforce','Payroll generated.'); }
    private function rules(string $m): array { return match($m){'procurement'=>['project_id'=>'nullable|exists:projects,id','material_id'=>'required|exists:materials,id','quantity'=>'required|numeric|min:0.01','status'=>'required|max:30'],'workforce'=>['worker_name'=>'required|max:255','project_id'=>'required|exists:projects,id','status'=>'required|max:30','hourly_rate'=>'required|numeric|min:0','work_hours'=>'required|numeric|min:.1|max:24'],'site-progress'=>['project_id'=>'required|exists:projects,id','report_date'=>'required|date','weather_condition'=>'nullable|max:100','labor_count'=>'nullable|integer|min:0','work_summary'=>'required|string','site_supervisor_id'=>'nullable|exists:users,id'],'inspections'=>['project_id'=>'required|exists:projects,id','inspector_name'=>'required|max:255','inspection_type'=>'required|in:Structural,Electrical,Plumbing,Fire Safety,Workplace Safety,Finishing,Monthly Progress Review','result'=>'required|in:Pass,Fail,Needs Action','remarks'=>'nullable|string','inspection_date'=>'required|date'],'documents'=>['project_id'=>'required|exists:projects,id','title'=>'required|max:255','category'=>'required|in:Drawing,Architectural,Structural,Legal','file_path'=>'required|max:500','version'=>'required|max:30','approval_status'=>'required|max:30'],'equipment'=>['name'=>'required|max:255','serial_number'=>'required|unique:equipment,serial_number','category'=>'required|max:100','operational_status'=>'required|in:Active,Under Maintenance,Idle','total_hours_logged'=>'nullable|numeric|min:0','hourly_rate'=>'required|numeric|min:0','last_maintenance'=>'nullable|date','next_maintenance_due'=>'nullable|date'],default=>[]}; }
    private function raiseLowStockRequest(Material $material, ?int $projectId, ?int $userId): void { if((float)$material->current_stock>(float)$material->reorder_level) return; $projectId=$projectId ?? DB::table('projects')->value('id'); $exists=DB::table('purchase_requests')->where('material_id',$material->id)->where(function($q)use($projectId){if($projectId)$q->where('project_id',$projectId)->orWhereNull('project_id');else $q->whereNull('project_id');})->whereIn('finance_status',['pending','held'])->exists(); if(!$exists){ $orderQty=max((float)$material->reorder_level, (float)$material->reorder_level-(float)$material->current_stock+20); if($orderQty<=0) $orderQty=50; DB::table('purchase_requests')->insert(['project_id'=>$projectId,'material_id'=>$material->id,'quantity'=>$orderQty,'requested_by'=>$userId,'status'=>'pending','estimated_cost'=>$orderQty*(float)$material->unit_price,'finance_status'=>'pending','created_at'=>now(),'updated_at'=>now()]); } }
    private function done(Request $r,string $module,string $message,string $action='create'): RedirectResponse { ActivityLog::create(['user_id'=>$r->user()?->id,'action'=>$action,'module'=>$module,'description'=>$message,'ip_address'=>$r->ip(),'timestamp'=>now()]); return back()->with('success',$message); }
}
