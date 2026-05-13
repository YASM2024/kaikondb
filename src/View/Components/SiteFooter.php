<?php

namespace Kaikon2\Kaikondb\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Kaikon2\Kaikondb\Models\ExpandedPage;

class SiteFooter extends Component
{
    /**
     * @var \Illuminate\Support\Collection<int, \Kaikon2\Kaikondb\Models\ExpandedPage>
     */
    public $expandedPages;

    public function __construct()
    {
        $this->expandedPages = ExpandedPage::where('open', 1)->orderBy('seq', 'asc')->get();
    }

    public function render(): View
    {
        return view('kaikon::components.site-footer-view');
    }
}
