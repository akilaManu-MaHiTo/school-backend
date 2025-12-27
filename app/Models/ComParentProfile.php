<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComParentProfile extends Model
{
    /** @use HasFactory<\Database\Factories\ComParentProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'parentId',
        'studentId',
    ];

    public function studentProfile()
    {
        return $this->hasOne(ComStudentProfile::class, 'studentId', 'studentId');
    }
}
