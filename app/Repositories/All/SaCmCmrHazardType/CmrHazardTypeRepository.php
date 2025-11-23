<?php
namespace App\Repositories\All\SaCmCmrHazardType;

use App\Models\SaCmCmrCnHazardType;
use App\Repositories\Base\BaseRepository;

class CmrHazardTypeRepository extends BaseRepository implements CmrHazardTypeInterface
{
    /**
     * @var SaCmCmrCnHazardType
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaCmCmrCnHazardType $model
     */
    public function __construct(SaCmCmrCnHazardType $model)
    {
        $this->model = $model;
    }


}
