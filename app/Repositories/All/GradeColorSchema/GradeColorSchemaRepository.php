<?php

namespace App\Repositories\All\GradeColorSchema;

use App\Models\GradeColorSchema;
use App\Repositories\Base\BaseRepository;

class GradeColorSchemaRepository extends BaseRepository implements GradeColorSchemaInterface
{
    /**
     * @var GradeColorSchema
     */
    protected $model;

    /**
     * GradeColorSchemaRepository constructor.
     */
    public function __construct(GradeColorSchema $model)
    {
        $this->model = $model;
    }
}
