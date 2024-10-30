<?php
namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Stock;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductWishlistController extends Controller
{
    public function index(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        
        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->with('product')
            ->get();

        $products = $wishlistItems->map(function ($item) {
            $product = $item->product;
            if (!$product) {
                return null;
            }
            
            $stock = Stock::where('product_id', $product->id)->first();
            
            return [
                'id' => $item->id,  // This is now the wishlist item id
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'photo' => $product->photo,
                'description' => $product->description,
                'details' => $product->details,
                'quantity' => $stock ? $stock->quantity : 0,
                'status' => $product->status,
                'stock_id' => $stock ? $stock->id : null,
            ];
        })->filter()->values();

        return response()->json($products);
    }

    public function store(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        
        $product = Wishlist::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();
        
        if ($product === null) {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id
            ]);
            return response()->json(['message' => 'Product added to wishlist successfully'], 200);
        } else {
            return response()->json(['message' => 'Product already in wishlist'], 400);
        }
    }

    public function destroy($id) {
        $wishlistItem = Wishlist::findOrFail($id);
        $wishlistItem->delete();
        return response()->json(['message' => 'Product removed from wishlist successfully'], 200);
    }

    public function count(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        return $user->wishlistProducts()->count();
    }
}
