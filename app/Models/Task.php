<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $guarded = [];

    private const GENERATED_DEFAULTS = [
        'Site preparation' => ['priority' => 'high', 'progress' => 100, 'status' => 'completed'],
        'Foundation' => ['priority' => 'critical', 'progress' => 100, 'status' => 'completed'],
        'Structure and columns' => ['priority' => 'critical', 'progress' => 75, 'status' => 'in_progress'],
        'Brickwork' => ['priority' => 'high', 'progress' => 50, 'status' => 'in_progress'],
        'Electrical' => ['priority' => 'medium', 'progress' => 30, 'status' => 'in_progress'],
        'Plumbing' => ['priority' => 'medium', 'progress' => 25, 'status' => 'in_progress'],
        'Plastering' => ['priority' => 'medium', 'progress' => 0, 'status' => 'pending'],
        'Flooring' => ['priority' => 'low', 'progress' => 0, 'status' => 'pending'],
        'Painting' => ['priority' => 'low', 'progress' => 0, 'status' => 'pending'],
        'Final inspection' => ['priority' => 'critical', 'progress' => 0, 'status' => 'pending'],
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function getProgressAttribute($value): int
    {
        return $this->isUntouchedGeneratedDefault() ? 0 : (int) $value;
    }

    public function getStatusAttribute($value): string
    {
        return $this->isUntouchedGeneratedDefault() ? 'pending' : (string) $value;
    }

    private function isUntouchedGeneratedDefault(): bool
    {
        $default = self::GENERATED_DEFAULTS[$this->attributes['title'] ?? ''] ?? null;

        if (!$default || !isset($this->attributes['created_at'], $this->attributes['updated_at'])) {
            return false;
        }

        return ($this->attributes['priority'] ?? null) === $default['priority']
            && (int) ($this->attributes['progress'] ?? -1) === $default['progress']
            && ($this->attributes['status'] ?? null) === $default['status']
            && $this->attributes['created_at'] === $this->attributes['updated_at'];
    }
}
