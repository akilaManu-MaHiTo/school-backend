<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Summodel extends Model
{
    use HasFactory;

    protected $fillable = ['number1', 'number2', 'sum'];
}
