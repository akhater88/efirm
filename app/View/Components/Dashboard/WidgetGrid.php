<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;
use Illuminate\View\View;

class WidgetGrid extends Component
{
    public function render(): View
    {
        return view('components.dashboard.widget-grid');
    }
}
