<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\OldStudentsUniversity;
use App\Models\OldStudentsOccupation;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'nameWithInitials',
        'userName',
        'email',
        'password',
        'employeeType',
        'employeeNumber',
        'mobile',
        'emailVerifiedAt',
        'otp',
        'userType',
        'assigneeLevel',
        'profileImage',
        'availability',
        'birthDate',
        'address',
        'nationalId',
        'dateOfRegister',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'isCompanyEmployee'  => 'boolean',
        'ResponsibleSection' => 'array',
        'assignedFactory'    => 'array',
    ];
    public function setAssignFactoryAttribute($value)
    {
        $this->attributes['assignedFactory']    = json_encode($value);
        $this->attributes['ResponsibleSection'] = json_encode($value);
    }

    // Accessor to retrieve assignFactory as an array
    public function getAssignFactoryAttribute($value)
    {
        return json_decode($value, true);
    }
    public function comPermission()
    {
        return $this->hasOne(ComPermission::class, 'userType', 'userType');
    }

    public function studentProfile()
    {
        return $this->hasOne(ComStudentProfile::class, 'studentId', 'id');
    }

    public function studentProfiles()
    {
        return $this->hasMany(ComStudentProfile::class, 'studentId', 'id');
    }

    public function oldUniversities()
    {
        return $this->hasMany(OldStudentsUniversity::class, 'studentId', 'id');
    }

    public function oldOccupations()
    {
        return $this->hasMany(OldStudentsOccupation::class, 'studentId', 'id');
    }
}
