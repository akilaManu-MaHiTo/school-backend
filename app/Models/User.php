<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'userName',
        'email',
        'password',
        'isCompanyEmployee',
        'employeeNumber',
        'mobile',
        'emailVerifiedAt',
        'otp',
        'userType',
        'gender',
        'department',
        'jobPosition',
        'responsibleSection',
        'assigneeLevel',
        'profileImage',
        'availability',
        'assignedFactory',

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
}
