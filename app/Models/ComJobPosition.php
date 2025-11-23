<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComJobPosition extends Model
{
    use HasFactory;

    protected $table = 'com_job_positions';

    protected $fillable = [
        'jobPosition',
    ];
}
