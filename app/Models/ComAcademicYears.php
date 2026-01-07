<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ComAcademicYears extends Model
{
    /** @use HasFactory<\Database\Factories\ComAcademicYearsFactory> */
    use HasFactory;
    protected $table = "com_academic_years";
    protected $fillable = [
        'year',
        'status',
        'createdBy',
    ];

    protected $appends = ['createdByData'];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function getCreatedByDataAttribute()
    {
        return $this->createdByUser;
    }
}
