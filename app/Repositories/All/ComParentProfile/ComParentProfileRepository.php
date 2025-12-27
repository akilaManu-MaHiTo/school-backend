<?php

namespace App\Repositories\All\ComParentProfile;

use App\Models\ComParentProfile;
use App\Repositories\Base\BaseRepository;

class ComParentProfileRepository extends BaseRepository implements ComParentProfileInterface
{
    public function __construct(ComParentProfile $model)
    {
        parent::__construct($model);
    }

    public function isDuplicate(int $parentId, int $studentProfileId, ?int $ignoreId = null): bool
    {
        $query = $this->model->newQuery()
            ->where('parentId', $parentId)
            ->where('studentProfileId', $studentProfileId);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
