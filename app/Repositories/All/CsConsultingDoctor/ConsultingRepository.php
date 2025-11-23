<?php
namespace App\Repositories\All\CsConsultingDoctor;

use App\Models\HsOhCsConsultingDoctor;
use App\Repositories\Base\BaseRepository;

class ConsultingRepository extends BaseRepository implements ConsultingInterface
{
    /**
     * @var HsOhCsConsultingDoctor
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param HsOhCsConsultingDoctor $model
     */
    public function __construct(HsOhCsConsultingDoctor $model)
    {
        $this->model = $model;
    }



}
