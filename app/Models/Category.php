<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A category of the catalogue.
 *
 * A category has a parent, so the catalogue is a tree: a category is either
 * a top level one or a subcategory of another category.
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'parent_id'];

    /**
     * The category this one belongs to.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * The categories that belong to this one.
     *
     * The relation used the name "App\\Category" before, and the model of
     * the project lives in "App\\Models", so the relation could never be
     * used.
     */
    public function subcategory(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
