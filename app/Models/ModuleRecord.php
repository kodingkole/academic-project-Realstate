<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRecord extends Model
{
    protected $fillable = [
        'module', 'title', 'details', 'status', 'amount', 'quantity', 'due_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }
}
