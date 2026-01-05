<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ComClassMng extends Model
{
    /** @use HasFactory<\Database\Factories\ComClassMngFactory> */
    use HasFactory;

    protected $table = 'com_class_mngs';
    protected $fillable = [
        'className',
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
