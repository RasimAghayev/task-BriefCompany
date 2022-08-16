<?php

namespace App\Http\Resources\API;

use App\Http\Controllers\API\CategoryController;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $product=[];
        $product['id']=$this->id;
        $product['price']=$this->priceFrom.'-'.$this->priceTo;
        $product['published']=$this->published;
        $product['categories'] = [];
        $categories=$this->categories;
        $categories=$categories->map(
            function ($category) use ($product){
                return  [
                    'category_id'=>$category->pivot->category_id,
                    'category_name'=>(new CategoryController())->show($category->pivot->category_id)->original['data']['name']
                ];
            }
        );
        $product['categories']=$categories;
        return $product;
    }
}
