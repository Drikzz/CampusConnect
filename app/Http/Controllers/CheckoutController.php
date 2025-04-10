<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\MeetupLocation;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function summary($id)
    {
        $product = Product::with(['seller.meetupLocations.location', 'images'])
            ->findOrFail($id);

        if (!$product->is_buyable) {
            return redirect()->back()->with('error', 'This product is not available for purchase.');
        }

        // Transform the product after accessing relationships
        $transformedProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'discounted_price' => $product->discounted_price,
            'discount' => $product->discount,
            'stock' => $product->stock,
            'description' => $product->description,
            'images' => array_map(function($image) {
                return asset('storage/' . $image);
            }, $product->images),
            'seller' => [
                'id' => $product->seller->id,
                'first_name' => $product->seller->first_name,
                'profile_picture' => $product->seller->profile_picture,
                'location' => $product->seller->location
            ]
        ];

        return Inertia::render('Products/Checkout', [
            'product' => $transformedProduct,
            'user' => Auth::user(),
            'meetupLocations' => $product->seller->meetupLocations()->with('location')->where('is_active', true)->get()
        ]);
    }

    public function checkout(Request $request)
    {
        try {
            $request->validate([
                'product_id' => ['required', 'numeric'],
                'sub_total' => ['required', 'numeric'],
                'email' => ['nullable', 'email'],
                'phone' => ['required', 'string'],
                'quantity' => ['required', 'numeric'],
                'payment_method' => ['required', 'string', 'in:cash,gcash'],
                'meetup_schedule' => ['required', 'string'], // Format: "locationId_dayNumber"
            ]);

            $product = Product::findOrFail($request->product_id);

            // Check if product is buyable
            if (!$product->is_buyable) {
                return back()->with('error', 'This product is not available for purchase.');
            }

            // Check if there's enough stock
            if ($product->stock < $request->quantity) {
                return back()->with('error', 'Not enough stock available.');
            }

            // Parse the meetup schedule
            [$locationId, $dayNumber] = explode('_', $request->meetup_schedule);
            $meetupLocation = MeetupLocation::findOrFail($locationId);

            // Create the order
            $order = Order::create([
                'buyer_id' => Auth::id(),
                'seller_code' => $product->seller_code,
                'phone' => $request->phone,
                'email' => $request->email,
                'sub_total' => $request->sub_total,
                'status' => 'Pending',
                'payment_method' => $request->payment_method,
                'meetup_location_id' => $locationId,
                'meetup_day' => $dayNumber,
                'meetup_time_from' => $meetupLocation->available_from,
                'meetup_time_until' => $meetupLocation->available_until,
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->discounted_price,
                'subtotal' => $request->sub_total,
                'seller_code' => $product->seller_code,
            ]);

            // Update stock
            $product->decrement('stock', $request->quantity);

            return redirect()->route('dashboard.orders')->with('success', 'Order placed successfully');
        } catch (\Exception $e) {
            \Log::error('Order placement failed:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to place order. Please try again.');
        }
    }
}
