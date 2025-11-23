<?php
namespace App\Repositories\All\CsMedicineStock;

use App\Models\HsOhCsMedicineStock;
use App\Repositories\Base\BaseRepository;

class MedicineStockRepository extends BaseRepository implements MedicineStockInterface
{
    /**
     * @var HsOhCsMedicineStock
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param HsOhCsMedicineStock $model
     */
    public function __construct(HsOhCsMedicineStock $model)
    {
        $this->model = $model;
    }



}
