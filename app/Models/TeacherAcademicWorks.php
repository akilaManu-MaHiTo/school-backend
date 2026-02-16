<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ComSubjects;
use App\Models\User;

class TeacherAcademicWorks extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherAcademicWorksFactory> */
    use HasFactory;

    protected $table = 'teacher_academic_works';

    protected $fillable = [
        'teacherId',
        'subjectId',
        'title',
        'academicWork',
        'date',
        'time',
        'approved',
        'createdBy',
    ];

    protected $appends = ['createdByData'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacherId');
    }

    public function subject()
    {
        return $this->belongsTo(ComSubjects::class, 'subjectId');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function getCreatedByDataAttribute()
    {
        return $this->createdByUser;
    }
}
