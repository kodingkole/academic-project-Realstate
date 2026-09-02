<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Milestone extends Model { protected $guarded = []; protected function casts(): array { return ['due_date'=>'date']; } }
