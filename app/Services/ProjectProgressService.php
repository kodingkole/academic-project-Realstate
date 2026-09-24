<?php

namespace App\Services;

use App\Models\Project;

class ProjectProgressService
{
    public function percentage(Project $project): int
    {
        $tasks = $project->relationLoaded('tasks') ? $project->tasks : $project->tasks()->get();

        if ($tasks->isEmpty()) {
            return (int) ($project->progress_percentage ?? 0);
        }

        return (int) round($tasks->avg('progress'));
    }
}
