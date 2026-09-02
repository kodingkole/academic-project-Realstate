<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JvAgreement extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['allocated_flats_json'=>'array','signed_at'=>'datetime','landowner_share_pct'=>'decimal:2','developer_share_pct'=>'decimal:2']; }
    public function submission(): BelongsTo { return $this->belongsTo(LandSubmission::class, 'land_submission_id'); }
}
