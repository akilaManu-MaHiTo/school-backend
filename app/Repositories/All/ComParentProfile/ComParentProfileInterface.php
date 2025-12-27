<?php

namespace App\Repositories\All\ComParentProfile;

use App\Repositories\Base\EloquentRepositoryInterface;

interface ComParentProfileInterface extends EloquentRepositoryInterface
{
	public function isDuplicate(int $parentId, int $studentProfileId, ?int $ignoreId = null): bool;
}
