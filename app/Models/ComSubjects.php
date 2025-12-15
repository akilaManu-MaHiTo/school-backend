<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComSubjects extends Model
{
    /** @use HasFactory<\Database\Factories\ComSubjectsFactory> */
    use HasFactory;

    protected $table = 'com_subjects';
    protected $fillable = [
        'subjectCode',
        'subjectName',
        'subjectMedium',
        'isBasketSubject',
        'basketGroup'
    ];
}
