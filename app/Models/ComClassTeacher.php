<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ComClassMng;
use App\Models\User;

class ComClassTeacher extends Model
{
    /** @use HasFactory<\Database\Factories\ComClassTeacherFactory> */
    use HasFactory;

    protected $table = 'com_class_teachers';

    protected $fillable = [
        'classId',
        'gradeId',
        'teacherId',
        'year',
    ];

    public function class()
    {
        return $this->belongsTo(ComClassMng::class, 'classId');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacherId');
    }
    public function grade()
    {
        return $this->belongsTo(ComGrades::class, 'gradeId');
    }
}
