<?php

namespace App\Http\Controllers;

use App\Models\ShoppingCart;
use App\Models\Stock;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShoppingCartController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $shoppingCartItems = $user->cartItems()
            ->with('stock.product')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($shoppingCartItems);
    }
    public function indexx()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $shoppingCartItems = $user->cartItems()
            ->with(['stock.product' => function ($query) {
                $query->select('id', 'name', 'price', 'photo');
            }])
            ->select('id', 'stock_id', 'quantity')
            ->orderBy('id', 'desc')
            ->get();
        
        $formattedItems = $shoppingCartItems->map(function ($item) {
            return [
                'id' => $item->id,
                'stock_id' => $item->stock_id,
                'quantity' => $item->quantity,
                'product' => $item->stock->product
            ];
        });
        
        return response()->json($formattedItems);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // $user = auth()->authenticate();

        $item = $user->cartItems()
            ->where('stock_id', $request->stock_id)
            ->first();

        if (!$item) {
            ShoppingCart::create([
                'user_id' => $user->id,
                'stock_id' => $request->stock_id,
                'quantity' => $request->quantity,
            ]);
        } else {
            $stock = Stock::findOrFail($request->stock_id);
            if (($item->quantity + $request->quantity) <= $stock->quantity) {
                $item->increment('quantity', $request->quantity);
            } else {
                $item->update(['quantity' => $stock->quantity]);
            }
        }

        $cartItemCount = $user->cartItems()->count();
        
        return response()->json([
            'message' => 'Item added to cart successfully',
            'cart_item_count' => $cartItemCount
        ]);
    }
    public function storee(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // $user = auth()->authenticate();

        $item = $user->cartItems()
            ->where('stock_id', $request->stock_id)
            ->first();

        if (!$item) {
            ShoppingCart::create([
                'user_id' => $user->id,
                'stock_id' => $request->stock_id,
                'quantity' => $request->quantity,
            ]);
        } else {
            $stock = Stock::findOrFail($request->stock_id);
            if (($item->quantity + $request->quantity) <= $stock->quantity) {
                $item->increment('quantity', $request->quantity);
            } else {
                $item->update(['quantity' => $stock->quantity]);
            }
        }

        $cartItemCount = $user->cartItems()->count();
        
        return response()->json([
            'message' => 'Item added to cart successfully',
            'cart_item_count' => $cartItemCount
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShoppingCart $shoppingCart)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($shoppingCart->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            "id" => $shoppingCart->id,
            "user_id" => $shoppingCart->user_id,
            "stock_id" => $shoppingCart->stock_id,
            "quantity" => $shoppingCart->quantity
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShoppingCart $shoppingCart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $cartItem = $user->cartItems()->findOrFail($id);

        if ($request->has('quantity') && is_numeric($request->quantity) && $request->quantity > 0) {
            $cartItem->update(['quantity' => $request->quantity]);
            return response()->json(['message' => 'Cart item quantity updated successfully']);
        } else {
            return response()->json(['error' => 'Invalid quantity'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $cartItem = $user->cartItems()->findOrFail($id);
        $cartItem->delete();

        return response()->json(['message' => 'Cart item deleted successfully']);
    }
}
