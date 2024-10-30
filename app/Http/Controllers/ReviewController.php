<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($productId)
    {
        $reviews = Review::with(['user', 'replies'])
            ->where('product_id', $productId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'reviews' => $reviews,
            'message' => 'Danh sách đánh giá cho sản phẩm đã được lấy thành công.'
        ], 200);
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
        // Validate the request data
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'review' => 'required|string|max:1000',
            'rating' => 'required_without:parent_id|nullable|integer|min:1|max:5',
            'parent_id' => 'nullable|exists:reviews,id'
        ]);

        // Get the authenticated user
        $user = JWTAuth::parseToken()->authenticate();

        // Check if it's a reply to an existing review
        if (isset($validatedData['parent_id'])) {
            // For replies, we don't need to check if the user has purchased the product
            $review = new Review();
            $review->user_id = $user->id;
            $review->product_id = $validatedData['product_id'];
            $review->review = $validatedData['review'];
            $review->parent_id = $validatedData['parent_id'];
            $review->save();
        } else {
            // For new reviews, check if the user has purchased the product
            $hasPurchased = \App\Models\Order::whereHas('stock', function ($query) use ($validatedData) {
                $query->where('product_id', $validatedData['product_id']);
            })
            ->where('user_id', $user->id)
            ->exists();

            if (!$hasPurchased) {
                return response()->json([
                    'message' => 'Bạn chưa mua sản phẩm này nên không thể đánh giá'
                ], 403);
            }

            // Create a new review
            $review = new Review();
            $review->user_id = $user->id;
            $review->product_id = $validatedData['product_id'];
            $review->review = $validatedData['review'];
            $review->rating = $validatedData['rating'];
            $review->save();
        }

        // Return a response
        return response()->json([
            'message' => 'Đánh giá đã được tạo thành công',
            'review' => $review
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        //
    }
}
