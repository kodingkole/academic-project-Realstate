<?php

namespace App\Services;

use App\Models\Project;

class ErpIntelligenceService
{
    public function projectRisk(Project $project): string
    {
        $milestones = $project->milestones;
        $overdue = $milestones->filter(fn ($item) => $item->due_date?->isPast() && $item->completion_percentage < 100)->count();
        $overdueRate = $milestones->count() ? ($overdue / $milestones->count()) * 100 : 0;
        $taskVelocity = (float) $project->tasks->avg('progress');
        return match (true) { $overdueRate >= 50 || ($overdueRate >= 25 && $taskVelocity < 35) => 'Critical', $overdueRate >= 25 || $taskVelocity < 35 => 'High', $overdueRate > 0 || $taskVelocity < 65 => 'Medium', default => 'Low' };
    }

}
