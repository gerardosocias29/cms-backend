<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'priority_number',
        'name',
        'birthday',
        'priority',
        'address',
        'symptoms',
        'bloodpressure',
        'heartrate',
        'temperature',
        'status',
        'assigned_user_id',
        'starting_department_id',
        'next_department_id',
        'prev_department_ids',
        'session_started',
        'session_ended',
    ];

    protected $casts = [
        'prev_department_ids' => 'array',
    ];

    public function assigned_to() {
        return $this->belongsTo(User::class, 'assigned_user_id', 'id');
    }

}
