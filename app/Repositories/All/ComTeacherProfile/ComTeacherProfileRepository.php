<?php

namespace App\Repositories\All\ComTeacherProfile;

use App\Models\ComTeacherProfile;
use App\Repositories\Base\BaseRepository;

class ComTeacherProfileRepository extends BaseRepository implements ComTeacherProfileInterface
{
    public function __construct(ComTeacherProfile $model)
    {
        parent::__construct($model);
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
