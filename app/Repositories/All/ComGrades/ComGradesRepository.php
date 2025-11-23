<?php

namespace App\Repositories\All\ComGrades;

use App\Models\ComGrades;
use App\Repositories\Base\BaseRepository;

class ComGradesRepository extends BaseRepository implements ComGradesInterface
{
    /**
     * @var ComGrades
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param ComGrades $model
     */
    public function __construct(ComGrades $model)
    {
        $this->model = $model;
    }

    /**
     * Check if a grade exists.
     *
     * @param int|string $grade
     * @return bool
     */
    public function existsByGrade($grade): bool
    {
        return $this->model->where('grade', $grade)->exists();
    }
}
