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
        'studentProfileId',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(ComStudentProfile::class, 'studentProfileId');
    }
}
