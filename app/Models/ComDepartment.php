<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComDepartment extends Model
{
    use HasFactory;
    protected $table = 'com_departments';
    protected $fillable =
    [
        'department',
    ];

}
