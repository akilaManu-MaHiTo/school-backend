<?php

namespace App\Repositories\All\ComStudentProfile;

use App\Repositories\Base\EloquentRepositoryInterface;

interface ComStudentProfileInterface extends EloquentRepositoryInterface
{
    public function isDuplicate(array $attributes, ?int $ignoreId = null): bool;
}
