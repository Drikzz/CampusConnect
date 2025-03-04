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
    //
    public function summary($id)
    {
        $product = Product::with(['seller.meetupLocations', 'images', 'variants'])
            ->findOrFail($id);
    
        if (!$product->is_buyable) {
            return redirect()->back()->with('error', 'This product is not available for purchase.');
        }
        // return view('products.order_sum', compact('product'));
        return Inertia::render('Products/Checkout', [
            'product' => $product,
            'user' => Auth::user(),
            'meetupLocations' => $product->seller->meetupLocations()->where('is_active', true)->get()
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'numeric'],
            'sub_total' => ['required', 'numeric'], // Fix validation field name
            'delivery_estimate' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['required', 'string'],
            'quantity' => ['required', 'numeric'],
            'address' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'payment_method' => ['required', 'string', 'in:cash,gcash'],
            'meetup_schedule' => ['required', 'string'], // Format: "locationId_dayNumber"
        ]);

        $product = Product::find($request->product_id);

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
            'buyer_id' => Auth::user()->id,
            'seller_code' => $product->seller_code, // Use product's seller_code directly
            'address' => "{$request->address}, {$request->city}, {$request->postal_code}",
            'delivery_estimate' => $request->delivery_estimate,
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
        // } catch (\Exception $e) {
        //     return back()->with('error', 'Failed to place order. Please try again.');
        // }
    }
}
