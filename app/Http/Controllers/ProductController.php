<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Traits\ApiResponse;

class ProductController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::all();
            return $this->success($products, 'Products retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve products', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $data = $request->validated();
            $product = Product::create($data);

            return $this->success($product, 'Product created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create product', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        try {
            return $this->success($product, 'Product retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve product', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $data = $request->validated();
            $product->update($data);
            return $this->success($product, 'Product updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update product', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return $this->success(null, 'Product deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete product', 500);
        }
    }
}
