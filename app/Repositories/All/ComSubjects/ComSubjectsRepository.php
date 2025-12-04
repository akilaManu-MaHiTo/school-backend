<?php

namespace App\Repositories\All\ComSubjects;

use App\Models\ComSubjects;
use App\Repositories\Base\BaseRepository;

class ComSubjectsRepository extends BaseRepository implements ComSubjectsInterface
{
    /**
     * @var ComSubjects
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param ComSubjects $model
     */
    public function __construct(ComSubjects $model)
    {
        $this->model = $model;
    }

    /**
     * Check if a subject exists.
     *
     * @param int|string $subject
     * @return bool
     */
    public function existsBySubject($subject): bool
    {
        return $this->model->where('subjectName', $subject)->exists();
    }
    public function existByCode($subject): bool
    {
        return $this->model->where('subjectCode', $subject)->exists();
    }
}
