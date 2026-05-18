<?php

namespace Kaikon2\Kaikondb\View\Components\Masters;

use Illuminate\View\Component;
use Illuminate\View\View;

class Page extends Component
{
    public function __construct(
        public string $header = '',
        public string $script = '',
        public ?string $scriptVersion = null,
    ) {}

    public function render(): View
    {
        return view('kaikon::components.masters.page');
    }
}
