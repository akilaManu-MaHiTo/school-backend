<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComUserType extends Model
{
    use HasFactory;

    protected $table = 'com_user_types';

    protected $fillable = [
        'userType',
        'userTypeDescription',
        'sectionCode',
        'areas',
        'other',
        'status',
    ];
}
