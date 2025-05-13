<x-kaikon::app-layout>
    @slot('header')
    運営情報管理
    @endslot
    <style>
    .row:nth-child(even) {
        background-color: #dee2e6; /* Bootstrapの薄いグレーより濃いめ */
    }

    .row:nth-child(odd) {
        background-color: #f8f9fa; /* Bootstrap標準の薄いグレー */
    }

    </style>
    <div class="container py-2">
        <div class="text-left bg-light p-3 p-sm-5 mb-4 rounded">
            <h2 class="mb-4">運営情報管理</h2>
            <div class="mb-4">
                <span class="h5"></span>
                <div class="row">
                    <div class="col-1 p-2 fw-bold">#</div>
                    <div class="col-4 col-sm-2 p-2 fw-bold">ページ名</div>
                    <div class="col-7 p-2 fw-bold">ページ本文</div>
                    <div class="col p-2 fw-bold">公開</div>
                </div>
                @foreach($expanded_pages as $page)
                <div class="row">
                    <div class="col-1 p-2">
                        {{ $page->seq }}
                    </div>
                    <div class="col-4 col-sm-2 p-2">
                        <a href="{{ route('expanded_page.showEdit', ['route_name' => $page->route_name]) }}">{{ $page->title }}</a>
                    </div>
                    <div class="col-7 p-2">
                        {{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($page->body))), 80, '...') }}
                    </div>
                    <div class="col p-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch"
                                name="open" id="switchOpen_{{ $page->id }}"
                                @if ($page->open) checked @endif>
                            <label class="form-check-label cursor-pointer" for="switchOpen_{{ $page->id }}">
                                {{ $page->open ? 'ON' : 'OFF' }}
                            </label>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="btn btn-secondary btn-sm">追加</div>
        </div>
    </div>
    @slot('scripts')
    <script>
    </script>
    @endslot
</x-kaikon::app-layout>