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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ErpController extends Controller
{
    private const TABLES=['projects'=>'projects','tasks'=>'tasks','inventory'=>'materials','procurement'=>'purchase_requests','workforce'=>'attendances','finance'=>'expenses','site-progress'=>'daily_progress_reports','inspections'=>'quality_inspections','documents'=>'documents','equipment'=>'equipment'];
    public function index(string $module, ErpIntelligenceService $ai): View { abort_unless(isset(self::TABLES[$module]),404); $records=DB::table(self::TABLES[$module])->latest()->limit(50)->get(); $projects=Project::orderBy('title')->get(); $materials=Material::orderBy('name')->get(); $users=DB::table('users')->orderBy('name')->get(); $insights=$module==='projects' ? Project::with(['milestones','tasks'])->get()->mapWithKeys(fn($p)=>[$p->id=>$ai->projectRisk($p)]) : collect(); return view('erp.module',compact('module','records','projects','materials','users','insights')); }
    public function storeProject(Request $r): RedirectResponse { Project::create($r->validate(['title'=>'required|max:255','location'=>'required|max:255','division'=>'nullable|max:100','total_budget'=>'required|numeric|min:0','land_area'=>'nullable|numeric|min:0','status'=>'required|in:planned,active,completed,delayed','start_date'=>'nullable|date','end_date'=>'nullable|date|after_or_equal:start_date'])); return $this->done($r,'projects','Project created.'); }
    public function updateProject(Request $r, Project $project): RedirectResponse
    {
        $data = $r->validate(['status'=>'required|in:planned,active,completed,delayed','progress_percentage'=>'required|integer|between:0,100','end_date'=>'nullable|date']);
        $changed = (int) $project->progress_percentage !== (int) $data['progress_percentage'] || $project->status !== $data['status'];
        $project->update($data);

        if ($changed) {
            InvestorBooking::where('project_id', $project->id)->pluck('user_id')->unique()->each(function ($userId) use ($project): void {
                InvestorNotification::create(['user_id'=>$userId, 'title'=>'Project progress updated', 'message'=>$project->title.' is now '.$project->progress_percentage.'% complete ('.ucfirst($project->status).').', 'type'=>'project']);
            });
        }

        return $this->done($r, 'projects', 'Project status and progress updated.', 'update');
    }
    public function storeMilestone(Request $r, Project $project): RedirectResponse { $project->milestones()->create($r->validate(['title'=>'required|max:255','due_date'=>'required|date','status'=>'required|max:30','completion_percentage'=>'required|integer|between:0,100'])); return $this->done($r,'projects','Milestone added.'); }
    public function storeTask(Request $r): RedirectResponse { Task::create($r->validate(['project_id'=>'required|exists:projects,id','title'=>'required|max:255','assigned_to'=>'nullable|exists:users,id','priority'=>'required|in:low,medium,high,critical','start_date'=>'nullable|date','end_date'=>'nullable|date','progress'=>'nullable|integer|between:0,100','status'=>'required|max:30'])); return $this->done($r,'tasks','Task created.'); }
    public function updateTaskProgress(Request $r, Task $task): RedirectResponse { $data=$r->validate(['progress'=>'required|integer|between:0,100']); $task->update($data+['status'=>$data['progress']===100?'completed':'in_progress']); return $this->done($r,'tasks','Progress updated.'); }
    public function updateTask(Request $r, Task $task): RedirectResponse { $task->update($r->validate(['project_id'=>'required|exists:projects,id','title'=>'required|max:255','assigned_to'=>'nullable|exists:users,id','priority'=>'required|in:low,medium,high,critical','start_date'=>'nullable|date','end_date'=>'nullable|date','progress'=>'nullable|integer|between:0,100','status'=>'required|max:30'])); return $this->done($r,'tasks','Task updated.','update'); }
    public function destroyTask(Request $r, Task $task): RedirectResponse { $title=$task->title; $task->delete(); return $this->done($r,'tasks',"Task '{$title}' deleted.",'delete'); }
    public function storeMaterial(Request $r): RedirectResponse { Material::create($r->validate(['name'=>'required|max:255','category'=>'required|max:100','current_stock'=>'required|numeric|min:0','unit'=>'required|max:30','reorder_level'=>'required|numeric|min:0','unit_price'=>'required|numeric|min:0'])); return $this->done($r,'inventory','Material created.'); }
    public function updateMaterial(Request $r, Material $material): RedirectResponse { $material->update($r->validate(['name'=>'required|max:255','category'=>'required|max:100','current_stock'=>'required|numeric|min:0','unit'=>'required|max:30','reorder_level'=>'required|numeric|min:0','unit_price'=>'required|numeric|min:0'])); return $this->done($r,'inventory','Material updated.','update'); }
    public function destroyMaterial(Request $r, Material $material): RedirectResponse { $name=$material->name; $material->delete(); return $this->done($r,'inventory',"Material '{$name}' deleted.",'delete'); }
    public function stockTransaction(Request $r): RedirectResponse { $data=$r->validate(['material_id'=>'required|exists:materials,id','project_id'=>'nullable|exists:projects,id','type'=>'required|in:IN,OUT,TRANSFER','quantity'=>'required|numeric|min:0.01','date'=>'required|date','notes'=>'nullable|string']); DB::transaction(function()use($data){ $m=Material::lockForUpdate()->findOrFail($data['material_id']); if($data['type']==='OUT'&&$m->current_stock<$data['quantity']) abort(422,'Insufficient stock.'); $m->increment('current_stock',$data['type']==='IN'?$data['quantity']:-$data['quantity']); InventoryTransaction::create($data); }); return $this->done($r,'inventory','Stock ledger updated.'); }
    public function autoReorder(Request $r, ErpIntelligenceService $ai): RedirectResponse { $count=$ai->autoReorder($r->user()?->id); return $this->done($r,'inventory',"{$count} purchase requests generated."); }
    public function storeExpense(Request $r): RedirectResponse { Expense::create($r->validate(['project_id'=>'required|exists:projects,id','category'=>'required|in:Materials,Labor,Equipment,Legal,Overhead','amount'=>'required|numeric|min:0','payment_method'=>'required|max:50','date'=>'required|date','description'=>'nullable|string'])); return $this->done($r,'finance','Expense recorded.'); }
    public function storeSupplier(Request $r): RedirectResponse { DB::table('suppliers')->insert($r->validate(['name'=>'required|max:255','contact_person'=>'nullable|max:255','phone'=>'nullable|max:50','email'=>'nullable|email','rating'=>'nullable|numeric|between:0,5','active_status'=>'nullable|boolean'])+['created_at'=>now(),'updated_at'=>now()]); return $this->done($r,'procurement','Supplier added.'); }
    public function storePurchaseRequest(Request $r): RedirectResponse { return $this->genericStore($r,'procurement'); }
    public function createPurchaseOrder(Request $r): RedirectResponse { $data=$r->validate(['project_id'=>'nullable|exists:projects,id','supplier_id'=>'required|exists:suppliers,id','material_id'=>'required|exists:materials,id','quantity'=>'required|numeric|min:.01','total_cost'=>'required|numeric|min:0','status'=>'required|in:issued,delivered,cancelled']); DB::table('purchase_orders')->insert($data+['created_at'=>now(),'updated_at'=>now()]); return $this->done($r,'procurement','Purchase order issued.'); }
    public function recordQrAttendance(Request $r): RedirectResponse { return $this->genericStore($r,'workforce'); }
    public function storeReport(Request $r): RedirectResponse { return $this->genericStore($r,'site-progress'); }
    public function storeInspection(Request $r): RedirectResponse { return $this->genericStore($r,'inspections'); }
    public function storeDocument(Request $r): RedirectResponse { return $this->genericStore($r,'documents'); }
    public function storeEquipment(Request $r): RedirectResponse { return $this->genericStore($r,'equipment'); }
    public function genericStore(Request $r, string $module): RedirectResponse { abort_unless(isset(self::TABLES[$module]),404); $rules=$this->rules($module); DB::table(self::TABLES[$module])->insert($r->validate($rules)+['created_at'=>now(),'updated_at'=>now()]); return $this->done($r,$module,'Record saved.'); }
    public function generatePayroll(Request $r): RedirectResponse { $data=$r->validate(['month_year'=>'required|date_format:Y-m','base_salary'=>'required|numeric|min:0']); $rows=DB::table('attendances')->where('check_in','like',$data['month_year'].'%')->select('worker_name')->selectRaw('COUNT(DISTINCT DATE(check_in)) days')->groupBy('worker_name')->get(); foreach($rows as $row) DB::table('payrolls')->updateOrInsert(['worker_name'=>$row->worker_name,'month_year'=>$data['month_year']],['role'=>'Worker','total_days_worked'=>$row->days,'base_salary'=>$data['base_salary'],'overtime_pay'=>0,'net_pay'=>round(($data['base_salary']/30)*$row->days,2),'payment_status'=>'pending','created_at'=>now(),'updated_at'=>now()]); return $this->done($r,'workforce','Payroll generated.'); }
    private function rules(string $m): array { return match($m){'procurement'=>['project_id'=>'nullable|exists:projects,id','material_id'=>'required|exists:materials,id','quantity'=>'required|numeric|min:0.01','status'=>'required|max:30'],'workforce'=>['worker_name'=>'required|max:255','project_id'=>'required|exists:projects,id','qr_code_hash'=>'required|max:255','status'=>'required|max:30','check_in'=>'required|date'],'site-progress'=>['project_id'=>'required|exists:projects,id','report_date'=>'required|date','weather_condition'=>'nullable|max:100','labor_count'=>'required|integer|min:0','work_summary'=>'required|string','site_supervisor_id'=>'nullable|exists:users,id'],'inspections'=>['project_id'=>'required|exists:projects,id','inspector_name'=>'required|max:255','inspection_type'=>'required|max:100','result'=>'required|in:Pass,Fail,Needs Action','remarks'=>'nullable|string','inspection_date'=>'required|date'],'documents'=>['project_id'=>'required|exists:projects,id','title'=>'required|max:255','category'=>'required|in:Drawing,Architectural,Structural,Legal','file_path'=>'required|max:500','version'=>'required|max:30','approval_status'=>'required|max:30'],'equipment'=>['name'=>'required|max:255','serial_number'=>'required|unique:equipment,serial_number','category'=>'required|max:100','operational_status'=>'required|in:Active,Under Maintenance,Idle','total_hours_logged'=>'nullable|numeric|min:0','last_maintenance'=>'nullable|date','next_maintenance_due'=>'nullable|date'],default=>[]}; }
    private function done(Request $r,string $module,string $message,string $action='create'): RedirectResponse { ActivityLog::create(['user_id'=>$r->user()?->id,'action'=>$action,'module'=>$module,'description'=>$message,'ip_address'=>$r->ip(),'timestamp'=>now()]); return back()->with('success',$message); }
}
