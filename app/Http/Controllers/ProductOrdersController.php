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
    public function index()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            // $orders = Order::where('user_id', $user->id)
            //     ->join('stocks', 'orders.stock_id', '=', 'stocks.id')
            //     ->join('products', 'stocks.product_id', '=', 'products.id')
            //     ->select('orders.id', 'products.name', 'products.photo', 'products.price', 'products.brand', 'orders.quantity', 'orders.status', 'orders.created_at')
            //     ->orderBy('orders.created_at', 'desc')
            //     ->get();

            // return response()->json($orders);
            $orders = Order::with(['stock.product'])
                ->where('user_id', $user->id)
                ->select('id', 'stock_id', 'quantity', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($orders);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
    public function indexx()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $orders = Order::with(['stock.product'])
                ->where('user_id', $user->id)
                ->select('id', 'stock_id', 'quantity', 'status')
                ->orderBy('created_at', 'desc')
                ->get();

            $ordersWithPaymentDate = $orders->map(function ($order) {
                $paymentDate = now()->subDays(rand(1, 3));
                return [
                    'id' => $order->id,
                    'stock_id' => $order->stock_id,
                    'quantity' => $order->quantity,
                    'status' => $order->status,
                    'payment_date' => $paymentDate->format('Y-m-d H:i:s'),
                    'stock' => [
                        'id' => $order->stock->id,
                        'product_id' => $order->stock->product_id,
                        'quantity' => $order->stock->quantity,
                        'size' => $order->stock->size,
                        'color' => $order->stock->color,
                        'product' => [
                            'id' => $order->stock->product->id,
                            'name' => $order->stock->product->name,
                            'price' => $order->stock->product->price,
                            'photo' => $order->stock->product->photo,
                            'description' => $order->stock->product->description,
                            'details' => $order->stock->product->details,
                            'status' => $order->stock->product->status,
                        ]
                    ],
                ];
            });

            return response()->json($ordersWithPaymentDate);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
    public function show($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            $order = Order::with(['stock.product'])
                ->where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            $productDetails = [
                'order_id' => $order->id,
                'product_id' => $order->stock->product->id,
                'name' => $order->stock->product->name,
                'photo' => $order->stock->product->photo,
                'price' => $order->stock->product->price,
                'brand' => $order->stock->product->brand,
                'quantity' => $order->quantity,
                'total_price' => $order->quantity * $order->stock->product->price,
                'status' => $order->status,
                'ordered_at' => $order->created_at->toDateTimeString(),
                'size' => $order->stock->size,
                'color' => $order->stock->color
            ];

            return response()->json(['order_details' => $productDetails], 200);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the order details'], 500);
        }
    }
    // public function show(Request $request, $productId)
    // {
    //     try {
    //         // Xác thực người dùng
    //         $user = JWTAuth::parseToken()->authenticate();
    //         $order = $user->orders()->with(['stock.product'])
    //         ->whereHas('stock.product', function($query) use ($productId) {
    //             $query->where('id', $productId);
    //         })->first();


    //         // Kiểm tra xem đơn hàng có tồn tại hay không
    //         if (!$order) {
    //             return response()->json(['error' => 'Sản phẩm không được tìm thấy trong đơn hàng của bạn.'], 404);
    //         }

    //         // Định dạng thông tin chi tiết sản phẩm
    //         $productDetails = [
    //             'id' => $order->stock->product->id,
    //             'name' => $order->stock->product->name,
    //             'photo' => $order->stock->product->photo,
    //             'price' => $order->stock->product->price,
    //             'brand' => $order->stock->product->brand,
    //             'quantity' => $order->quantity,
    //             'total_price' => $order->quantity * $order->stock->product->price,
    //             'status' => $order->status,
    //             'ordered_at' => $order->created_at->toDateTimeString(),
    //         ];

    //         return response()->json(['product_details' => $productDetails], 200);
    //     } catch (JWTException $e) {
    //         return response()->json(['error' => 'Token không hợp lệ'], 401);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Không thể lấy thông tin sản phẩm.'], 500);
    //     }
    // }

    // public function calculateOrderAmount(array $items): int
    // {
    //     $price = 0;
    //     $checkoutItems = [];
    //     foreach ($items as $item) {
    //         if ($item['quantity'] > 0) {
    //             $checkoutItems[] = ['stock_id' => $item['stock_id'], 'quantity' => $item['quantity']];
    //         } else {
    //             abort(500);
    //         }
    //     }

    //     $user = JWTAuth::parseToken()->authenticate();

    //     $cartList = $user->cartItems()
    //         ->with('stock.product')
    //         ->get();
    //     foreach ($cartList as $cartItem) {
    //         foreach ($checkoutItems as $checkoutItem) {
    //             if ($cartItem->stock_id == $checkoutItem['stock_id']) {
    //                 $price += $cartItem->stock->product->price * $checkoutItem['quantity'];
    //             }
    //         }
    //     }
    //     return $price * 100;
    // }

    // public function stripePost(Request $request)
    // {
    //     // Stripe implementation commented out
    // }

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
    public function storee(Request $request)
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




}
