<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComStudentProfile extends Model
{
    /** @use HasFactory<\Database\Factories\ComStudentProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'studentId',
        'academicGradeId',
        'academicClassId',
        'academicYear',
        'academicMedium',
        'isStudentApproved',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'studentId');
    }
    public function grade()
    {
        return $this->belongsTo(ComGrades::class, 'academicGradeId');
    }
    public function class()
    {
        return $this->belongsTo(ComClassMng::class, 'academicClassId');
    }
}
