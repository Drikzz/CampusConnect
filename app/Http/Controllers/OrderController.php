<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

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
        // Ensure the seller can only view their own orders
        if ($order->seller_code !== auth()->user()->seller_code) {
            abort(403);
        }

        $order->load(['buyer', 'items.product']);
        return view('seller.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Processing,Completed,Canceled'
        ]);

        if ($order->seller_code !== auth()->user()->seller_code) {
            abort(403);
        }

        $order->update($validated);
        return back()->with('success', 'Order status updated successfully');
    }

    public function cancelOrder(Order $order)
    {
        if ($order->buyer_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized action');
        }

        if ($order->status !== 'Pending') {
            return back()->with('error', 'Only pending orders can be cancelled');
        }

        $order->update(['status' => 'Cancelled']);
        return back()->with('success', 'Order cancelled successfully');
    }
}
