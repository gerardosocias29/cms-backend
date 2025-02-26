<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Department extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'name',
    'description',
  ];

  public function specializations () {
    return $this->hasMany(DepartmentSpecialization::class);
}
}
