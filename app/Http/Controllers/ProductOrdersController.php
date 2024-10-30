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
            $orders = Order::with(['stock.product'])
                ->where('user_id', $user->id)
                ->select('id', 'stock_id', 'quantity', 'status', 'created_at', 'order_code')
                ->orderBy('created_at', 'desc')
                ->get();

            $groupedOrders = $orders->groupBy('order_code')->map(function ($group) {
                $totalQuantity = $group->sum('quantity');
                $totalAmount = $group->sum(function ($order) {
                    return $order->quantity * $order->stock->product->price;
                });

                return [
                    'order_code' => $group->first()->order_code,
                    'order_date' => $group->first()->created_at,
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount,
                    'status' => $group->first()->status,
                    'items' => $group->map(function ($order) {
                        return [
                            'product_name' => $order->stock->product->name,
                            'quantity' => $order->quantity,
                            'price' => $order->stock->product->price,
                            'subtotal' => $order->quantity * $order->stock->product->price,
                        ];
                    }),
                ];
            })->values();

            return response()->json($groupedOrders);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
    public function indexxx()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $orders = Order::with(['stock.product'])
                ->where('user_id', $user->id)
                ->select('id', 'stock_id', 'quantity', 'status', 'created_at', 'order_code')
                ->orderBy('created_at', 'desc')
                ->get();

            $groupedOrders = $orders->groupBy('order_code')->map(function ($group) {
                $totalQuantity = $group->sum('quantity');
                $totalAmount = $group->sum(function ($order) {
                    return $order->quantity * $order->stock->product->price;
                });

                return [
                    'order_code' => $group->first()->order_code,
                    'order_date' => $group->first()->created_at,
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount,
                    'status' => $group->first()->status,
                    'items' => $group->map(function ($order) {
                        return [
                            'product_id' => $order->stock->product->id,
                            'product_name' => $order->stock->product->name,
                            'quantity' => $order->quantity,
                            'price' => $order->stock->product->price,
                            'subtotal' => $order->quantity * $order->stock->product->price,
                            'photo' => $order->stock->product->photo,
                            'description' => $order->stock->product->description,
                            'details' => $order->stock->product->details,
                            'status' => $order->stock->product->status,
                            'stock_id' => $order->stock_id,
                        ];
                    }),
                ];
            })->values();

            return response()->json($groupedOrders);

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
    public function show($orderId)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            $order = Order::with(['stock.product'])
                ->where('user_id', $user->id)
                ->where('id', $orderId)
                ->firstOrFail();

            $orderDetails = [
                'id' => $order->id,
                'status' => $order->status,
                'ordered_at' => $order->created_at->toDateTimeString(),
                'total_price' => $order->quantity * $order->stock->product->price,
                'product' => [
                    'id' => $order->stock->product->id,
                    'name' => $order->stock->product->name,
                    'photo' => $order->stock->product->photo,
                    'price' => $order->stock->product->price,
                    'brand' => $order->stock->product->brand,
                    'description' => $order->stock->product->description,
                    'details' => $order->stock->product->details,
                ],
                'quantity' => $order->quantity,
                'size' => $order->stock->size,
                'color' => $order->stock->color
            ];

            return response()->json($orderDetails, 200);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the order details'], 500);
        }
    }
    
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $note = "";
        
        $items = $request->json()->all();
        
        // Generate a unique order code
        do {
            $orderCode = '#' . strtoupper(substr(md5(now() . $user->id . rand()), 0, 6));
        } while (Order::where('order_code', $orderCode)->exists());
        
        foreach ($items as $item) {
            Order::create([
                'user_id' => $user->id,
                'stock_id' => $item['stock_id'],
                'quantity' => $item['quantity'],
                'note' => $note,
                'status' => 'completed',
                'order_code' => $orderCode,
            ]);
            
            Stock::findOrFail($item['stock_id'])->decrement('quantity', $item['quantity']);
            $user->cartItems()->where('stock_id', $item['stock_id'])->delete();
        }
        
        return response()->json([
            'message' => 'Orders created successfully',
            'order_code' => $orderCode
        ], 201);
    }
    public function storee(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $note = "";
        
        $items = $request->json()->all();
        
        // Generate a unique order code
        do {
            $orderCode = '#' . strtoupper(substr(md5(now() . $user->id . rand()), 0, 6));
        } while (Order::where('order_code', $orderCode)->exists());
        
        foreach ($items as $item) {
            Order::create([
                'user_id' => $user->id,
                'stock_id' => $item['stock_id'],
                'quantity' => $item['quantity'],
                'note' => $note,
                'status' => 'completed',
                'order_code' => $orderCode,
            ]);
            
            Stock::findOrFail($item['stock_id'])->decrement('quantity', $item['quantity']);
            $user->cartItems()->where('stock_id', $item['stock_id'])->delete();
        }
        
        return response()->json([
            'message' => 'Orders created successfully',
            'order_code' => $orderCode
        ], 201);
    }
}
