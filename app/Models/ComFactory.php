<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComFactory extends Model
{
    use HasFactory;

    protected $table = 'com_factories';

    protected $fillable = [
        'factoryName',
    ];
}
