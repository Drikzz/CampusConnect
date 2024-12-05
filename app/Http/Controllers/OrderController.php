<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('seller_id', auth()->id())
            ->latest()
            ->paginate(10);
        return view('components.sellerLayout', compact('orders'));
    }

    public function pending()
    {
        $orders = Order::where('seller_id', auth()->id())
            ->where('status', 'Pending')
            ->latest()
            ->paginate(10);
        return view('seller.orders.index', compact('orders'));
    }

    public function processing()
    {
        $orders = Order::where('seller_id', auth()->id())
            ->where('status', 'Processing')
            ->latest()
            ->paginate(10);
        return view('seller.orders.index', compact('orders'));
    }

    public function completed()
    {
        $orders = Order::where('seller_id', auth()->id())
            ->where('status', 'Completed')
            ->latest()
            ->paginate(10);
        return view('seller.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        return view('seller.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $order->update(['status' => $request->status]);
        return back()->with('success', 'Order status updated successfully');
    }
}
