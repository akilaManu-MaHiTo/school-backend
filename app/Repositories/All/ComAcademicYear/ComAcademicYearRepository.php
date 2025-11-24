<?php

namespace App\Repositories\All\ComAcademicYear;

use App\Models\ComAcademicYears;
use App\Repositories\Base\BaseRepository;

class ComAcademicYearRepository extends BaseRepository implements ComAcademicYearInterface
{

    protected $model;

    public function __construct(ComAcademicYears $model)
    {
        $this->model = $model;
    }

    public function existsByYear($year): bool
    {
        return $this->model->where('year', $year)->exists();
    }
}
