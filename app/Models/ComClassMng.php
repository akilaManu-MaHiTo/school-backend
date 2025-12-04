<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComClassMng extends Model
{
    /** @use HasFactory<\Database\Factories\ComClassMngFactory> */
    use HasFactory;

    protected $table = 'com_class_mngs';
    protected $fillable = [
        'className',
    ];
}
