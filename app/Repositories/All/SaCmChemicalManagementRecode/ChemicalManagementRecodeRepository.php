<?php
namespace App\Repositories\All\SaCmChemicalManagementRecode;

use App\Models\SaCmChemicalManagemantRecode;
use App\Repositories\Base\BaseRepository;

class ChemicalManagementRecodeRepository extends BaseRepository implements ChemicalManagementRecodeInterface
{
    /**
     * @var SaCmChemicalManagemantRecode
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaCmChemicalManagemantRecode $model
     */
    public function __construct(SaCmChemicalManagemantRecode $model)
    {
        $this->model = $model;
    }

    public function getByReviewerId($reviewerId)
    {
        return $this->model->where('reviewerId', $reviewerId)->get();
    }

    public function filterByParams($startDate = null, $endDate = null, $division = null)
    {
        $query = $this->model->newQuery();

        if ($startDate && $endDate) {
            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }

        if ($division) {
            $query->where('division', $division);
        }

        return $query->get();
    }
    public function filterByYear($year)
    {
        return $this->model->whereYear('updated_at', $year)->get();
    }

}
