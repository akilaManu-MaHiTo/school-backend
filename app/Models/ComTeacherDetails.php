<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ComTeacherDetails extends Model
{
    /** @use HasFactory<\Database\Factories\ComTeacherDetailsFactory> */
    use HasFactory;

    protected $table = 'com_teacher_details';

    protected $fillable = [
        'teacherId',
        'civilStatus',
        'dateOfRetirement',
        'dateOfFirstRegistration',
        'teacherType',
        'teacherGrade',
        'dateOfGrade',
        'salaryType',
        'registerPostNumber',
        'registerPostDate',
        'registerSubject',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacherId');
    }
}
