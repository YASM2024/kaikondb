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
        $this->expandedPages = ExpandedPage::where('open', 1)->orderBy('seq', 'asc')->get()
            ->push((object) [
                'route_name' => 'developer',
                'title' => '開発・ヘルプ',
                'title_en' => 'Development & Help',
            ]);
    }

    public function render(): View
    {
        return view('kaikon::components.site-footer-view');
    }
}
