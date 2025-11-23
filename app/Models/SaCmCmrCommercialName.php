<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaCmCmrCommercialName extends Model
{
    use HasFactory;

    protected $fillable = [
        'commercialName',
        'substanceName',
        'molecularFormula',
        'chemicalFormType',
        'reachRegistrationNumber',
        'whereAndWhyUse',
        'zdhcCategory',
        'zdhcLevel',
        'casNumber',
        'colourIndex',
        'useOfPPE',
        'hazardType',
        'ghsClassification',
    ];
    protected $casts = [
        'useOfPPE' => 'array',
        'hazardType' => 'array',
    ];

}
