<x-kaikon::photos.page :header="__('messages.Photos')">

    <div id="component-search-photos">
        <div class="container mt-4 py-2">
            @include('kaikon::photos.partials.header-section', [
                'title' => __('messages.Photos'),
                'intro' => '県内で撮影された昆虫写真を掲載・検索できます。',
                'copyrightNotice' => '※著作権は撮影者に帰属します。無断転用はお控えください。',
            ])

            @include('kaikon::photos.partials.search-tabs', [
                'photographers' => $photographers,
                'data' => $data,
            ])
        </div>
    </div>

    @include('kaikon::photos.partials.results-area')

    @include('kaikon::photos.partials.modals-index')

</x-kaikon::photos.page>
