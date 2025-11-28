<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Cart;
use App\Models\User;
use Validator;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function checkout(Request $request){
    $user = auth()->user();
    $cartItems = Cart::with('product')->where('user_id', $user->id)->get();

    if ($cartItems->isEmpty()) {
        return response()->json([
            'ok' => false,
            'message' => 'Cart is empty'
        ], 400);
    }

    $total_amount = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);

    $order = Order::create([
        'user_id' => $user->id,
        'total_amount' => $total_amount,
        'status' => 'pending'
    ]);

    foreach ($cartItems as $item) {
        $order->items()->create([
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'price' => $item->product->price
        ]);

        $item->product->decrement('stock', $item->quantity);
    }

    Cart::where('user_id', $user->id)->delete();
   
    $order->load('items.product');

    return response()->json([
        'ok' => true,
        'message' => 'Order created successfully',
        'data' => $order
    ], 201);
    }

    public function index() {
    $user = auth()->user();
    $orders = $user->role === 'admin' 
        ? Order::with(['items.product', 'user.profile'])->get() 
        : Order::with(['items.product', 'user.profile'])
               ->where('user_id', $user->id)
               ->get();

    return response()->json([
        'ok' => true,
        'data' => $orders
    ], 200);
    }
    public function show(Order $order) {
        $user = auth()->user();

        if($order->user_id !== $user->id){
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'ok' => true,
            'data' => $order->load('items.product')
        ], 200);
    }

        public function update(Request $request, Order $order){
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
        if (!$isAdmin) {
            return response()->json([
                'ok' => false,
                'message' => 'Status is read-only for normal users.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,paid,shipped'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'ok' => true,
            'message' => 'Order status updated successfully',
            'data' => $order
        ], 200);
    }
}
