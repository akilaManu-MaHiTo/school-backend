<?php

namespace App\Repositories\All\StudentMarks;

use App\Repositories\Base\EloquentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface StudentMarksInterface extends EloquentRepositoryInterface
{
    public function findByStudent(int $studentProfileId): Collection;
}
