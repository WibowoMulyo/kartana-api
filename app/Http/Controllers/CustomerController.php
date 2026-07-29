<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Traits\ApiResponse;

class CustomerController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $customers = Customer::all();
            return $this->success($customers, 'Customers retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve customers', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            $data = $request->validated();
            $customer = Customer::create($data);
            return $this->success($customer, 'Customer created successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to create customer', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        try {
            return $this->success($customer, 'Customer retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve customer', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        try {
            $data = $request->validated();
            $customer->update($data);
            return $this->success($customer, 'Customer updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update customer', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return $this->success(null, 'Customer deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete customer', 500);
        }
    }
}
