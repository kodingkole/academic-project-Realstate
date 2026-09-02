<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Equipment extends Model { protected $table = 'equipment'; protected $guarded = []; protected function casts(): array { return ['last_maintenance'=>'date','next_maintenance_due'=>'date']; } }
