<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorNotification extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    public function investor(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
