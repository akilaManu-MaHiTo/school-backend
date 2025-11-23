<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsHrDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'divisionName',
    ];
}
