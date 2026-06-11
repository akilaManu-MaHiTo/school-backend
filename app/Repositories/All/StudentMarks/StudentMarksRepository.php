<?php

namespace App\Repositories\All\StudentMarks;

use App\Models\StudentMarks;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class StudentMarksRepository extends BaseRepository implements StudentMarksInterface
{
    public function __construct(StudentMarks $model)
    {
        parent::__construct($model);
    }

    public function findByStudent(int $studentProfileId): Collection
    {
        return $this->model->newQuery()
            ->with(['studentProfile', 'subject'])
            ->where('studentProfileId', $studentProfileId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function existsByStudentSubjectYearTerm(
        int $studentProfileId,
        int $academicSubjectId,
        string $academicYear,
        string $academicTerm
    ): bool
    {
        return $this->model->newQuery()
            ->where('studentProfileId', $studentProfileId)
            ->where('academicSubjectId', $academicSubjectId)
            ->where('academicYear', $academicYear)
            ->where('academicTerm', $academicTerm)
            ->exists();
    }
}
