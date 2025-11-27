<?php

namespace App\Http\Controllers;
use App\Models\Cart;
use App\Models\Product;
use Validator;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store (Request $request){
        $validator = validator::make($request->all(),[
           // "user_id" => "required|exists:users,id",
            "product_id" => "required|exists:products,id",
            "quantity" => "required|integer|min:1|max:10",
        ]);

        if($validator->fails()){
            return response()->json([
                "ok" => false,
                "message" => "Request didn't pass the validation",
                "errors" => $validator->errors()
            ],400);
        }

        $validated = $validator->validated();
        $product = Product::find($validated['product_id']);

        $total_price = $product->price * $validated['quantity'];

        if ($validated['quantity'] > $product->stock) {
            return response()->json([
                "ok" => false,
                "message" => "Not enough stock available",
            ], 400);
        }

        $cart = Cart::create([
            "user_id" => auth()->user()->id,
            "product_id" => $validated["product_id"],
            "quantity" => $validated["quantity"],
            "total_price" => $total_price,
        ]);
        return response()->json([
            "ok" => true,
            "message" => "Cart item has been created!",
            "data" => $cart
        ],201);
    }

    public function index() {
        $user = auth()->user();
        $cartItems = Cart::with('product')->where('user_id', $user->id)->get();

        return response()->json([
            'ok' => true,
            'data' => $cartItems
        ], 200);
    }
    public function show (Request $request, Cart $cart){
        if($cart->user_id !== auth()->user()->id){
            return response()->json ([
                "ok" => false,
                "message" => "You are not authorized to view this cart item."
            ],403);
        }

        return response()->json ([
            "ok" => true,
            "data" => $cart
        ],200);
    }

    public function update (Request $request, Cart $cart){
        if($cart->user_id !== auth()->user()->id){
            return response()->json ([
                "ok" => false,
                "message" => "You are not authorized to update this cart item."
            ],403);
        }

        $validator = validator::make($request->all(),[
            "quantity" => "required|integer|min:1|max:10",
        ]);

        if($validator->fails()){
            return response()->json([
                "ok" => false,
                "message" => "Request didn't pass the validation",
                "errors" => $validator->errors()
            ],400);
        }

        $validated = $validator->validated();
        $product = Product::find($cart->product_id);

        $total_price = $product->price * $validated['quantity'];

        if ($validated['quantity'] > $product->stock) {
            return response()->json([
                "ok" => false,
                "message" => "Not enough stock available",
            ], 400);
        }

        $cart->quantity = $validated['quantity'];
        $cart->total_price = $total_price;
        $cart->save();

        return response()->json ([
            "ok" => true,
            "message" => "Cart item has been updated!",
            "data" => $cart
        ],200);
    }

    public function destroy (Request $request, Cart $cart){
        if($cart->user_id !== auth()->user()->id){
            return response()->json ([
                "ok" => false,
                "message" => "You are not authorized to delete this cart item."
            ],403);
        }

        $cart->delete();

        return response()->json ([
            "ok" => true,
            "message" => "Cart item has been deleted!"
        ],200);
    }
}
