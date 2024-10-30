<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Wishlist;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function adminindex(Request $request)
    {
        $perPage = $request->input('perPage', 58);
        
        $products = Product::with('category', 'stocks')
            ->orderByRaw('CASE WHEN status = 1 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($products);
    }
    public function index(Request $request)
    {
        $perPage = 58;
        if ($request->perPage){
            $perPage = $request->perPage;
        }
        
        return Product::with('category', 'stocks')
            ->where('status', 1)  // Only include products with status 1
            ->orderBy('created_at','desc')
            ->paginate($perPage);
    }

    public function productByCategory($category){
        return Product::with('category', 'stocks')
            ->where('category_id', $category)
            ->orderBy('created_at','desc')
            ->paginate(4);
    }
    
    public function newestProduct(){
        return Product::with('category', 'stocks')            
            ->orderBy('created_at','desc')
            ->paginate(4);
    }

    public function topSelling()
    {                        
        $products = DB::table('products')
                ->join('orders','orders.stock_id', '=','products.id')
                ->join('categories','categories.id', '=', 'products.category_id')
                ->select(DB::raw('products.id, products.name, products.category_id, categories.name as category_name, photo, price, SUM(quantity) as total_quantity'))
                ->where('orders.status','=','completed')
                ->groupBy('products.id','products.name', 'products.category_id','categories.name','photo', 'price')
                ->orderBy('total_quantity','desc')
                ->limit(4)
                ->get();
                
        return $products;       
    }

    public function show($id)
    {
        $product = Product::with('category', 'stocks')->findOrFail($id);
        if ($product->reviews()->exists()) {
            $product['review'] = $product->reviews()->avg('rating');
            $product['num_reviews'] = $product->reviews()->count();
        }
        return $product;
    }

    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = $user->id;

        $validatedData = $request->validate([
            'category_id' => 'required|integer',
            'brand' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'details' => 'required|string',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'status' => 'required|boolean',
        ]);

        $data = 'Untitled.png';
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $originalFileName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = pathinfo($originalFileName, PATHINFO_FILENAME);
            $newFileName = $fileName;
            $counter = 1;

            while (file_exists(public_path('../public/img/image/' . $newFileName . '.' . $extension))) {
                $newFileName = $fileName . '_' . $counter;
                $counter++;
            }

            $finalFileName = $newFileName . '.' . $extension;
            $path = $file->move(public_path('../public/img/image'), $finalFileName);
            $url = asset('image/' . $finalFileName);
            
            $data = $finalFileName;
        }

        // Create the product
        $product = Product::create([
            'user_id' => $userId,
            'category_id' => $validatedData['category_id'],
            'photo' => $data,
            'brand' => $validatedData['brand'],
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'details' => $validatedData['details'],
            'price' => $validatedData['price'],
            'discount' => $validatedData['discount'],
            'status' => $validatedData['status'],
        ]);
            
        $stock = Stock::create([
            'product_id' => $product->id,
            'size' => $request->size,
            'color' => $request->color,
            'quantity' => $request->quantity,
        ]);

        return response()->json(compact('product', 'stock'));
    }
    public function update(Request $request, $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        $product = Product::findOrFail($id);

        if ($product->user_id !== $user->id && $user->role_id != 1) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $validatedData = $request->validate([
            'category_id' => 'sometimes|required|integer',
            'brand' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'details' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'discount' => 'sometimes|nullable|numeric',
        ]);

        $product->fill($validatedData);
        $product->save();

        $product = $product->fresh();

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }

    public function updatePhoto(Request $request, $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $product = Product::findOrFail($id);

        if ($product->user_id !== $user->id && $user->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($product->photo) {
                $oldPhotoPath = public_path('../public/img/image/' . $product->photo);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            $file = $request->file('photo');
            $originalFileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = pathinfo($originalFileName, PATHINFO_FILENAME);

            // Generate a unique filename if it already exists
            $counter = 1;
            while (file_exists(public_path('../public/img/image/' . $originalFileName))) {
                $originalFileName = $fileName . '_' . $counter . '.' . $fileExtension;
                $counter++;
            }

            $path = $file->move(public_path('../public/img/image'), $originalFileName);
            
            $product->photo = $originalFileName;
            $product->save();

            return response()->json(['photo' => $originalFileName]);
        }

        return response()->json(['error' => 'No photo provided'], 400);
    }

    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $product = Product::findOrFail($id);
        if ($product->user_id !== $user->id) {
            return response()->json(['Error' => 'Unauthorized'], 403);
        }

        if ($product->photo != null) {
            $photoPath = public_path('../public/img/image/') . $product->photo;
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }
        $product->delete();

        return response()->json(['Message' => 'Successfully deleted product']);
    }


    
    public function getproductid(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        
        $perPage = $request->input('perPage', 100); // Default to 10 items per page if not specified
        
        $query = Product::with('category', 'stocks');
        
        if ($user->role_id != 1) {
            $query->where('user_id', $user->id);
        }
        
        $products = $query->orderBy('created_at', 'desc')
                          ->paginate($perPage);
        
        return response()->json($products);
    }
    public function toggleStatus($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $product = Product::findOrFail($id);

        if ($product->user_id !== $user->id && $user->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product->status = !$product->status;
        $product->save();

        $statusMessage = $product->status ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Product successfully {$statusMessage}",
            'status' => $product->status
        ]);
    }

    public function mobileindex(Request $request)
    {
        $perPage = $request->input('perPage', 6); // Default to 6 items per page for mobile
        
        $products = Product::with(['category', 'stocks', 'wishlists'])
            ->where('status', 1) // Only include active products
            ->paginate($perPage);

        // Transform the data to include only necessary information for mobile view
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
                'stock_id' => $product->stocks->first() ? $product->stocks->first()->id : null,
                'wishlist_id' => $product->wishlists->isNotEmpty() ? $product->wishlists->first()->id : null
            ];
        });

        return response()->json([
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
    }
    public function search(Request $request, $name)
    {
        $products = Product::where('name', 'LIKE', "%{$name}%")
            ->where('status', 1) // Only include active products
            ->with(['category', 'stocks', 'wishlists'])
            ->get();

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
                'stock_id' => $product->stocks->first() ? $product->stocks->first()->id : null,
                'wishlist_id' => $product->wishlists->isNotEmpty() ? $product->wishlists->first()->id : null
            ];
        });

        return response()->json($transformedProducts);
    }
    // public function search($name){
//     return Product::with('category','stocks')
//         ->where('name', 'like', '%'.$name.'%')
//         ->orderBy('created_at','desc')
//         ->paginate(4);
// }
}

