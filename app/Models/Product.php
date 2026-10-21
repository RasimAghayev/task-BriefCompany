<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A product of the catalogue.
 *
 * A product belongs to at least one category and carries a price range: the
 * price it starts at and the price it ends at.
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'priceFrom', 'priceTo', 'published'];

    /**
     * The prices are read as numbers, not as strings.
     */
    protected $casts = [
        'priceFrom' => 'float',
        'priceTo' => 'float',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }
}
