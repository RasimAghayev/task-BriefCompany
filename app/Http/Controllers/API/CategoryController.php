<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CategoryRequest;
use App\Http\Resources\API\CategoryResource;
use App\Models\Category;
use App\Support\PerPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::with('subcategory')
            ->orderBy('id')
            ->paginate(PerPage::from($request))
            ->through(fn (Category $category) => new CategoryResource($category));

        return $this->sendPaginatedResponse($categories, 'Category retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return $this->sendResponse(new CategoryResource($category), 'Category created successfully.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $category = Category::find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        return $this->sendResponse(new CategoryResource($category), 'Category retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, $id): JsonResponse
    {
        $category = Category::find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        $category->update($request->validated());

        return $this->sendResponse(new CategoryResource($category), 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $category = Category::find($id);

        // The category was not looked up before, so a request for a category
        // that does not exist ended in an error of the framework.
        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        $categoryCount = DB::table('category_product')->where('category_id', $id)->count();

        if ($categoryCount > 0) {
            return $this->sendError('Category used other product.');
        }

        $category->delete();

        return $this->sendResponse([], 'Category deleted successfully.');
    }
}
