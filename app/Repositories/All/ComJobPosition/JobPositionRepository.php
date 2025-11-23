<?php
namespace App\Repositories\All\ComJobPosition;

use App\Models\ComJobPosition;
use App\Repositories\Base\BaseRepository;

class JobPositionRepository extends BaseRepository implements JobPositionInterface
{
    /**
     * @var ComJobPosition
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param ComJobPosition $model
     */
    public function __construct(ComJobPosition $model)
    {
        $this->model = $model;
    }



}
