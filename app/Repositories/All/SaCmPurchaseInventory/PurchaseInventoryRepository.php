<?php
namespace App\Repositories\All\SaCmPurchaseInventory;

use App\Models\SaCmPurchaseInventoryRecord;
use App\Repositories\Base\BaseRepository;

class PurchaseInventoryRepository extends BaseRepository implements PurchaseInventoryInterface
{
    /**
     * @var SaCmPurchaseInventoryRecord
     */
    protected $model;

    /**
     * HazardRiskRepository constructor.
     *
     * @param SaCmPurchaseInventoryRecord $model
     */
    public function __construct(SaCmPurchaseInventoryRecord $model)
    {
        $this->model = $model;
    }
    public function filterByStatus(string $status)
    {
        return $this->model->where('status', $status)->get();
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
