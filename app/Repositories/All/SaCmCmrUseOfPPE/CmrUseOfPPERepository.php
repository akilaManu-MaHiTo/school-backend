<?php
namespace App\Repositories\All\SaCmCmrUseOfPPE;

use App\Models\SaCmCmrCnUseOfPpe;
use App\Repositories\Base\BaseRepository;

class CmrUseOfPPERepository extends BaseRepository implements CmrUseOfPPEInterface
{
    /**
     * @var SaCmCmrCnUseOfPpe
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaCmCmrCnUseOfPpe $model
     */
    public function __construct(SaCmCmrCnUseOfPpe $model)
    {
        $this->model = $model;
    }


}
