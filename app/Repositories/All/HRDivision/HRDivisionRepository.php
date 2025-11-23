<?php
namespace App\Repositories\All\HRDivision;

use App\Models\HsHrDivision;
use App\Repositories\Base\BaseRepository;

class HRDivisionRepository extends BaseRepository implements HRDivisionInterface
{
    /**
     * @var HsHrDivision
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param HsHrDivision $model
     */
    public function __construct(HsHrDivision $model)
    {
        $this->model = $model;
    }



}
