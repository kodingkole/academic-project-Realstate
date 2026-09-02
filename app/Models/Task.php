<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Task extends Model { protected $guarded = []; protected function casts(): array { return ['start_date'=>'date','end_date'=>'date']; } }
