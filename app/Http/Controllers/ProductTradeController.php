<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TradeTransaction;
use App\Models\TradeOfferedItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductTradeController extends Controller
{
    public function index(Request $request)
    {
        // Start with a query for tradable products only
        $query = Product::where('is_tradable', true)
                        ->where('status', 'Active');
        
        // Apply category filter
        if ($request->filled('category') && $request->category !== 'all') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('name', $request->category);
            });
        }
        
        // Apply price filters
        if ($request->filled('price.min')) {
            $query->where('price', '>=', $request->input('price.min'));
        }
        
        if ($request->filled('price.max')) {
            $query->where('price', '<=', $request->input('price.max'));
        }
        
        // Apply matching type logic if needed
        if ($request->filled('matchingType')) {
            // This logic would depend on your specific implementation
            // of 'any' vs 'all' matching
        }
        
        // Get the tradable products with pagination
        $products = $query->with(['category', 'seller'])
                         ->paginate(9)
                         ->withQueryString();
        
        return Inertia::render('Products/Trade', [
            'products' => $products,
            'filters' => $request->all()
        ]);
    }
    
    /**
     * Submit a trade offer
     */
    public function submitTradeOffer(Request $request)
    {
        Log::info('Trade offer submission started', [
            'user_id' => Auth::id(),
            'data' => $request->except('offered_items.*.images')
        ]);
        
        try {
            $validated = $request->validate([
                'seller_product_id' => 'required|exists:products,id',
                'additional_cash' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
                'offered_items' => 'required|array|min:1',
                'offered_items.*.name' => 'required|string|max:255',
                'offered_items.*.quantity' => 'required|integer|min:1',
                'offered_items.*.estimated_value' => 'required|numeric|min:0',
                'offered_items.*.description' => 'nullable|string|max:1000',
                'offered_items.*.images' => 'nullable|array',
                'offered_items.*.images.*' => 'nullable|image|max:2048',
            ]);
            
            // Get the product with seller information
            $product = Product::findOrFail($validated['seller_product_id']);
            
            // Get seller_code from product
            $sellerCode = $product->seller_code;
            
            if (empty($sellerCode)) {
                Log::error('Product has no seller_code', [
                    'product_id' => $product->id
                ]);
                
                return redirect()->back()
                    ->with('error', 'Cannot identify the seller of this product.')
                    ->withInput();
            }
            
            // Find seller_id from seller_code
            $seller = User::where('seller_code', $sellerCode)->first();
            
            if (!$seller) {
                Log::error('No seller found with seller_code', [
                    'seller_code' => $sellerCode,
                    'product_id' => $product->id
                ]);
                
                return redirect()->back()
                    ->with('error', 'Cannot find the seller for this product.')
                    ->withInput();
            }
            
            Log::info('Found seller for trade transaction', [
                'seller_id' => $seller->id,
                'seller_code' => $sellerCode
            ]);
            
            // Now start the transaction
            DB::beginTransaction();
            
            try {
                // Create trade transaction
                $tradeTransaction = TradeTransaction::create([
                    'buyer_id' => Auth::id(),
                    'seller_id' => $seller->id,
                    'seller_code' => $sellerCode, // Store both for redundancy
                    'seller_product_id' => $product->id,
                    'additional_cash' => $validated['additional_cash'],
                    'notes' => $validated['notes'],
                    'status' => 'pending'
                ]);
                
                Log::info('Created trade transaction', [
                    'trade_id' => $tradeTransaction->id
                ]);
                
                // Process each offered item
                foreach ($validated['offered_items'] as $itemData) {
                    $images = [];
                    
                    // Process images if they exist
                    if (!empty($itemData['images'])) {
                        foreach ($itemData['images'] as $image) {
                            if ($image && !is_string($image)) {
                                $path = $image->store('trade_images', 'public');
                                $images[] = $path; 
                            }
                        }
                    }
                    
                    // Create offered item record
                    $offeredItem = TradeOfferedItem::create([
                        'trade_transaction_id' => $tradeTransaction->id,
                        'name' => $itemData['name'],
                        'quantity' => $itemData['quantity'],
                        'estimated_value' => $itemData['estimated_value'],
                        'description' => $itemData['description'] ?? null,
                        'images' => !empty($images) ? json_encode($images) : null,
                    ]);
                    
                    Log::info('Created offered item', [
                        'item_id' => $offeredItem->id,
                        'name' => $offeredItem->name
                    ]);
                }
                
                DB::commit();
                
                // Send notification to seller here (if implemented)
                
                return redirect()->back()->with([
                    'success' => 'Trade offer submitted successfully! The seller will be notified.',
                    'trade_id' => $tradeTransaction->id
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e; // Re-throw for the outer catch
            }
            
        } catch (\Exception $e) {
            Log::error('Trade offer submission error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with([
                'error' => 'Failed to submit trade offer: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Cancel a trade offer
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function cancelTrade($id)
    {
        try {
            // Find the trade
            $trade = TradeTransaction::findOrFail($id);
            
            // Check authorization - only the buyer who made the offer can cancel it
            if ($trade->buyer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to cancel this trade offer'
                ], 403);
            }
            
            // Check if the trade is in a cancellable state (only pending trades can be cancelled)
            if ($trade->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending trade offers can be cancelled'
                ], 400);
            }
            
            // Update the trade status to 'canceled'
            $trade->status = 'canceled';
            $trade->save();
            
            Log::info('Trade offer cancelled', [
                'trade_id' => $trade->id,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Trade offer cancelled successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling trade offer: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'trade_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel trade offer: ' . $e->getMessage()
            ], 500);
        }
    }
}
