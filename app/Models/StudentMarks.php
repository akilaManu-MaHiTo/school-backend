<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMarks extends Model
{
    /** @use HasFactory<\Database\Factories\StudentMarksFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'studentProfileId',
        'academicSubjectId',
        'studentMark',
        'markGrade',
        'academicYear',
        'academicTerm',
    ];

    protected $casts = [
        'studentProfileId'   => 'integer',
        'academicSubjectId'  => 'integer',
        'studentMark'        => 'string',
        'markGrade'          => 'string',
        'academicYear'       => 'string',
        'academicTerm'       => 'string',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(ComStudentProfile::class, 'studentProfileId');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ComSubjects::class, 'academicSubjectId');
    }
}
