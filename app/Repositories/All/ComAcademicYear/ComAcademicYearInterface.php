<?php

namespace App\Repositories\All\ComAcademicYear;

use App\Repositories\Base\EloquentRepositoryInterface;

interface ComAcademicYearInterface extends EloquentRepositoryInterface
{
    public function existsByYear($year): bool;
}
