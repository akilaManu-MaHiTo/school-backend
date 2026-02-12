<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ComClassMng;
use App\Models\ComGrades;
use App\Models\User;

class StudentNotifications extends Model
{
    /** @use HasFactory<\Database\Factories\StudentNotificationsFactory> */
    use HasFactory;

    protected $table = 'student_notifications';

    protected $fillable = [
        'createdBy',
        'title',
        'description',
        'year',
        'gradeId',
        'classId',
        'ignoreUserIds',
    ];

    protected $casts = [
        'ignoreUserIds' => 'array',
    ];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function grade()
    {
        return $this->belongsTo(ComGrades::class, 'gradeId');
    }

    public function class()
    {
        return $this->belongsTo(ComClassMng::class, 'classId');
    }
}
