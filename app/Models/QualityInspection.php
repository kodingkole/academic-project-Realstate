<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class QualityInspection extends Model { protected $guarded=[]; protected function casts():array{return ['inspection_date'=>'date'];} }
