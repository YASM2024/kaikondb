<?php

namespace Kaikon2\Kaikondb\View\Components\Expanded;

use Illuminate\View\Component;
use Illuminate\View\View;

class FormPage extends Component
{
    public function __construct(
        public string $header = 'ページ管理',
        public string $actionType = 'create',
    ) {}

    public function render(): View
    {
        return view('kaikon::expanded.layout');
    }
}
