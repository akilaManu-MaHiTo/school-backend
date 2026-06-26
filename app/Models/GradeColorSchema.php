<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeColorSchema extends Model
{
    /** @use HasFactory<\Database\Factories\GradeColorSchemaFactory> */
    use HasFactory;

    protected $table = 'grade_color_schemas';

    protected $fillable = [
        'gradeName',
        'marksRange',
        'color',
    ];
}
