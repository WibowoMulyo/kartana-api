<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $orders = Order::query()
                ->with('customer')
                ->when($request->status, fn ($q, $v) => $q->where('status', $v))
                ->when($request->customer_id, fn ($q, $v) => $q->where('customer_id', $v))
                ->latest()
                ->paginate($request->per_page ?? 10);

            return $this->success($orders);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $data = $request->validated();

            $order = DB::transaction(function () use ($data) {
                $order = Order::create([
                    'customer_id' => $data['customer_id'],
                    'total_price' => 0,
                    'status' => 'pending',
                    'ordered_at' => now()
                ]);

                $total = 0;

                foreach ($data['order_items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    if($product->stock < $item['quantity']) {
                        throw new \RuntimeException("Insufficient stock for product: {$product->name}");
                    }

                    $unitPrice = $product->price;
                    $subtotal = $unitPrice * $item['quantity'];
                    $total += $subtotal;

                    $order->OrderItems()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    $product->decrement('stock', $item['quantity']);
                }

                $order->update(['total_price' => $total]);

                return $order;
            });

            return $this->success($order->load('OrderItems.product', 'customer'), 'Order created successfully.', 201);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        try {
            $order->load('OrderItems.product', 'customer');
            return $this->success($order, 'Order retrieved successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        try {
            $data = $request->validated();
            $order->update($data);
            return $this->success($order, 'Order updated successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        try {
            $order->delete();
            return $this->success(null, 'Order deleted successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Cancel the specified order.
     */
    public function cancel(Order $order)
    {
        try {
            if ($order->status === 'cancelled') {
                return $this->error('Order is already cancelled.', 400);
            }

            if ($order->status !== 'pending') {
                return $this->error('Only pending orders can be cancelled.', 400);
            }

            DB::transaction(function () use ($order) {
                foreach ($order->OrderItems as $item) {
                    $product = $item->product;
                    $product->increment('stock', $item->quantity);
                }

                $order->update(['status' => 'cancelled']);
            });

            return $this->success($order, 'Order cancelled successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
