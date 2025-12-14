<?php

namespace App\Repositories\All\ComTeacherProfile;

use App\Models\ComTeacherProfile;
use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Collection;

class ComTeacherProfileRepository extends BaseRepository implements ComTeacherProfileInterface
{
    public function __construct(ComTeacherProfile $model)
    {
        parent::__construct($model);
    }

    public function getTeacherYears(int $teacherId): Collection
    {
        return $this->model->newQuery()
            ->select('academicYear')
            ->where('teacherId', $teacherId)
            ->whereNotNull('academicYear')
            ->distinct()
            ->orderByDesc('academicYear')
            ->pluck('academicYear');
    }

    public function getTeacherClassesForYear(int $teacherId, string $year, string $gradeId): Collection
    {
        return $this->model->newQuery()
            ->with(['class', 'grade', 'subject'])
            ->where('teacherId', $teacherId)
            ->where('academicYear', $year)
            ->where('academicGradeId', $gradeId)
            ->get();
    }

    public function getTeacherGradesForYear(int $teacherId, string $year): Collection
    {
        return $this->model->newQuery()
            ->with('grade')
            ->where('teacherId', $teacherId)
            ->where('academicYear', $year)
            ->get();
    }

    public function getTeacherMediumsForYear(int $teacherId, string $year): Collection
    {
        return $this->model->newQuery()
            ->select('id', 'academicMedium')
            ->where('teacherId', $teacherId)
            ->where('academicYear', $year)
            ->whereNotNull('academicMedium')
            ->get();
    }

    public function getTeacherSubjects(
        int $teacherId,
        string $year,
        int|string $gradeId,
        int|string $classId,
        string $medium
    ): Collection {
        return $this->model->newQuery()
            ->with('subject')
            ->where('teacherId', $teacherId)
            ->where('academicYear', $year)
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicMedium', $medium)
            ->get();
    }

    public function isDuplicate(array $attributes, ?int $ignoreId = null): bool
    {
        foreach (['teacherId', 'academicGradeId', 'academicSubjectId', 'academicClassId', 'academicYear'] as $key) {
            if (! array_key_exists($key, $attributes)) {
                return false;
            }
        }

        $query = $this->model->newQuery()
            ->where('teacherId', $attributes['teacherId'])
            ->where('academicGradeId', $attributes['academicGradeId'])
            ->where('academicSubjectId', $attributes['academicSubjectId'])
            ->where('academicClassId', $attributes['academicClassId'])
            ->where('academicYear', $attributes['academicYear']);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
