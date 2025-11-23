<?php
namespace App\Repositories\All\SaCmCmrProductStandard;

use App\Models\SaCmCmrProductStandards;
use App\Repositories\Base\BaseRepository;

class ProductStandardRepository extends BaseRepository implements ProductStandardInterface
{
    /**
     * @var SaCmCmrProductStandards
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaCmCmrProductStandards $model
     */
    public function __construct(SaCmCmrProductStandards $model)
    {
        $this->model = $model;
    }


}
