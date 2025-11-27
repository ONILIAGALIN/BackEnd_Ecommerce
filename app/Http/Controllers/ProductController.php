<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Validator;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * store a newly created product in storage.
     * POST::/api/products
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store (Request $request){
        $validator = validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            "rating" => 'sometimes|numeric|min:0|max:5',
            'image' => "required|image|mimes:jpeg,jpg,png|max:32000" // Ensure the image is required and valid
        ]);

        if($validator->fails()){
            return response()->json([
                "ok" => false,
                "message" => "Request didn't pass validation",
                "errors" => $validator->errors()
            ],400);
        }

        $validated = $validator->validated();
        if (isset($validated['image'])) {
            $image = $request->file("image");
        }
        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            "rating" => $validated['rating'] ?? null,
            'extension' => isset($image) ? $image->getClientOriginalExtension() : null
        ]);
        if (isset($validated['image'])) {
            $image->move(public_path('storage/uploads/products'), $product->id . '.' . $image->getClientOriginalExtension());
        }

        return response()->json([
            'ok' => true,
            'data' => $product,
            'message' => "New product has been created"
        ], 201);
    }

    /**
     * Display a listing of the products.
     * GET::/api/products
     * @return \Illuminate\Http\Response
     */

    public function index(){
        return response()->json([
            "ok" => true,
            "message" => "Products Retrieved Successfully",
            "data" => Product::all()
        ]);
    }

    /**
     * Retrieve the specific product using id.
     * Get::/api/[products]/{products}
     * @param Request
     * @param  Product
     * @return \Illuminate\Http\Response
     */

    public function show (Request $request, Product $product){
        return response()->json([
            "ok" => true,
            "message" => "Specific Product Retrieved successfully",
            "data" => $product
        ],200);
    }

    /**
     * Update specific product using inputs from Request and id from URI
     * PATCH: /api/products/{product}
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, Product $product){
        $validator = validator($request->all(),[
            'name' => 'sometimes|string|max:255|unique:products,name,'.$product->id,
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'rating' => 'sometimes|numeric|min:0|max:5',
            'extension' => 'sometimes|string',
        ]);
        if($validator->fails()){
            return response()->json ([
                "ok" => false,
                "message" => "Request didn't pass validation",
                "errors" => $validator->errors()
            ],400);
        }

        $validated = $validator->validated();
        if(isset($validated['image'])){
          $image = $request->file("image");
          $validated['extension'] =  isset($image) ? $image->getClientOriginalExtension() : null;
          unset($validated['image']);
        }
        $product->update($validated);
        if(isset($image)){
          $image->move(public_path('storage/uploads/products'),  $product->id. '.' .  $image->getClientOriginalExtension());
          // Storage::put('/uploads/product/' . $product->id. '.' .  $image->getClientOriginalExtension(), $image);
        }
        return response()->json([
            "ok" => true,
            "message" => "Product Updated successfully",
            "data" => $product
        ],200);
    }

     /**
     * Delete specific product using id from URI
     * DELETE: /api/products/{product}
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
            Storage::disk('public')->delete($product->image_url);
        }
        $product->delete();
        return response()->json([
            'ok' => true,
            'message' => "Product deleted successfully"
        ], 200);
    }
}
