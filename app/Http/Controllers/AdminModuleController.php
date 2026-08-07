<?php

namespace App\Http\Controllers;

use App\Models\ModuleRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    public const MODULES = [
        'projects' => ['label' => 'Projects', 'singular' => 'Project', 'icon' => 'P'],
        'tasks' => ['label' => 'Tasks', 'singular' => 'Task', 'icon' => 'T'],
        'inventory' => ['label' => 'Inventory', 'singular' => 'Stock Item', 'icon' => 'I'],
        'finance' => ['label' => 'Finance', 'singular' => 'Transaction', 'icon' => '$'],
        'workforce' => ['label' => 'Workforce', 'singular' => 'Team Member', 'icon' => 'W'],
        'documents' => ['label' => 'Documents', 'singular' => 'Document', 'icon' => 'D'],
    ];

    public function index(string $module): View
    {
        $config = $this->config($module);
        $records = ModuleRecord::where('module', $module)->latest()->paginate(10);

        return view('admin.modules.index', compact('module', 'config', 'records'));
    }

    public function create(string $module): View
    {
        $config = $this->config($module);
        $record = new ModuleRecord(['status' => 'Active']);

        return view('admin.modules.form', compact('module', 'config', 'record'));
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        $this->config($module);
        ModuleRecord::create(['module' => $module] + $this->validated($request));

        return redirect()->route('admin.modules.index', $module)
            ->with('success', 'Record created successfully.');
    }

    public function edit(string $module, ModuleRecord $record): View
    {
        $config = $this->config($module);
        $this->ensureModule($module, $record);

        return view('admin.modules.form', compact('module', 'config', 'record'));
    }

    public function update(Request $request, string $module, ModuleRecord $record): RedirectResponse
    {
        $this->config($module);
        $this->ensureModule($module, $record);
        $record->update($this->validated($request));

        return redirect()->route('admin.modules.index', $module)
            ->with('success', 'Record updated successfully.');
    }

    public function destroy(string $module, ModuleRecord $record): RedirectResponse
    {
        $this->config($module);
        $this->ensureModule($module, $record);
        $record->delete();

        return redirect()->route('admin.modules.index', $module)
            ->with('success', 'Record deleted successfully.');
    }

    private function config(string $module): array
    {
        abort_unless(isset(self::MODULES[$module]), 404);
        return self::MODULES[$module];
    }

    private function ensureModule(string $module, ModuleRecord $record): void
    {
        abort_unless($record->module === $module, 404);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', 'max:50'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'due_date' => ['nullable', 'date'],
        ]);
    }
}
