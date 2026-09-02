<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorPayment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function investor(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function booking(): BelongsTo { return $this->belongsTo(InvestorBooking::class, 'booking_id'); }
}
