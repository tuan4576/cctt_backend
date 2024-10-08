<?php

namespace App\Http\Controllers;
// use Stripe;
use App\Models\Order;
use App\Models\Stock;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductOrdersController extends Controller
{
    public function calculateOrderAmount(array $items): int
    {
        $price = 0;
        $checkoutItems = [];
        foreach ($items as $item) {
            if ($item['quantity'] > 0) {
                $checkoutItems[] = ['stock_id' => $item['stock_id'], 'quantity' => $item['quantity']];
            } else {
                abort(500);
            }
        }

        $user = JWTAuth::parseToken()->authenticate();

        $cartList = $user->cartItems()
            ->with('stock.product')
            ->get();
        foreach ($cartList as $cartItem) {
            foreach ($checkoutItems as $checkoutItem) {
                if ($cartItem->stock_id == $checkoutItem['stock_id']) {
                    $price += $cartItem->stock->product->price * $checkoutItem['quantity'];
                }
            }
        }
        return $price * 100;
    }

    public function stripePost(Request $request)
    {
        // Stripe implementation commented out
    }

    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $note = "";
        
        $items = $request->json()->all();
        
        foreach ($items as $item) {
            Order::create([
                'user_id' => $user->id,
                'stock_id' => $item['stock_id'],
                'quantity' => $item['quantity'],
                'note' => $note,
                'status' => 'completed',
            ]);
            
            Stock::findOrFail($item['stock_id'])->decrement('quantity', $item['quantity']);
            $user->cartItems()->where('stock_id', $item['stock_id'])->delete();
        }
        
        return response()->json(['message' => 'Orders created successfully'], 201);
    }
    // public function store(Request $request)
    // {
    //     $user = JWTAuth::parseToken()->authenticate();
    //     $note = "";
    //     Order::create([
    //         'user_id' => $user->id,
    //         'stock_id' => $request->stock_id, 
    //         'quantity' => $request->quantity,
    //         'note' => $note,
    //         'status' => 'completed',
    //     ]);
    //     Stock::findOrFail($request->stock_id)->decrement('quantity', $request->quantity);
    //     $user->cartItems()->where('stock_id', $request->stock_id)->delete();
    // }

    public function show(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $orders = $user->orders()->with('stock.product')->get();
        return $orders;
    }

    public function index()
    {
        $orders = DB::table('orders')
                ->join('users','users.id', '=','orders.user_id')
                ->join('products','products.id', '=', 'orders.stock_id')
                ->select(DB::raw('users.name, products.category_id, products.name, photo, price, brand, status, orders.created_at'))            
                ->groupBy('users.name', 'products.category_id', 'products.name', 'photo', 'price', 'brand', 'status', 'orders.created_at')
                ->orderBy('orders.created_at','desc')
                ->limit(4)
                ->get();

        return $orders;
    }
    
}
