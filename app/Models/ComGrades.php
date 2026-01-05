<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ComGrades extends Model
{
    /** @use HasFactory<\Database\Factories\ComGradesFactory> */
    use HasFactory;

    protected $table = 'com_grades';
    protected $fillable = [
        'grade',
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
