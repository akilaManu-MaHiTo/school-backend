<?php

namespace App\Repositories\All\ComStudentProfile;

use App\Models\ComStudentProfile;
use App\Repositories\Base\BaseRepository;

class ComStudentProfileRepository extends BaseRepository implements ComStudentProfileInterface
{
    public function __construct(ComStudentProfile $model)
    {
        parent::__construct($model);
    }

    public function isDuplicate(array $attributes, ?int $ignoreId = null): bool
    {
        foreach (['studentId', 'academicYear', 'academicGradeId'] as $key) {
            if (! array_key_exists($key, $attributes)) {
                return false;
            }
        }

        $query = $this->model->newQuery()
            ->where('studentId', $attributes['studentId'])
            ->where('academicYear', $attributes['academicYear']);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
