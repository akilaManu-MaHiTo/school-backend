<?php
namespace App\Repositories\All\ComResponsibleSection;

use App\Models\ComResponsibleSection;
use App\Repositories\Base\BaseRepository;

class ComResponsibleSectionRepository extends BaseRepository implements ComResponsibleSectionInterface
{
    /**
     * @var ComResponsibleSection
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param ComResponsibleSection $model
     */
    public function __construct(ComResponsibleSection $model)
    {
        $this->model = $model;
    }



}
