<?php
namespace App\Repositories\All\HRCategory;

use App\Models\HsHrCategory;
use App\Repositories\Base\BaseRepository;

class HRCategoryRepository extends BaseRepository implements HRCategoryInterface
{
    /**
     * @var HsHrCategory
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param HsHrCategory $model
     */
    public function __construct(HsHrCategory $model)
    {
        $this->model = $model;
    }



}
