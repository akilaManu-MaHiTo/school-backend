<?php
namespace App\Repositories\All\ComUserType;

use App\Models\ComUserType;
use App\Repositories\Base\BaseRepository;

class UserTypeRepository extends BaseRepository implements UserTypeInterface
{
    /**
     * @var UserType
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param UserType $model
     */
    public function __construct(ComUserType $model)
    {
        $this->model = $model;
    }



}
