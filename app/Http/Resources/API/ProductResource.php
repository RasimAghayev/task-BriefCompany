<?php

namespace App\Http\Resources\API;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // The name of the product was missing from the response before,
            // so the consumer had no way to tell two products apart.
            'name' => $this->name,
            'priceFrom' => (float) $this->priceFrom,
            'priceTo' => (float) $this->priceTo,
            'price' => $this->priceFrom.'-'.$this->priceTo,
            'published' => $this->published,
            'status' => is_null($this->deleted_at) ? 'Active' : 'Deleted',
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            // The name of a category is read from the relation that is
            // already loaded. Before, the resource called the controller of
            // the category for every single product, which built a whole
            // response per category and per product.
            'categories' => $this->categories->map(
                fn ($category) => [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                ]
            ),
        ];
    }
}
