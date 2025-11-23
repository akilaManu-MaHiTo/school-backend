<?php
namespace App\Repositories\All\OhMiPiSupplierType;

use App\Models\OhMiPiSupplierType;
use App\Repositories\Base\BaseRepository;

class SupplierTypeRepository extends BaseRepository implements SupplierTypeInterface
{
    protected $model;

    public function __construct(OhMiPiSupplierType $model)
    {
        $this->model = $model;
    }
}
