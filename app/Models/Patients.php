<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Patients extends Model
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
    ];

}
