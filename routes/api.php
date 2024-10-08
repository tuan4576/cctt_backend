<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductOrdersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:api')->get('/user', function (Request $request)
{ return $request->user();
});
// Route::get('/dashboard', 'App\Http\Controllers\DashboardController@index');
Route::get('/auth', 'App\Http\Controllers\UserController@getAuthenticatedUser');
Route::post('/register', 'App\Http\Controllers\UserController@register');
Route::post('/login', 'App\Http\Controllers\UserController@login');
// Route::post('/login', [UserController::class, 'login']);
Route::post('/admin/login', 'App\Http\Controllers\AdminController@login');
Route::get('/adminuser', 'App\Http\Controllers\UserController@index');


// Route::get('/user/default-address', 'App\Http\Controllers\UserAddressController@show');
// Route::post('/user/create-user-address','App\Http\Controllers\UserAddressController@createUser');
// Route::post('/user/address', 'App\Http\Controllers\UserAddressController@store');
// Route::post('/user/address', 'App\Http\Controllers\UserAddressController@store');
Route::post('/users/photo', 'App\Http\Controllers\UserController@updatePhoto');
Route::post('/users/background', 'App\Http\Controllers\UserController@updateBackground');

Route::get('/products', 'App\Http\Controllers\ProductController@index');
Route::get('/admin/products', 'App\Http\Controllers\ProductController@adminindex');
Route::get('/products/user', 'App\Http\Controllers\ProductController@getproductid');
Route::get('/products/{id}', 'App\Http\Controllers\ProductController@show');
Route::post('/products', 'App\Http\Controllers\ProductController@store');
Route::delete('/products/{id}', 'App\Http\Controllers\ProductController@destroy');
Route::put('/products/{id}/toggle-status', 'App\Http\Controllers\ProductController@toggleStatus');
Route::put('/products/{id}', 'App\Http\Controllers\ProductController@update');
// Route::put('/products/{id}', 'App\Http\Controllers\ProductController@update');
Route::post('/products/{id}/photo', 'App\Http\Controllers\ProductController@updatePhoto');

// Route::get('/products/hot-deal', 'App\Http\Controllers\ProductDealsController@hotDeals');
// Route::post('/stripe', 'App\Http\Controllers\ProductOrdersController@stripePost');
Route::post('/products/orders', 'App\Http\Controllers\ProductOrdersController@store');

Route::get('/product/categories', 'App\Http\Controllers\CategoryController@index');
Route::put('/product/categories/{id}', 'App\Http\Controllers\CategoryController@update');
// Route::get('/product/categories/{id}/top-selling','App\Http\Controllers\CategoryController@topSelling');
Route::get('/product/categories/{id}/new', 'App\Http\Controllers\CategoryController@new');
Route::get('/product/categories/{id}', 'App\Http\Controllers\CategoryController@query');

Route::get('/shopping-cart/count', 'App\Http\Controllers\ShoppingCartController@cartCount');
Route::get('/shopping-cart', 'App\Http\Controllers\ShoppingCartController@index');
Route::post('/shopping-cart', 'App\Http\Controllers\ShoppingCartController@store');
Route::put('/shopping-cart/{id}', 'App\Http\Controllers\ShoppingCartController@update');
Route::delete('/shopping-cart/{id}', 'App\Http\Controllers\ShoppingCartController@destroy');

Route::get('/products/wishlist/count', 'App\Http\Controllers\ProductWishlistController@count');
Route::get('/products/wishlist', 'App\Http\Controllers\ProductWishlistController@index');
Route::post('/products/wishlist', 'App\Http\Controllers\ProductWishlistController@store');
Route::delete('/products/wishlist/{id}', 'App\Http\Controllers\ProductWishlistController@destroy');

#
Route::get('/products/stocks/{id}', 'App\Http\Controllers\Stocks@show');
Route::post('/newsletter', 'App\Http\Controllers\NewsLetterController@store');
// Route::resource('products', ProductController::class);
// Route::resource('categories', CategoryController::class);

Route::post('/product/orders', 'App\Http\Controllers\ProductOrdersController@store');
Route::get('/product/orders', 'App\Http\Controllers\ProductOrdersController@index');


