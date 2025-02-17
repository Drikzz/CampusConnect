<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;
use App\Models\Order;

class OrderItem extends Component
{
  public $order;

  public function __construct(Order $order)
  {
    $this->order = $order;
  }

  public function render()
  {
    return view('components.dashboard.order-item');
  }
}
