@extends('layouts.portal') @section('page-heading',ucwords(str_replace('-',' ',$module)))
@section('content')
<div class="erp-hero compact"><div><span class="erp-kicker">CONSTRUCTION ERP</span><h2>{{ ucwords(str_replace('-',' ',$module)) }}</h2><p>Live operational records, actions and exception monitoring.</p></div>@if($module !== 'documents' && $module !== 'tasks')<button class="erp-button" data-modal-open="recordModal">+ {{ $module === 'projects' ? 'Add project' : ($module === 'inventory' ? 'Add material' : 'Add record') }}</button>@endif</div>
@if($module==='tasks')<section class="erp-kpis"><a href="#task-records" class="erp-kpi-link" data-task-filter="all"><span>Total tasks</span><b>{{ $taskSummary['total'] }}</b><small>Show all construction work items</small></a><a href="#task-records" class="erp-kpi-link" data-task-filter="pending"><span>Pending</span><b>{{ $taskSummary['pending'] }}</b><small>Show tasks not started</small></a><a href="#task-records" class="erp-kpi-link" data-task-filter="in_progress"><span>In progress</span><b>{{ $taskSummary['in_progress'] }}</b><small>Show currently running tasks</small></a><a href="#task-records" class="erp-kpi-link" data-task-filter="all"><span>Average progress</span><b>{{ $taskSummary['average_progress'] }}%</b><small>Show all tasks</small></a></section><section class="erp-panel"><div class="erp-panel-head"><div><h3>Construction task tracker</h3><p>Manage work items or auto-generate the 10 standard construction phases for projects.</p></div><div style="display:flex;gap:10px;align-items:center;"><form method="POST" action="{{ route('erp.tasks.generate') }}">@csrf<button type="submit" class="erp-button" style="background:#0284c7;">Auto-Generate Standard Tasks</button></form><button class="erp-button" data-modal-open="recordModal">+ Add construction task</button></div></div></section>@endif
@if($module === 'procurement')
<section class="erp-panel"><div class="erp-panel-head"><div><h3>Recent Records</h3><p>{{ $records->count() }} active purchase request(s)</p></div></div><div class="erp-table-wrap"><table class="erp-table"><thead><tr><th>ID</th><th>Project</th><th>Product</th><th>Quantity</th><th>Estimated cost</th><th>Status</th></tr></thead><tbody>
@forelse($records as $record)<tr><td>{{ $record->id }}</td><td>{{ $record->project }}</td><td>{{ $record->material }}</td><td>{{ (float) $record->quantity }}</td><td>BDT {{ number_format((float) $record->estimated_cost) }}</td><td><form method="POST" action="{{ route('erp.procurement.requests.status', $record->id) }}" class="procurement-status-form">@csrf @method('PATCH')<select name="status" aria-label="Status for {{ $record->material }}">@foreach(['pending', 'ordered', 'received', 'cancelled'] as $status)<option value="{{ $status }}" @selected($record->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="erp-secondary" type="submit">Save</button></form></td></tr>
@empty<tr><td colspan="6" class="erp-empty">No active purchase requests yet.</td></tr>@endforelse
</tbody></table></div></section>
@else
@if(in_array($module, ['tasks','workforce','finance','site-progress','inspections','documents'], true))
<section class="erp-panel"><div class="erp-panel-head"><div><h3>{{ $module === 'workforce' ? 'Worker work log' : 'Recent records' }}</h3><p>{{ $module === 'workforce' ? 'Worker assignments and hours worked.' : 'Project records shown with project names.' }}</p></div></div><div class="erp-table-wrap"><table class="erp-table"><thead><tr><th>Project</th><th>{{ $module === 'workforce' ? 'Worker' : 'Record' }}</th><th>Status</th><th>Details</th><th>Last update</th></tr></thead><tbody>@forelse($records as $record)<tr><td><b>{{ $projects->firstWhere('id', $record->project_id)?->title ?? 'Project unavailable' }}</b></td><td>{{ $module === 'workforce' ? $record->worker_name : ($record->title ?? $record->category ?? $record->inspection_type ?? 'Update') }}</td><td><span class="erp-pill">{{ ucfirst($record->status ?? $record->approval_status ?? $record->result ?? 'recorded') }}</span></td><td>@if($module === 'tasks'){{ $record->progress }}% complete · {{ ucfirst($record->priority) }} priority @elseif($module === 'workforce'){{ (float) $record->work_hours }} hours · BDT {{ number_format((float) $record->hourly_rate) }}/hour @elseif($module === 'finance')BDT {{ number_format((float) $record->amount) }} · {{ $record->payment_method }} @elseif($module === 'site-progress'){{ \Illuminate\Support\Str::limit($record->work_summary, 55) }} @elseif($module === 'inspections'){{ $record->inspector_name }} · {{ $record->inspection_type }} @else{{ $record->category ?? 'Document' }} @endif</td><td>{{ \Carbon\Carbon::parse($record->updated_at ?? $record->created_at)->format('d M Y') }}</td></tr>@empty<tr><td colspan="5" class="erp-empty">No records yet.</td></tr>@endforelse</tbody></table></div></section>
@else
<section class="erp-panel"><div class="erp-panel-head"><div><h3>Recent Records</h3><p>{{ $records->count() }} latest entries</p></div></div><div class="erp-table-wrap"><table class="erp-table"><thead><tr>@if($records->isNotEmpty()) @foreach(array_slice(array_keys((array)$records->first()),0,7) as $column)<th>{{ str_replace('_',' ',$column) }}</th>@endforeach @endif</tr></thead><tbody>@forelse($records as $record)<tr>@foreach(array_slice((array)$record,0,7) as $value)<td>@if(is_numeric($value) && (!is_string($value) || !str_starts_with($value, '0') || $value === '0' || str_starts_with($value, '0.'))){{ floor((float)$value) == (float)$value ? number_format((float)$value) : rtrim(rtrim((string)$value, '0'), '.') }}@elseif(is_string($value)){{ \Illuminate\Support\Str::limit($value,35) }}@else{{ $value }}@endif</td>@endforeach</tr>@empty<tr><td class="erp-empty">@if($module==='documents') No document records yet. @elseif($module==='tasks') No tasks recorded yet. Use “+ Add construction task” to begin. @else No records yet. Use “Add record” to begin. @endif</td></tr>@endforelse</tbody></table></div></section>
@endif
@endif
<dialog class="erp-modal" id="recordModal"><form method="POST" action="{{ $module==='projects'?route('erp.projects.store'):($module==='tasks'?route('erp.tasks.store'):($module==='inventory'?route('erp.inventory.materials'):($module==='finance'?route('erp.finance.expenses'):route('erp.'.$module.'.store')))) }}">@csrf<div class="erp-modal-head"><div><span class="erp-kicker">QUICK ENTRY</span><h3>{{ $module === 'projects' ? 'Add project' : ($module === 'tasks' ? 'New Construction Task' : 'New ' . ucfirst($module) . ' record') }}</h3></div><button type="button" data-modal-close>×</button></div><div class="erp-form">
@if($module==='projects')<label>Project title<input name="title" placeholder="Enter the project name" required></label><label>Location<input name="location" required></label><label>Division<select name="division" required><option value="">Select division</option>@foreach(['Dhaka','Chittagong','Rajshahi','Sylhet','Khulna','Barisal','Rangpur','Mymensingh'] as $division)<option value="{{ $division }}">{{ $division }}</option>@endforeach</select></label><label>Total budget (BDT)<input type="number" name="total_budget" placeholder="Minimum 10,000,000 BDT" required min="10000000" step="100000"><small>Minimum: BDT 10,000,000 (1 crore)</small></label><label>Land area (sqft)<input type="number" name="land_area" placeholder="Minimum 1,440 sqft" required min="1440"><small>Minimum: 1,440 sqft (2 Katha)</small></label>
@elseif($module==='tasks')<label>Project<select name="project_id" required>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></label><label>Task title<input name="title" required></label><label>Assigned to<select name="assigned_to"><option value="">Unassigned</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></label><label>Priority<select name="priority"><option>low</option><option selected>medium</option><option>high</option><option>critical</option></select></label><label>Progress<input type="number" name="progress" value="0" min="0" max="100"></label><label>Status<input name="status" value="pending"></label>
@elseif($module==='inventory')<label>Name<input name="name" required></label><label>Category<input name="category" required></label><label>Current stock<input type="number" step="any" name="current_stock" required><small>How much is physically in the store now.</small></label><label>Unit<input name="unit" placeholder="bags, ton, pcs" required><small>Use the same unit when recording usage.</small></label><label>Reorder level<input type="number" step="any" name="reorder_level" required><small>When stock reaches this amount, a purchase request is raised.</small></label><label>Unit price (BDT)<input type="number" step="any" name="unit_price" required><small>Approximate price for one bag, ton, or piece.</small></label>
@elseif($module==='finance')<label>Project<select name="project_id" required>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></label><label>Category<select name="category">@foreach(['Materials','Labor','Equipment','Legal','Overhead'] as $v)<option>{{ $v }}</option>@endforeach</select></label><label>Amount<input type="number" step="any" name="amount" required></label><label>Payment method<input name="payment_method" value="Bank Transfer" required></label><label>Date<input type="date" name="date" value="{{ now()->toDateString() }}" required></label><label>Description<textarea name="description"></textarea></label>
@elseif($module==='equipment')<label>Name<input name="name" placeholder="Excavator, concrete mixer, crane" required></label><label>Serial number<input name="serial_number" required></label><label>Category<input name="category" placeholder="Earthwork / lifting / concrete" required></label><label>Status<select name="operational_status"><option>Active</option><option>Under Maintenance</option><option>Idle</option></select></label><label>Hours logged<input type="number" name="total_hours_logged" value="0"></label><label>Hourly rental / operating rate<input type="number" step="any" name="hourly_rate" value="0" required></label><label>Next maintenance<input type="date" name="next_maintenance_due"></label>
@elseif($module==='procurement')<label>Project<select name="project_id"><option value="">General stock</option>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></label><label>Material<select name="material_id" required>@foreach($materials as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select></label><label>Quantity<input type="number" step="any" name="quantity" required></label><label>Status<input name="status" value="pending" required></label>
@elseif($module==='workforce')<label>Worker name<input name="worker_name" required></label><label>Project / flat<select name="project_id" required>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></label><label>Hourly rate (BDT)<input type="number" name="hourly_rate" value="60" min="0" step="any" readonly style="background:#f1f5f9;cursor:not-allowed;" required></label><label>Hours worked<input type="number" name="work_hours" min=".1" max="24" step="any" value="8" required></label><label>Status<input name="status" value="present"></label>
@elseif($module==='site-progress')<label>Project / flat<select name="project_id" required>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></label><label>Report date<input type="date" name="report_date" value="{{ now()->toDateString() }}" required></label><label>Weather<input name="weather_condition" value="Clear"></label><input type="hidden" name="labor_count" value="0"><label class="wide">Work summary<textarea name="work_summary" required></textarea></label>
@elseif($module==='inspections')<label>Project / flat<select name="project_id" required>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></label><label>Inspector<input name="inspector_name" required></label><label>Inspection type<select name="inspection_type">@foreach(['Structural','Electrical','Plumbing','Fire Safety','Workplace Safety','Finishing','Monthly Progress Review'] as $type)<option>{{ $type }}</option>@endforeach</select></label><label>Result<select name="result"><option>Pass</option><option>Fail</option><option>Needs Action</option></select></label><label>Inspection date<input type="date" name="inspection_date" value="{{ now()->toDateString() }}" required></label><label>Remarks<textarea name="remarks"></textarea></label>
@elseif($module==='documents')<label>Project<select name="project_id" required>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></label><label>Title<input name="title" required></label><label>Category<select name="category"><option>Drawing</option><option>Architectural</option><option>Structural</option><option>Legal</option></select></label><label>File path<input name="file_path" required></label><label>Version<input name="version" value="1.0"></label><label>Approval status<input name="approval_status" value="pending"></label>@endif
</div><div class="erp-modal-actions"><button type="button" data-modal-close>Cancel</button><button class="erp-button">{{ $module === 'projects' ? 'Save project' : ($module === 'tasks' ? 'Save task' : 'Save record') }}</button></div></form></dialog>
@if($module==='inventory')
<section class="erp-panel" style="margin-top:24px">
    <div class="erp-panel-head">
        <div>
            <span class="erp-kicker">STEP 1 · RECORD WHAT WAS USED</span>
            <h3>Use material from stock</h3>
            <p>Enter the amount actually used on a project. This immediately subtracts it from Current stock. Project estimates below do not subtract stock.</p>
        </div>
        <form method="POST" action="{{ route('erp.inventory.reorder') }}">
            @csrf
            <button class="erp-secondary" type="submit">Check low stock for purchase</button>
        </form>
    </div>
    <form method="POST" action="{{ route('erp.inventory.transaction') }}" class="erp-form">
        @csrf
        <input type="hidden" name="type" value="OUT">
        <label>Which project used it?<select name="project_id" required>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></label>
        <label>Material<select name="material_id" required>@foreach($materials as $m)<option value="{{ $m->id }}">{{ $m->name }} (Current: {{ (float)$m->current_stock }} {{ $m->unit }} | Reorder at: {{ (float)$m->reorder_level }})</option>@endforeach</select></label>
        <label>Amount used<input type="number" step="any" min="0.01" name="quantity" placeholder="e.g. 25 bags" required></label>
        <label>Date<input type="date" name="date" value="{{ now()->toDateString() }}" required></label>
        <label class="wide">Where was it used? (optional)<input name="notes" placeholder="e.g. 2nd floor pillar work"></label>
        <div class="wide"><button class="erp-button">Save usage and update stock</button></div>
    </form>
</section>
<section class="erp-two" style="margin-top:24px"><section class="erp-panel"><div class="erp-panel-head"><div><span class="erp-kicker">OPTIONAL · PLANNING ONLY</span><h3>Estimate project material needs</h3><p>This is a forecast. Saving it does not change stock or create a purchase request.</p></div></div><form method="POST" action="{{ route('erp.inventory.requirements') }}" class="erp-form">@csrf<label>Project<select name="project_id">@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></label><label>Material<select name="material_id">@foreach($materials as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select></label><label>Total amount this project may need<input type="number" name="planned_quantity" min="0" step="any" required></label><label>Estimated amount used per day<input type="number" name="daily_quantity" min="0" step="any" required></label><div class="wide"><button class="erp-button">Save estimate</button></div></form></section><section class="erp-panel"><div class="erp-panel-head"><div><span class="erp-kicker">SAVED ESTIMATES</span><h3>Project material estimates</h3><p>Stock shown here is the shared store balance across projects.</p></div></div>@forelse($requirements as $requirement)<div class="health-row"><div><b>{{ $requirement->material_name }}</b><small>{{ $requirement->project_title }} · Estimated total: {{ (float)$requirement->planned_quantity }} {{ $requirement->unit }} · Per day: {{ (float)$requirement->daily_quantity }} {{ $requirement->unit }}</small></div><span class="erp-pill">{{ (float)$requirement->current_stock }} left</span></div>@empty<p class="erp-empty">No estimates saved. You can skip this section if you do not need forecasts.</p>@endforelse</section></section><section class="erp-panel" style="margin-top:24px"><div class="erp-panel-head"><div><span class="erp-kicker">STEP 2 · WHEN STOCK IS LOW</span><h3>Purchase requests</h3><p>A request is created when recorded usage brings stock to or below its reorder level. Procurement tracks order and delivery; Finance checks funding separately.</p></div></div>@forelse($purchaseRequests as $request)<div class="health-row"><div><b>{{ $request->material_name }} · {{ (float)$request->quantity }}</b><small>{{ $request->project_title ?? 'Shared stock' }} · Estimated BDT {{ number_format((float)$request->estimated_cost) }} · Order: {{ ucfirst($request->status) }} · Funding: {{ ucfirst($request->finance_status) }}</small></div><span class="erp-pill">{{ ucfirst($request->status) }}</span></div>@empty<p class="erp-empty">No purchase requests yet. A request appears here after stock reaches its reorder level.</p>@endforelse</section>
<section class="erp-panel" style="margin-top:24px"><div class="erp-panel-head"><div><span class="erp-kicker">STEP 3 · AFTER DELIVERY</span><h3>Receive purchased material</h3><p>Open Procurement and set the request to “Received” only after the material arrives. Its requested quantity will then be added to shared stock.</p></div></div></section>
@endif
@if($module==='finance')
<section class="erp-panel" style="margin-top:24px"><div class="erp-panel-head"><div><h3>Procurement funding approval</h3><p>Only approve when verified investor payments cover the request. Approval adds the amount to the project budget.</p></div></div>@forelse($purchaseRequests as $request)<div class="health-row"><div><b>{{ $request->material_name }} · {{ (float)$request->quantity }}</b><small>{{ $request->project_title ?? 'General stock' }} · Estimated BDT {{ number_format($request->estimated_cost) }} · {{ ucfirst($request->finance_status) }}</small></div><div>@if($request->project_id)<small>Available: BDT {{ number_format($fundedProjects[$request->project_id] ?? 0) }}</small>@endif @if($request->finance_status!=='approved')<form method="POST" action="{{ route('erp.finance.procurement.approve',$request->id) }}">@csrf<button class="erp-button">Check & approve</button></form>@endif</div></div>@empty<p class="erp-empty">No procurement requests waiting for funding.</p>@endforelse</section>
@endif
@if($module==='equipment')
<section class="erp-panel" style="margin-top:24px"><div class="erp-panel-head"><div><h3>Assign rented / company equipment</h3><p>Usage hours × hourly rate are charged automatically to the selected project budget.</p></div></div><form method="POST" action="{{ route('erp.equipment.usage') }}" class="erp-form">@csrf<label>Project<select name="project_id">@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></label><label>Equipment<select name="equipment_id">@foreach(DB::table('equipment')->where('operational_status','Active')->get() as $equipment)<option value="{{ $equipment->id }}">{{ $equipment->name }} · BDT {{ (float)$equipment->hourly_rate }}/hour</option>@endforeach</select></label><label>Hours used<input type="number" name="hours_used" min=".1" step="any" required></label><label>Date<input type="date" name="usage_date" value="{{ now()->toDateString() }}" required></label><label class="wide">Notes<input name="notes" placeholder="e.g. foundation excavation"></label><div class="wide"><button class="erp-button">Record usage & charge project</button></div></form></section>
@endif
@if($module==='projects')
<section class="erp-panel" style="margin-top:24px;">
    <div class="erp-panel-head"><div><h3>Project Control</h3><p>Deactivate a project to hide it from dashboards. Activate it again to restore visibility.</p></div></div>
    <div class="erp-table-wrap"><table class="erp-table"><thead><tr><th>Project</th><th>Location</th><th>Status</th><th>Control</th></tr></thead><tbody>
    @foreach($records as $record)
        <tr><td><b>{{ $record->title }}</b></td><td>{{ $record->location }}</td><td>{{ ucfirst($record->status) }}</td><td><form method="POST" action="{{ route('erp.projects.update', $record->id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $record->status === 'active' ? 'inactive' : 'active' }}"><button type="submit" class="erp-secondary">{{ $record->status === 'active' ? 'Deactivate' : 'Activate' }}</button></form></td></tr>
    @endforeach
    </tbody></table></div>
</section>
@endif
@if($module==='tasks')
@foreach($records as $record)<dialog class="erp-modal" id="editTasksModal-{{ $record->id }}"><form method="POST" action="{{ route('erp.tasks.update',$record->id) }}">@csrf @method('PUT')<div class="erp-modal-head"><div><span class="erp-kicker">ADMIN CONTROL</span><h3>Edit task</h3></div><button type="button" data-modal-close>Close</button></div><div class="erp-form"><label>Project<select name="project_id">@foreach($projects as $p)<option value="{{ $p->id }}" @selected($record->project_id==$p->id)>{{ $p->title }}</option>@endforeach</select></label><label>Task title<input name="title" value="{{ $record->title }}" required></label><label>Assigned to<select name="assigned_to"><option value="">Unassigned</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected($record->assigned_to==$u->id)>{{ $u->name }}</option>@endforeach</select></label><label>Priority<select name="priority">@foreach(['low','medium','high','critical'] as $priority)<option value="{{ $priority }}" @selected($record->priority===$priority)>{{ $priority }}</option>@endforeach</select></label><label>Start date<input type="date" name="start_date" value="{{ $record->start_date }}"></label><label>End date<input type="date" name="end_date" value="{{ $record->end_date }}"></label><label>Progress<input type="number" name="progress" min="0" max="100" value="{{ $record->progress }}"></label><label>Status<input name="status" value="{{ $record->status }}" required></label></div><div class="erp-modal-actions"><button type="button" data-modal-close>Cancel</button><button class="erp-button">Save changes</button></div></form></dialog>@endforeach
<section class="erp-panel"><div class="erp-panel-head"><div><h3>Admin controls</h3><p>Edit or delete tasks.</p></div></div>@foreach($records as $record)<div class="health-row"><b>{{ $record->title }}</b><div class="erp-actions"><button type="button" data-modal-open="editTasksModal-{{ $record->id }}">Edit</button><form method="POST" action="{{ route('erp.tasks.destroy',$record->id) }}" onsubmit="return confirm('Delete this task?')">@csrf @method('DELETE')<button class="danger">Delete</button></form></div></div>@endforeach</section>
@elseif($module==='inventory')
@foreach($records as $record)<dialog class="erp-modal" id="editInventoryModal-{{ $record->id }}"><form method="POST" action="{{ route('erp.inventory.materials.update',$record->id) }}">@csrf @method('PUT')<div class="erp-modal-head"><div><span class="erp-kicker">ADMIN CONTROL</span><h3>Edit material</h3></div><button type="button" data-modal-close>Close</button></div><div class="erp-form"><label>Name<input name="name" value="{{ $record->name }}" required></label><label>Category<input name="category" value="{{ $record->category }}" required></label><label>Current stock<input type="number" step="any" name="current_stock" value="{{ (float)$record->current_stock }}" required></label><label>Unit<input name="unit" value="{{ $record->unit }}" required></label><label>Reorder level<input type="number" step="any" name="reorder_level" value="{{ (float)$record->reorder_level }}" required></label><label>Unit price<input type="number" step="any" name="unit_price" value="{{ (float)$record->unit_price }}" required></label></div><div class="erp-modal-actions"><button type="button" data-modal-close>Cancel</button><button class="erp-button">Save changes</button></div></form></dialog>@endforeach
<section class="erp-panel"><div class="erp-panel-head"><div><h3>Admin controls</h3><p>Edit or delete materials.</p></div></div>@foreach($records as $record)<div class="health-row"><b>{{ $record->name }}</b><div class="erp-actions"><button type="button" data-modal-open="editInventoryModal-{{ $record->id }}">Edit</button><form method="POST" action="{{ route('erp.inventory.materials.destroy',$record->id) }}" onsubmit="return confirm('Delete this material?')">@csrf @method('DELETE')<button class="danger">Delete</button></form></div></div>@endforeach</section>
@endif
@if($module==='tasks')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const panels = document.querySelectorAll('.erp-panel');
    const controlsPanel = panels[panels.length - 1];
    const actions = controlsPanel?.querySelectorAll('.erp-actions') ?? [];
    const rows = document.querySelectorAll('.erp-table tbody tr');
    if (!actions.length || actions.length !== rows.length) return;

    const header = document.querySelector('.erp-table thead tr');
    const actionHeader = document.createElement('th');
    actionHeader.textContent = 'Actions';
    header?.appendChild(actionHeader);

    actions.forEach((action, index) => {
        const cell = document.createElement('td');
        cell.appendChild(action);
        rows[index].appendChild(cell);
    });
    controlsPanel.remove();
});
</script>
@endif
@if($module==='documents' && $soldUnits->isNotEmpty())
<section class="erp-panel" style="margin-top:24px">
    <div class="erp-panel-head">
        <div>
            <h3>Sold Units — Buyer & Payment Summary</h3>
            <p>{{ $soldUnits->count() }} units sold · BDT {{ number_format($soldUnits->sum('total_paid')) }} collected · BDT {{ number_format($soldUnits->sum('due')) }} outstanding</p>
        </div>
    </div>
    <div class="erp-table-wrap">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Unit</th>
                    <th>Project</th>
                    <th>Buyer</th>
                    <th>Contact</th>
                    <th style="text-align:right">Total (BDT)</th>
                    <th style="text-align:right">Paid (BDT)</th>
                    <th style="text-align:right">Due (BDT)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($soldUnits as $unit)
                <tr>
                    <td><b>{{ $unit->unit_no }}</b></td>
                    <td style="font-size:12px;color:#64748b">{{ $unit->project_title }}</td>
                    <td><b>{{ $unit->buyer_name }}</b></td>
                    <td style="font-size:11px;color:#64748b">{{ $unit->buyer_email }}<br>{{ $unit->buyer_phone }}</td>
                    <td style="text-align:right">BDT {{ number_format($unit->investment_amount) }}</td>
                    <td style="text-align:right;color:#047857;font-weight:700">BDT {{ number_format($unit->total_paid) }}</td>
                    <td style="text-align:right;color:{{ $unit->due > 0 ? '#dc2626' : '#64748b' }};font-weight:700">
                        {{ $unit->due > 0 ? 'BDT '.number_format($unit->due) : '—' }}
                    </td>
                    <td>
                        <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:800;background:{{ $unit->due <= 0 ? '#ecfdf5' : '#fef3c7' }};color:{{ $unit->due <= 0 ? '#047857' : '#92400e' }};">
                            {{ $unit->due <= 0 ? 'Fully Paid' : 'Installment' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
@if($module === 'tasks')
@push('scripts')
<script>
(() => {
    const cards = document.querySelectorAll('[data-task-filter]');
    const table = [...document.querySelectorAll('.erp-table')].find((item) =>
        item.tHead?.rows[0] && [...item.tHead.rows[0].cells].some((cell) => cell.textContent.trim() === 'Last update')
    );
    if (!cards.length || !table) return;

    const panel = table.closest('.erp-panel');
    panel.id = 'task-records';
    const rows = [...table.tBodies[0].rows];
    let emptyRow = document.getElementById('task-filter-empty');
    if (!emptyRow) {
        emptyRow = table.tBodies[0].insertRow();
        emptyRow.innerHTML = '<td colspan="5" class="erp-empty">No tasks match this filter.</td>';
        emptyRow.hidden = true;
    }

    cards.forEach((card) => card.addEventListener('click', () => {
        const filter = card.dataset.taskFilter;
        let visible = 0;
        rows.forEach((row) => {
            const isTaskRow = row.cells.length === 5;
            const rowStatus = row.cells[2].textContent.trim().toLowerCase().replaceAll('_', ' ');
            const matches = isTaskRow && (filter === 'all' || rowStatus === filter.replace('_', ' '));
            row.hidden = !matches;
            if (matches) visible++;
        });
        emptyRow.hidden = visible > 0;
    }));
})();
</script>
@endpush
@endif
@endsection
