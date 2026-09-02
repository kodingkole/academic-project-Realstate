<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class Attendance extends Model { protected $guarded=[]; protected function casts():array{return ['check_in'=>'datetime','check_out'=>'datetime'];} }
