<h4 class="my-3 px-3 px-md-0">{{ $title }}</h4>
<noscript>
    <div class="container pt-3 py-2">
        <p class="text-danger fw-bold">検索機能を利用するには、ブラウザーで JavaScript を有効にしてください。</p>
    </div>
</noscript>
@if (!empty($intro))
    <div class="container pt-3 py-2">
        {!! $intro !!}
    </div>
@endif
