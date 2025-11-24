<?php

namespace App\Repositories\All\ComGrades;

use App\Repositories\Base\EloquentRepositoryInterface;

interface ComGradesInterface extends EloquentRepositoryInterface
{
    /**
     * Check if a grade value already exists.
     *
     * @param  int|string  $grade
     * @return bool
     */
    public function existsByGrade($grade): bool;
}
