<?php
namespace App\Repositories\All\SaCmCmrCommercialName;

use App\Models\SaCmCmrCommercialName;
use App\Repositories\Base\BaseRepository;

class CommercialNameRepository extends BaseRepository implements CommercialNameInterface
{
    /**
     * @var SaEEmrAcCategory
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaEEmrAcCategory $model
     */
    public function __construct(SaCmCmrCommercialName $model)
    {
        $this->model = $model;
    }


}
