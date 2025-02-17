<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class StatCard extends Component
{
  public $title;
  public $value;
  public $icon;
  public $color;

  public function __construct($title, $value, $icon, $color = 'blue')
  {
    $this->title = $title;
    $this->value = $value;
    $this->icon = $icon;
    $this->color = $color;
  }

  public function render()
  {
    return view('components.dashboard.stat-card');
  }
}
