<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display all orders for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['orderItems.product'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Validate stock availability for all products
            $orderItems = [];
            $totalPrice = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if (!$product->hasStock($item['quantity'])) {
                    throw ValidationException::withMessages([
                        'items' => ["Insufficient stock for product: {$product->name}. Available: {$product->stock}, Requested: {$item['quantity']}"],
                    ]);
                }

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $product->price,
                ];

                $totalPrice += $product->price * $item['quantity'];
            }

            // Create the order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            // Create order items and reduce stock
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $item['price_at_purchase'],
                ]);

                // Reduce product stock
                $item['product']->reduceStock($item['quantity']);
            }

            DB::commit();

            // Load the order with relationships
            $order->load(['orderItems.product', 'user']);

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($e instanceof ValidationException) {
                throw $e;
            }

            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        // Check if user owns this order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. You can only view your own orders.',
            ], 403);
        }

        $order->load(['orderItems.product', 'user']);

        return response()->json([
            'order' => $order,
        ]);
    }

    /**
     * Cancel an order (if status is pending).
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        // Check if user owns this order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. You can only cancel your own orders.',
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot cancel order. Order status is: ' . $order->status,
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Restore stock for all order items
            foreach ($order->orderItems as $orderItem) {
                $product = $orderItem->product;
                $product->increment('stock', $orderItem->quantity);
            }

            // Update order status
            $order->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order->fresh(['orderItems.product']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}