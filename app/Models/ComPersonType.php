<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComPersonType extends Model
{
    use HasFactory;

    protected $table = 'com_person_types';

    protected $fillable = [
        'personTypeName',
    ];
}
