<?php
namespace App\Repositories\All\SaCmPirTestingLab;

use App\Models\SaCmPirTestingLab;
use App\Repositories\Base\BaseRepository;

class TestingLabRepository extends BaseRepository implements TestingLabInterface
{
    /**
     * @var SaCmPirTestingLab
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaCmPirTestingLab $model
     */
    public function __construct(SaCmPirTestingLab $model)
    {
        $this->model = $model;
    }


}
