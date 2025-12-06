<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComTeacherProfile extends Model
{
    /** @use HasFactory<\Database\Factories\ComTeacherProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'teacherId',
        'academicGradeId',
        'academicSubjectId',
        'academicClassId',
        'academicYear',
        'academicMedium',
    ];

 public function teacher()
    {
        return $this->belongsTo(User::class, 'teacherId');
    }

    public function grade()
    {
        return $this->belongsTo(ComGrades::class, 'academicGradeId');
    }

    public function subject()
    {
        return $this->belongsTo(ComSubjects::class, 'academicSubjectId');
    }

    public function class()
    {
        return $this->belongsTo(ComClassMng::class, 'academicClassId');
    }

}
