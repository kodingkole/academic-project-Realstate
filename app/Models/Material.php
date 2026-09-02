<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Material extends Model { protected $guarded = []; protected function casts(): array { return ['current_stock'=>'decimal:2','reorder_level'=>'decimal:2','unit_price'=>'decimal:2']; } }
