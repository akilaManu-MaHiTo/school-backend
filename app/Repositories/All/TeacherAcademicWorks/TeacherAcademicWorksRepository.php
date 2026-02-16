<?php

namespace App\Repositories\All\TeacherAcademicWorks;

use App\Models\TeacherAcademicWorks;
use App\Repositories\Base\BaseRepository;

class TeacherAcademicWorksRepository extends BaseRepository implements TeacherAcademicWorksInterface
{
    /**
     * @var TeacherAcademicWorks
     */
    protected $model;

    public function __construct(TeacherAcademicWorks $model)
    {
        $this->model = $model;
    }
}
