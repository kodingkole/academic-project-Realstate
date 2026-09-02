<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class SitePhoto extends Model { protected $guarded=[]; protected function casts():array{return ['upload_timestamp'=>'datetime'];} }
