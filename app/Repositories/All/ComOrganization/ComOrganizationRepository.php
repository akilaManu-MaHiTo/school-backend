<?php
namespace App\Repositories\All\ComOrganization;

use App\Models\ComOrganization;
use App\Repositories\Base\BaseRepository;

class ComOrganizationRepository extends BaseRepository implements ComOrganizationInterface
{
    /**
     * @var ComOrganization
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param ComOrganization $model
     */
    public function __construct(ComOrganization $model)
    {
        $this->model = $model;
    }
    public function first()
{
    return $this->model->orderBy('id')->first();
}

}
