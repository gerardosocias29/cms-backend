<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'description',
        'name',
    ];

}
