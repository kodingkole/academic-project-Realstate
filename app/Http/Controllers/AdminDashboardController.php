<?php

namespace App\Http\Controllers;

use App\Models\ModuleRecord;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the protected admin dashboard.
     */
    public function index(): View
    {
        $projectCount = ModuleRecord::where('module', 'projects')->count();
        $taskCount = ModuleRecord::where('module', 'tasks')->count();

        $statistics = [
            'total_projects' => $projectCount ?: 12,
            'active_projects' => $projectCount
                ? ModuleRecord::where('module', 'projects')->whereIn('status', ['Active', 'In Progress'])->count()
                : 8,
            'total_investors' => 146,
            'pending_tasks' => $taskCount
                ? ModuleRecord::where('module', 'tasks')->where('status', 'Pending')->count()
                : 24,
        ];

        $recentProjects = ModuleRecord::where('module', 'projects')->latest()->take(5)->get()->map(fn ($project) => [
            'name' => $project->title,
            'manager' => $project->details ?: 'Not assigned',
            'status' => $project->status,
            'progress' => $project->status === 'Completed' ? 100 : min($project->quantity ?? 0, 100),
        ]);

        if ($recentProjects->isEmpty()) {
            $recentProjects = collect([
                ['name' => 'Skyline Residence', 'manager' => 'Nadia Rahman', 'status' => 'In Progress', 'progress' => 72],
                ['name' => 'Green Valley Heights', 'manager' => 'Hasan Mahmud', 'status' => 'In Progress', 'progress' => 48],
                ['name' => 'Urban Trade Center', 'manager' => 'Sabbir Ahmed', 'status' => 'Near Completion', 'progress' => 86],
            ]);
        }

        return view(
            'admin.dashboard',
            compact('statistics', 'recentProjects')
        );
    }
}
