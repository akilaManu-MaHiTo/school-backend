<?php

namespace App\Repositories\All\ComTeacherProfile;

use App\Repositories\Base\EloquentRepositoryInterface;
use Illuminate\Support\Collection;

interface ComTeacherProfileInterface extends EloquentRepositoryInterface
{
    public function isDuplicate(array $attributes, ?int $ignoreId = null): bool;

    public function getTeacherYears(int $teacherId): Collection;

    public function getTeacherClassesForYear(int $teacherId, string $year, string $gradeId): Collection;

    public function getTeacherGradesForYear(int $teacherId, string $year): Collection;

    public function getTeacherMediumsForYear(int $teacherId, string $year): Collection;

    public function getTeacherSubjects(
        int $teacherId,
        string $year,
        int|string $gradeId,
        int|string $classId,
        string $medium
    ): Collection;
}
