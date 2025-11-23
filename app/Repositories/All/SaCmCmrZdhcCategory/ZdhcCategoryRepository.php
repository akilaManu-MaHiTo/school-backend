<?php
namespace App\Repositories\All\SaCmCmrZdhcCategory;

use App\Models\SaCmCmrZdhcCategory;
use App\Repositories\Base\BaseRepository;

class ZdhcCategoryRepository extends BaseRepository implements ZdhcCategoryInterface
{
    /**
     * @var SaCmCmrZdhcCategory
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaCmCmrZdhcCategory $model
     */
    public function __construct(SaCmCmrZdhcCategory $model)
    {
        $this->model = $model;
    }


}
