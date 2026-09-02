<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseRequest;

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

    public function autoReorder(?int $userId): int
    {
        $created = 0;
        Material::query()->whereColumn('current_stock', '<', 'reorder_level')->each(function (Material $material) use ($userId, &$created): void {
            $exists = PurchaseRequest::query()->where('material_id', $material->id)->where('status', 'pending')->exists();
            if (! $exists) { PurchaseRequest::create(['material_id'=>$material->id, 'quantity'=>max(1, ($material->reorder_level * 2) - $material->current_stock), 'requested_by'=>$userId, 'status'=>'pending']); $created++; }
        });
        return $created;
    }
}
