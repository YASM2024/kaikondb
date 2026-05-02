<x-kaikon::app-layout>
    @slot('header')
    ページ管理
    @endslot
    <style>
    .row-zebra:nth-child(even) {
        background-color: #dee2e6; /* Bootstrapの薄いグレーより濃いめ */
    }

    .row-zebra:nth-child(odd) {
        background-color: #f8f9fa; /* Bootstrap標準の薄いグレー */
    }

    </style>
    <div class="container py-2">
        <div class="text-left bg-light p-3 p-sm-5 mb-4 rounded">
            <div class="mb-4">
                <div class="h2">拡張ページ管理</div>
            </div>
            <div class="mb-4">
                <div class="h5 mb-2">
                    <div class="mb-2">カテゴリ名</div>
                    <input type="text" id="expanded-page" class="form-control form-sm" style="width:12em;" value="{{__('settings.ExpandedArea')}}" disabled>
                </div>
                <small>※カテゴリ名は/config/kaikon.phpで設定・変更できます。</small>
            </div>
            <div class="mb-4">
                <div class="h5 mb-2 d-flex justify-content-between align-items-center">
                    <div>ページ一覧</div>
                    <div>
                        <a href="{{ route('expanded_page.showCreate') }}" class="btn btn-secondary btn-sm">追加</a>
                    </div>
                </div>
                <div class="row row-zebra">
                    <div class="col-1 p-2 fw-bold">#</div>
                    <div class="col-3 p-2 fw-bold">ページ名</div>
                    <div class="col-6 p-2 fw-bold">ページ本文</div>
                    <div class="col-1 p-2 fw-bold text-center">表示</div>
                    <div class="col-1 p-2 fw-bold text-center">公開</div>
                </div>
                @foreach($expanded_pages as $page)
                <div class="row row-zebra">
                    <div class="col-1 p-2">
                        {{ $page->seq }}
                    </div>
                    <div class="col-3 p-2">
                        {{ $page->title }}
                        <a href="{{ route('expanded_page.showEdit', ['route_name' => $page->route_name]) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-decoration-none"
                            title="編集"
                            aria-label="編集">
                            <i class="bi bi-pencil-square text-primary"></i>
                        </a>
                    </div>
                    <div class="col-6 p-2">
                        {{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($page->body))), 80, '...') }}
                    </div>

                    <div class="col-1 p-2 text-center" style="color: #0F766E; ">
                        <a href="{{ route('expanded_page.preview', ['route_name' => $page->route_name]) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-decoration-none"
                            title="表示確認"
                            aria-label="表示確認">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>

                    <div class="col-1 p-2 text-center">
                        <button
                            type="button"
                            class="btn p-0 border-0 bg-transparent toggle-open-btn"
                            data-page-id="{{ $page->id }}"
                            data-open="{{ $page->open ? 1 : 0 }}"
                            aria-label="公開状態切り替え"
                        >
                            <i class="bi {{ $page->open ? 'bi-eye' : 'bi-eye-slash' }} {{ $page->open ? 'text-success' : 'text-muted' }}"></i>
                        </button>
                    </div>

                </div>
                @endforeach
            </div>
        </div>
    </div>
    @slot('scripts')
    <script>

        function updateEyeIcon(button, isOpen) {
            const icon = button.querySelector('i');
            if (!icon) return;

            icon.classList.toggle('bi-eye', isOpen);
            icon.classList.toggle('bi-eye-slash', !isOpen);
            icon.classList.toggle('text-success', isOpen);
            icon.classList.toggle('text-muted', !isOpen);
        }

        document.querySelectorAll('.toggle-open-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const button = e.currentTarget;
                const pageId = button.dataset.pageId;
                const currentOpen = button.dataset.open === '1';
                const newOpen = !currentOpen;

                button.dataset.open = newOpen ? '1' : '0';
                updateEyeIcon(button, newOpen);

                try {
                    const response = await fetch('{{ route("expanded_page.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            id: pageId,
                            open: newOpen ? '1' : '0'
                        })
                    });

                    if (response.ok) {
                        location.reload();
                    } else {
                        throw new Error('サーバーエラーが発生しました');
                    }
                } catch (error) {
                    alert('変更を適用できませんでした: ' + error.message);
                    button.dataset.open = currentOpen ? '1' : '0';
                    updateEyeIcon(button, currentOpen);
                }
            });
        });

    </script>
    @endslot
</x-kaikon::app-layout>