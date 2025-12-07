<?php

namespace App\Repositories\All\ComTeacherProfile;

use App\Repositories\Base\EloquentRepositoryInterface;

interface ComTeacherProfileInterface extends EloquentRepositoryInterface
{
    public function isDuplicate(array $attributes, ?int $ignoreId = null): bool;
}
