<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Product;


/**
 * @method static create()
 */
class Category extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = ['name','parent_id'];

    public function subcategory()
    {
        return $this->hasMany('App\Category', 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class,'category_product');
    }
}
