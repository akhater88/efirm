<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;
use Illuminate\View\View;

class WidgetCard extends Component
{
    public function __construct(
        public string $title = '',
        public string $icon = '',
        public string $viewAllUrl = '',
        public string $viewAllLabel = '',
        public string $createUrl = '',
        public string $createLabel = '',
        public string $state = 'default',
        public string $emptyMessage = '',
        public string $errorMessage = '',
    ) {}

    public function render(): View
    {
        return view('components.dashboard.widget-card');
    }
}
