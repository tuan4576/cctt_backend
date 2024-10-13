<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductOrdersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:api')->get('/user', function (Request $request)
{ return $request->user();
});
Route::get('/auth', 'App\Http\Controllers\UserController@getAuthenticatedUser');
Route::post('/register', 'App\Http\Controllers\UserController@register');
Route::post('/login', 'App\Http\Controllers\UserController@login');
Route::post('/users/photo', 'App\Http\Controllers\UserController@updatePhoto');
Route::post('/users/background', 'App\Http\Controllers\UserController@updateBackground');

Route::get('/products', 'App\Http\Controllers\ProductController@index');
Route::get('/products/user', 'App\Http\Controllers\ProductController@getproductid');
Route::get('/products/{id}', 'App\Http\Controllers\ProductController@show');
// Route::put('/products/{id}', 'App\Http\Controllers\ProductController@update');


// Route::get('/products/hot-deal', 'App\Http\Controllers\ProductDealsController@hotDeals');
// Route::post('/stripe', 'App\Http\Controllers\ProductOrdersController@stripePost');
// Route::post('/products/orders', 'App\Http\Controllers\ProductOrdersController@store');
// Route::get('/products/orders', 'App\Http\Controllers\ProductOrdersController@index');

// Route::get('/products/orders/{id}', 'App\Http\Controllers\ProductOrdersController@show');


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

Route::post('/product/orders', 'App\Http\Controllers\ProductOrdersController@store');
Route::get('/product/orders', 'App\Http\Controllers\ProductOrdersController@index');
Route::get('/product/orders/{id}', 'App\Http\Controllers\ProductOrdersController@show');


// Route::post('/create-payment', [PaymentController::class, 'createPayment']);
// Route::get('/paypal-success', [PaymentController::class, 'paypalSuccess'])->name('paypal.success');
// Route::get('/paypal-cancel', [PaymentController::class, 'paypalCancel'])->name('paypal.cancel');



// admin 
Route::post('/admin/login', 'App\Http\Controllers\AdminController@login');

Route::get('/admin/products', 'App\Http\Controllers\ProductController@adminindex');
Route::post('/products', 'App\Http\Controllers\ProductController@store');
Route::put('/products/{id}', 'App\Http\Controllers\ProductController@update');

Route::delete('/products/{id}', 'App\Http\Controllers\ProductController@destroy');
Route::put('/products/{id}/toggle-status', 'App\Http\Controllers\ProductController@toggleStatus');
Route::post('/products/{id}/photo', 'App\Http\Controllers\ProductController@updatePhoto');
Route::get('/adminuser', 'App\Http\Controllers\UserController@index');

// Route::get('/admin/categories', 'App\Http\Controllers\CategoryController@adminindex');
Route::post('/admin/categories', 'App\Http\Controllers\CategoryController@adminstore');
Route::get('/admin/categories/{id}', 'App\Http\Controllers\CategoryController@getCategoryById');

Route::put('/admin/categories/{id}', 'App\Http\Controllers\CategoryController@adminupdate');
Route::delete('/admin/categories/{id}', 'App\Http\Controllers\CategoryController@admindestroy');




// Mobile routes
Route::prefix('mobile')->group(function () {
    // auth
    Route::post('/login', 'App\Http\Controllers\UserController@login');
    Route::post('/register', 'App\Http\Controllers\UserController@registerr');
    //prduct
    Route::get('/products', 'App\Http\Controllers\ProductController@mobileindex');
    //category
    Route::get('/categories', 'App\Http\Controllers\CategoryController@mobileindex');
    Route::get('/categories/{id}/products', 'App\Http\Controllers\ProductController@productByCategory');
    //shoping cart
    Route::post('/shopping-cart', 'App\Http\Controllers\ShoppingCartController@storee');
    Route::get('/shopping-cart', 'App\Http\Controllers\ShoppingCartController@indexx');
    Route::put('/shopping-cart/{id}', 'App\Http\Controllers\ShoppingCartController@update');
    Route::delete('/shopping-cart/{id}', 'App\Http\Controllers\ShoppingCartController@destroy');
    //product orders
    Route::post('/orders', 'App\Http\Controllers\ProductOrdersController@storee');
    Route::get('/orders', 'App\Http\Controllers\ProductOrdersController@indexx');
});
