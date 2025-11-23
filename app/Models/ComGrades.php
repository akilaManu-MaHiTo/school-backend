<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComGrades extends Model
{
    /** @use HasFactory<\Database\Factories\ComGradesFactory> */
    use HasFactory;

    protected $table = 'com_grades';
    protected $fillable = [
        'grade',
    ];
}
