<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\ProductRequest;
use App\Http\Resources\API\ProductResource;
use App\Models\Product;
use App\Support\PerPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::withTrashed()
            ->with('categories')
            ->orderByDesc('id')
            ->paginate(PerPage::from($request))
            ->through(fn (Product $product) => new ProductResource($product));

        return $this->sendPaginatedResponse($products, 'Product retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['categories'];
        unset($data['categories']);

        $product = Product::create($data);
        $product->categories()->sync($categoryIds);

        $product->load('categories');

        return $this->sendResponse(new ProductResource($product), 'Product created successfully.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $product = Product::with('categories')->find($id);

        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }

        return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id): JsonResponse
    {
        $product = Product::find($id);

        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }

        $data = $request->validated();
        $categoryIds = $data['categories'];
        unset($data['categories']);

        $product->update($data);

        // The categories were not touched by the update before, so a product
        // kept the categories it was created with.
        $product->categories()->sync($categoryIds);
        $product->load('categories');

        return $this->sendResponse(new ProductResource($product), 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->sendResponse([], 'Product deleted successfully.');
    }
}
