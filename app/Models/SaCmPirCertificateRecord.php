<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaCmPirCertificateRecord extends Model
{
    use HasFactory;
    protected $primaryKey = 'certificateId';

    protected $fillable = [
        'inventoryId',
        'testName',
        'testDate',
        'testLab',
        'issuedDate',
        'expiryDate',
        'positiveList',
        'description',
        'documents',

    ];

    protected $casts = [
        'documents'          => 'array',
    ];

    public function impact()
    {
        return $this->belongsTo(SaCmPurchaseInventoryRecord::class, 'inventoryId');
    }
}
