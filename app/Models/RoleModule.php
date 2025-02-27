<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class RoleModule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'role_id',
        'page',
        'description'
    ];
}
