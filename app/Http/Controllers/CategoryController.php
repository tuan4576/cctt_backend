<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Review;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Category::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function new($id)
    // {
    //     $products = Product::with('category')->where('category_id', $id)->orderBy('id', 'desc')->paginate(5);

    //     foreach($products as $product) {
    //         if($product->reviews()->exists()) {
    //             $product['review'] = $product->reviews()->avg('rating');
    //         }
    //     }
    //     return $products;
    // }

    
    // public function topSelling($id) {

    //     $products = Product::with('category')->where('category_id', $id)->take(6)->get();

    //     foreach($products as $product) {
    //         if($product->reviews()->exists())
    //             $product['review'] = $product->reviews()->avg('rating');

    //         if($product->stocks()->exists()) {
    //             $num_orders = 0;
    //             $stocks = $product->stocks()->get();
    //             foreach($stocks as $stock)
    //                 $num_orders += $stock->orders()->count();
    //             $product['num_orders'] = $num_orders;
    //         }  else {
    //             $product['num_orders'] = 0;
    //         }
    //     }
    //     return $products->sortByDesc('num_orders')->values()->all();
    // }



    public function store(Request $request)
    {
        // Validate the required fields
        $request->validate([
            'name' => 'required',
            'description' => 'required',
        ]);

        try {
            // Create a new category with the request data
            Category::create($request->all());

            // Return a success response
            return response()->json([
                'message' => 'Category Created Successfully!!'
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Category creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Category creation failed!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:categories,name,' . $id,
        ]);

        try {
            $name = $request->input('name');
            if ($name) {
                $category->name = $name;
                $category->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Category updated successfully',
                    'data' => $category
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No name provided in the form-data',
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Category update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Category update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        try {
            // Delete the category
            $category->delete();

            // Return a success response
            return response()->json([
                'message' => 'Category Deleted Successfully!!'
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Category deletion failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Category deletion failed!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Query products by category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function query($id)
    {
        try {
            $category = Category::findOrFail($id);
            $products = Product::where('category_id', $id)
                ->where('status', 1)  // Only include products with status 1
                ->with('stocks')
                ->paginate(40);

            return response()->json([
                'category' => $category,
                'products' => $products
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error querying products by category: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error querying products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get top selling products for a category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function topSelling($id)
    {
        try {
            $category = Category::findOrFail($id);
            $products = Product::where('category_id', $id)
                ->withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->with('stocks')
                ->take(10)
                ->get();

            return response()->json([
                'category' => $category,
                'products' => $products
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting top selling products: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error getting top selling products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get new products for a category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function new($id)
    {
        try {
            $category = Category::findOrFail($id);
            $products = Product::where('category_id', $id)
                ->orderBy('created_at', 'desc')
                ->with('stocks')
                ->take(10)
                ->get();

            return response()->json([
                'category' => $category,
                'products' => $products
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting new products: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error getting new products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
