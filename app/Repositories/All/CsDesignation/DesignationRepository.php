<?php
namespace App\Repositories\All\CsDesignation;

use App\Models\HsOhCsDesignation;
use App\Repositories\Base\BaseRepository;

class DesignationRepository extends BaseRepository implements DesignationInterface
{
    /**
     * @var HsOhCsDesignation
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param HsOhCsDesignation $model
     */
    public function __construct(HsOhCsDesignation $model)
    {
        $this->model = $model;
    }



}
