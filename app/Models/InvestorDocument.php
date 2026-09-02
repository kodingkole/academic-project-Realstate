<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorDocument extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['issued_at' => 'date'];
    }

    public function investor(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
}
