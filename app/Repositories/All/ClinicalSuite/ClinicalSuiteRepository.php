<?php
namespace App\Repositories\All\ClinicalSuite;

use App\Models\HsOhCsClinicalSuite;
use App\Repositories\Base\BaseRepository;

class ClinicalSuiteRepository extends BaseRepository implements ClinicalSuiteInterface
{
    /**
     * @var HsOhCsClinicalSuite
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param HsOhCsClinicalSuite $model
     */
    public function __construct(HsOhCsClinicalSuite $model)
    {
        $this->model = $model;
    }



}
