<?php
namespace App\Repositories\All\SaCmPirPositiveList;

use App\Models\SaCmPirPositiveList;
use App\Repositories\Base\BaseRepository;

class PositiveListRepository extends BaseRepository implements PositiveListInterface
{
    /**
     * @var SaCmPirPositiveList
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaCmPirPositiveList $model
     */
    public function __construct(SaCmPirPositiveList $model)
    {
        $this->model = $model;
    }


}
