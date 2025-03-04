<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Queue extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'department_id',
    'priority',
    'number',
    'status',
  ];

  public function department() {
    return $this->belongsTo(Department::class);
  }
  
}
