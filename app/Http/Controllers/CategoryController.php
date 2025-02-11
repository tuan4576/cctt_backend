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
    public function queryMobile($id)
    {
        try {
            $category = Category::findOrFail($id);
            $products = Product::where('category_id', $id)
                ->where('status', 1)
                ->with('stocks')
                ->paginate(40);

            $transformedProducts = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'photo' => $product->photo,
                    'category' => $product->category ? $product->category->name : null,
                    'in_stock' => $product->stocks->sum('quantity') > 0,
                    'description' => $product->description,
                    'details' => $product->details,
                    'status' => $product->status,
                    'stock_id' => $product->stocks->first() ? $product->stocks->first()->id : null
                ];
            });

            return response()->json([
                'category' => $category,
                'current_page' => $products->currentPage(),
                'data' => $transformedProducts,
                'first_page_url' => $products->url(1),
                'from' => $products->firstItem(),
                'last_page' => $products->lastPage(),
                'last_page_url' => $products->url($products->lastPage()),
                'next_page_url' => $products->nextPageUrl(),
                'path' => $products->path(),
                'per_page' => $products->perPage(),
                'prev_page_url' => $products->previousPageUrl(),
                'to' => $products->lastItem(),
                'total' => $products->total(),
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

    public function mobileindex()
    {
        $categories = Category::select('id', 'name', 'photo')->get();
        return response()->json($categories);
    }
    /**
     * Update the specified category name in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function adminupdate(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $category->name = $request->name;
            $category->save();

            return response()->json([
                'message' => 'Category name updated successfully',
                'category' => $category
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating category name: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error updating category name',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Get products by category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getCategoryById($id)
    {
        try {
            $category = Category::findOrFail($id);
            return response()->json([
                'id' => $category->id,
                'name' => $category->name
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting category: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error getting category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Store a newly created category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function adminstore(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:categories',
            ]);

            $category = new Category();
            $category->name = $validatedData['name'];
            $category->save();

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error creating category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Remove the specified category from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admindestroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
