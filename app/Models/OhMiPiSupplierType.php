<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OhMiPiSupplierType extends Model
{
    use HasFactory;

    protected $table = 'oh_mi_pi_supplier_types';

    protected $fillable = [
        'typeName',
    ];
}
