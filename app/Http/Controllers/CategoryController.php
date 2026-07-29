<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\ApiResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return $this->success($categories, 'Categories retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve categories', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $data = $request->validated();
            $category = Category::create($data);
            return $this->success($category, 'Category created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create category', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        try {
            return $this->success($category, 'Category retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve category', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $data = $request->validated();
            $category->update($data);
            return $this->success($category, 'Category updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update category', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            if($category->isEmpty()) {
                return $this->error('Category not found', 404);
            }

            $category->delete();
            return $this->success(null, 'Category deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete category', 500);
        }
    }
}
