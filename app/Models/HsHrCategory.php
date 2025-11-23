<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsHrCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoryName',
        'subCategory',
        'observationType',
    ];

    public function subcategories()
    {
        return $this->hasMany(HsHrCategory::class, 'categoryName', 'categoryName');
    }

    public function subSubcategories()
    {
        return $this->hasMany(HsHrCategory::class, 'subCategory', 'subCategory');
    }

    public function observations()
    {
        return $this->hasMany(HsHrCategory::class, 'subSubCategory', 'subSubCategory');
    }

}
