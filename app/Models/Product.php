<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['name', 'priceFrom', 'priceTo', 'published'];

    public function categories()
    {
        return $this->belongsToMany(Category::class,'category_product');
    }
}
