<?php

namespace App\Http\Controllers;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Cart;
use Validator;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function checkout(Request $request) {
    $user = auth()->user();

    // 1. Get user cart
    $cartItems = Cart::where('user_id', $user->id)->get();
    if($cartItems->isEmpty()) {
        return response()->json([
            "ok" => false,
            "message" => "Cart is empty"
        ], 400);
    }

    // 2. Create order
    $order = Order::create([
        'user_id' => $user->id,
        'total_price' => $cartItems->sum(fn($item) => $item->quantity * $item->product->price),
        'status' => 'pending'
    ]);

    // 3. Create order_items from cart
    foreach ($cartItems as $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'price' => $item->product->price, // price from backend
        ]);

        // Deduct stock
        $item->product->decrement('stock', $item->quantity);
    }

    // 4. Clear cart
    Cart::where('user_id', $user->id)->delete();

    return response()->json([
        'ok' => true,
        'message' => 'Order created successfully',
        'data' => $order->load('orderItems')
    ], 201);
}

}
