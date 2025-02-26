<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class DepartmentSpecialization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'department_id',
        'name',
    ];

    public function department () {
        return $this->belongsTo(Department::class);
    }
    
}
