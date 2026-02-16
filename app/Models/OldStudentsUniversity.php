<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OldStudentsUniversity extends Model
{
    /** @use HasFactory<\Database\Factories\OldStudentsUniversityFactory> */
    use HasFactory;

    protected $fillable = [
        'studentId',
        'universityName',
        'country',
        'city',
        'degree',
        'faculty',
        'yearOfAdmission',
        'yearOfGraduation',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'studentId');
    }
}
