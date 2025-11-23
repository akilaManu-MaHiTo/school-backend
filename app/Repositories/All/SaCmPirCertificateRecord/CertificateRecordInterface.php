<?php

namespace App\Repositories\All\SaCmPirCertificateRecord;

use App\Repositories\Base\EloquentRepositoryInterface;

// Interface
interface CertificateRecordInterface extends EloquentRepositoryInterface {
    public function findByInventoryId(int $inventoryId);
    public function deleteByInventoryId(int $inventoryId);
}
