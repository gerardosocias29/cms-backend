<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Department extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'name',
    'description',
    'staffCount',
    'totalBeds',
    'status',
  ];

  protected $appends = ['occupancy', 'waitingPatients'];

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

  /**
   * Get the occupancy percentage
   */
  public function getOccupancyAttribute()
  {
    return 0;
    // Calculate occupancy based on patients in department vs total beds
    // For now, return a random value between 0-100 for demonstration
    if ($this->totalBeds <= 0) {
      return 0;
    }

    // In a real implementation, you would count actual patients
    // For now, we'll simulate with a random number
    $patientsInDepartment = rand(0, $this->totalBeds);
    return min(100, round(($patientsInDepartment / $this->totalBeds) * 100));
  }

  /**
   * Get the number of waiting patients
   */
  public function getWaitingPatientsAttribute()
  {
    // In a real implementation, you would count actual waiting patients
    // For now, return a random value for demonstration
    return 0;
  }
}
