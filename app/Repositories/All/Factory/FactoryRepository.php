<?php
namespace App\Repositories\All\Factory;

use App\Models\ComFactory;
use App\Repositories\Base\BaseRepository;

class FactoryRepository extends BaseRepository implements FactoryInterface
{
    /**
     * @var  ComFactory
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param ComFactory $model
     */
    public function __construct(ComFactory $model)
    {
        $this->model = $model;
    }



}
