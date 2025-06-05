<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\{SoftDeletes};
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'department_ids',
        'department_specialization_id',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = [
        'additional_departments',
        'all_departments',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'department_ids' => 'array',
        ];
    }

    public function getJWTIdentifier() { return $this->getKey(); }
    public function getJWTCustomClaims() { return []; }

    public function department_specialization() {
        return $this->belongsTo(DepartmentSpecialization::class);
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the additional departments associated with the user.
     * This is an accessor method that returns a collection of departments.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAdditionalDepartmentsAttribute() {
        if (empty($this->department_ids)) {
            return collect([]);
        }

        return Department::whereIn('id', $this->department_ids)->get();
    }

    /**
     * Get all departments (primary + additional) as a collection
     * For superadmin users (role_id = 1), returns ALL departments
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllDepartmentsAttribute() {
        // If user is superadmin (role_id = 1), return all departments
        if ($this->role_id == 1) {
            return Department::all();
        }

        $departmentIds = $this->getAllDepartmentIds();

        if (empty($departmentIds)) {
            return collect([]);
        }

        return Department::whereIn('id', $departmentIds)->get();
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function patients() {
        return $this->hasMany(Patient::class, 'assigned_user_id', 'id');
    }

    /**
     * Get all departments (primary + additional) that the user belongs to
     * For superadmin users (role_id = 1), returns ALL department IDs
     *
     * @return array
     */
    public function getAllDepartmentIds(): array
    {
        // If user is superadmin (role_id = 1), return all department IDs
        if ($this->role_id == 1) {
            return Department::pluck('id')->toArray();
        }

        $departmentIds = [];

        if ($this->department_id) {
            $departmentIds[] = $this->department_id;
        }

        if (!empty($this->department_ids)) {
            $departmentIds = array_merge($departmentIds, $this->department_ids);
        }

        return array_unique($departmentIds);
    }
}
