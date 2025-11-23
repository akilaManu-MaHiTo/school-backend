<?php
namespace App\Repositories\All\ComPersonType;

use App\Models\ComPersonType;
use App\Repositories\Base\BaseRepository;

class PersonTypeRepository extends BaseRepository implements PersonTypeInterface
{
    /**
     * @var ComPersonType
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param ComPersonType $model
     */
    public function __construct(ComPersonType $model)
    {
        $this->model = $model;
    }



}
