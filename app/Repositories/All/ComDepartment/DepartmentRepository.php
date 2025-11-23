<?php
namespace App\Repositories\All\ComDepartment;

use App\Models\ComDepartment;
use App\Repositories\Base\BaseRepository;

class DepartmentRepository extends BaseRepository implements DepartmentInterface
{
    /**
     * @var ComDepartment
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param ComDepartment $model
     */
    public function __construct(ComDepartment $model)
    {
        $this->model = $model;
    }



}
