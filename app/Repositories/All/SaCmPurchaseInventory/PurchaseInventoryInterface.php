<?php
namespace App\Repositories\All\SaCmPurchaseInventory;

use App\Repositories\Base\EloquentRepositoryInterface;

// Interface
interface PurchaseInventoryInterface extends EloquentRepositoryInterface
{
    public function filterByStatus(string $status);
    public function getByReviewerId($reviewerId);
    public function filterByParams($startDate, $endDate, $division);
    public function filterByYear($year);

}
