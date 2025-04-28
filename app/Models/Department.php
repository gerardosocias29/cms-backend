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

  public function patient() {
    $date = \Carbon\Carbon::now()->toDateString();

    return $this->hasOne(Patient::class, 'next_department_id')
                ->where('status', 'in-progress')
                ->whereDate('created_at', $date)
                ->orderBy('created_at', 'desc');
  }
}
