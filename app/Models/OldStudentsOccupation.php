<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OldStudentsOccupation extends Model
{
    /** @use HasFactory<\Database\Factories\OldStudentsOccupationFactory> */
    use HasFactory;

    protected $fillable = [
        'studentId',
        'companyName',
        'occupation',
        'description',
        'dateOfRegistration',
        'country',
        'city',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'studentId');
    }
}
