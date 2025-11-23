<?php
namespace App\Repositories\All\AssigneeLevel;

use App\Models\ComAssigneeLevel;
use App\Repositories\Base\BaseRepository;

class   AssigneeLevelRepository extends BaseRepository implements AssigneeLevelInterface
{
    /**
     * @var ComAssigneeLevel
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param ComAssigneeLevel $model
     */
    public function __construct(ComAssigneeLevel $model)
    {
        $this->model = $model;
    }



}
