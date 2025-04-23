<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SellerReview;
use App\Models\TradeTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SellerReviewController extends Controller
{
    /**
     * Display reviews for a specific seller
     * 
     * Route: GET /seller-reviews/{sellerCode}
     */
    public function index($sellerCode)
    {
        $reviews = SellerReview::with('reviewer:id,first_name,last_name')
            ->where('seller_code', $sellerCode)
            ->latest()
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'reviewer_id' => $review->reviewer_id,
                    'reviewer' => [
                        'name' => $review->reviewer->first_name . ' ' . $review->reviewer->last_name,
                    ],
                    'rating' => $review->rating,
                    'review' => $review->review,
                    'is_verified_purchase' => $review->is_verified_purchase,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'seller_code' => 'required|exists:users,seller_code',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:1000',
                'transaction_id' => 'required|integer',
                'transaction_type' => ['required', Rule::in(['order', 'trade'])],
            ]);

            // Verify transaction belongs to the user and is completed
            if ($validated['transaction_type'] === 'order') {
                $transaction = Order::where('id', $validated['transaction_id'])
                    ->where('buyer_id', Auth::id())
                    ->where('status', 'Completed')
                    ->first();
            } else {
                $transaction = TradeTransaction::where('id', $validated['transaction_id'])
                    ->where('buyer_id', Auth::id())
                    ->where('status', 'completed')
                    ->first();
            }

            if (!$transaction) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or incomplete transaction',
                        'debug' => [
                            'transaction_id' => $validated['transaction_id'],
                            'transaction_type' => $validated['transaction_type'],
                            'user_id' => Auth::id()
                        ]
                    ], 403);
                }
                return back()->withErrors(['error' => 'Invalid or incomplete transaction']);
            }

            // Check if user already reviewed this seller
            $existingReview = SellerReview::where('reviewer_id', Auth::id())
                ->where('seller_code', $validated['seller_code'])
                ->first();

            if ($existingReview) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have already reviewed this seller'
                    ], 422);
                }
                return back()->withErrors(['error' => 'You have already reviewed this seller']);
            }

            // Create the review
            $review = SellerReview::create([
                'reviewer_id' => Auth::id(),
                'seller_code' => $validated['seller_code'],
                'rating' => $validated['rating'],
                'review' => $validated['review'],
                'is_verified_purchase' => $validated['transaction_type'] === 'order',
                'order_id' => $validated['transaction_type'] === 'order' ? $validated['transaction_id'] : null,
            ]);

            // Handle response based on request type
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'review' => $review,
                    'message' => 'Review submitted successfully'
                ]);
            }

            // For Inertia requests, redirect back with success flash message for toast
            return back()->with('success', 'Review submitted successfully');
            
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error submitting review: ' . $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Error submitting review: ' . $e->getMessage()]);
        }
    }

    /**
     * Update a review
     */
    public function update(Request $request, $id)
    {
        $review = SellerReview::where('id', $id)
            ->where('reviewer_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $validated['rating'],
            'review' => $validated['review'],
        ]);

        return response()->json([
            'success' => true,
            'review' => $review,
            'message' => 'Review updated successfully'
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy($id)
    {
        $review = SellerReview::where('id', $id)
            ->where('reviewer_id', Auth::id())
            ->firstOrFail();

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    /**
     * Get seller's average rating
     * 
     * Route: GET /seller-reviews/rating/{sellerCode}
     */
    public function getSellerRating($sellerCode)
    {
        $seller = User::where('seller_code', $sellerCode)->firstOrFail();
        
        // Fetch all reviews with their ratings to ensure we have complete data
        $reviews = SellerReview::where('seller_code', $sellerCode)->get();
        
        // Get the count of reviews for each rating value (1-5) using a more reliable method
        $ratingCounts = [];
        foreach ($reviews as $review) {
            $rating = (int)$review->rating;
            if (!isset($ratingCounts[$rating])) {
                $ratingCounts[$rating] = 0;
            }
            $ratingCounts[$rating]++;
        }
        
        // Calculate total number of reviews
        $totalReviews = count($reviews);
        
        // Calculate sum of (rating × count) for each rating
        $weightedSum = 0;
        foreach ($ratingCounts as $rating => $count) {
            // Ensure both rating and count are treated as numbers
            $numRating = (float)$rating;
            $numCount = (int)$count;
            $weightedSum += $numRating * $numCount;
        }
        
        // Calculate the average using the correct formula
        $avgRating = $totalReviews > 0 ? $weightedSum / $totalReviews : 0;
        
        // Debug information to help identify issues
        $debugInfo = [
            'rating_counts_raw' => $ratingCounts,
            'total_reviews' => $totalReviews,
            'weighted_sum' => $weightedSum,
            'calculation' => $totalReviews > 0 ? "$weightedSum / $totalReviews = " . ($weightedSum / $totalReviews) : "No reviews"
        ];
        
        $stats = [
            'avg_rating' => round($avgRating, 1),
            'total_reviews' => $totalReviews,
            'rating_counts' => $ratingCounts,
            'debug' => $debugInfo
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
            // Add seller information for debugging
            'seller' => [
                'code' => $seller->seller_code,
                'name' => $seller->first_name . ' ' . $seller->last_name
            ]
        ]);
    }
}
