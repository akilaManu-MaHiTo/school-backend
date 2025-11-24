<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComAcademicYears extends Model
{
    /** @use HasFactory<\Database\Factories\ComAcademicYearsFactory> */
    use HasFactory;
    protected $table = "com_academic_years";
    protected $fillable = [
        'year',
        'status',
    ];
}
