<?php

namespace Kaikon2\Kaikondb\View\Components\Photos;

use Illuminate\View\Component;
use Illuminate\View\View;

class Page extends Component
{
    public function __construct(
        public string $header = '',
    ) {}

    public function render(): View
    {
        return view('kaikon::photos.layout');
    }
}
