<?php
namespace App\Repositories\All\ComPermission;

use App\Repositories\Base\BaseRepository;
use App\Models\ComPermission;

class ComPermissionRepository extends BaseRepository implements ComPermissionInterface
{
    /**
     * @var ComPermission
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param ComPermission $model
     */
    public function __construct(ComPermission $model)
    {
        $this->model = $model;
    }

}
