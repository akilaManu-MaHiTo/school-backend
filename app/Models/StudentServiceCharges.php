<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ComPaymentCategory;

class StudentServiceCharges extends Model
{
    /** @use HasFactory<\Database\Factories\StudentServiceChargesFactory> */
    use HasFactory;

    protected $table = 'student_service_charges';

    protected $fillable = [
        'studentId',
        'chargesCategoryId',
        'amount',
        'dateCharged',
        'yearForCharge',
        'remarks',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'studentId');
    }

    public function category()
    {
        return $this->belongsTo(ComPaymentCategory::class, 'chargesCategoryId');
    }
}
