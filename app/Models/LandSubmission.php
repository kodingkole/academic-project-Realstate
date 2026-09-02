<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LandSubmission extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['submitted_at'=>'datetime','katha_size'=>'decimal:2','asking_price'=>'decimal:2']; }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function lawyer(): BelongsTo { return $this->belongsTo(Lawyer::class, 'assigned_lawyer_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function agreement(): HasOne { return $this->hasOne(JvAgreement::class); }
}
