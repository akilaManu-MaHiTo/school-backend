<?php
namespace App\Repositories\All\SaCmCmrChemicalFormType;

use App\Models\SaCmCmrChemicalFormType;
use App\Repositories\Base\BaseRepository;

class ChemicalFormTypeRepository extends BaseRepository implements ChemicalFormTypeInterface
{
    /**
     * @var SaEEmrAcCategory
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaEEmrAcCategory $model
     */
    public function __construct( SaCmCmrChemicalFormType $model)
    {
        $this->model = $model;
    }


}
