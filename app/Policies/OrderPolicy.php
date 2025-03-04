<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order)
    {
        // Both buyer and seller can view the order
        return $user->id === $order->buyer_id || $user->seller_code === $order->seller_code;
    }

    public function update(User $user, Order $order)
    {
        // Only buyer can update pending orders
        return $user->id === $order->buyer_id && $order->status === 'Pending';
    }

    public function cancel(User $user, Order $order)
    {
        // Only buyer can cancel pending orders
        return $user->id === $order->buyer_id && $order->status === 'Pending';
    }
}
