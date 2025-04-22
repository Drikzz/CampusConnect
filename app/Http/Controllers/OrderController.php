<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\MeetupLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $sellerCode = auth()->user()->seller_code;

        // Get counts
        $pendingCount = Order::where('seller_code', $sellerCode)
            ->where('status', 'Pending')
            ->count();

        $completedCount = Order::where('seller_code', $sellerCode)
            ->where('status', 'Completed')
            ->count();

        $canceledCount = Order::where('seller_code', $sellerCode)
            ->where('status', 'Canceled')
            ->count();

        // Get recent orders
        $recentOrders = Order::where('seller_code', $sellerCode)
            ->with('buyer')
            ->latest()
            ->paginate(10); // Using paginate instead of get()

        if (request()->ajax()) {
            return view('seller.orders.partials.orders-table', compact('recentOrders'))->render();
        }

        return view('seller.orders.index', compact(
            'pendingCount',
            'completedCount',
            'canceledCount',
            'recentOrders'
        ));
    }

    public function filter($status)
    {
        $sellerCode = auth()->user()->seller_code;

        $orders = Order::where('seller_code', $sellerCode)
            ->when($status !== 'all', function ($query) use ($status) {
                return $query->where('status', ucfirst($status));
            })
            ->with('buyer')
            ->latest()
            ->paginate(10);

        if (request()->wantsJson()) {
            return response()->json([
                'html' => view('seller.orders.partials.orders-table', ['recentOrders' => $orders])->render(),
                'pagination' => $orders->links()->toHtml()
            ]);
        }

        return view('seller.orders.partials.orders-table', ['recentOrders' => $orders])->render();
    }

    public function pending()
    {
        $pendingOrders = Order::where('seller_code', auth()->user()->seller_code)
            ->where('status', 'Pending')
            ->with(['buyer', 'items.product'])
            ->latest()
            ->paginate(10);

        return view('seller.orders.pending', compact('pendingOrders'));
    }

    public function completed()
    {
        $completedOrders = Order::where('seller_code', auth()->user()->seller_code)
            ->where('status', 'Completed')
            ->with(['buyer', 'items.product'])
            ->latest()
            ->paginate(10);

        return view('seller.orders.completed', compact('completedOrders'));
    }

    public function canceled()
    {
        $canceledOrders = Order::where('seller_code', auth()->user()->seller_code)
            ->where('status', 'Canceled')
            ->with(['buyer', 'items.product'])
            ->latest()
            ->paginate(10);

        return view('seller.orders.canceled', compact('canceledOrders'));
    }

    public function show(Order $order)
    {
        try {
            if (!Auth::user()->can('view', $order)) {
                abort(403);
            }

            $order->load([
                'items.product',
                'meetup_location',
                'buyer',
                'seller'
            ]);

            // Transform order data for frontend
            $orderData = [
                'id' => $order->id,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'total' => $order->total,
                'sub_total' => $order->sub_total,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'image_url' => $item->product->image_url,
                        ],
                    ];
                }),
                'meetup_location' => $order->meetup_location ? [
                    'id' => $order->meetup_location->id,
                    'name' => $order->meetup_location->name,
                    'full_name' => $order->meetup_location->full_name,
                    'description' => $order->meetup_location->description,
                ] : null,
                'meetup_schedule' => $order->meetup_schedule,
                'meetup_notes' => $order->meetup_notes,
                'seller' => [
                    'id' => $order->seller->id,
                    'name' => $order->seller->name,
                    'email' => $order->seller->email,
                ],
                'buyer' => [
                    'id' => $order->buyer->id,
                    'name' => $order->buyer->name,
                    'email' => $order->buyer->email,
                ],
                'can_edit' => $order->status === 'Pending' && Auth::id() === $order->buyer_id,
            ];

            return Inertia::render('Dashboard/UserOrderDetails', [
                'order' => $orderData,
                'user' => Auth::user(),
            ]);
        } catch (\Exception $e) {
            report($e); // Log the error
            return back()->with('error', 'Unable to load order details.');
        }
    }

    public function update(Request $request, Order $order)
    {
        // Ensure user is the buyer and can update the order
        if (!Auth::user()->can('update', $order)) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow updates if order is pending
        if ($order->status !== 'Pending') {
            return back()->with('error', 'Only pending orders can be edited.');
        }

        $validated = $request->validate([
            'address' => 'required|string',
            'phone' => 'required|string',
            'meetup_location_id' => 'nullable|exists:meetup_locations,id',
            'meetup_schedule' => 'nullable|date',
            'meetup_notes' => 'nullable|string',
        ]);

        $order->update($validated);
        return back()->with('success', 'Order details updated successfully.');
    }

    public function cancel(Request $request, Order $order)
    {
        if (!Auth::user()->can('cancel', $order)) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($order->status, ['Pending', 'Accepted'])) {
            return back()->with('error', 'This order cannot be cancelled.');
        }

        // Update this part to pass the data to the model's cancel method
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        // Use the model's cancel method
        $order->cancel($validated['cancellation_reason'], Auth::id());

        return back()->with('success', 'Order cancelled successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:cash,gcash',
            'meetup_schedule' => 'required|string', // Format: "locationId_dayNumber"
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $buyer = Auth::user();
        $seller = $product->user;

        // Create the order
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_code' => $seller->seller_code,
            'status' => Order::STATUS_PENDING,
            'payment_method' => $validated['payment_method'],
            'meetup_schedule' => $validated['meetup_schedule'],
        ]);

        // Create order items
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'price' => $product->price,
        ]);

        // Update product stock
        $product->decrement('stock', $validated['quantity']);

        return redirect()->route('orders.show', $order->id)->with('success', 'Order placed successfully.');
    }
}
