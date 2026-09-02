<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorBooking extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'investment_amount' => 'decimal:2',
            'monthly_installment_amount' => 'decimal:2',
            'next_payment_date' => 'date',
            'forfeited_at' => 'datetime',
        ];
    }

    public function investor(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function payments() { return $this->hasMany(InvestorPayment::class, 'booking_id'); }
}
