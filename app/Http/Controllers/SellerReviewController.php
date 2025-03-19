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

            // Check if user already reviewed this seller
            $existingReview = SellerReview::where('reviewer_id', Auth::id())
                ->where('seller_code', $validated['seller_code'])
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this seller'
                ], 422);
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

            return response()->json([
                'success' => true,
                'review' => $review,
                'message' => 'Review submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting review: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
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
     */
    public function getSellerRating($sellerCode)
    {
        $seller = User::where('seller_code', $sellerCode)->firstOrFail();
        
        // Get the count of reviews for each rating value (1-5)
        $ratingCounts = SellerReview::where('seller_code', $sellerCode)
            ->select('rating', \DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();
        
        // Calculate total number of reviews
        $totalReviews = array_sum($ratingCounts);
        
        // Calculate sum of (rating × count) for each rating
        $weightedSum = 0;
        foreach ($ratingCounts as $rating => $count) {
            $weightedSum += $rating * $count;
        }
        
        // Calculate the average using the correct formula
        $avgRating = $totalReviews > 0 ? $weightedSum / $totalReviews : 0;
        
        $stats = [
            'avg_rating' => round($avgRating, 1),
            'total_reviews' => $totalReviews,
            'rating_counts' => $ratingCounts,
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
