<?php
namespace App\Repositories\All\SaCmPirSuplierName;

use App\Models\SaCmPirSuplierName;
use App\Repositories\Base\BaseRepository;

class SuplierNameRepository extends BaseRepository implements SuplierNameInterface
{
    /**
     * @var SaCmPirSuplierName
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaCmPirSuplierName $model
     */
    public function __construct(SaCmPirSuplierName $model)
    {
        $this->model = $model;
    }


}
