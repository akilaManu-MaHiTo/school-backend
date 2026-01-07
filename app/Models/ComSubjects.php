<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'basketGroup',
        'createdBy',
    ];

    protected $appends = ['createdByData'];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function getCreatedByDataAttribute()
    {
        return $this->createdByUser;
    }
}
