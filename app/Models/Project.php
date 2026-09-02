<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Project extends Model { protected $guarded = []; protected function casts(): array { return ['start_date'=>'date','end_date'=>'date','total_budget'=>'decimal:2']; } public function milestones(): HasMany { return $this->hasMany(Milestone::class); } public function tasks(): HasMany { return $this->hasMany(Task::class); } public function expenses(): HasMany { return $this->hasMany(Expense::class); } }
