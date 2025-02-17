<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class ActionButton extends Component
{
  public $route;
  public $icon;
  public $text;

  public function __construct($route, $icon, $text)
  {
    $this->route = $route;
    $this->icon = $icon;
    $this->text = $text;
  }

  public function render()
  {
    return view('components.dashboard.action-button');
  }
}