Route::post('/create-payment', [PaymentController::class, 'createPayment']);
Route::get('/paypal-success', [PaymentController::class, 'paypalSuccess'])->name('paypal.success');
Route::get('/paypal-cancel', [PaymentController::class, 'paypalCancel'])->name('paypal.cancel');






// Route::get('/dashboard', [DashboardController::class, 'index']);

// // JWT Authenficiation
// // Route::get('/auth', 'App\Http\Controllers\UserController@getAuthenticatedUser');
// Route::get('/auth', [UserController::class, 'me']);
// Route::get('/users', [UserController::class, 'index']);
// Route::post('/register', [UserController::class, 'register']);
// Route::post('/login', [UserController::class, 'login']);
// Route::post('/logout', [UserController::class, 'logout']);

// // Address
// Route::get('/user/default-address', [UserAddressController::class, 'show']);
// Route::post('/user/create-user-address', [UserAddressController::class, 'createUser']);
// Route::post('/user/address', [UserAddressController::class, 'store']);

// // Product
// Route::get('/products/newest', [ProductController::class, 'newestProduct']);
// Route::get('/products/top-selling', [ProductController::class, 'topSelling']);
// Route::get('/products', [ProductController::class, 'index']);
// Route::get('/products/{id}', [ProductController::class, 'show']);
// Route::put('/products/{id}', [ProductController::class, 'update']);
// Route::post('/products', [ProductController::class, 'store']);
// Route::get('/products/search/{name}', [ProductController::class, 'search']);
// Route::delete('/products/{id}', [ProductController::class, 'destroy']);
// Route::get('/products/categories/{id}', [ProductController::class, 'productByCategory']);

// // Product Deal
// Route::get('/product/hot-deal', [ProductDealsController::class,'hotDeals']);

// // Product Orders
// // Route::post('/stripe', [ProductOrdersController::class,'stripePost']);
// Route::post('/product/orders', [ProductOrdersController::class,'store']);
// Route::get('/product/orders', [ProductOrdersController::class,'index']);

// // Order
// Route::post('/order', [UserOrderController::class,'store']);
// Route::get('/orders', [UserOrderController::class,'index']);
// Route::get('/orders/{id}', [UserOrderController::class,'show']);
// Route::put('/orders/{id}', [UserOrderController::class,'update']);

// // Order Detail
// Route::post('/orderdetail', [UserOrderDetailController::class,'store']);
// Route::get('/orderdetail/{id}', [UserOrderDetailController::class,'index']);

// // Categories
// Route::get('/categories', [CategoryController::class,'index']);
// Route::get('/categories/{id}', [CategoryController::class,'show']);
// Route::post('/categories', [CategoryController::class,'store']);
// Route::delete('/categories/{id}', [CategoryController::class,'destroy']);
// Route::put('/categories/{id}', [CategoryController::class,'update']);


// // Product Shopping Cart
// Route::get('/product/cart-list/count', [ProductShoppingCartController::class,'cartCount']);
// Route::get('/product/cart-list', [ProductShoppingCartController::class,'index']);
// Route::post('/product/cart-list', [ProductShoppingCartController::class,'store']);
// Route::post('/product/cart-list/guest', [ProductShoppingCartController::class,'guestCart']);
// Route::put('/product/cart-list/{id}', [ProductShoppingCartController::class,'update']);
// Route::get('/product/cart-list/{id}', [ProductShoppingCartController::class,'show']);
// Route::delete('/product/cart-list/{id}', [ProductShoppingCartController::class,'destroy']);

// //Product Wishlist
// Route::get('/product/wishlist/count', [ProductWishlistController::class,'count']);
// Route::get('/product/wishlist', [ProductWishlistController::class,'index']);
// Route::post('/product/wishlist', [ProductWishlistController::class,'store']);
// Route::delete('/product/wishlist/{id}', [ProductWishlistController::class,'destroy']);

// // Product Stocks
// Route::get('/stocks/{id}', [StockController::class,'show']);
// Route::put('/stocks/{id}', [StockController::class,'update']);

// // Newsletter
// Route::post('/newsletter', [NewsLetterController::class,'store']);
