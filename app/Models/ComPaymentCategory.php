<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ComPaymentCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ComPaymentCategoryFactory> */
    use HasFactory;

    protected $table = 'com_payment_categories';
    protected $fillable = [
        'categoryName',
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
